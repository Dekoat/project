<?php
/**
 * Database Configuration for RMUTP Server
 * Host: shost.rmutp.ac.th
 */

// === LOCAL DEVELOPMENT (XAMPP) ===
$host = "localhost";
$user = "root";
$pass = "";
$db = "eng_chatbot";

$conn = new mysqli($host, $user, $pass, $db);
$conn->set_charset("utf8mb4"); // รองรับภาษาไทย

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>
