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

<div style="margin-bottom: 30px; border-bottom: 1px solid #e2e8f0; padding-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
    <h2 style="margin: 0;">Admin Dashboard</h2>
    <a href="product-add.php" class="btn" style="padding: 8px 16px; border-radius: 6px;">Add New Product</a>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
    <div style="background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; text-align: center;">
        <h4 style="color: #64748b; font-size: 0.8rem; text-transform: uppercase; margin-bottom: 5px;">Revenue</h4>
        <p style="font-size: 1.5rem; font-weight: bold; margin: 0;">£<?php echo number_format($stats_revenue, 2); ?></p>
    </div>
    <div style="background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; text-align: center;">
        <h4 style="color: #64748b; font-size: 0.8rem; text-transform: uppercase; margin-bottom: 5px;">Total Orders</h4>
        <p style="font-size: 1.5rem; font-weight: bold; margin: 0;"><?php echo $stats_orders; ?></p>
    </div>
    <div style="background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; text-align: center;">
        <h4 style="color: #64748b; font-size: 0.8rem; text-transform: uppercase; margin-bottom: 5px;">Active Products</h4>
        <p style="font-size: 1.5rem; font-weight: bold; margin: 0;"><?php echo $stats_products; ?></p>
    </div>
    <div style="background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; text-align: center;">
        <h4 style="color: #64748b; font-size: 0.8rem; text-transform: uppercase; margin-bottom: 5px;">Users</h4>
        <p style="font-size: 1.5rem; font-weight: bold; margin: 0;"><?php echo $stats_users; ?></p>
    </div>
</div>

<div style="display: flex; gap: 10px; margin-bottom: 40px; border-bottom: 1px solid #f1f5f9; padding-bottom: 20px;">
    <a href="products.php" style="color: var(--secondary-color); font-weight: 500; font-size: 0.9rem;">Products</a>
    <span style="color: #cbd5e1;">|</span>
    <a href="categories.php" style="color: var(--secondary-color); font-weight: 500; font-size: 0.9rem;">Categories</a>
    <span style="color: #cbd5e1;">|</span>
    <a href="orders.php" style="color: var(--secondary-color); font-weight: 500; font-size: 0.9rem;">Orders</a>
    <span style="color: #cbd5e1;">|</span>
    <a href="users.php" style="color: var(--secondary-color); font-weight: 500; font-size: 0.9rem;">Users</a>
</div>

<div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 30px;">
    <!-- Recent Orders -->
    <div style="background: #fff; padding: 25px; border-radius: 8px; border: 1px solid #e2e8f0;">
        <h3 style="margin: 0 0 20px; font-size: 1rem;">Recent Orders</h3>
        <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
            <thead>
                <tr style="text-align: left; border-bottom: 1px solid #f1f5f9; color: #64748b;">
                    <th style="padding: 10px 0;">ID</th>
                    <th style="padding: 10px 0;">Customer</th>
                    <th style="padding: 10px 0;">Status</th>
                    <th style="padding: 10px 0; text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_orders as $ro): ?>
                <tr style="border-bottom: 1px solid #f8fafc;">
                    <td style="padding: 12px 0;">#<?php echo $ro['id']; ?></td>
                    <td style="padding: 12px 0;"><?php echo h($ro['customer']); ?></td>
                    <td style="padding: 12px 0;"><span style="font-size: 0.75rem; background: #f1f5f9; padding: 2px 8px; border-radius: 4px;"><?php echo ucfirst($ro['status']); ?></span></td>
                    <td style="padding: 12px 0; text-align: right; font-weight: bold;">£<?php echo number_format($ro['total_amount'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Stock Alerts -->
    <div style="background: #fff; padding: 25px; border-radius: 8px; border: 1px solid #e2e8f0;">
        <h3 style="margin: 0 0 20px; font-size: 1rem; color: #ef4444;">Stock Alerts</h3>
        <?php if (empty($low_stock_items)): ?>
            <p style="color: #94a3b8; font-size: 0.85rem;">All items in stock.</p>
        <?php else: ?>
            <ul style="list-style: none; padding: 0;">
                <?php foreach ($low_stock_items as $item): ?>
                <li style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 10px; margin-bottom: 10px; border-bottom: 1px solid #f8fafc; font-size: 0.9rem;">
                    <span><?php echo h($item['name']); ?></span>
                    <span style="color: #ef4444; font-weight: bold;"><?php echo $item['stock_qty']; ?> left</span>
                </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
