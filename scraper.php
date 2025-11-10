<?php
/**
 * Web Scraper for RMUTP Engineering Faculty
 * Auto-sync news and personnel data from official website
 * 
 * ⚠️ Note: ตัวอย่างนี้เป็น Template - ต้องปรับ URL และ selector ให้ตรงกับเว็บจริง
 * สำหรับการทำงานจริง ควรใช้ร่วมกับ Cron Job หรือ Task Scheduler
 */

include "db.php";
header('Content-Type: application/json; charset=utf-8');

// รับ action จาก parameter
$action = $_GET['action'] ?? 'news';
$result = ['success' => false, 'message' => '', 'data' => []];

/**
 * ฟังก์ชัน Scrape ข่าวจากเว็บคณะ
 */
function scrapeNews($conn) {
    $url = "https://eng.rmutp.ac.th/"; // ⚠️ ปรับ URL ให้ตรงกับเว็บจริง
    $result = ['items' => 0, 'errors' => []];
    
    try {
        // ใช้ cURL ดึงข้อมูล
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $html = curl_exec($ch);
        
        if(curl_errno($ch)) {
            throw new Exception('cURL Error: ' . curl_error($ch));
        }
        curl_close($ch);
        
        // Parse HTML (ต้องติดตั้ง php-dom extension)
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        libxml_clear_errors();
        
        $xpath = new DOMXPath($dom);
        
        // ⚠️ ปรับ XPath selector ให้ตรงกับโครงสร้าง HTML ของเว็บจริง
        // ตัวอย่างนี้สมมติว่าข่าวอยู่ใน <div class="news-item">
        $newsNodes = $xpath->query("//div[contains(@class, 'news-item')]");
        
        foreach($newsNodes as $node) {
            // ดึงข้อมูลจาก HTML elements
            $titleNode = $xpath->query(".//h3 | .//h2", $node)->item(0);
            $linkNode = $xpath->query(".//a", $node)->item(0);
            $dateNode = $xpath->query(".//*[contains(@class, 'date')]", $node)->item(0);
            $summaryNode = $xpath->query(".//p", $node)->item(0);
            
            $title = $titleNode ? trim($titleNode->textContent) : 'No title';
            $link = $linkNode ? $linkNode->getAttribute('href') : '';
            $date = $dateNode ? trim($dateNode->textContent) : date('Y-m-d');
            $summary = $summaryNode ? trim($summaryNode->textContent) : '';
            
            // แปลง relative URL เป็น absolute
            if(!empty($link) && !preg_match('/^https?:\/\//', $link)) {
                $link = rtrim($url, '/') . '/' . ltrim($link, '/');
            }
            
            // แปลงรูปแบบวันที่ (ถ้าจำเป็น)
            $date = parseThaiDate($date);
            
            // เช็คว่ามีข่าวนี้ในฐานข้อมูลแล้วหรือยัง
            $check = $conn->prepare("SELECT id FROM news WHERE url = ? OR title = ?");
            $check->bind_param("ss", $link, $title);
            $check->execute();
            $check->store_result();
            
            if($check->num_rows == 0) {
                // Insert ข่าวใหม่
                $stmt = $conn->prepare("INSERT INTO news (title, summary, url, date_post, source) VALUES (?, ?, ?, ?, 'scraped')");
                $stmt->bind_param("ssss", $title, $summary, $link, $date);
                if($stmt->execute()) {
                    $result['items']++;
                }
                $stmt->close();
            }
            $check->close();
        }
        
        return $result;
        
    } catch(Exception $e) {
        $result['errors'][] = $e->getMessage();
        return $result;
    }
}

/**
 * ฟังก์ชัน Scrape ข้อมูลบุคลากร
 */
function scrapeStaff($conn) {
    $url = "https://eng.rmutp.ac.th/staff"; // ⚠️ ปรับ URL
    $result = ['items' => 0, 'errors' => []];
    
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $html = curl_exec($ch);
        
        if(curl_errno($ch)) {
            throw new Exception('cURL Error: ' . curl_error($ch));
        }
        curl_close($ch);
        
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        libxml_clear_errors();
        
        $xpath = new DOMXPath($dom);
        
        // ⚠️ ปรับ selector ตามโครงสร้าง HTML จริง
        $staffNodes = $xpath->query("//div[contains(@class, 'staff-card')]");
        
        foreach($staffNodes as $node) {
            $nameNode = $xpath->query(".//h3 | .//h4", $node)->item(0);
            $titleNode = $xpath->query(".//*[contains(@class, 'title')]", $node)->item(0);
            $deptNode = $xpath->query(".//*[contains(@class, 'department')]", $node)->item(0);
            $emailNode = $xpath->query(".//a[contains(@href, 'mailto:')]", $node)->item(0);
            $phoneNode = $xpath->query(".//*[contains(@class, 'phone')]", $node)->item(0);
            
            $fullname = $nameNode ? trim($nameNode->textContent) : '';
            $title = $titleNode ? trim($titleNode->textContent) : '';
            $department = $deptNode ? trim($deptNode->textContent) : '';
            $email = $emailNode ? trim($emailNode->textContent) : '';
            $phone = $phoneNode ? trim($phoneNode->textContent) : '';
            
            if(empty($fullname)) continue;
            
            // เช็คว่ามีข้อมูลนี้แล้วหรือยัง
            $check = $conn->prepare("SELECT id FROM staff WHERE fullname = ?");
            $check->bind_param("s", $fullname);
            $check->execute();
            $check->store_result();
            
            if($check->num_rows == 0) {
                $stmt = $conn->prepare("INSERT INTO staff (fullname, title, department, email, phone) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $fullname, $title, $department, $email, $phone);
                if($stmt->execute()) {
                    $result['items']++;
                }
                $stmt->close();
            } else {
                // Update ข้อมูลที่มีอยู่
                $stmt = $conn->prepare("UPDATE staff SET title=?, department=?, email=?, phone=? WHERE fullname=?");
                $stmt->bind_param("sssss", $title, $department, $email, $phone, $fullname);
                $stmt->execute();
                $stmt->close();
            }
            $check->close();
        }
        
        return $result;
        
    } catch(Exception $e) {
        $result['errors'][] = $e->getMessage();
        return $result;
    }
}

/**
 * ฟังก์ชันแปลงวันที่ภาษาไทยเป็นรูปแบบ Y-m-d
 */
function parseThaiDate($dateStr) {
    // ตัวอย่างการแปลง - ปรับให้เหมาะกับรูปแบบวันที่ของเว็บ
    $thaiMonths = [
        'มกราคม' => '01', 'กุมภาพันธ์' => '02', 'มีนาคม' => '03',
        'เมษายน' => '04', 'พฤษภาคม' => '05', 'มิถุนายน' => '06',
        'กรกฎาคม' => '07', 'สิงหาคม' => '08', 'กันยายน' => '09',
        'ตุลาคม' => '10', 'พฤศจิกายน' => '11', 'ธันวาคม' => '12',
        'ม.ค.' => '01', 'ก.พ.' => '02', 'มี.ค.' => '03',
        'เม.ย.' => '04', 'พ.ค.' => '05', 'มิ.ย.' => '06',
        'ก.ค.' => '07', 'ส.ค.' => '08', 'ก.ย.' => '09',
        'ต.ค.' => '10', 'พ.ย.' => '11', 'ธ.ค.' => '12'
    ];
    
    foreach($thaiMonths as $thai => $num) {
        if(mb_strpos($dateStr, $thai) !== false) {
            $dateStr = str_replace($thai, '-' . $num . '-', $dateStr);
            break;
        }
    }
    
    // ลบตัวอักษรภาษาไทยที่เหลือ
    $dateStr = preg_replace('/[ก-๙]/u', '', $dateStr);
    $dateStr = preg_replace('/\s+/', '-', trim($dateStr));
    
    // พยายามแปลงเป็น timestamp
    $timestamp = strtotime($dateStr);
    if($timestamp) {
        return date('Y-m-d', $timestamp);
    }
    
    return date('Y-m-d'); // fallback เป็นวันปัจจุบัน
}

// ===== Main Execution =====
switch($action) {
    case 'news':
        $scrapeResult = scrapeNews($conn);
        $result['success'] = true;
        $result['message'] = "Scraped {$scrapeResult['items']} news items";
        $result['data'] = $scrapeResult;
        break;
        
    case 'staff':
        $scrapeResult = scrapeStaff($conn);
        $result['success'] = true;
        $result['message'] = "Scraped {$scrapeResult['items']} staff members";
        $result['data'] = $scrapeResult;
        break;
        
    case 'all':
        $newsResult = scrapeNews($conn);
        $staffResult = scrapeStaff($conn);
        $result['success'] = true;
        $result['message'] = "Scraped {$newsResult['items']} news and {$staffResult['items']} staff";
        $result['data'] = ['news' => $newsResult, 'staff' => $staffResult];
        break;
        
    default:
        $result['message'] = "Invalid action. Use: ?action=news, ?action=staff, or ?action=all";
}

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

/*
 * ===== คำแนะนำการใช้งาน =====
 * 
 * 1. เรียกใช้ Manual:
 *    http://localhost/eng_chatbot_api/scraper.php?action=news
 *    http://localhost/eng_chatbot_api/scraper.php?action=staff
 *    http://localhost/eng_chatbot_api/scraper.php?action=all
 * 
 * 2. ตั้ง Cron Job (Linux/Mac):
 *    # รันทุก 6 ชั่วโมง
 *    0 */6 * * * php /path/to/scraper.php?action=all
 * 
 * 3. ตั้ง Task Scheduler (Windows):
 *    - Program: C:\xampp\php\php.exe
 *    - Arguments: C:\xampp\htdocs\eng_chatbot_api\scraper.php
 *    - Trigger: ทุก 6 ชั่วโมง
 * 
 * 4. เรียกใช้ผ่าน Webhook:
 *    สามารถเรียก scraper.php จาก GitHub Actions, Zapier, หรือ IFTTT
 * 
 * ⚠️ สำคัญ: ต้องปรับ XPath selector ให้ตรงกับโครงสร้าง HTML ของเว็บจริง
 */
?>
