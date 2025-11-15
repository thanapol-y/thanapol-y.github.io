<?php
/**
 * หน้าจัดการกิจกรรมสำหรับ Admin (CRUD)
 */

$page_title = 'จัดการกิจกรรม';
$admin_path = true;
require_once '../includes/header.php';

if (!is_logged_in() || !is_admin()) {
    redirect('../index.php');
}

$action = $_GET['action'] ?? 'list';
$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// ======================== CREATE / UPDATE ========================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_event'])) {
    $event_name = sanitize_input($_POST['event_name'] ?? '');
    $description = sanitize_input($_POST['description'] ?? '');
    $event_date = sanitize_input($_POST['event_date'] ?? '');
    $event_time = sanitize_input($_POST['event_time'] ?? '');
    $location = sanitize_input($_POST['location'] ?? '');
    $max_participants = (int)($_POST['max_participants'] ?? 100);
    $status = sanitize_input($_POST['status'] ?? 'open');
    $edit_id = (int)($_POST['event_id'] ?? 0);
    
    try {
        if ($edit_id > 0) {
            // UPDATE
            $stmt = $pdo->prepare("UPDATE events SET 
                event_name = ?, description = ?, event_date = ?, event_time = ?,
                location = ?, max_participants = ?, status = ?
                WHERE event_id = ?");
            $stmt->execute([
                $event_name, $description, $event_date, $event_time,
                $location, $max_participants, $status, $edit_id
            ]);
            $_SESSION['alert_message'] = 'แก้ไขกิจกรรมสำเร็จ';
        } else {
            // CREATE
            $stmt = $pdo->prepare("INSERT INTO events 
                (event_name, description, event_date, event_time, location, max_participants, status, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $event_name, $description, $event_date, $event_time,
                $location, $max_participants, $status, $_SESSION['user_id']
            ]);
            $_SESSION['alert_message'] = 'เพิ่มกิจกรรมสำเร็จ';
        }
        
        $_SESSION['alert_type'] = 'success';
        redirect('events.php');
        
    } catch (PDOException $e) {
        $_SESSION['alert_message'] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        $_SESSION['alert_type'] = 'danger';
    }
}

// ======================== DELETE ========================
if (isset($_GET['delete']) && $event_id > 0) {
    try {
        // ลบการลงทะเบียนก่อน (cascade)
        $stmt = $pdo->prepare("DELETE FROM events WHERE event_id = ?");
        $stmt->execute([$event_id]);
        
        $_SESSION['alert_message'] = 'ลบกิจกรรมสำเร็จ';
        $_SESSION['alert_type'] = 'success';
        
    } catch (PDOException $e) {
        $_SESSION['alert_message'] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        $_SESSION['alert_type'] = 'danger';
    }
    
    redirect('events.php');
}

// ======================== FETCH DATA ========================
if ($action === 'edit' && $event_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE event_id = ?");
    $stmt->execute([$event_id]);
    $edit_event = $stmt->fetch();
}

if ($action === 'list') {
    $stmt = $pdo->query("
        SELECT e.*, u.full_name as creator_name,
               (SELECT COUNT(*) FROM registrations WHERE event_id = e.event_id AND status = 'registered') as reg_count
        FROM events e
        LEFT JOIN users u ON e.created_by = u.user_id
        ORDER BY e.event_date DESC
    ");
    $events = $stmt->fetchAll();
}
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">แดชบอร์ด</a></li>
        <li class="breadcrumb-item active">จัดการกิจกรรม</li>
    </ol>
</nav>

<?php if ($action === 'list'): ?>
    <!-- ==================== LIST VIEW ==================== -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="bi bi-calendar-event"></i> จัดการกิจกรรม</h2>
                <a href="?action=add" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> เพิ่มกิจกรรมใหม่
                </a>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="eventsTable">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="25%">ชื่อกิจกรรม</th>
                            <th width="12%">วันที่</th>
                            <th width="15%">สถานที่</th>
                            <th width="10%">ผู้เข้าร่วม</th>
                            <th width="10%">สถานะ</th>
                            <th width="13%">ผู้สร้าง</th>
                            <th width="10%">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        foreach ($events as $event): 
                            $percentage = ($event['reg_count'] / $event['max_participants']) * 100;
                        ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($event['event_name']); ?></strong>
                                </td>
                                <td>
                                    <?php echo date('d/m/Y', strtotime($event['event_date'])); ?>
                                    <?php if ($event['event_time']): ?>
                                        <br><small><?php echo date('H:i', strtotime($event['event_time'])); ?> น.</small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($event['location']); ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="me-2"><?php echo $event['reg_count']; ?>/<?php echo $event['max_participants']; ?></span>
                                        <div class="progress flex-grow-1" style="height: 8px;">
                                            <div class="progress-bar <?php echo $percentage >= 80 ? 'bg-danger' : ($percentage >= 50 ? 'bg-warning' : 'bg-success'); ?>" 
                                                 style="width: <?php echo $percentage; ?>%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    $status_badge = [
                                        'open' => '<span class="badge bg-success">เปิดรับสมัคร</span>',
                                        'closed' => '<span class="badge bg-secondary">ปิดรับสมัคร</span>',
                                        'cancelled' => '<span class="badge bg-danger">ยกเลิก</span>'
                                    ];
                                    echo $status_badge[$event['status']] ?? '';
                                    ?>
                                </td>
                                <td>
                                    <small><?php echo htmlspecialchars($event['creator_name']); ?></small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="?action=edit&id=<?php echo $event['event_id']; ?>" 
                                           class="btn btn-warning" 
                                           title="แก้ไข">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="?delete=1&id=<?php echo $event['event_id']; ?>" 
                                           class="btn btn-danger" 
                                           title="ลบ"
                                           onclick="return confirm('ยืนยันการลบกิจกรรมนี้?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php elseif ($action === 'add' || $action === 'edit'): ?>
    <!-- ==================== ADD/EDIT FORM ==================== -->
    <div class="row">
        <div class="col-12">
            <h2>
                <i class="bi bi-<?php echo $action === 'edit' ? 'pencil' : 'plus-circle'; ?>"></i> 
                <?php echo $action === 'edit' ? 'แก้ไขกิจกรรม' : 'เพิ่มกิจกรรมใหม่'; ?>
            </h2>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="">
                        <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="event_id" value="<?php echo $edit_event['event_id']; ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label for="event_name" class="form-label">ชื่อกิจกรรม <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control" 
                                   id="event_name" 
                                   name="event_name" 
                                   value="<?php echo htmlspecialchars($edit_event['event_name'] ?? ''); ?>" 
                                   required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">รายละเอียด</label>
                            <textarea class="form-control" 
                                      id="description" 
                                      name="description" 
                                      rows="4"><?php echo htmlspecialchars($edit_event['description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="event_date" class="form-label">วันที่จัดกิจกรรม <span class="text-danger">*</span></label>
                                <input type="date" 
                                       class="form-control" 
                                       id="event_date" 
                                       name="event_date" 
                                       value="<?php echo $edit_event['event_date'] ?? ''; ?>" 
                                       required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="event_time" class="form-label">เวลา</label>
                                <input type="time" 
                                       class="form-control" 
                                       id="event_time" 
                                       name="event_time" 
                                       value="<?php echo $edit_event['event_time'] ?? ''; ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="location" class="form-label">สถานที่ <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control" 
                                   id="location" 
                                   name="location" 
                                   value="<?php echo htmlspecialchars($edit_event['location'] ?? ''); ?>" 
                                   required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="max_participants" class="form-label">จำนวนผู้เข้าร่วมสูงสุด</label>
                                <input type="number" 
                                       class="form-control" 
                                       id="max_participants" 
                                       name="max_participants" 
                                       value="<?php echo $edit_event['max_participants'] ?? 100; ?>" 
                                       min="1">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">สถานะ</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="open" <?php echo ($edit_event['status'] ?? '') === 'open' ? 'selected' : ''; ?>>เปิดรับสมัคร</option>
                                    <option value="closed" <?php echo ($edit_event['status'] ?? '') === 'closed' ? 'selected' : ''; ?>>ปิดรับสมัคร</option>
                                    <option value="cancelled" <?php echo ($edit_event['status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>ยกเลิก</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" name="save_event" class="btn btn-primary">
                                <i class="bi bi-save"></i> บันทึก
                            </button>
                            <a href="events.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> ยกเลิก
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>