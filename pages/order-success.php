<?php
require_once __DIR__ . '/../includes/header.php';
require_login();

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = get_current_user_id();

$order = fetch_one("SELECT * FROM orders WHERE id = ? AND user_id = ?", "ii", [$order_id, $user_id]);

if (!$order) {
    set_flash_message('error', 'Order not found.');
    header('Location: ' . $base_url . '/pages/account.php');
    exit;
}
?>

<div style="text-align: center; padding: 40px; background: #fff; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
    <h1 style="color: #27ae60; margin-bottom: 20px;">Thank You!</h1>
    <p style="font-size: 1.2rem; margin-bottom: 10px;">Your order has been placed successfully.</p>
    <p>Order ID: <strong>#<?php echo $order_id; ?></strong></p>
    <p>Total Amount: <strong>£<?php echo number_format($order['total_amount'], 2); ?></strong></p>
    
    <div style="margin-top: 30px;">
        <a href="<?php echo $base_url; ?>/pages/orders.php" class="btn">View My Orders</a>
        <a href="<?php echo $base_url; ?>/pages/downloads.php" class="btn" style="background: var(--secondary-color);">My Digital Downloads</a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>