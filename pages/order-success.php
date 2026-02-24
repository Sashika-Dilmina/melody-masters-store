<?php
// pages/order-success.php – shown after a successful order placement
require_once __DIR__ . '/../includes/header.php';
require_login();

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id  = get_current_user_id();

// Must belong to the current user
$order = fetch_one(
    "SELECT * FROM orders WHERE id = ? AND user_id = ?",
    "ii", [$order_id, $user_id]
);

if (!$order) {
    set_flash_message('error', 'Order not found.');
    header('Location: ' . $base_url . '/pages/account.php');
    exit;
}

// Fetch order items
$items = fetch_all(
    "SELECT product_name, product_type, unit_price, quantity, line_total
     FROM order_items WHERE order_id = ?",
    "i", [$order_id]
);

$has_digital = false;
foreach ($items as $item) {
    if ($item['product_type'] === 'digital') { $has_digital = true; break; }
}
?>

<div style="max-width: 600px; margin: 0 auto; padding: 20px;">

    <!-- Success Header -->
    <div style="text-align: center; margin-bottom: 40px;">
        <h1 style="color: #10b981; font-size: 2rem; margin-bottom: 10px;">Order Confirmation</h1>
        <p style="color: #64748b; font-size: 1rem;">
            Successfully placed order reference <strong>#<?php echo $order_id; ?></strong>.
        </p>
        <?php if ($order['order_number']): ?>
            <p style="color: #94a3b8; font-size: 0.85rem; margin-top: 5px;">Tracking ID: <?php echo h($order['order_number']); ?></p>
        <?php endif; ?>
    </div>

    <!-- Order Items Summary -->
    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; margin-bottom: 30px;">
        <div style="padding: 15px 20px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; font-weight: 600; font-size: 0.9rem;">
            Items Purchased
        </div>
        <div style="padding: 20px;">
            <?php foreach ($items as $item): ?>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; font-size: 0.95rem; border-bottom: 1px solid #f1f5f9;">
                <div>
                    <?php echo h($item['product_name']); ?>
                    <span style="color: #94a3b8; font-size: 0.8rem; margin-left: 5px;">(Qty: <?php echo (int)$item['quantity']; ?>)</span>
                </div>
                <div style="font-weight: 500;">£<?php echo number_format($item['line_total'], 2); ?></div>
            </div>
            <?php endforeach; ?>

            <div style="margin-top: 20px; border-top: 2px solid #f1f5f9; padding-top: 15px;">
                <div style="display: flex; justify-content: space-between; font-size: 0.9rem; color: #64748b; margin-bottom: 5px;">
                    <span>Subtotal</span>
                    <span>£<?php echo number_format($order['subtotal'], 2); ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 0.9rem; color: #64748b; margin-bottom: 5px;">
                    <span>Shipping</span>
                    <span>£<?php echo number_format($order['shipping_amount'], 2); ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 1.1rem; font-weight: bold; margin-top: 10px;">
                    <span>Grand Total</span>
                    <span style="color: var(--primary-color);">£<?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
            </div>
        </div>
    </div>

    <?php if ($has_digital): ?>
    <div style="background: #f0f7ff; border: 1px solid #dbeafe; padding: 15px; border-radius: 6px; margin-bottom: 30px; font-size: 0.9rem; color: #1e40af; text-align: center;">
        Your digital products are ready for access. Visit your download center to get started.
    </div>
    <?php endif; ?>

    <!-- Navigation Actions -->
    <div style="display: flex; gap: 10px;">
        <a href="account.php" class="btn" style="flex: 1; text-align: center; padding: 12px; border-radius: 4px; border: 1px solid #e2e8f0; background: #fff; color: #475569;">My Account</a>
        <?php if ($has_digital): ?>
            <a href="downloads.php" class="btn" style="flex: 1; text-align: center; padding: 12px; border-radius: 4px;">Download Library</a>
        <?php endif; ?>
        <a href="shop.php" class="btn" style="flex: 1; text-align: center; padding: 12px; border-radius: 4px; background: var(--secondary-color);">Return to Shop</a>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>