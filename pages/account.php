<?php
// pages/account.php – Customer Account Dashboard
require_once __DIR__ . '/../includes/header.php';
require_login();

$user_id = get_current_user_id();
$user = fetch_one("SELECT * FROM users WHERE id = ?", "i", [$user_id]);
$is_admin = (get_current_user_role() === 'admin');

// Get summary stats (only for customers/staff who might shop)
$order_count = 0;
$download_count = 0;
$recent_orders = [];

if (!$is_admin) {
    $order_count = fetch_one("SELECT COUNT(*) as count FROM orders WHERE user_id = ?", "i", [$user_id])['count'];
    $download_count = fetch_one("
        SELECT COUNT(DISTINCT product_id) as count 
        FROM order_items oi 
        JOIN orders o ON oi.order_id = o.id 
        JOIN products p ON oi.product_id = p.id 
        WHERE o.user_id = ? AND p.product_type = 'digital' AND o.status != 'cancelled'
    ", "i", [$user_id])['count'];

    $recent_orders = fetch_all("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 3", "i", [$user_id]);
}
?>

<div style="margin-bottom: 30px; border-bottom: 1px solid #e2e8f0; padding-bottom: 20px;">
    <h2 style="margin: 0;">My Account</h2>
    <p style="color: #64748b; margin-top: 5px;">Welcome, <?php echo h($user['full_name']); ?></p>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px;">
    <?php if (!$is_admin): ?>
        <div style="background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; text-align: center;">
            <h4 style="color: #94a3b8; font-size: 0.8rem; text-transform: uppercase; margin-bottom: 5px;">Total Orders</h4>
            <p style="font-size: 1.5rem; font-weight: bold; margin: 0;"><?php echo $order_count; ?></p>
        </div>
        <div style="background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; text-align: center;">
            <h4 style="color: #94a3b8; font-size: 0.8rem; text-transform: uppercase; margin-bottom: 5px;">Digital Assets</h4>
            <p style="font-size: 1.5rem; font-weight: bold; margin: 0;"><?php echo $download_count; ?></p>
        </div>
    <?php endif; ?>
    
    <div style="background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; text-align: center;">
        <h4 style="color: #94a3b8; font-size: 0.8rem; text-transform: uppercase; margin-bottom: 5px;">Member Role</h4>
        <p style="font-size: 1.2rem; font-weight: bold; margin: 0; padding-top: 5px; color: var(--secondary-color);"><?php echo ucfirst(h($user['role'])); ?></p>
    </div>
    
    <?php if ($is_admin): ?>
        <div style="background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; text-align: center;">
            <h4 style="color: #94a3b8; font-size: 0.8rem; text-transform: uppercase; margin-bottom: 5px;">Access Level</h4>
            <p style="font-size: 1.1rem; font-weight: bold; margin: 0; padding-top: 5px; color: #10b981;">Full Administrative</p>
        </div>
    <?php endif; ?>
</div>

<div style="display: grid; grid-template-columns: <?php echo $is_admin ? '1fr' : '1fr 1fr'; ?>; gap: 30px; align-items: start;">
    
    <!-- Profile Section -->
    <div style="background: #fff; padding: 25px; border-radius: 8px; border: 1px solid #e2e8f0;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #f1f5f9; padding-bottom: 15px;">
            <h3 style="margin: 0; font-size: 1.1rem;">Profile Details</h3>
            <a href="profile-edit.php" style="font-size: 0.85rem; color: var(--secondary-color);">Edit Profile</a>
        </div>
        <div style="line-height: 2;">
            <div><span style="color: #64748b; font-size: 0.85rem;">Name:</span> <span style="font-weight: 500;"><?php echo h($user['full_name']); ?></span></div>
            <div><span style="color: #64748b; font-size: 0.85rem;">Email:</span> <span style="font-weight: 500;"><?php echo h($user['email']); ?></span></div>
        </div>
    </div>

    <?php if (!$is_admin): ?>
        <!-- Recent Orders (Customers only) -->
        <div style="background: #fff; padding: 25px; border-radius: 8px; border: 1px solid #e2e8f0;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #f1f5f9; padding-bottom: 15px;">
                <h3 style="margin: 0; font-size: 1.1rem;">Recent Orders</h3>
                <a href="orders.php" style="font-size: 0.85rem; color: var(--secondary-color);">All Orders</a>
            </div>
            
            <?php if (empty($recent_orders)): ?>
                <p style="color: #94a3b8; font-size: 0.9rem;">No orders found.</p>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <?php foreach ($recent_orders as $order): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px; background: #f8fafc; border-radius: 6px; font-size: 0.9rem;">
                        <div>
                            <div style="font-weight: bold;">Order #<?php echo $order['id']; ?></div>
                            <div style="font-size: 0.75rem; color: #94a3b8;"><?php echo date('d M Y', strtotime($order['created_at'])); ?></div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-weight: bold;">£<?php echo number_format($order['total_amount'], 2); ?></div>
                            <div style="font-size: 0.75rem; color: var(--secondary-color);"><?php echo ucfirst($order['status']); ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <!-- Administrative Panel Shortcut for Admins -->
        <div style="background: #f8fafc; padding: 30px; border-radius: 12px; border: 1px dashed #cbd5e1; text-align: center;">
            <h3 style="margin: 0 0 10px; font-size: 1.1rem; color: var(--primary-color);">Management Access</h3>
            <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 20px;">You are currently logged in as a store administrator. System-wide management tools are available in the Admin Panel.</p>
            <a href="<?php echo $base_url; ?>/admin/dashboard.php" class="btn" style="padding: 10px 25px; border-radius: 8px; font-weight: 700; font-size: 0.85rem;">Go to Admin Panel</a>
        </div>
    <?php endif; ?>
    
    <!-- Quick Links -->
    <div style="grid-column: 1 / -1; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
        <?php if (!$is_admin): ?>
            <a href="downloads.php" class="btn" style="background: #fff; color: var(--text-color); border: 1px solid #e2e8f0; padding: 15px; border-radius: 8px; text-decoration: none; text-align: center;">
                My Digital Downloads
            </a>
            <a href="orders.php" class="btn" style="background: #fff; color: var(--text-color); border: 1px solid #e2e8f0; padding: 15px; border-radius: 8px; text-decoration: none; text-align: center;">
                Order History
            </a>
        <?php endif; ?>
        
        <a href="profile-edit.php" class="btn" style="background: #fff; color: var(--text-color); border: 1px solid #e2e8f0; padding: 15px; border-radius: 8px; text-decoration: none; text-align: center;">
            Update Profile Settings
        </a>
        
        <a href="logout.php" class="btn" style="background: #fff; color: #ef4444; border: 1px solid #fee2e2; padding: 15px; border-radius: 8px; text-decoration: none; text-align: center;">
            Logout
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>