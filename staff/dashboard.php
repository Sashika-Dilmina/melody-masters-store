<?php
// staff/dashboard.php
require_once __DIR__ . '/../includes/header.php';
require_role('staff');

$stats_pending_orders = fetch_one("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'")['count'];
$low_stock_products = fetch_one("SELECT COUNT(*) as count FROM products WHERE stock_qty <= 5 AND product_type = 'physical' AND is_active = 1")['count'];
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 1px solid #e2e8f0; padding-bottom: 20px;">
    <h2 style="margin: 0;">Staff Dashboard</h2>
    <span style="font-size: 0.85rem; color: #64748b;">Role: Internal Staff</span>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 25px; margin-bottom: 40px;">
    
    <div style="background: #fff; padding: 25px; border-radius: 8px; border: 1px solid #e2e8f0;">
        <h4 style="color: #64748b; font-size: 0.75rem; text-transform: uppercase; margin-bottom: 10px; letter-spacing: 0.5px;">Pending Orders</h4>
        <p style="font-size: 2rem; font-weight: bold; margin: 0;"><?php echo $stats_pending_orders; ?></p>
        <a href="orders.php" style="display: inline-block; margin-top: 15px; font-size: 0.85rem; color: var(--secondary-color); font-weight: 500;">Manage Orders</a>
    </div>

    <div style="background: #fff; padding: 25px; border-radius: 8px; border: 1px solid #e2e8f0;">
        <h4 style="color: #64748b; font-size: 0.75rem; text-transform: uppercase; margin-bottom: 10px; letter-spacing: 0.5px;">Low Stock</h4>
        <p style="font-size: 2rem; font-weight: bold; margin: 0; color: #ef4444;"><?php echo $low_stock_products; ?></p>
        <a href="stock.php" style="display: inline-block; margin-top: 15px; font-size: 0.85rem; color: var(--secondary-color); font-weight: 500;">Update Inventory</a>
    </div>

</div>

<div style="background: #f8fafc; padding: 25px; border-radius: 8px; border: 1px solid #e2e8f0;">
    <h3 style="margin: 0 0 20px; font-size: 1rem;">Navigation Hub</h3>
    <div style="display: flex; gap: 15px; flex-wrap: wrap;">
        <a href="orders.php" class="btn" style="padding: 10px 20px; border-radius: 4px; font-size: 0.9rem;">Orders Management</a>
        <a href="stock.php" class="btn" style="background: #475569; padding: 10px 20px; border-radius: 4px; font-size: 0.9rem;">Inventory Control</a>
        <a href="<?php echo $base_url; ?>/pages/shop.php" target="_blank" class="btn" 
           style="background: #fff; color: var(--primary-color); border: 1px solid #cbd5e1; padding: 9px 20px; border-radius: 4px; font-size: 0.9rem;">View Shop Frontend</a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
