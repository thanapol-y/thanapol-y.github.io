<?php
/**
 * ออกจากระบบ
 */

session_start();

// บันทึก log ก่อนออกจากระบบ (optional)
if (isset($_SESSION['user_id'])) {
    require_once 'config/db.php';
    
    try {
        $stmt = $pdo->prepare("INSERT INTO admin_logs (admin_id, action, description, ip_address) 
                               VALUES (?, 'LOGOUT', 'User logged out', ?)");
        $stmt->execute([$_SESSION['user_id'], $_SERVER['REMOTE_ADDR']]);
    } catch (PDOException $e) {
        // ไม่ต้องทำอะไร ถ้า log ไม่สำเร็จ
    }
}

// ทำลาย session ทั้งหมด
$_SESSION = array();

// ลบ session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// ทำลาย session
session_destroy();

// Redirect กลับไปหน้า login พร้อมข้อความ
session_start();
$_SESSION['alert_message'] = 'ออกจากระบบเรียบร้อยแล้ว';
$_SESSION['alert_type'] = 'success';

header("Location: login.php");
exit();
?>