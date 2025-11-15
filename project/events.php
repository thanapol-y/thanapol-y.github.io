<?php
/**
 * หน้าแสดงกิจกรรมทั้งหมดและให้ลงทะเบียน
 */

$page_title = 'กิจกรรมทั้งหมด';
require_once 'includes/header.php';

// ตรวจสอบว่า login แล้วหรือยัง
if (!is_logged_in()) {
    $_SESSION['alert_message'] = 'กรุณาเข้าสู่ระบบก่อนใช้งาน';
    $_SESSION['alert_type'] = 'warning';
    redirect('login.php');
}

// จัดการการลงทะเบียนกิจกรรม
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_id'])) {
    $event_id = (int)$_POST['event_id'];
    $user_id = $_SESSION['user_id'];
    
    try {
        // เริ่ม transaction
        $pdo->beginTransaction();
        
        // ตรวจสอบว่ากิจกรรมเต็มหรือยัง
        $check_stmt = $pdo->prepare("SELECT max_participants, current_participants, status 
                                      FROM events 
                                      WHERE event_id = ?");
        $check_stmt->execute([$event_id]);
        $event = $check_stmt->fetch();
        
        if (!$event) {
            throw new Exception('ไม่พบกิจกรรมนี้');
        }
        
        if ($event['status'] !== 'open') {
            throw new Exception('กิจกรรมนี้ปิดรับสมัครแล้ว');
        }
        
        if ($event['current_participants'] >= $event['max_participants']) {
            throw new Exception('กิจกรรมนี้เต็มแล้ว');
        }
        
        // ตรวจสอบว่าลงทะเบียนแล้วหรือยัง
        $check_reg = $pdo->prepare("SELECT reg_id FROM registrations 
                                     WHERE user_id = ? AND event_id = ? AND status = 'registered'");
        $check_reg->execute([$user_id, $event_id]);
        
        if ($check_reg->fetch()) {
            throw new Exception('คุณได้ลงทะเบียนกิจกรรมนี้แล้ว');
        }
        
        // ลงทะเบียน
        $insert_reg = $pdo->prepare("INSERT INTO registrations (user_id, event_id, status) 
                                      VALUES (?, ?, 'registered')");
        $insert_reg->execute([$user_id, $event_id]);
        
        // อัพเดทจำนวนผู้เข้าร่วม
        $update_event = $pdo->prepare("UPDATE events 
                                        SET current_participants = current_participants + 1 
                                        WHERE event_id = ?");
        $update_event->execute([$event_id]);
        
        // Commit transaction
        $pdo->commit();
        
        $_SESSION['alert_message'] = 'ลงทะเบียนกิจกรรมสำเร็จ!';
        $_SESSION['alert_type'] = 'success';
        
    } catch (Exception $e) {
        // Rollback ถ้าเกิด error
        $pdo->rollBack();
        $_SESSION['alert_message'] = $e->getMessage();
        $_SESSION['alert_type'] = 'danger';
    }
    
    redirect('events.php');
}

// ดึงข้อมูลกิจกรรมทั้งหมดที่ยังเปิดรับสมัคร
try {
    $stmt = $pdo->prepare("
        SELECT e.*, u.full_name as creator_name,
               (SELECT COUNT(*) FROM registrations r 
                WHERE r.event_id = e.event_id 
                AND r.user_id = ? 
                AND r.status = 'registered') as is_registered
        FROM events e
        LEFT JOIN users u ON e.created_by = u.user_id
        WHERE e.event_date >= CURDATE()
        ORDER BY e.event_date ASC, e.event_time ASC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $events = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $events = [];
    error_log("Events fetch error: " . $e->getMessage());
}
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-calendar-event"></i> กิจกรรมทั้งหมด</h2>
            <div>
                <a href="my_events.php" class="btn btn-outline-primary">
                    <i class="bi bi-bookmark-check"></i> กิจกรรมของฉัน
                </a>
            </div>
        </div>
    </div>
</div>

<?php if (empty($events)): ?>
    <div class="alert alert-info text-center">
        <i class="bi bi-info-circle"></i> ขณะนี้ยังไม่มีกิจกรรมที่เปิดรับสมัคร
    </div>
<?php else: ?>
    <div class="row">
        <?php foreach ($events as $event): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <?php if ($event['image_url']): ?>
                        <img src="<?php echo htmlspecialchars($event['image_url']); ?>" 
                             class="card-img-top" 
                             alt="<?php echo htmlspecialchars($event['event_name']); ?>"
                             style="height: 200px; object-fit: cover;">
                    <?php else: ?>
                        <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" 
                             style="height: 200px;">
                            <i class="bi bi-image text-white" style="font-size: 3rem;"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($event['event_name']); ?></h5>
                        <p class="card-text text-muted small">
                            <?php echo htmlspecialchars(substr($event['description'], 0, 100)); ?>
                            <?php echo strlen($event['description']) > 100 ? '...' : ''; ?>
                        </p>
                        
                        <ul class="list-unstyled small">
                            <li class="mb-2">
                                <i class="bi bi-calendar3 text-primary"></i> 
                                <?php echo date('d/m/Y', strtotime($event['event_date'])); ?>
                                <?php if ($event['event_time']): ?>
                                    เวลา <?php echo date('H:i', strtotime($event['event_time'])); ?> น.
                                <?php endif; ?>
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-geo-alt text-danger"></i> 
                                <?php echo htmlspecialchars($event['location']); ?>
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-people text-success"></i> 
                                <?php echo $event['current_participants']; ?>/<?php echo $event['max_participants']; ?> คน
                                
                                <!-- แสดง progress bar -->
                                <div class="progress mt-1" style="height: 5px;">
                                    <?php 
                                    $percentage = ($event['current_participants'] / $event['max_participants']) * 100;
                                    $bar_color = $percentage >= 80 ? 'bg-danger' : ($percentage >= 50 ? 'bg-warning' : 'bg-success');
                                    ?>
                                    <div class="progress-bar <?php echo $bar_color; ?>" 
                                         role="progressbar" 
                                         style="width: <?php echo $percentage; ?>%"></div>
                                </div>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="card-footer bg-transparent">
                        <?php if ($event['is_registered']): ?>
                            <!-- ลงทะเบียนแล้ว -->
                            <button class="btn btn-success w-100" disabled>
                                <i class="bi bi-check-circle"></i> ลงทะเบียนแล้ว
                            </button>
                        <?php elseif ($event['current_participants'] >= $event['max_participants']): ?>
                            <!-- เต็มแล้ว -->
                            <button class="btn btn-secondary w-100" disabled>
                                <i class="bi bi-x-circle"></i> เต็มแล้ว
                            </button>
                        <?php elseif ($event['status'] !== 'open'): ?>
                            <!-- ปิดรับสมัคร -->
                            <button class="btn btn-secondary w-100" disabled>
                                <i class="bi bi-lock"></i> ปิดรับสมัคร
                            </button>
                        <?php else: ?>
                            <!-- ลงทะเบียนได้ -->
                            <form method="POST" action="" onsubmit="return confirm('ยืนยันการลงทะเบียนกิจกรรมนี้?');">
                                <input type="hidden" name="event_id" value="<?php echo $event['event_id']; ?>">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-plus-circle"></i> ลงทะเบียน
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>