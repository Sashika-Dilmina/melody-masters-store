<?php
// pages/profile-edit.php
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
        set_flash_message('error', 'Full name and email are required.');
    } elseif ($new_password && $new_password !== $confirm_password) {
        set_flash_message('error', 'New passwords do not match.');
    } elseif ($new_password && strlen($new_password) < 8) {
        set_flash_message('error', 'New password must be at least 8 characters.');
    } else {
        $existing = fetch_one("SELECT id FROM users WHERE email = ? AND id != ?", "si", [$email, $user_id]);
        
        if ($existing) {
             set_flash_message('error', 'Email already used by another account.');
        } else {
             if (!empty($new_password)) {
                 $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                 execute_query("UPDATE users SET full_name=?, email=?, password_hash=? WHERE id=?", "sssi", [$full_name, $email, $hashed_password, $user_id]);
             } else {
                 execute_query("UPDATE users SET full_name=?, email=? WHERE id=?", "ssi", [$full_name, $email, $user_id]);
             }
             
             $_SESSION['username'] = $full_name;
             set_flash_message('success', 'Profile updated.');
             header('Location: ' . $base_url . '/pages/account.php');
             exit;
        }
    }
}
?>

<div style="max-width: 600px; margin: 0 auto; padding-top: 20px;">
    <div style="margin-bottom: 30px; border-bottom: 1px solid #e2e8f0; padding-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
        <h2 style="margin: 0;">Account Settings</h2>
        <a href="account.php" style="font-size: 0.9rem; color: #64748b;">Return to Overview</a>
    </div>

    <div style="background: #fff; padding: 30px; border: 1px solid #e2e8f0; border-radius: 8px;">
        <form method="POST" action="">
            <?php echo csrf_field(); ?>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 500; margin-bottom: 8px;">Display Name</label>
                <input type="text" name="full_name" required value="<?php echo h($user['full_name']); ?>" 
                       style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.95rem;">
            </div>
            
            <div style="margin-bottom: 30px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 500; margin-bottom: 8px;">Email Identifier</label>
                <input type="email" name="email" required value="<?php echo h($user['email']); ?>" 
                       style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.95rem;">
            </div>
            
            <div style="background: #f8fafc; padding: 20px; border-radius: 6px; margin-bottom: 30px; border: 1px solid #e2e8f0;">
                <h4 style="margin: 0 0 10px; font-size: 0.9rem;">Security Credentials</h4>
                <p style="font-size: 0.8rem; color: #64748b; margin-bottom: 15px;">Modify only if you wish to change your current password.</p>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-size: 0.8rem; margin-bottom: 5px;">New Password</label>
                    <input type="password" name="new_password" 
                           style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.9rem;">
                </div>
                
                <div style="margin-bottom: 0;">
                    <label style="display: block; font-size: 0.8rem; margin-bottom: 5px;">Verify New Password</label>
                    <input type="password" name="confirm_password" 
                           style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.9rem;">
                </div>
            </div>
            
            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn" style="flex: 2; padding: 12px; border-radius: 4px; font-weight: bold;">Update Profile</button>
                <a href="account.php" class="btn" 
                   style="flex: 1; background: #fff; color: #64748b; border: 1px solid #cbd5e1; padding: 11px; border-radius: 4px; text-align: center; font-size: 0.9rem;">Discard</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
