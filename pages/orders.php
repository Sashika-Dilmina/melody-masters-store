<?php
require_once __DIR__ . '/../includes/header.php';
require_login();

$user_id = get_current_user_id();
$orders = fetch_all("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC", "i", [$user_id]);

?>
<h2>My Orders</h2>

<?php if (empty($orders)): ?>
    <p>You have not placed any orders yet. <a href="<?php echo $base_url; ?>/pages/shop.php">Start shopping</a>.</p>
<?php else: ?>
    <table style="width: 100%; border-collapse: collapse; background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
        <thead>
            <tr style="background: var(--primary-color); color: #fff;">
                <th style="padding: 10px; text-align: left;">Order ID</th>
                <th style="padding: 10px; text-align: left;">Date</th>
                <th style="padding: 10px; text-align: center;">Status</th>
                <th style="padding: 10px; text-align: right;">Total Amount</th>
                <th style="padding: 10px; text-align: center;">Items</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <?php 
                $items = fetch_all("SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?", "i", [$order['id']]); 
                ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 15px;">#<?php echo $order['id']; ?></td>
                    <td style="padding: 15px;"><?php echo date('d M Y, H:i', strtotime($order['created_at'])); ?></td>
                    <td style="padding: 15px; text-align: center;">
                        <span style="padding: 3px 8px; border-radius: 12px; font-size: 0.8rem; background: <?php echo $order['status'] === 'shipped' ? '#d4edda' : '#fff3cd'; ?>;">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </td>
                    <td style="padding: 15px; text-align: right;">£<?php echo number_format($order['total_amount'], 2); ?></td>
                    <td style="padding: 15px;">
                        <ul style="margin: 0; padding-left: 20px; font-size: 0.9rem;">
                            <?php foreach ($items as $item): ?>
                                <li><?php echo h($item['name']); ?> (x<?php echo $item['quantity']; ?>)</li>
                            <?php endforeach; ?>
                        </ul>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
