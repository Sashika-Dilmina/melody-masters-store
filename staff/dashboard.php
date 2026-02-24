<?php
require_once __DIR__ . '/../includes/header.php';
require_role('staff');

$stats_pending_orders = fetch_one("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'")['count'];
$low_stock_products = fetch_one("SELECT COUNT(*) as count FROM products WHERE stock_qty <= 5 AND product_type = 'physical'")['count'];

?>
<h2>Staff Dashboard</h2>
<div style="display: flex; gap: 20px; margin-bottom: 30px;">
    <div style="padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); flex: 1; text-align: center;">
        <h3>Pending Orders</h3>
        <p style="font-size: 2rem; font-weight: bold; color: var(--secondary-color);"><?php echo $stats_pending_orders; ?></p>
    </div>
    <div style="padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); flex: 1; text-align: center;">
        <h3>Low Stock Products</h3>
        <p style="font-size: 2rem; font-weight: bold; color: #f39c12;"><?php echo $low_stock_products; ?></p>
    </div>
</div>

<div style="display: flex; gap: 15px; flex-wrap: wrap;">
   <a href="orders.php" class="btn">View & Process Orders</a>
   <a href="stock.php" class="btn">Update Product Stock</a>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
