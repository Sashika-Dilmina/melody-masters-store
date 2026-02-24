<?php
require_once __DIR__ . '/../includes/header.php';
require_login();

$user_id = get_current_user_id();
$user = fetch_one("SELECT * FROM users WHERE id = ?", "i", [$user_id]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    
    $full_name = sanitize_input($_POST['full_name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($full_name) || empty($email)) {
        set_flash_message('error', 'Username and email are required.');
    } elseif ($new_password && $new_password !== $confirm_password) {
        set_flash_message('error', 'New passwords do not match.');
    } else {
        // Check if email or username already taken by another user
        $existing = fetch_one("SELECT id FROM users WHERE (email = ? OR full_name = ?) AND id != ?", "ssi", [$email, $full_name, $user_id]);
        
        if ($existing) {
             set_flash_message('error', 'Email or username already used by another account.');
        } else {
             if (!empty($new_password)) {
                 $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                 execute_query("UPDATE users SET full_name=?, email=?, password_hash=? WHERE id=?", "sssi", [$full_name, $email, $hashed_password, $user_id]);
             } else {
                 execute_query("UPDATE users SET full_name=?, email=? WHERE id=?", "ssi", [$full_name, $email, $user_id]);
             }
             
             // Update session username if changed
             $_SESSION['username'] = $full_name;
             
             set_flash_message('success', 'Profile updated successfully.');
             header('Location: ' . $base_url . '/pages/account.php');
             exit;
        }
    }
}
?>

<h2>Edit Profile</h2>
<div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); max-width: 500px;">
    <form method="POST" action="">
        <?php echo csrf_field(); ?>
        
        <div style="margin-bottom: 15px;">
            <label>Full Name</label><br>
            <input type="text" name="full_name" required value="<?php echo h($user['full_name']); ?>" style="width: 100%; padding: 8px;">
        </div>
        
        <div style="margin-bottom: 15px;">
            <label>Email</label><br>
            <input type="email" name="email" required value="<?php echo h($user['email']); ?>" style="width: 100%; padding: 8px;">
        </div>
        
        <hr style="margin: 20px 0; border: 0; border-top: 1px solid #eee;">
        <p style="font-size: 0.9em; color: #666; margin-bottom: 15px;">Leave passwords blank if you do not wish to change your current password.</p>
        
        <div style="margin-bottom: 15px;">
            <label>New Password</label><br>
            <input type="password" name="new_password" style="width: 100%; padding: 8px;">
        </div>
        
        <div style="margin-bottom: 15px;">
            <label>Confirm New Password</label><br>
            <input type="password" name="confirm_password" style="width: 100%; padding: 8px;">
        </div>
        
        <div style="display: flex; gap: 10px; margin-top: 20px;">
            <button type="submit" class="btn" style="flex: 1;">Save Changes</button>
            <a href="<?php echo $base_url; ?>/pages/account.php" class="btn" style="background: #e2e8f0; color: #1e293b; text-align: center; flex: 1;">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
