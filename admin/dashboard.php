<?php
// admin/dashboard.php
require_once __DIR__ . '/../includes/header.php';
require_role('admin');

$stats_users   = (int)(fetch_one("SELECT COUNT(*) AS c FROM users")['c'] ?? 0);
$stats_orders  = (int)(fetch_one("SELECT COUNT(*) AS c FROM orders")['c'] ?? 0);
$stats_revenue = (float)(fetch_one("SELECT COALESCE(SUM(total_amount),0) AS t FROM orders WHERE status != 'cancelled'")['t'] ?? 0);
$stats_products = (int)(fetch_one("SELECT COUNT(*) AS c FROM products WHERE is_active = 1")['c'] ?? 0);

// Low-stock items
$low_stock_items = fetch_all(
    "SELECT id, name, stock_qty FROM products WHERE product_type = 'physical' AND stock_qty <= 5 AND is_active = 1 ORDER BY stock_qty ASC"
);

// Recent orders
$recent_orders = fetch_all("
    SELECT o.id, o.status, o.total_amount, o.created_at, u.full_name AS customer
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
    LIMIT 5
");
?>

<div class="space-between mb-5 reveal">
    <h1 class="title" style="font-size: 2rem;">Admin Dashboard</h1>
    <div class="row">
        <a href="product-add.php" class="btn btn-primary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Add New Product
        </a>
    </div>
</div>

<div class="grid grid-4 mb-5 reveal" style="animation-delay: 0.1s;">
    <div class="card card-hover" style="border-left: 4px solid var(--accent);">
        <h4 class="text-xs muted mb-1" style="text-transform: uppercase; font-weight: 700;">Revenue</h4>
        <p style="font-size: 1.5rem; font-weight: 800; color: var(--primary);">£<?php echo number_format($stats_revenue, 2); ?></p>
    </div>
    <div class="card card-hover" style="border-left: 4px solid var(--teal);">
        <h4 class="text-xs muted mb-1" style="text-transform: uppercase; font-weight: 700;">Total Orders</h4>
        <p style="font-size: 1.5rem; font-weight: 800; color: var(--primary);"><?php echo $stats_orders; ?></p>
    </div>
    <div class="card card-hover" style="border-left: 4px solid #F59E0B;">
        <h4 class="text-xs muted mb-1" style="text-transform: uppercase; font-weight: 700;">Active Products</h4>
        <p style="font-size: 1.5rem; font-weight: 800; color: var(--primary);"><?php echo $stats_products; ?></p>
    </div>
    <div class="card card-hover" style="border-left: 4px solid #8B5CF6;">
        <h4 class="text-xs muted mb-1" style="text-transform: uppercase; font-weight: 700;">Total Users</h4>
        <p style="font-size: 1.5rem; font-weight: 800; color: var(--primary);"><?php echo $stats_users; ?></p>
    </div>
</div>

<div class="card mb-5 reveal" style="padding: 1rem; background: var(--bg-soft); border: 1px solid var(--border); animation-delay: 0.15s;">
    <div class="row" style="justify-content: center; gap: 2rem; flex-wrap: wrap;">
        <a href="products.php" class="sidebar-link">Products Management</a>
        <a href="categories.php" class="sidebar-link">Categories</a>
        <a href="orders.php" class="sidebar-link">Order Management</a>
        <a href="users.php" class="sidebar-link">Store Users</a>
    </div>
</div>

<div class="dashboard-layout reveal" style="animation-delay: 0.2s;">
    <!-- Recent Orders -->
    <div class="stack">
        <div class="card" style="padding: 2.5rem;">
            <div class="space-between mb-4">
                <h3 class="m-0" style="font-size: 1.1rem;">Recent Orders</h3>
                <a href="orders.php" class="btn btn-link">View all &rarr;</a>
            </div>
            <div class="table-container" style="border: none;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Status</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_orders as $ro): ?>
                        <tr>
                            <td style="font-weight: 700;">#<?php echo $ro['id']; ?></td>
                            <td class="muted"><?php echo h($ro['customer']); ?></td>
                            <td>
                                <span class="badge badge-status <?php echo strtolower($ro['status']); ?>">
                                    <?php echo ucfirst($ro['status']); ?>
                                </span>
                            </td>
                            <td class="text-right" style="font-weight: 700;">£<?php echo number_format($ro['total_amount'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Stock Alerts -->
    <div class="stack">
        <div class="card" style="padding: 2.5rem;">
            <div class="row mb-4" style="gap: 8px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--error);"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                <h3 class="m-0" style="font-size: 1.1rem; color: var(--error);">Stock Inventory Alerts</h3>
            </div>
            <?php if (empty($low_stock_items)): ?>
                <div class="text-center" style="padding: 2rem; background: var(--bg-soft); border-radius: 12px;">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--success); margin-bottom: 0.75rem;"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    <p class="muted text-sm">All products are healthy and in stock.</p>
                </div>
            <?php else: ?>
                <div class="stack" style="gap: 1rem;">
                    <?php foreach ($low_stock_items as $item): ?>
                    <div class="space-between" style="padding: 1rem; background: #FEF2F2; border-radius: 10px; border: 1px solid #FECACA;">
                        <span style="font-weight: 600; font-size: 0.9rem; color: #991B1B;"><?php echo h($item['name']); ?></span>
                        <span class="badge" style="background: #991B1B; color: white;"><?php echo $item['stock_qty']; ?> left</span>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
