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

<div class="auth-page-wrapper">
    <div class="auth-container">
        <!-- Brand Panel -->
        <div class="brand-panel">
            <h1>Melody Masters</h1>
            <p>Shop for musical instruments and digital masterclasses.</p>
        </div>

        <!-- Form Panel -->
        <div class="form-panel">
            <h2>Create Account</h2>
            
            <form method="POST" action="">
                <?php echo csrf_field(); ?>
                
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" name="full_name" id="full_name" required placeholder="Sashika Dilmina" class="auth-input" value="<?php echo h($_POST['full_name'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" name="email" id="email" required placeholder="name@example.com" class="auth-input" value="<?php echo h($_POST['email'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="password">Create Password</label>
                    <div class="password-box">
                        <input type="password" name="password" id="password" required placeholder="Min. 8 characters" class="auth-input">
                        <button type="button" class="toggle-btn" onclick="togglePass('password')">Show</button>
                    </div>
                    <p style="font-size: 11px; color: #888; margin-top: 5px;">Use 8+ characters.</p>
                </div>

                <button type="submit" class="btn-primary">Register Account</button>
            </form>

            <div class="auth-footer">
                <p>Already have an account? <a href="login.php">Sign In Instead</a></p>
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