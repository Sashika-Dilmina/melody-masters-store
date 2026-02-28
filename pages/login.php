<?php
// pages/login.php
require_once __DIR__ . '/../includes/header.php';

if (is_logged_in()) {
    header('Location: ' . $base_url . '/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        set_flash_message('error', 'Email and password are required.');
    } else {
        $user = fetch_one("SELECT * FROM users WHERE email = ?", "s", [$email]);
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['username'] = $user['full_name'];
            
            set_flash_message('success', 'Login successful.');
            
            $redirect = $base_url . '/index.php';
            if ($user['role'] === 'admin') $redirect = $base_url . '/admin/dashboard.php';
            elseif ($user['role'] === 'staff') $redirect = $base_url . '/staff/dashboard.php';
            
            header('Location: ' . $redirect);
            exit;
        } else {
            set_flash_message('error', 'Invalid email or password.');
        }
    }
}
?>

<div style="display: flex; min-height: 80vh; align-items: center; justify-content: center; padding: 40px 20px;">
    <div style="width: 100%; max-width: 420px; background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border: 1px solid rgba(0, 0, 0, 0.05); border-radius: 20px; padding: 50px 40px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.02);">
        <div style="text-align: center; margin-bottom: 40px;">
            <h2 style="font-size: 1.75rem; font-weight: 800; color: var(--primary-color); letter-spacing: -0.02em; margin-bottom: 10px;">Welcome Back</h2>
            <p style="color: #64748b; font-size: 0.95rem;">Enter your credentials to continue</p>
        </div>
        
        <form method="POST" action="">
            <?php echo csrf_field(); ?>
            <div style="margin-bottom: 24px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 8px;">Email</label>
                <input type="email" name="email" required placeholder="name@example.com"
                       style="width: 100%; padding: 12px 16px; border: 1px solid #e2e8f0; border-radius: 12px; font-size: 0.95rem; background: #fff; transition: all 0.2s ease;"
                       value="<?php echo h($_POST['email'] ?? ''); ?>">
            </div>
            
            <div style="margin-bottom: 30px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 8px;">Password</label>
                <input type="password" name="password" required placeholder="••••••••"
                       style="width: 100%; padding: 12px 16px; border: 1px solid #e2e8f0; border-radius: 12px; font-size: 0.95rem; background: #fff; transition: all 0.2s ease;">
            </div>
            
            <button type="submit" class="btn" style="width: 100%; padding: 14px; border-radius: 12px; font-weight: 700; font-size: 1rem; background: var(--primary-color); color: #fff; border: none; cursor: pointer; transition: all 0.2s ease;">LogIn</button>
        </form>
        
        <div style="margin-top: 35px; text-align: center; font-size: 0.95rem; color: #64748b;">
            Not registered yet? <a href="register.php" style="color: var(--secondary-color); font-weight: 700; text-decoration: none;">Create an Account</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>