<?php
/**
 * ไฟล์ Header สำหรับทุกหน้า
 */
session_start();
require_once __DIR__ . '/../config/db.php';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'ระบบลงทะเบียนกิจกรรม'; ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo isset($admin_path) ? '../' : ''; ?>assets/css/style.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?php echo isset($admin_path) ? '../' : ''; ?>index.php">
                <i class="bi bi-calendar-event"></i> ระบบลงทะเบียนกิจกรรม
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (is_logged_in()): ?>
                        <!-- เมนูสำหรับผู้ที่ login แล้ว -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo isset($admin_path) ? '../' : ''; ?>events.php">
                                <i class="bi bi-list-ul"></i> กิจกรรมทั้งหมด
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo isset($admin_path) ? '../' : ''; ?>my_events.php">
                                <i class="bi bi-bookmark-check"></i> กิจกรรมของฉัน
                            </a>
                        </li>
                        
                        <?php if (is_admin()): ?>
                            <!-- เมนูสำหรับ admin -->
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo isset($admin_path) ? '' : 'admin/'; ?>index.php">
                                    <i class="bi bi-speedometer2"></i> แดชบอร์ด
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> 
                                <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                                <small class="text-white-50">(<?php echo htmlspecialchars($_SESSION['student_id']); ?>)</small>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#">โปรไฟล์</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="<?php echo isset($admin_path) ? '../' : ''; ?>logout.php">
                                        <i class="bi bi-box-arrow-right"></i> ออกจากระบบ
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <!-- เมนูสำหรับผู้ที่ยังไม่ login -->
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">
                                <i class="bi bi-box-arrow-in-right"></i> เข้าสู่ระบบ
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">
                                <i class="bi bi-person-plus"></i> สมัครสมาชิก
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Main Content Container -->
    <div class="container mt-4 mb-5">
        <?php
        // แสดงข้อความแจ้งเตือนจาก session
        if (isset($_SESSION['alert_message'])) {
            echo show_alert($_SESSION['alert_message'], $_SESSION['alert_type'] ?? 'info');
            unset($_SESSION['alert_message'], $_SESSION['alert_type']);
        }
        ?>