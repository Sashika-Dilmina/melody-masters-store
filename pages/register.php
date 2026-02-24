<?php
require_once __DIR__ . '/../includes/header.php';

if (is_logged_in()) {
    header('Location: ' . $base_url . '/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    $full_name = sanitize_input($_POST['full_name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($full_name) || empty($email) || empty($password)) {
        set_flash_message('error', 'All fields are required.');
    } else {
        $existing = fetch_one("SELECT id FROM users WHERE email = ?", "s", [$email]);
        if ($existing) {
             set_flash_message('error', 'Email already registered.');
        } else {
             $hashed_password = password_hash($password, PASSWORD_DEFAULT);
             $stmt = execute_query("INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, 'customer')", "sss", [$full_name, $email, $hashed_password]);
             if ($stmt) {
                 set_flash_message('success', 'Registration successful. Please log in.');
                 header('Location: ' . $base_url . '/pages/login.php');
                 exit;
             }
        }
    }
}
?>

<h2>Register</h2>
<form method="POST" action="">
    <?php echo csrf_field(); ?>
    <div style="margin-bottom: 15px;">
        <label>Full Name</label><br>
        <input type="text" name="full_name" required>
    </div>
    <div style="margin-bottom: 15px;">
        <label>Email</label><br>
        <input type="email" name="email" required>
    </div>
    <div style="margin-bottom: 15px;">
        <label>Password</label><br>
        <input type="password" name="password" required>
    </div>
    <button type="submit" class="btn">Register</button>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>