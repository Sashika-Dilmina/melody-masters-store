<?php
// pages/orders.php – customer order history
require_once __DIR__ . '/../includes/header.php';
require_login();

$user_id = get_current_user_id();
$orders  = fetch_all(
    "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC",
    "i", [$user_id]
);
?>

<div class="mb-5 reveal">
    <h1 class="title" style="font-size: 2rem;">Order History</h1>
    <p class="muted">Review and track your past masterpieces.</p>
</div>

<?php if (empty($orders)): ?>
    <div class="card text-center reveal" style="padding: 5rem 2rem;">
        <div style="width: 80px; height: 80px; background: var(--bg-soft); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: var(--text-muted); border: 1px solid var(--border);">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
        </div>
        <h2 class="mb-2">No orders found</h2>
        <p class="muted mb-5">You haven't placed any orders yet. Start your collection today!</p>
        <a href="shop.php" class="btn btn-primary" style="padding: 1rem 2.5rem;">Explore our Shop</a>
    </div>
<?php else: ?>
    <div class="stack reveal" style="gap: 2rem;">
    <?php foreach ($orders as $order):
        $items = fetch_all(
            "SELECT product_name, quantity, unit_price, line_total
               FROM order_items WHERE order_id = ?",
            "i", [$order['id']]
        );
    ?>
        <div class="card card-hover" style="padding: 0; overflow: hidden;">
            <div class="space-between" style="background: var(--bg-soft); padding: 1.25rem 2rem; border-bottom: 1px solid var(--border);">
                <div>
                    <span style="font-weight: 800; font-size: 1rem; color: var(--primary);">Order #<?php echo $order['id']; ?></span>
                    <span class="muted text-sm" style="margin-left: 12px; font-weight: 500;">
                        <?php echo date('d M Y', strtotime($order['created_at'])); ?>
                    </span>
                </div>
                <div class="row" style="gap: 1.5rem;">
                    <span class="badge badge-status <?php echo strtolower($order['status']); ?>" style="padding: 0.4rem 1rem;">
                        <?php echo ucfirst($order['status']); ?>
                    </span>
                    <span style="font-weight: 800; font-size: 1.25rem; color: var(--accent);">£<?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
            </div>

            <div style="padding: 1.5rem 2rem;">
                <div class="stack mb-4" style="gap: 0.75rem;">
                    <?php foreach ($items as $item): ?>
                        <div class="space-between" style="padding-bottom: 0.75rem; border-bottom: 1px dashed var(--bg-soft);">
                            <div style="font-size: 0.95rem;">
                                <span style="font-weight: 600; color: var(--primary);"><?php echo h($item['product_name']); ?></span>
                                <span class="muted" style="font-size: 0.8rem; margin-left: 8px;">× <?php echo (int)$item['quantity']; ?></span>
                            </div>
                            <span style="font-weight: 600; color: var(--primary-light);">£<?php echo number_format($item['line_total'], 2); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="row" style="justify-content: flex-end; gap: 2.5rem; border-top: 1px solid var(--bg-soft); padding-top: 1.5rem;">
                    <div class="text-xs muted" style="font-weight: 600; text-transform: uppercase;">
                        Subtotal: <span style="color: var(--primary); margin-left: 5px;">£<?php echo number_format($order['subtotal'], 2); ?></span>
                    </div>
                    <div class="text-xs muted" style="font-weight: 600; text-transform: uppercase;">
                        Shipping: <span style="color: var(--primary); margin-left: 5px;"><?php echo $order['shipping_amount'] > 0 ? '£' . number_format($order['shipping_amount'], 2) : 'FREE'; ?></span>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
