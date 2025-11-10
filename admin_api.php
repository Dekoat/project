<?php
/**
 * Admin API Backend
 * Handle CRUD operations for admin panel
 */

include "db.php";
header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';
$response = ['success' => false, 'message' => '', 'items' => []];

switch($action) {
    // ===== STATISTICS =====
    case 'stats':
        $stats = [];
        
        // Total chats
        $res = $conn->query("SELECT COUNT(*) as count FROM chat_logs");
        $stats['total_chats'] = $res->fetch_assoc()['count'];
        
        // Total FAQ
        $res = $conn->query("SELECT COUNT(*) as count FROM faq WHERE is_active=1");
        $stats['total_faq'] = $res->fetch_assoc()['count'];
        
        // Total News
        $res = $conn->query("SELECT COUNT(*) as count FROM news WHERE is_active=1");
        $stats['total_news'] = $res->fetch_assoc()['count'];
        
        // Total Staff
        $res = $conn->query("SELECT COUNT(*) as count FROM staff WHERE is_active=1");
        $stats['total_staff'] = $res->fetch_assoc()['count'];
        
        // Popular questions
        $res = $conn->query("SELECT user_message, response_type, COUNT(*) as frequency 
                           FROM chat_logs 
                           WHERE LENGTH(user_message) > 3
                           GROUP BY user_message, response_type 
                           ORDER BY frequency DESC 
                           LIMIT 10");
        $popular = [];
        while($row = $res->fetch_assoc()) $popular[] = $row;
        $stats['popular_questions'] = $popular;
        
        echo json_encode($stats, JSON_UNESCAPED_UNICODE);
        exit;
    
    // ===== FAQ OPERATIONS =====
    case 'list_faq':
        $res = $conn->query("SELECT * FROM faq ORDER BY priority DESC, id DESC");
        while($row = $res->fetch_assoc()) $response['items'][] = $row;
        $response['success'] = true;
        break;
    
    case 'add_faq':
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $conn->prepare("INSERT INTO faq (question, answer, category) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $data['question'], $data['answer'], $data['category']);
        if($stmt->execute()) {
            $faq_id = $stmt->insert_id;
            
            // Add keywords
            if(!empty($data['keywords'])) {
                $keywords = explode(',', $data['keywords']);
                $stmt2 = $conn->prepare("INSERT INTO faq_keyword (faq_id, keyword) VALUES (?, ?)");
                foreach($keywords as $kw) {
                    $kw = trim($kw);
                    if($kw) {
                        $stmt2->bind_param("is", $faq_id, $kw);
                        $stmt2->execute();
                    }
                }
                $stmt2->close();
            }
            
            $response['success'] = true;
            $response['message'] = 'เพิ่ม FAQ สำเร็จ';
        } else {
            $response['message'] = 'เกิดข้อผิดพลาด: ' . $conn->error;
        }
        $stmt->close();
        break;
    
    case 'delete_faq':
        $id = intval($_GET['id']);
        if($conn->query("DELETE FROM faq WHERE id=$id")) {
            $response['success'] = true;
            $response['message'] = 'ลบ FAQ สำเร็จ';
        }
        break;
    
    // ===== NEWS OPERATIONS =====
    case 'list_news':
        $res = $conn->query("SELECT * FROM news ORDER BY date_post DESC LIMIT 50");
        while($row = $res->fetch_assoc()) $response['items'][] = $row;
        $response['success'] = true;
        break;
    
    case 'add_news':
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $conn->prepare("INSERT INTO news (title, summary, url, date_post, source) VALUES (?, ?, ?, ?, 'manual')");
        $stmt->bind_param("ssss", $data['title'], $data['summary'], $data['url'], $data['date']);
        if($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'เพิ่มข่าวสำเร็จ';
        } else {
            $response['message'] = 'เกิดข้อผิดพลาด: ' . $conn->error;
        }
        $stmt->close();
        break;
    
    case 'delete_news':
        $id = intval($_GET['id']);
        if($conn->query("DELETE FROM news WHERE id=$id")) {
            $response['success'] = true;
            $response['message'] = 'ลบข่าวสำเร็จ';
        }
        break;
    
    // ===== STAFF OPERATIONS =====
    case 'list_staff':
        $res = $conn->query("SELECT * FROM staff ORDER BY department, fullname");
        while($row = $res->fetch_assoc()) $response['items'][] = $row;
        $response['success'] = true;
        break;
    
    case 'add_staff':
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $conn->prepare("INSERT INTO staff (fullname, title, department, role, email, phone) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $data['fullname'], $data['title'], $data['department'], $data['role'], $data['email'], $data['phone']);
        if($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'เพิ่มบุคลากรสำเร็จ';
        } else {
            $response['message'] = 'เกิดข้อผิดพลาด: ' . $conn->error;
        }
        $stmt->close();
        break;
    
    case 'delete_staff':
        $id = intval($_GET['id']);
        if($conn->query("DELETE FROM staff WHERE id=$id")) {
            $response['success'] = true;
            $response['message'] = 'ลบบุคลากรสำเร็จ';
        }
        break;
    
    // ===== CHAT LOGS =====
    case 'logs':
        $filter = $_GET['filter'] ?? 'all';
        $where = $filter === 'all' ? '' : "WHERE response_type='$filter'";
        $res = $conn->query("SELECT * FROM chat_logs $where ORDER BY date_created DESC LIMIT 100");
        while($row = $res->fetch_assoc()) $response['items'][] = $row;
        $response['success'] = true;
        break;
    
    default:
        $response['message'] = 'Invalid action';
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
