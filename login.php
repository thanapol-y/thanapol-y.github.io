<?php
/**
 * หน้า Login สำหรับนักศึกษาและ admin
 */

$page_title = 'เข้าสู่ระบบ';
require_once 'includes/header.php';

// ถ้า login แล้วให้ redirect ไปหน้าหลัก
if (is_logged_in()) {
    redirect('index.php');
}

$error = '';

// ตรวจสอบการ submit form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // รับค่าจาก form และทำความสะอาด
    $student_id = sanitize_input($_POST['student_id'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validation พื้นฐาน
    if (empty($student_id) || empty($password)) {
        $error = 'กรุณากรอกรหัสนักศึกษาและรหัสผ่าน';
    } else {
        try {
            // ค้นหาผู้ใช้จากรหัสนักศึกษา โดยใช้ Prepared Statement
            $stmt = $pdo->prepare("SELECT user_id, student_id, full_name, email, password_hash, role 
                                   FROM users 
                                   WHERE student_id = ? 
                                   LIMIT 1");
            $stmt->execute([$student_id]);
            $user = $stmt->fetch();
            
            // ตรวจสอบรหัสผ่าน
            if ($user && password_verify($password, $user['password_hash'])) {
                // Login สำเร็จ - สร้าง session
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                // Regenerate session ID เพื่อป้องกัน session fixation
                session_regenerate_id(true);
                
                // บันทึก log การ login (optional)
                $log_stmt = $pdo->prepare("INSERT INTO admin_logs (admin_id, action, description, ip_address) 
                                           VALUES (?, 'LOGIN', 'User logged in', ?)");
                $log_stmt->execute([$user['user_id'], $_SERVER['REMOTE_ADDR']]);
                
                // Redirect ตามบทบาท
                if ($user['role'] === 'admin') {
                    $_SESSION['alert_message'] = 'ยินดีต้อนรับ Admin: ' . $user['full_name'];
                    $_SESSION['alert_type'] = 'success';
                    redirect('admin/index.php');
                } else {
                    $_SESSION['alert_message'] = 'เข้าสู่ระบบสำเร็จ';
                    $_SESSION['alert_type'] = 'success';
                    redirect('events.php');
                }
            } else {
                // Login ไม่สำเร็จ
                $error = 'อีเมลหรือรหัสผ่านไม่ถูกต้อง';
                
                // บันทึก log การพยายาม login ที่ล้มเหลว (optional)
                $log_stmt = $pdo->prepare("INSERT INTO admin_logs (admin_id, action, description, ip_address) 
                                           VALUES (0, 'FAILED_LOGIN', ?, ?)");
                $log_stmt->execute(["Failed login attempt for email: $email", $_SERVER['REMOTE_ADDR']]);
            }
            
        } catch (PDOException $e) {
            $error = 'เกิดข้อผิดพลาดในระบบ กรุณาลองใหม่อีกครั้ง';
            error_log("Login error: " . $e->getMessage());
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white text-center">
                <h4><i class="bi bi-box-arrow-in-right"></i> เข้าสู่ระบบ</h4>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" id="loginForm">
                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="bi bi-envelope"></i> อีเมล
                        </label>
                        <input type="email" 
                               class="form-control" 
                               id="email" 
                               name="email" 
                               value="<?php echo htmlspecialchars($email ?? ''); ?>"
                               required 
                               autofocus>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="bi bi-lock"></i> รหัสผ่าน
                        </label>
                        <input type="password" 
                               class="form-control" 
                               id="password" 
                               name="password" 
                               required>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember">
                        <label class="form-check-label" for="remember">
                            จดจำการเข้าสู่ระบบ
                        </label>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-box-arrow-in-right"></i> เข้าสู่ระบบ
                        </button>
                    </div>
                </form>
                
                <hr>
                
                <div class="text-center">
                    <p class="mb-0">ยังไม่มีบัญชี? 
                        <a href="register.php" class="text-decoration-none">
                            สมัครสมาชิก
                        </a>
                    </p>
                </div>
                
                <!-- ข้อมูลสำหรับทดสอบ -->
                <div class="alert alert-info mt-3 small">
                    <strong>สำหรับทดสอบ:</strong><br>
                    Admin: admin@university.ac.th / admin123<br>
                    Student: somchai@student.ac.th / student123
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Client-side validation
document.getElementById('loginForm').addEventListener('submit', function(e) {
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    
    if (!email || !password) {
        e.preventDefault();
        alert('กรุณากรอกอีเมลและรหัสผ่าน');
        return false;
    }
    
    // ตรวจสอบรูปแบบอีเมล
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        e.preventDefault();
        alert('กรุณากรอกอีเมลที่ถูกต้อง');
        return false;
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>