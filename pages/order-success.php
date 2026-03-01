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
    if (strtoupper($item['product_type']) === 'DIGITAL') { $has_digital = true; break; }
}
?>

<div class="section reveal" style="max-width: 800px; margin: 0 auto;">

    <!-- Success Header -->
    <div class="text-center mb-5">
        <div style="width: 80px; height: 80px; background: rgba(16, 185, 129, 0.1); color: var(--success); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; box-shadow: 0 0 0 8px rgba(16, 185, 129, 0.05);">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
        </div>
        <h1 class="title" style="color: var(--success); font-size: 2.5rem; margin-bottom: 0.5rem;">Payment Successful!</h1>
        <p class="subtitle mb-4">Your order reference is <strong>#<?php echo $order_id; ?></strong>.</p>
        <?php if ($order['order_number']): ?>
            <span class="badge badge-status" style="padding: 0.5rem 1rem;">Tracking ID: <?php echo h($order['order_number']); ?></span>
        <?php endif; ?>
    </div>

    <!-- Order Items Summary -->
    <div class="card mb-5" style="padding: 0; overflow: hidden;">
        <div style="padding: 1.5rem 2rem; background: var(--bg-soft); border-bottom: 1px solid var(--border);">
            <h3 style="font-size: 1.1rem; margin: 0;">Order Summary</h3>
        </div>
        <div style="padding: 2rem;">
            <div class="stack mb-4" style="gap: 1rem;">
                <?php foreach ($items as $item): ?>
                <div class="space-between" style="padding-bottom: 0.75rem; border-bottom: 1px dashed var(--border);">
                    <div>
                        <span style="font-weight: 700; color: var(--primary);"><?php echo h($item['product_name']); ?></span>
                        <span class="muted" style="font-size: 0.85rem; margin-left: 8px;">× <?php echo (int)$item['quantity']; ?></span>
                    </div>
                    <span style="font-weight: 700; color: var(--primary_light);">£<?php echo number_format($item['line_total'], 2); ?></span>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="stack" style="gap: 0.75rem; margin-top: 2rem; padding-top: 1.5rem; border-top: 1.5px solid var(--bg-soft);">
                <div class="space-between text-sm muted">
                    <span>Net Subtotal</span>
                    <span style="font-weight: 600;">£<?php echo number_format($order['subtotal'], 2); ?></span>
                </div>
                <div class="space-between text-sm muted">
                    <span>Shipping Fee</span>
                    <span style="font-weight: 600;"><?php echo $order['shipping_amount'] > 0 ? '£' . number_format($order['shipping_amount'], 2) : 'FREE'; ?></span>
                </div>
                <div class="divider" style="margin: 1rem 0;"></div>
                <div class="space-between">
                    <span style="font-weight: 800; font-size: 1.25rem; color: var(--primary);">Total Paid</span>
                    <span style="font-weight: 800; font-size: 1.75rem; color: var(--accent);">£<?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
            </div>
        </div>
    </div>

    <?php if ($has_digital): ?>
    <div class="alert alert-info mb-5" style="text-align: center; justify-content: center; padding: 1.5rem;">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 10px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
        <span style="font-weight: 600;">Success! Your digital assets are now available in your personal library.</span>
    </div>
    <?php endif; ?>

    <!-- Navigation Actions -->
    <div class="row" style="gap: 1.25rem; flex-wrap: wrap;">
        <a href="account.php" class="btn btn-outline" style="flex: 1; padding: 1rem; min-width: 150px;">Go to Profile</a>
        <?php if ($has_digital): ?>
            <a href="downloads.php" class="btn btn-primary" style="flex: 1; padding: 1rem; background: var(--teal); color: white; min-width: 150px;">Access Downloads</a>
        <?php endif; ?>
        <a href="shop.php" class="btn btn-primary" style="flex: 1; padding: 1rem; min-width: 150px;">Back to Store</a>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>