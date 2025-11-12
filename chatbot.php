<?php
/**
 * EngBot - Intelligent Chatbot for RMUTP Engineering Faculty
 * Features:
 * - NLP-based keyword detection
 * - Real-time data sync from faculty website
 * - Multi-category FAQ system
 * - Conversation logging and analytics
 * - Hybrid response with direct links
 */

// Start session FIRST (before any output)
session_start();

include "db.php";
header('Content-Type: application/json; charset=utf-8');
if (!isset($_SESSION['chatbot_session'])) {
    $_SESSION['chatbot_session'] = uniqid('chat_', true);
}
$session_id = $_SESSION['chatbot_session'];
$user_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

$raw = trim($_GET['msg'] ?? '');
$msg = mb_strtolower($raw, 'UTF-8');
$response = ['type'=>'text','text'=>'ขออภัย ไม่พบข้อมูลที่ต้องการ กรุณาลองคำอื่นหรือถามว่า "ข่าว" / "อาจารย์" / "ติดต่อ" / "ทุน"'];
$matched_keywords = [];

if($msg === '') {
  echo json_encode($response); exit;
}

// Function to log chat
function logChat($conn, $user_msg, $bot_resp, $type, $keywords, $session, $ip) {
  // ปิดการบันทึก Log ชั่วคราว (ถ้ายังไม่ Import database.sql)
  try {
    $stmt = $conn->prepare("INSERT INTO chat_logs (user_message, bot_response, response_type, matched_keywords, session_id, user_ip) VALUES (?, ?, ?, ?, ?, ?)");
    if($stmt) {
      $kw = json_encode($keywords, JSON_UNESCAPED_UNICODE);
      $resp = json_encode($bot_resp, JSON_UNESCAPED_UNICODE);
      $stmt->bind_param("ssssss", $user_msg, $resp, $type, $kw, $session, $ip);
      $stmt->execute();
      $stmt->close();
    }
  } catch(Exception $e) {
    // ถ้าตาราง chat_logs ยังไม่มี ก็ข้ามไป
    error_log("Chat log error: " . $e->getMessage());
  }
}


// ===== 1. NEWS INTENT =====
if(preg_match('/(ข่าว|ประกาศ|ประชาสัมพันธ์|กิจกรรม|อีเว้นท์|event)/u', $msg, $matches)){
  $matched_keywords[] = $matches[0];
  $sql = "SELECT id,title,summary,url, DATE_FORMAT(date_post,'%d/%m/%Y') as date_post, source
    FROM news
    WHERE is_active=1
    ORDER BY date_post DESC, id DESC
    LIMIT 6";
  $res = $conn->query($sql);
  $items = [];
  while($r = $res->fetch_assoc()) $items[] = $r;
  if($items){
    $response = ['type'=>'news','items'=>$items];
    logChat($conn, $raw, $response, 'news', $matched_keywords, $session_id, $user_ip);
    echo json_encode($response, JSON_UNESCAPED_UNICODE); exit;
  }
}

// ===== 2. STAFF/PERSONNEL INTENT =====
if(preg_match('/(อาจารย์|ครู|หัวหน้า|บุคลากร|ผู้สอน|คณาจารย์|staff|teacher)/u', $msg, $matches)){
  $matched_keywords[] = $matches[0];
  
  // ถ้าระบุสาขาเฉพาะ
  $dept_keywords = [
    'คอม' => 'คอมพิวเตอร์',
    'computer' => 'คอมพิวเตอร์',
    'ไฟฟ้า' => 'ไฟฟ้า',
    'electrical' => 'ไฟฟ้า',
    'โยธา' => 'โยธา',
    'civil' => 'โยธา',
    'อุตสาหการ' => 'อุตสาหการ',
    'industrial' => 'อุตสาหการ',
    'เครื่องกล' => 'เครื่องกล',
    'mechanical' => 'เครื่องกล',
    'อิเล็กทรอนิกส์' => 'อิเล็กทรอนิกส์',
    'electronic' => 'อิเล็กทรอนิกส์'
  ];
  
  $dept_filter = '';
  foreach($dept_keywords as $kw => $dept) {
    if(mb_strpos($msg, $kw) !== false) {
      $dept_filter = " AND department LIKE '%" . $conn->real_escape_string($dept) . "%'";
      $matched_keywords[] = $kw;
      break;
    }
  }
  
  // ถ้าค้นหาชื่อเฉพาะ
  if(preg_match('/ชื่อ\s*([ก-๙a-z\s]+)/u', $msg, $name_match)) {
    $name = trim($name_match[1]);
    $matched_keywords[] = $name;
    $sql = "SELECT id, fullname, title, department, email, phone, office
            FROM staff
            WHERE is_active=1 AND (
              LOWER(fullname) LIKE LOWER('%" . $conn->real_escape_string($name) . "%')
            )
            ORDER BY department, role DESC
            LIMIT 10";
  } else {
    // แสดงหัวหน้าสาขาหรือทั้งหมด
    $sql = "SELECT id, fullname, title, department, email, phone, office, role
            FROM staff
            WHERE is_active=1 $dept_filter
            ORDER BY 
              CASE WHEN role LIKE '%หัวหน้า%' THEN 1 ELSE 2 END,
              department, fullname
            LIMIT 15";
  }
  
  $res = $conn->query($sql);
  $items = [];
  while($r = $res->fetch_assoc()) $items[] = $r;
  
  if($items) {
    $response = ['type'=>'staff','items'=>$items];
    logChat($conn, $raw, $response, 'staff', $matched_keywords, $session_id, $user_ip);
    echo json_encode($response, JSON_UNESCAPED_UNICODE); exit;
  }
}

// ===== 3. CONTACT INFO INTENT =====
if(preg_match('/(ติดต่อ|เบอร์|โทร|email|ที่อยู่|แผนที่|map|location|facebook|line)/u', $msg, $matches)){
  $matched_keywords[] = $matches[0];
  $sql = "SELECT info_key, info_value, display_name, icon 
          FROM contact_info WHERE is_active=1 ORDER BY sort_order";
  $res = $conn->query($sql);
  $contacts = [];
  while($r = $res->fetch_assoc()) {
    $contacts[] = $r;
  }
  
  if($contacts) {
    $text = "📋 ข้อมูลติดต่อคณะวิศวกรรมศาสตร์ RMUTP\n\n";
    foreach($contacts as $c) {
      $text .= $c['icon'] . " " . $c['display_name'] . ": " . $c['info_value'] . "\n";
    }
    $response = ['type'=>'contact', 'text'=>$text, 'items'=>$contacts];
    logChat($conn, $raw, $response, 'contact', $matched_keywords, $session_id, $user_ip);
    echo json_encode($response, JSON_UNESCAPED_UNICODE); exit;
  }
}

// ===== 4. SCHOLARSHIP INTENT =====
if(preg_match('/(ทุน|ทุนการศึกษา|scholarship|เรียนฟรี|ช่วยเหลือ)/u', $msg, $matches)){
  $matched_keywords[] = $matches[0];
  // ค้นหา FAQ เกี่ยวกับทุน
  $sql = "SELECT answer FROM faq WHERE is_active=1 AND (
            question LIKE '%ทุน%' OR category='ทุนการศึกษา'
          ) ORDER BY priority DESC LIMIT 1";
  $res = $conn->query($sql);
  if($res && $row = $res->fetch_assoc()){
    $response = ['type'=>'text','text'=>$row['answer']];
    logChat($conn, $raw, $response, 'faq', $matched_keywords, $session_id, $user_ip);
    echo json_encode($response, JSON_UNESCAPED_UNICODE); exit;
  }
}

// ===== 5. FAQ SEARCH (Keyword & Full-text) =====
// ลองค้นหาจาก keyword table ก่อน
$kw = $conn->real_escape_string($msg);
$sql = "SELECT DISTINCT f.id, f.question, f.answer, f.category, COUNT(k.id) as match_score
        FROM faq f
        LEFT JOIN faq_keyword k ON f.id = k.faq_id
        WHERE f.is_active=1 AND (
          k.keyword LIKE '%$kw%' OR
          LOWER(f.question) LIKE LOWER('%$kw%')
        )
        GROUP BY f.id
        ORDER BY match_score DESC, f.priority DESC
        LIMIT 1";
$res = $conn->query($sql);
if($res && $row = $res->fetch_assoc()){
  $matched_keywords[] = 'faq_match';
  $response = ['type'=>'text','text'=>$row['answer'], 'category'=>$row['category']];
  logChat($conn, $raw, $response, 'faq', $matched_keywords, $session_id, $user_ip);
  echo json_encode($response, JSON_UNESCAPED_UNICODE); exit;
}

// ===== 6. FALLBACK - LOG UNANSWERED =====
logChat($conn, $raw, $response, 'no_match', $matched_keywords, $session_id, $user_ip);
echo json_encode($response, JSON_UNESCAPED_UNICODE);

