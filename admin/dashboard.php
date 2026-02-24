<?php
require_once __DIR__ . '/../includes/header.php';
require_role('admin');

$stats_users = fetch_one("SELECT COUNT(*) as count FROM users")['count'];
$stats_orders = fetch_one("SELECT COUNT(*) as count FROM orders")['count'];
$stats_revenue = fetch_one("SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled'")['total'] ?? 0;

// Low stock alerts (Physical items with 5 or fewer in stock)
$low_stock_items = fetch_all("SELECT id, name, stock_qty FROM products WHERE product_type = 'physical' AND stock_qty <= 5 ORDER BY stock_qty ASC");
?>
<h2>Admin Dashboard</h2>
<div style="display: flex; gap: 20px; margin-bottom: 30px;">
    // ... EXISTING CODE ...
    <div style="padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); flex: 1; text-align: center;">
        <h3>Total Users</h3>
        <p style="font-size: 2rem; font-weight: bold; color: var(--primary-color);"><?php echo $stats_users; ?></p>
    </div>
    <div style="padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); flex: 1; text-align: center;">
        <h3>Total Orders</h3>
        <p style="font-size: 2rem; font-weight: bold; color: var(--primary-color);"><?php echo $stats_orders; ?></p>
    </div>
    <div style="padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); flex: 1; text-align: center;">
        <h3>Total Revenue</h3>
        <p style="font-size: 2rem; font-weight: bold; color: var(--secondary-color);">£<?php echo number_format($stats_revenue, 2); ?></p>
    </div>
</div>

<div style="display: flex; gap: 15px; flex-wrap: wrap;">
   <a href="products.php" class="btn">Manage Products</a>
   <a href="categories.php" class="btn">Manage Categories</a>
   <a href="users.php" class="btn">Manage Users</a>
   <a href="orders.php" class="btn">Manage Orders</a>
</div>

<?php if (count($low_stock_items) > 0): ?>
<div style="margin-top: 30px; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
    <h3 style="color: #d97706; margin-bottom: 15px;"><i class="fa-solid fa-triangle-exclamation"></i> Low Stock Alerts</h3>
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: #fef3c7; color: #92400e;">
                <th style="padding: 10px; text-align: left;">Product ID</th>
                <th style="padding: 10px; text-align: left;">Name</th>
                <th style="padding: 10px; text-align: center;">Current Stock</th>
                <th style="padding: 10px; text-align: center;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($low_stock_items as $item): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 10px;"><?php echo $item['id']; ?></td>
                    <td style="padding: 10px;"><?php echo h($item['name']); ?></td>
                    <td style="padding: 10px; text-align: center; color: #dc2626; font-weight: bold;"><?php echo $item['stock_qty']; ?></td>
                    <td style="padding: 10px; text-align: center;">
                        <a href="product-edit.php?id=<?php echo $item['id']; ?>" class="btn" style="padding: 4px 8px; font-size: 0.8rem;">Update Stock</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
