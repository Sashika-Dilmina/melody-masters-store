<?php
// pages/register.php
require_once __DIR__ . '/../includes/header.php';

if (is_logged_in()) {
    header('Location: ' . $base_url . '/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    $full_name = sanitize_input($_POST['full_name'] ?? '');
    $email     = strtolower(trim($_POST['email'] ?? ''));
    $password  = $_POST['password'] ?? '';
    
    if (empty($full_name) || empty($email) || empty($password)) {
        set_flash_message('error', 'All fields are required.');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        set_flash_message('error', 'Invalid email address provided.');
    } elseif (strlen($password) < 8) {
        set_flash_message('error', 'Password must be at least 8 characters.');
    } else {
        $existing = fetch_one("SELECT id FROM users WHERE email = ?", "s", [$email]);
        if ($existing) {
             set_flash_message('error', 'Email already exists in our system.');
        } else {
             $hashed_password = password_hash($password, PASSWORD_DEFAULT);
             $stmt = execute_query(
                 "INSERT INTO users (full_name, email, password_hash, role, is_active) VALUES (?, ?, ?, 'customer', 1)", 
                 "sss", [$full_name, $email, $hashed_password]
             );
             if ($stmt) {
                 set_flash_message('success', 'Account created successfully.');
                 header('Location: ' . $base_url . '/pages/login.php');
                 exit;
             }
        }
    }
}
?>

<div style="display: flex; min-height: 80vh; align-items: center; justify-content: center; padding: 40px 20px;">
    <div style="width: 100%; max-width: 440px; background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border: 1px solid rgba(0, 0, 0, 0.05); border-radius: 20px; padding: 50px 40px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.02);">
        <div style="text-align: center; margin-bottom: 40px;">
            <h2 style="font-size: 1.75rem; font-weight: 800; color: var(--primary-color); letter-spacing: -0.02em; margin-bottom: 10px;">Create Profile</h2>
            <p style="color: #64748b; font-size: 0.95rem;">Join the Melody Masters community</p>
        </div>
        
        <form method="POST" action="">
            <?php echo csrf_field(); ?>
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 8px;">Full Name</label>
                <input type="text" name="full_name" required placeholder="John Doe"
                       style="width: 100%; padding: 12px 16px; border: 1px solid #e2e8f0; border-radius: 12px; font-size: 0.95rem; background: #fff; transition: all 0.2s ease;"
                       value="<?php echo h($_POST['full_name'] ?? ''); ?>">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 8px;">Email</label>
                <input type="email" name="email" required placeholder="name@example.com"
                       style="width: 100%; padding: 12px 16px; border: 1px solid #e2e8f0; border-radius: 12px; font-size: 0.95rem; background: #fff; transition: all 0.2s ease;"
                       value="<?php echo h($_POST['email'] ?? ''); ?>">
            </div>
            
            <div style="margin-bottom: 30px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 8px;">Password</label>
                <input type="password" name="password" required placeholder="Min. 8 characters"
                       style="width: 100%; padding: 12px 16px; border: 1px solid #e2e8f0; border-radius: 12px; font-size: 0.95rem; background: #fff; transition: all 0.2s ease;">
                <p style="font-size: 0.75rem; color: #94a3b8; margin-top: 8px; font-weight: 500;">Complexity requirement: 8 characters minimum.</p>
            </div>
            
            <button type="submit" class="btn" style="width: 100%; padding: 14px; border-radius: 12px; font-weight: 700; font-size: 1rem; background: var(--primary-color); color: #fff; border: none; cursor: pointer; transition: all 0.2s ease;">Register</button>
        </form>
        
        <div style="margin-top: 35px; text-align: center; font-size: 0.95rem; color: #64748b;">
            Already Registered? <a href="login.php" style="color: var(--secondary-color); font-weight: 700; text-decoration: none;">Sign In</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>