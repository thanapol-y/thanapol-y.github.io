<?php
/**
 * ไฟล์การเชื่อมต่อฐานข้อมูล
 * ใช้ PDO (PHP Data Objects) เพื่อความปลอดภัยและยืดหยุ่น
 */

// การตั้งค่าฐานข้อมูล
define('DB_HOST', 'localhost');
define('DB_NAME', 'event_registration');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ตัวแปร DSN (Data Source Name)
$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

// ตัวเลือกสำหรับ PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,    // แสดง error แบบ exception
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,          // ดึงข้อมูลเป็น associative array
    PDO::ATTR_EMULATE_PREPARES   => false,                     // ปิด emulated prepared statements
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"        // ตั้งค่า charset
];

try {
    // สร้างการเชื่อมต่อ PDO
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
    // ตั้งค่าเขตเวลา (timezone) สำหรับ MySQL
    $pdo->exec("SET time_zone = '+07:00'");
    
} catch (PDOException $e) {
    // จัดการ error การเชื่อมต่อ
    // ในโปรดักชั่นควรบันทึก error ลง log แทนการแสดงออกมา
    die("Connection failed: " . $e->getMessage());
}

/**
 * ฟังก์ชันตรวจสอบและป้องกัน SQL Injection
 * @param string $data ข้อมูลที่ต้องการตรวจสอบ
 * @return string ข้อมูลที่ปลอดภัย
 */
function sanitize_input($data) {
    $data = trim($data);                    // ตัดช่องว่างหน้า-หลัง
    $data = stripslashes($data);            // ลบ backslashes
    $data = htmlspecialchars($data);        // แปลง special characters เป็น HTML entities
    return $data;
}

/**
 * ฟังก์ชันตรวจสอบ session
 * @return bool true ถ้า login แล้ว
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * ฟังก์ชันตรวจสอบว่าเป็น admin หรือไม่
 * @return bool true ถ้าเป็น admin
 */
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * ฟังก์ชัน redirect ไปหน้าอื่น
 * @param string $url URL ที่ต้องการ redirect
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * ฟังก์ชันแสดงข้อความแจ้งเตือน
 * @param string $message ข้อความ
 * @param string $type ประเภท (success, danger, warning, info)
 */
function show_alert($message, $type = 'info') {
    return '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">' .
           htmlspecialchars($message) .
           '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' .
           '</div>';
}
?>