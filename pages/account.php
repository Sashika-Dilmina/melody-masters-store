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

<div class="mb-5 reveal">
    <h1 class="title" style="font-size: 2rem;">My Dashboard</h1>
    <p class="muted">Hello, <strong><?php echo h($user['full_name']); ?></strong>. Welcome back to your musical hub.</p>
</div>

<div class="grid grid-3 mb-5 reveal" style="animation-delay: 0.1s;">
    <?php if (!$is_admin): ?>
        <div class="card card-hover text-center" style="padding: 2rem; border-color: rgba(59, 130, 246, 0.2);">
            <div style="width: 40px; height: 40px; background: rgba(59, 130, 246, 0.1); color: var(--accent); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
            </div>
            <h4 class="text-xs muted mb-1" style="text-transform: uppercase; font-weight: 700;">Total Orders</h4>
            <p style="font-size: 2rem; font-weight: 800; color: var(--primary);"><?php echo $order_count; ?></p>
        </div>
        <div class="card card-hover text-center" style="padding: 2rem; border-color: rgba(20, 184, 166, 0.2);">
             <div style="width: 40px; height: 40px; background: rgba(20, 184, 166, 0.1); color: var(--teal); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
            </div>
            <h4 class="text-xs muted mb-1" style="text-transform: uppercase; font-weight: 700;">Digital Library</h4>
            <p style="font-size: 2rem; font-weight: 800; color: var(--primary);"><?php echo $download_count; ?></p>
        </div>
    <?php endif; ?>
    
    <div class="card card-hover text-center" style="padding: 2rem; border-color: rgba(11, 18, 32, 0.1);">
         <div style="width: 40px; height: 40px; background: rgba(11, 18, 32, 0.05); color: var(--primary); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
        </div>
        <h4 class="text-xs muted mb-1" style="text-transform: uppercase; font-weight: 700;">Membership Status</h4>
        <p style="font-size: 1.25rem; font-weight: 800; color: var(--accent);"><?php echo ucfirst(h($user['role'])); ?></p>
    </div>
</div>

<div class="dashboard-layout reveal" style="animation-delay: 0.2s;">
    <!-- Sidebar / Quick Links -->
    <div class="stack">
        <div class="card" style="padding: 1.5rem;">
            <h3 class="mb-4" style="font-size: 1.1rem;">Account Settings</h3>
            <div class="sidebar-nav">
                <a href="<?php echo $base_url; ?>/pages/account.php" class="sidebar-link active">
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
                <a href="profile-edit.php" class="sidebar-link">
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
    
    <!-- Main Content Area -->
    <div class="stack">
        <div class="card">
            <div class="space-between mb-5">
                <h3 class="m-0" style="font-size: 1.25rem;">Profile Information</h3>
                <a href="profile-edit.php" class="btn btn-outline" style="padding: 0.5rem 1rem; font-size: 0.85rem;">Edit Details</a>
            </div>
            <div class="grid grid-2" style="gap: 2rem;">
                <div>
                    <label class="muted mb-1" style="font-weight: 500;">Full Name</label>
                    <p style="font-weight: 700; font-size: 1.1rem;"><?php echo h($user['full_name']); ?></p>
                </div>
                <div>
                    <label class="muted mb-1" style="font-weight: 500;">Email Address</label>
                    <p style="font-weight: 700; font-size: 1.1rem;"><?php echo h($user['email']); ?></p>
                </div>
                <div>
                    <label class="muted mb-1" style="font-weight: 500;">Account Since</label>
                    <p style="font-weight: 700; font-size: 1.1rem;"><?php echo date('F Y', strtotime($user['created_at'])); ?></p>
                </div>
                <div>
                    <label class="muted mb-1" style="font-weight: 500;">Language Preference</label>
                    <p style="font-weight: 700; font-size: 1.1rem;">English (UK)</p>
                </div>
            </div>
        </div>

        <?php if (!$is_admin): ?>
            <div class="card">
                <div class="space-between mb-5">
                    <h3 class="m-0" style="font-size: 1.25rem;">Recent Order Activity</h3>
                    <a href="orders.php" class="btn btn-link">See all history &rarr;</a>
                </div>
                
                <?php if (empty($recent_orders)): ?>
                    <div class="text-center" style="padding: 2rem;">
                        <p class="muted">No orders placed yet. <a href="<?php echo $base_url; ?>/pages/shop.php">Start exploring!</a></p>
                    </div>
                <?php else: ?>
                    <div class="table-container" style="border: none;">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th class="text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_orders as $order): ?>
                                <tr>
                                    <td style="font-weight: 700;">#<?php echo $order['id']; ?></td>
                                    <td class="muted"><?php echo date('d M Y', strtotime($order['created_at'])); ?></td>
                                    <td>
                                        <span class="badge badge-status <?php echo strtolower($order['status']); ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td class="text-right" style="font-weight: 700;">£<?php echo number_format($order['total_amount'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="card" style="background: var(--bg-soft); border: 2px dashed var(--border); text-align: center; padding: 4rem 2rem;">
                <div style="width: 60px; height: 60px; background: var(--white); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; box-shadow: var(--shadow-sm);">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--accent);"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
                </div>
                <h3 class="mb-2">Admin Management Interface</h3>
                <p class="muted mb-5">You are currently logged in with administrative privileges. Access the full store management suite below.</p>
                <a href="<?php echo $base_url; ?>/admin/dashboard.php" class="btn btn-primary" style="padding: 1rem 2.5rem;">Launch Admin Panel</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>