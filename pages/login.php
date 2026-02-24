<?php
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
            
            set_flash_message('success', 'Welcome back, ' . h($user['full_name']) . '!');
            if ($user['role'] === 'admin') {
                header('Location: ' . $base_url . '/admin/dashboard.php');
            } elseif ($user['role'] === 'staff') {
                header('Location: ' . $base_url . '/staff/dashboard.php');
            } else {
                header('Location: ' . $base_url . '/pages/account.php');
            }
            exit;
        } else {
            set_flash_message('error', 'Invalid email or password.');
        }
    }
}
?>

<h2>Login</h2>
<form method="POST" action="">
    <?php echo csrf_field(); ?>
    <div style="margin-bottom: 15px;">
        <label>Email</label><br>
        <input type="email" name="email" required>
    </div>
    <div style="margin-bottom: 15px;">
        <label>Password</label><br>
        <input type="password" name="password" required>
    </div>
    <button type="submit" class="btn">Login</button>
</form>
<p style="margin-top: 15px;">Don't have an account? <a href="<?php echo $base_url; ?>/pages/register.php">Register here</a>.</p>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>