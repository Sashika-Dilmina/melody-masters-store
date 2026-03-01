<?php
// pages/profile-edit.php
require_once __DIR__ . '/../includes/header.php';
require_login();

$user_id = get_current_user_id();
$user = fetch_one("SELECT * FROM users WHERE id = ?", "i", [$user_id]);
$is_admin = (get_current_user_role() === 'admin');

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
             set_flash_message('success', 'Profile updated successfully.');
             header('Location: ' . $base_url . '/pages/account.php');
             exit;
        }
    }
}
?>

<div class="mb-5 reveal">
    <h1 class="title" style="font-size: 2rem;">Account Settings</h1>
    <p class="muted">Manage your personal information and security preferences.</p>
</div>

<div class="dashboard-layout reveal" style="animation-delay: 0.1s;">
    <!-- Sidebar -->
    <div class="stack">
        <div class="card" style="padding: 1.5rem;">
            <h3 class="mb-4" style="font-size: 1.1rem;">Account Settings</h3>
            <div class="sidebar-nav">
                <a href="<?php echo $base_url; ?>/pages/account.php" class="sidebar-link">
                    <svg style="margin-right: 12px;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                    Dashboard Overview
                </a>
                <?php if (!$is_admin): ?>
                    <a href="orders.php" class="sidebar-link">
                        <svg style="margin-right: 12px;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                        Order History
                    </a>
                    <a href="downloads.php" class="sidebar-link">
                        <svg style="margin-right: 12px;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        Digital Library
                    </a>
                <?php endif; ?>
                <a href="profile-edit.php" class="sidebar-link active">
                    <svg style="margin-right: 12px;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    Personal Details
                </a>
                <div class="divider" style="margin: 1rem 0;"></div>
                <a href="logout.php" class="sidebar-link" style="color: var(--error);">
                    <svg style="margin-right: 12px;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                    Logout Session
                </a>
            </div>
        </div>
    </div>

    <!-- Main Form -->
    <div class="stack">
        <form method="POST" action="">
            <?php echo csrf_field(); ?>
            <div class="card mb-4" style="padding: 2.5rem;">
                <h3 class="mb-5" style="font-size: 1.25rem;">Personal Information</h3>
                
                <div class="grid grid-2 mb-4">
                    <div>
                        <label class="mb-2" style="font-weight: 600;">Full Name</label>
                        <input type="text" name="full_name" required value="<?php echo h($user['full_name']); ?>" class="input">
                    </div>
                    <div>
                        <label class="mb-2" style="font-weight: 600;">Email Address</label>
                        <input type="email" name="email" required value="<?php echo h($user['email']); ?>" class="input">
                    </div>
                </div>
            </div>

            <div class="card mb-5" style="padding: 2.5rem;">
                <div class="row mb-5" style="gap: 12px;">
                    <div style="width: 32px; height: 32px; background: rgba(245, 158, 11, 0.1); color: #F59E0B; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    </div>
                    <h3 class="m-0" style="font-size: 1.25rem;">Security & Password</h3>
                </div>

                <p class="muted text-sm mb-4">Leave the password fields blank if you do not wish to change your current password.</p>

                <div class="grid grid-2">
                    <div>
                        <label class="mb-2" style="font-weight: 600;">New Password</label>
                        <input type="password" name="new_password" placeholder="••••••••" class="input">
                    </div>
                    <div>
                        <label class="mb-2" style="font-weight: 600;">Confirm New Password</label>
                        <input type="password" name="confirm_password" placeholder="••••••••" class="input">
                    </div>
                </div>
            </div>

            <div class="row" style="gap: 1.5rem; justify-content: flex-end;">
                <a href="account.php" class="btn btn-outline" style="padding: 1rem 2rem;">Discard Changes</a>
                <button type="submit" class="btn btn-primary" style="padding: 1rem 2.5rem;">Save Profile Settings</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
