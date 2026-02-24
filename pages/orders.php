<?php
// pages/orders.php – customer order history
require_once __DIR__ . '/../includes/header.php';
require_login();

$user_id = get_current_user_id();
$orders  = fetch_all(
    "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC",
    "i", [$user_id]
);

$status_colors = [
    'pending'    => ['bg' => '#fef3c7', 'color' => '#92400e'],
    'paid'       => ['bg' => '#dcfce7', 'color' => '#166534'],
    'processing' => ['bg' => '#dbeafe', 'color' => '#1e40af'],
    'shipped'    => ['bg' => '#f0fdf4', 'color' => '#166534'],
    'completed'  => ['bg' => '#dcfce7', 'color' => '#15803d'],
    'cancelled'  => ['bg' => '#fee2e2', 'color' => '#991b1b'],
];
?>

<div style="margin-bottom: 30px; border-bottom: 1px solid #e2e8f0; padding-bottom: 20px;">
    <h2 style="margin: 0;">Order History</h2>
</div>

<?php if (empty($orders)): ?>
    <div style="background: #fff; border: 1px solid #e2e8f0; padding: 40px; border-radius: 8px; text-align: center;">
        <p style="color: #64748b; margin-bottom: 20px;">You haven't placed any orders yet.</p>
        <a href="shop.php" class="btn">View Product Catalog</a>
    </div>
<?php else: ?>
    <div style="display: flex; flex-direction: column; gap: 25px;">
    <?php foreach ($orders as $order):
        $items = fetch_all(
            "SELECT product_name, quantity, unit_price, line_total
               FROM order_items WHERE order_id = ?",
            "i", [$order['id']]
        );
        $sc = $status_colors[$order['status']] ?? ['bg' => '#f1f5f9', 'color' => '#475569'];
    ?>
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
            <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; background: #f8fafc; padding: 15px 20px; border-bottom: 1px solid #e2e8f0;">
                <div>
                    <span style="font-weight: 700; font-size: 0.9rem;">Order #<?php echo $order['id']; ?></span>
                    <span style="color: #94a3b8; font-size: 0.8rem; margin-left: 10px;">
                        <?php echo date('d M Y', strtotime($order['created_at'])); ?>
                    </span>
                </div>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <span style="padding: 3px 10px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; background: <?php echo $sc['bg']; ?>; color: <?php echo $sc['color']; ?>;">
                        <?php echo ucfirst($order['status']); ?>
                    </span>
                    <span style="font-weight: 700; font-size: 1.1rem; color: var(--primary-color);">£<?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
            </div>

            <div style="padding: 20px;">
                <?php foreach ($items as $item): ?>
                    <div style="display: flex; justify-content: space-between; font-size: 0.9rem; padding: 8px 0; border-bottom: 1px solid #f8fafc;">
                        <span><?php echo h($item['product_name']); ?> <span style="color: #94a3b8; font-size: 0.8rem;">(x<?php echo (int)$item['quantity']; ?>)</span></span>
                        <span style="font-weight: 500;">£<?php echo number_format($item['line_total'], 2); ?></span>
                    </div>
                <?php endforeach; ?>

                <div style="margin-top: 15px; display: flex; justify-content: flex-end; gap: 20px; font-size: 0.8rem; color: #64748b;">
                    <span>Subtotal: £<?php echo number_format($order['subtotal'], 2); ?></span>
                    <span>Shipping: £<?php echo number_format($order['shipping_amount'], 2); ?></span>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
