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

<div class="auth-page-wrapper">
    <div class="auth-container">
        <!-- Brand Panel -->
        <div class="brand-panel">
            <h1>Melody Masters</h1>
            <p>Shop the finest instruments & digital sheet music online.</p>
        </div>

        <!-- Form Panel -->
        <div class="form-panel">
            <h2>Sign In</h2>
            
            <form method="POST" action="">
                <?php echo csrf_field(); ?>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" name="email" id="email" required placeholder="name@example.com" class="auth-input" value="<?php echo h($_POST['email'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-box">
                        <input type="password" name="password" id="password" required placeholder="Enter password" class="auth-input">
                        <button type="button" class="toggle-btn" onclick="togglePass('password')">Show</button>
                    </div>
                </div>

                <button type="submit" class="btn-primary">Sign In</button>
            </form>

            <div class="auth-footer">
                <p>Don't have an account? <a href="register.php">Create Account</a></p>
                <a href="#" class="forgot-link">Forgot Password?</a>
            </div>
        </div>
    </div>
</div>

<script>
function togglePass(id) {
    var input = document.getElementById(id);
    var btn = input.nextElementSibling;
    if (input.type === "password") {
        input.type = "text";
        btn.textContent = "Hide";
    } else {
        input.type = "password";
        btn.textContent = "Show";
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>