<?php
/**
 * Test Database Connection for RMUTP Server
 * ทดสอบการเชื่อมต่อฐานข้อมูล
 */

echo "<h1>🔍 ทดสอบระบบ EngBot</h1>";
echo "<hr>";

// 1. ทดสอบ PHP Version
echo "<h2>1. PHP Version</h2>";
echo "PHP Version: <strong>" . phpversion() . "</strong><br>";
echo "Required: PHP 7.4+<br>";
echo phpversion() >= "7.4" ? "✅ <span style='color:green;'>OK</span>" : "❌ <span style='color:red;'>ต้องอัปเกรด PHP</span>";
echo "<br><br>";

// 2. ทดสอบ Extensions
echo "<h2>2. PHP Extensions</h2>";
$required_extensions = ['mysqli', 'curl', 'mbstring', 'json', 'session'];
foreach($required_extensions as $ext) {
    $loaded = extension_loaded($ext);
    $status = $loaded ? "✅ <span style='color:green;'>Enabled</span>" : "❌ <span style='color:red;'>Missing</span>";
    echo "$ext: $status<br>";
}
echo "<br>";

// 3. ทดสอบการเชื่อมต่อฐานข้อมูล
echo "<h2>3. Database Connection</h2>";
include "db.php";

if($conn->connect_error) {
    echo "❌ <span style='color:red;'>Connection Failed: " . $conn->connect_error . "</span><br>";
} else {
    echo "✅ <span style='color:green;'>Connection Successful</span><br>";
    echo "Database: <strong>" . $conn->server_info . "</strong><br>";
    echo "Character Set: <strong>" . $conn->character_set_name() . "</strong><br>";
}
echo "<br>";

// 4. ทดสอบตาราง
echo "<h2>4. Database Tables</h2>";
$tables = ['news', 'staff', 'faq', 'faq_keyword', 'chat_logs', 'contact_info'];
$all_ok = true;

foreach($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if($result && $result->num_rows > 0) {
        // นับจำนวนแถว
        $count = $conn->query("SELECT COUNT(*) as cnt FROM $table")->fetch_assoc()['cnt'];
        echo "✅ <span style='color:green;'>$table</span> ($count records)<br>";
    } else {
        echo "❌ <span style='color:red;'>$table - ไม่พบตาราง</span><br>";
        $all_ok = false;
    }
}
echo "<br>";

// 5. ทดสอบ Chatbot API
echo "<h2>5. Chatbot API Test</h2>";
echo "<form method='get' action='chatbot.php' target='_blank'>";
echo "<input type='text' name='msg' value='ข่าว' style='padding:8px; width:300px;'>";
echo "<button type='submit' style='padding:8px 20px; background:#a90d2c; color:white; border:none; cursor:pointer;'>ทดสอบ</button>";
echo "</form>";
echo "<small>คลิกปุ่มเพื่อทดสอบ API</small><br><br>";

// 6. ทดสอบ File Permissions
echo "<h2>6. File Permissions</h2>";
$files = ['chat.html', 'chatbot.php', 'admin.php', 'db.php', 'scraper.php'];
foreach($files as $file) {
    if(file_exists($file)) {
        $perms = substr(sprintf('%o', fileperms($file)), -3);
        echo "✅ $file (Permissions: $perms)<br>";
    } else {
        echo "❌ <span style='color:red;'>$file - ไม่พบไฟล์</span><br>";
    }
}
echo "<br>";

// 7. สรุปผล
echo "<h2>7. สรุปผล</h2>";
if($all_ok && $conn && !$conn->connect_error) {
    echo "<div style='background:#d4edda; color:#155724; padding:15px; border-radius:5px; border-left:4px solid #28a745;'>";
    echo "<strong>✅ ระบบพร้อมใช้งาน!</strong><br>";
    echo "คุณสามารถเริ่มใช้งานแชทบอทได้แล้ว<br>";
    echo "<a href='chat.html' style='color:#155724; font-weight:bold;'>→ เปิดแชทบอท</a> | ";
    echo "<a href='admin.php' style='color:#155724; font-weight:bold;'>→ Admin Panel</a>";
    echo "</div>";
} else {
    echo "<div style='background:#f8d7da; color:#721c24; padding:15px; border-radius:5px; border-left:4px solid #dc3545;'>";
    echo "<strong>⚠️ พบปัญหา!</strong><br>";
    echo "กรุณาตรวจสอบข้อผิดพลาดด้านบน<br>";
    echo "ดูคู่มือแก้ไขที่: <a href='DEPLOY.md' style='color:#721c24;'>DEPLOY.md</a>";
    echo "</div>";
}

echo "<br><hr>";
echo "<small>EngBot v1.0 | RMUTP Engineering Faculty</small>";
?>

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    max-width: 800px;
    margin: 40px auto;
    padding: 20px;
    background: #f5f7fa;
}
h1 { color: #a90d2c; }
h2 { color: #333; margin-top: 20px; }
</style>
