<?php
/**
 * หน้าสมัครสมาชิกสำหรับนักศึกษา
 */

$page_title = 'สมัครสมาชิก';
require_once 'includes/header.php';

// ถ้า login แล้วให้ redirect
if (is_logged_in()) {
    redirect('index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // รับข้อมูลจาก form
    $student_id = sanitize_input($_POST['student_id'] ?? '');
    $full_name = sanitize_input($_POST['full_name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $department = sanitize_input($_POST['department'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($student_id) || empty($full_name) || empty($email) || empty($password)) {
        $error = 'กรุณากรอกข้อมูลให้ครบถ้วน';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'รูปแบบอีเมลไม่ถูกต้อง';
    } elseif (strlen($password) < 6) {
        $error = 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร';
    } elseif ($password !== $confirm_password) {
        $error = 'รหัสผ่านไม่ตรงกัน';
    } else {
        try {
            // ตรวจสอบว่ารหัสนักศึกษาหรืออีเมลซ้ำหรือไม่
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE student_id = ? OR email = ?");
            $stmt->execute([$student_id, $email]);
            
            if ($stmt->fetch()) {
                $error = 'รหัสนักศึกษาหรืออีเมลนี้มีในระบบแล้ว';
            } else {
                // Hash รหัสผ่าน
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert ข้อมูลผู้ใช้ใหม่
                $insert_stmt = $pdo->prepare("INSERT INTO users 
                    (student_id, full_name, email, password_hash, phone, department, role) 
                    VALUES (?, ?, ?, ?, ?, ?, 'student')");
                
                $insert_stmt->execute([
                    $student_id,
                    $full_name,
                    $email,
                    $password_hash,
                    $phone,
                    $department
                ]);
                
                // สมัครสมาชิกสำเร็จ
                $_SESSION['alert_message'] = 'สมัครสมาชิกสำเร็จ! กรุณาเข้าสู่ระบบ';
                $_SESSION['alert_type'] = 'success';
                redirect('login.php');
            }
            
        } catch (PDOException $e) {
            $error = 'เกิดข้อผิดพลาดในระบบ กรุณาลองใหม่อีกครั้ง';
            error_log("Registration error: " . $e->getMessage());
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-success text-white text-center">
                <h4><i class="bi bi-person-plus"></i> สมัครสมาชิก</h4>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" id="registerForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="student_id" class="form-label">
                                <i class="bi bi-card-text"></i> รหัสนักศึกษา <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="student_id" 
                                   name="student_id" 
                                   value="<?php echo htmlspecialchars($student_id ?? ''); ?>"
                                   required 
                                   placeholder="เช่น 6501234567">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="full_name" class="form-label">
                                <i class="bi bi-person"></i> ชื่อ-นามสกุล <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="full_name" 
                                   name="full_name" 
                                   value="<?php echo htmlspecialchars($full_name ?? ''); ?>"
                                   required
                                   placeholder="เช่น สมชาย ใจดี">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="bi bi-envelope"></i> อีเมล <span class="text-danger">*</span>
                        </label>
                        <input type="email" 
                               class="form-control" 
                               id="email" 
                               name="email" 
                               value="<?php echo htmlspecialchars($email ?? ''); ?>"
                               required
                               placeholder="example@student.ac.th">
                        <small class="text-muted">ใช้อีเมลมหาวิทยาลัย</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">
                                <i class="bi bi-telephone"></i> เบอร์โทรศัพท์
                            </label>
                            <input type="tel" 
                                   class="form-control" 
                                   id="phone" 
                                   name="phone" 
                                   value="<?php echo htmlspecialchars($phone ?? ''); ?>"
                                   placeholder="0812345678">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="department" class="form-label">
                                <i class="bi bi-building"></i> สาขา/คณะ
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="department" 
                                   name="department" 
                                   value="<?php echo htmlspecialchars($department ?? ''); ?>"
                                   placeholder="เช่น วิศวกรรมคอมพิวเตอร์">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">
                                <i class="bi bi-lock"></i> รหัสผ่าน <span class="text-danger">*</span>
                            </label>
                            <input type="password" 
                                   class="form-control" 
                                   id="password" 
                                   name="password" 
                                   required
                                   minlength="6">
                            <small class="text-muted">อย่างน้อย 6 ตัวอักษร</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="confirm_password" class="form-label">
                                <i class="bi bi-lock-fill"></i> ยืนยันรหัสผ่าน <span class="text-danger">*</span>
                            </label>
                            <input type="password" 
                                   class="form-control" 
                                   id="confirm_password" 
                                   name="confirm_password" 
                                   required
                                   minlength="6">
                        </div>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="terms" required>
                        <label class="form-check-label" for="terms">
                            ฉันยอมรับ <a href="#">ข้อกำหนดและเงื่อนไข</a>
                        </label>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="bi bi-person-plus"></i> สมัครสมาชิก
                        </button>
                        <a href="login.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> กลับไปเข้าสู่ระบบ
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Client-side validation
document.getElementById('registerForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (password !== confirmPassword) {
        e.preventDefault();
        alert('รหัสผ่านไม่ตรงกัน');
        return false;
    }
    
    if (password.length < 6) {
        e.preventDefault();
        alert('รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร');
        return false;
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>