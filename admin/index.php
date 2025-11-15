<?php
/**
 * แดชบอร์ด Admin
 * แสดงสถิติและข้อมูลภาพรวมของระบบ
 */

$page_title = 'แดชบอร์ด Admin';
$admin_path = true; // ใช้สำหรับ header รู้ว่าอยู่ใน admin folder
require_once '../includes/header.php';

// ตรวจสอบสิทธิ์ admin
if (!is_logged_in() || !is_admin()) {
    $_SESSION['alert_message'] = 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้';
    $_SESSION['alert_type'] = 'danger';
    redirect('../index.php');
}

// ดึงสถิติต่างๆ
try {
    // จำนวนนักศึกษาทั้งหมด
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'student'");
    $total_students = $stmt->fetch()['total'];
    
    // จำนวนกิจกรรมทั้งหมด
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM events");
    $total_events = $stmt->fetch()['total'];
    
    // จำนวนกิจกรรมที่เปิดรับสมัคร
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM events WHERE status = 'open' AND event_date >= CURDATE()");
    $active_events = $stmt->fetch()['total'];
    
    // จำนวนการลงทะเบียนทั้งหมด
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM registrations WHERE status = 'registered'");
    $total_registrations = $stmt->fetch()['total'];
    
    // กิจกรรมล่าสุด 5 รายการ
    $stmt = $pdo->query("
        SELECT e.*, u.full_name as creator_name,
               (SELECT COUNT(*) FROM registrations WHERE event_id = e.event_id AND status = 'registered') as reg_count
        FROM events e
        LEFT JOIN users u ON e.created_by = u.user_id
        ORDER BY e.created_at DESC
        LIMIT 5
    ");
    $recent_events = $stmt->fetchAll();
    
    // การลงทะเบียนล่าสุด
    $stmt = $pdo->query("
        SELECT r.*, u.full_name, u.student_id, e.event_name, e.event_date
        FROM registrations r
        JOIN users u ON r.user_id = u.user_id
        JOIN events e ON r.event_id = e.event_id
        ORDER BY r.registered_at DESC
        LIMIT 10
    ");
    $recent_registrations = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Admin dashboard error: " . $e->getMessage());
}
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item active">แดชบอร์ด</li>
    </ol>
</nav>

<div class="row mb-4">
    <div class="col-12">
        <h2><i class="bi bi-speedometer2"></i> แดชบอร์ด Admin</h2>
        <p class="text-muted">ภาพรวมระบบลงทะเบียนกิจกรรม</p>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">นักศึกษาทั้งหมด</h6>
                        <h2 class="mb-0"><?php echo number_format($total_students); ?></h2>
                    </div>
                    <div>
                        <i class="bi bi-people" style="font-size: 3rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">กิจกรรมทั้งหมด</h6>
                        <h2 class="mb-0"><?php echo number_format($total_events); ?></h2>
                    </div>
                    <div>
                        <i class="bi bi-calendar-event" style="font-size: 3rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">กิจกรรมเปิดรับสมัคร</h6>
                        <h2 class="mb-0"><?php echo number_format($active_events); ?></h2>
                    </div>
                    <div>
                        <i class="bi bi-check-circle" style="font-size: 3rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">การลงทะเบียน</h6>
                        <h2 class="mb-0"><?php echo number_format($total_registrations); ?></h2>
                    </div>
                    <div>
                        <i class="bi bi-clipboard-check" style="font-size: 3rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-lightning"></i> เมนูด่วน</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2 d-md-flex">
                    <a href="events.php?action=add" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> เพิ่มกิจกรรมใหม่
                    </a>
                    <a href="events.php" class="btn btn-success">
                        <i class="bi bi-list-ul"></i> จัดการกิจกรรม
                    </a>
                    <a href="participants.php" class="btn btn-info">
                        <i class="bi bi-people"></i> ดูรายชื่อผู้เข้าร่วม
                    </a>
                    <a href="../events.php" class="btn btn-outline-secondary">
                        <i class="bi bi-eye"></i> ดูหน้านักศึกษา
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Events -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-calendar-event"></i> กิจกรรมล่าสุด</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>ชื่อกิจกรรม</th>
                                <th>วันที่</th>
                                <th>ผู้เข้าร่วม</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_events as $event): ?>
                                <tr>
                                    <td>
                                        <a href="events.php?id=<?php echo $event['event_id']; ?>">
                                            <?php echo htmlspecialchars($event['event_name']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($event['event_date'])); ?></td>
                                    <td>
                                        <span class="badge bg-primary">
                                            <?php echo $event['reg_count']; ?>/<?php echo $event['max_participants']; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Registrations -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-clipboard-check"></i> การลงทะเบียนล่าสุด</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>นักศึกษา</th>
                                <th>กิจกรรม</th>
                                <th>เวลา</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_registrations as $reg): ?>
                                <tr>
                                    <td>
                                        <?php echo htmlspecialchars($reg['full_name']); ?>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars($reg['student_id']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($reg['event_name']); ?></td>
                                    <td>
                                        <small><?php echo date('d/m/Y H:i', strtotime($reg['registered_at'])); ?></small>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>