<?php
require_once __DIR__ . '/../includes/header.php';
require_login();

$user_id = get_current_user_id();
$user = fetch_one("SELECT * FROM users WHERE id = ?", "i", [$user_id]);

?>
<h2>My Account</h2>
<div style="display: flex; flex-wrap: wrap; gap: 20px;">
    <!-- Profile Card -->
    <div style="flex: 1; min-width: 300px; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
        <h3 style="margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px;">My Profile</h3>
        <p style="margin-bottom: 10px;"><strong>Username:</strong> <?php echo h($user['full_name']); ?></p>
        <p style="margin-bottom: 10px;"><strong>Email:</strong> <?php echo h($user['email']); ?></p>
        <p style="margin-bottom: 10px;"><strong>Role:</strong> <?php echo ucfirst(h($user['role'])); ?></p>
        
        <div style="margin-top: 20px;">
            <a href="profile-edit.php" class="btn" style="width: 100%; text-align: center;">Edit Profile</a>
        </div>
    </div>
    
    <!-- Quick Links Card -->
    <div style="flex: 1; min-width: 300px; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
        <h3 style="margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px;">Quick Links</h3>
        <ul style="list-style: none; padding: 0;">
            <li style="margin-bottom: 10px;">
                <a href="<?php echo $base_url; ?>/pages/orders.php" style="display: block; padding: 10px; background: #f8fafc; border-radius: 4px; color: var(--text-color); font-weight: 500;"><i class="fa-solid fa-box"></i> My Orders</a>
            </li>
            <li style="margin-bottom: 10px;">
                <a href="<?php echo $base_url; ?>/pages/downloads.php" style="display: block; padding: 10px; background: #f8fafc; border-radius: 4px; color: var(--text-color); font-weight: 500;"><i class="fa-solid fa-download"></i> My Digital Downloads</a>
            </li>
        </ul>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>