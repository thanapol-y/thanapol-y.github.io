<?php
/**
 * หน้าแสดงกิจกรรมที่ผู้ใช้ลงทะเบียนไว้
 */

$page_title = 'กิจกรรมของฉัน';
require_once 'includes/header.php';

if (!is_logged_in()) {
    redirect('login.php');
}

// จัดการการยกเลิกกิจกรรม
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_event'])) {
    $reg_id = (int)$_POST['reg_id'];
    $user_id = $_SESSION['user_id'];
    
    try {
        $pdo->beginTransaction();
        
        // ตรวจสอบว่าเป็นการลงทะเบียนของผู้ใช้จริง
        $check = $pdo->prepare("SELECT r.event_id, e.event_date 
                                FROM registrations r
                                JOIN events e ON r.event_id = e.event_id
                                WHERE r.reg_id = ? AND r.user_id = ? AND r.status = 'registered'");
        $check->execute([$reg_id, $user_id]);
        $registration = $check->fetch();
        
        if (!$registration) {
            throw new Exception('ไม่พบข้อมูลการลงทะเบียน');
        }
        
        // ตรวจสอบว่ายังยกเลิกได้หรือไม่ (ยกเลิกได้ก่อนวันกิจกรรม 1 วัน)
        $event_date = new DateTime($registration['event_date']);
        $today = new DateTime();
        $diff = $today->diff($event_date);
        
        if ($diff->days < 1 && $diff->invert == 0) {
            throw new Exception('ไม่สามารถยกเลิกได้ เนื่องจากใกล้วันกิจกรรมเกินไป');
        }
        
        // อัพเดทสถานะเป็น cancelled
        $cancel = $pdo->prepare("UPDATE registrations 
                                 SET status = 'cancelled', cancelled_at = NOW() 
                                 WHERE reg_id = ?");
        $cancel->execute([$reg_id]);
        
        // ลดจำนวนผู้เข้าร่วม
        $update = $pdo->prepare("UPDATE events 
                                 SET current_participants = current_participants - 1 
                                 WHERE event_id = ?");
        $update->execute([$registration['event_id']]);
        
        $pdo->commit();
        
        $_SESSION['alert_message'] = 'ยกเลิกกิจกรรมสำเร็จ';
        $_SESSION['alert_type'] = 'success';
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['alert_message'] = $e->getMessage();
        $_SESSION['alert_type'] = 'danger';
    }
    
    redirect('my_events.php');
}

// ดึงข้อมูลกิจกรรมที่ลงทะเบียนไว้
try {
    $stmt = $pdo->prepare("
        SELECT r.*, e.event_name, e.description, e.event_date, e.event_time, 
               e.location, e.max_participants, e.current_participants
        FROM registrations r
        JOIN events e ON r.event_id = e.event_id
        WHERE r.user_id = ?
        ORDER BY e.event_date DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $my_events = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $my_events = [];
    error_log("My events error: " . $e->getMessage());
}
?>

<div class="row mb-4">
    <div class="col-12">
        <h2><i class="bi bi-bookmark-check"></i> กิจกรรมของฉัน</h2>
        <p class="text-muted">รายการกิจกรรมที่คุณได้ลงทะเบียนไว้</p>
    </div>
</div>

<?php if (empty($my_events)): ?>
    <div class="alert alert-info text-center">
        <i class="bi bi-info-circle"></i> คุณยังไม่ได้ลงทะเบียนกิจกรรมใดๆ
        <br>
        <a href="events.php" class="btn btn-primary mt-3">
            <i class="bi bi-search"></i> ค้นหากิจกรรม
        </a>
    </div>
<?php else: ?>
    <div class="row">
        <div class="col-12">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="30%">ชื่อกิจกรรม</th>
                            <th width="15%">วันที่</th>
                            <th width="15%">เวลา</th>
                            <th width="20%">สถานที่</th>
                            <th width="10%">สถานะ</th>
                            <th width="5%">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        foreach ($my_events as $event): 
                            $event_date = new DateTime($event['event_date']);
                            $today = new DateTime();
                            $is_past = $event_date < $today;
                        ?>
                            <tr class="<?php echo $is_past ? 'table-secondary' : ''; ?>">
                                <td><?php echo $no++; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($event['event_name']); ?></strong>
                                    <?php if ($is_past): ?>
                                        <span class="badge bg-secondary ms-2">ผ่านไปแล้ว</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <i class="bi bi-calendar3"></i> 
                                    <?php echo date('d/m/Y', strtotime($event['event_date'])); ?>
                                </td>
                                <td>
                                    <i class="bi bi-clock"></i> 
                                    <?php echo $event['event_time'] ? date('H:i', strtotime($event['event_time'])) . ' น.' : '-'; ?>
                                </td>
                                <td>
                                    <i class="bi bi-geo-alt"></i> 
                                    <?php echo htmlspecialchars($event['location']); ?>
                                </td>
                                <td>
                                    <?php
                                    $status_badges = [
                                        'registered' => '<span class="badge bg-success">ลงทะเบียนแล้ว</span>',
                                        'cancelled' => '<span class="badge bg-danger">ยกเลิกแล้ว</span>',
                                        'attended' => '<span class="badge bg-info">เข้าร่วมแล้ว</span>'
                                    ];
                                    echo $status_badges[$event['status']] ?? '';
                                    ?>
                                </td>
                                <td>
                                    <?php if ($event['status'] === 'registered' && !$is_past): ?>
                                        <form method="POST" action="" style="display:inline;" 
                                              onsubmit="return confirm('ยืนยันการยกเลิกกิจกรรมนี้?');">
                                            <input type="hidden" name="reg_id" value="<?php echo $event['reg_id']; ?>">
                                            <button type="submit" name="cancel_event" class="btn btn-sm btn-danger" title="ยกเลิก">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-secondary" disabled>
                                            <i class="bi bi-dash-circle"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- สรุปสถิติ -->
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-success">
                        <?php echo count(array_filter($my_events, fn($e) => $e['status'] === 'registered')); ?>
                    </h3>
                    <p class="mb-0">กิจกรรมที่ลงทะเบียน</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-danger">
                        <?php echo count(array_filter($my_events, fn($e) => $e['status'] === 'cancelled')); ?>
                    </h3>
                    <p class="mb-0">กิจกรรมที่ยกเลิก</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-info">
                        <?php echo count(array_filter($my_events, fn($e) => $e['status'] === 'attended')); ?>
                    </h3>
                    <p class="mb-0">กิจกรรมที่เข้าร่วม</p>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>