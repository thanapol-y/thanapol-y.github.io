<?php
/**
 * หน้าแรกของระบบ
 */

$page_title = 'หน้าแรก';
require_once 'includes/header.php';

// ถ้า login แล้วให้ redirect ไปหน้ากิจกรรม
if (is_logged_in()) {
    redirect('events.php');
}

// ดึงสถิติเบื้องต้น
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM events WHERE status = 'open' AND event_date >= CURDATE()");
    $active_events = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'student'");
    $total_students = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM registrations WHERE status = 'registered'");
    $total_registrations = $stmt->fetch()['total'];
} catch (PDOException $e) {
    $active_events = 0;
    $total_students = 0;
    $total_registrations = 0;
}
?>

<!-- Hero Section -->
<div class="row mb-5">
    <div class="col-12">
        <div class="jumbotron bg-primary text-white p-5 rounded">
            <div class="text-center">
                <h1 class="display-4 mb-3">
                    <i class="bi bi-calendar-event"></i> 
                    ระบบลงทะเบียนเข้าร่วมกิจกรรม
                </h1>
                <p class="lead mb-4">
                    สำหรับนักศึกษา - ลงทะเบียนและจัดการกิจกรรมของคุณได้อย่างง่ายดาย
                </p>
                <hr class="my-4 bg-white">
                <div class="d-grid gap-2 d-md-flex justify-content-center">
                    <a href="login.php" class="btn btn-light btn-lg">
                        <i class="bi bi-box-arrow-in-right"></i> เข้าสู่ระบบ
                    </a>
                    <a href="register.php" class="btn btn-outline-light btn-lg">
                        <i class="bi bi-person-plus"></i> สมัครสมาชิก
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics -->
<div class="row text-center mb-5">
    <div class="col-md-4">
        <div class="card border-primary shadow-sm h-100">
            <div class="card-body">
                <i class="bi bi-calendar-check text-primary" style="font-size: 3rem;"></i>
                <h3 class="mt-3 text-primary"><?php echo number_format($active_events); ?></h3>
                <p class="text-muted mb-0">กิจกรรมที่เปิดรับสมัคร</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-success shadow-sm h-100">
            <div class="card-body">
                <i class="bi bi-people text-success" style="font-size: 3rem;"></i>
                <h3 class="mt-3 text-success"><?php echo number_format($total_students); ?></h3>
                <p class="text-muted mb-0">นักศึกษาที่ลงทะเบียน</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-info shadow-sm h-100">
            <div class="card-body">
                <i class="bi bi-clipboard-check text-info" style="font-size: 3rem;"></i>
                <h3 class="mt-3 text-info"><?php echo number_format($total_registrations); ?></h3>
                <p class="text-muted mb-0">การเข้าร่วมกิจกรรม</p>
            </div>
        </div>
    </div>
</div>

<!-- Features -->
<div class="row mb-5">
    <div class="col-12">
        <h2 class="text-center mb-4">
            <i class="bi bi-star"></i> คุณสมบัติของระบบ
        </h2>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="bi bi-speedometer2 text-primary" style="font-size: 3rem;"></i>
                </div>
                <h5 class="card-title">ลงทะเบียนง่าย รวดเร็ว</h5>
                <p class="card-text text-muted">
                    ระบบใช้งานง่าย สามารถลงทะเบียนเข้าร่วมกิจกรรมได้ภายในไม่กี่คลิก
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="bi bi-calendar-range text-success" style="font-size: 3rem;"></i>
                </div>
                <h5 class="card-title">จัดการกิจกรรม</h5>
                <p class="card-text text-muted">
                    ดูรายการกิจกรรมที่ลงทะเบียนไว้ และสามารถยกเลิกได้ตามเงื่อนไข
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="bi bi-bell text-warning" style="font-size: 3rem;"></i>
                </div>
                <h5 class="card-title">แจ้งเตือนอัตโนมัติ</h5>
                <p class="card-text text-muted">
                    รับการแจ้งเตือนเมื่อมีกิจกรรมใหม่หรือเมื่อใกล้ถึงวันกิจกรรม
                </p>
            </div>
        </div>
    </div>
</div>

<!-- How to use -->
<div class="row mb-5">
    <div class="col-12">
        <div class="card bg-light border-0">
            <div class="card-body">
                <h3 class="text-center mb-4">
                    <i class="bi bi-question-circle"></i> วิธีใช้งาน
                </h3>
                <div class="row">
                    <div class="col-md-3 text-center mb-3">
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                             style="width: 60px; height: 60px; font-size: 1.5rem; font-weight: bold;">
                            1
                        </div>
                        <h6>สมัครสมาชิก</h6>
                        <p class="small text-muted">ใช้รหัสนักศึกษาและอีเมล</p>
                    </div>
                    <div class="col-md-3 text-center mb-3">
                        <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                             style="width: 60px; height: 60px; font-size: 1.5rem; font-weight: bold;">
                            2
                        </div>
                        <h6>เข้าสู่ระบบ</h6>
                        <p class="small text-muted">ใช้รหัสนักศึกษาและรหัสผ่าน</p>
                    </div>
                    <div class="col-md-3 text-center mb-3">
                        <div class="bg-info text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                             style="width: 60px; height: 60px; font-size: 1.5rem; font-weight: bold;">
                            3
                        </div>
                        <h6>เลือกกิจกรรม</h6>
                        <p class="small text-muted">ดูและเลือกกิจกรรมที่สนใจ</p>
                    </div>
                    <div class="col-md-3 text-center mb-3">
                        <div class="bg-warning text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                             style="width: 60px; height: 60px; font-size: 1.5rem; font-weight: bold;">
                            4
                        </div>
                        <h6>ลงทะเบียน</h6>
                        <p class="small text-muted">กดปุ่มลงทะเบียนเสร็จสิ้น</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CTA -->
<div class="row">
    <div class="col-12">
        <div class="alert alert-primary text-center" role="alert">
            <h4 class="alert-heading">
                <i class="bi bi-info-circle"></i> พร้อมที่จะเริ่มต้นแล้วหรือยัง?
            </h4>
            <p>สมัครสมาชิกวันนี้และเริ่มลงทะเบียนกิจกรรมที่คุณสนใจได้เลย!</p>
            <hr>
            <a href="register.php" class="btn btn-primary btn-lg">
                <i class="bi bi-person-plus"></i> สมัครสมาชิกเลย
            </a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>