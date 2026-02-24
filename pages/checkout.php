<?php
require_once __DIR__ . '/../includes/header.php';
require_login();

$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    set_flash_message('error', 'Your cart is empty.');
    header('Location: ' . $base_url . '/pages/shop.php');
    exit;
}

$cart_items = [];
$physical_subtotal = 0;
$digital_subtotal = 0;
$has_physical = false;

$ids = array_map('intval', array_keys($cart));
if (!empty($ids)) {
    $id_list = implode(',', $ids);
    $products = fetch_all("SELECT * FROM products WHERE id IN ($id_list)");
    
    foreach ($products as $p) {
        $qty = $cart[$p['id']]['quantity'];
        $subtotal = $p['price'] * $qty;
        
        if ($p['product_type'] === 'physical') {
            $physical_subtotal += $subtotal;
            $has_physical = true;
        } else {
            $digital_subtotal += $subtotal;
        }
        
        $cart_items[] = [
            'product' => $p,
            'quantity' => $qty,
            'subtotal' => $subtotal
        ];
    }
}

$shipping = calculate_shipping($physical_subtotal);
$total = $physical_subtotal + $digital_subtotal + $shipping;
?>

<h2>Checkout</h2>
<div style="display: flex; flex-wrap: wrap; gap: 30px;">
    <div style="flex: 2; min-width: 300px; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
        <h3>Shipping & Billing Information</h3>
        <form method="POST" action="<?php echo $base_url; ?>/api/checkout-place-order.php" id="checkout-form">
            <?php echo csrf_field(); ?>
            <div style="margin-bottom: 15px;">
                <label>Full Name</label><br>
                <input type="text" name="shipping_name" required style="width: 100%; padding: 8px;">
            </div>
            
            <?php if ($has_physical): ?>
                <div style="margin-bottom: 15px;">
                    <label>Shipping Address</label><br>
                    <textarea name="shipping_address" required style="width: 100%; padding: 8px; height: 80px;"></textarea>
                </div>
            <?php else: ?>
                <div style="background: #e9ecef; padding: 15px; border-radius: 4px; margin-bottom: 15px;">
                    <em>Only digital items in cart. No shipping address required.</em>
                </div>
            <?php endif; ?>
            
            <button type="submit" class="btn" style="width: 100%; background: #27ae60; font-size: 1.1rem; padding: 12px;">Place Order</button>
        </form>
    </div>
    
    <div style="flex: 1; min-width: 300px; background: #f9f9f9; padding: 20px; border-radius: 8px; border: 1px solid #ddd;">
        <h3>Order Summary</h3>
        <ul style="list-style: none; padding: 0; margin-bottom: 20px;">
            <?php foreach ($cart_items as $item): ?>
                <li style="display: flex; justify-content: space-between; margin-bottom: 10px; border-bottom: 1px dashed #ccc; padding-bottom: 5px;">
                    <span><?php echo h($item['product']['name']); ?> (x<?php echo $item['quantity']; ?>)</span>
                    <span>£<?php echo number_format($item['subtotal'], 2); ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
        
        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
            <span>Subtotal (Physical):</span>
            <span>£<?php echo number_format($physical_subtotal, 2); ?></span>
        </div>
        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
            <span>Subtotal (Digital):</span>
            <span>£<?php echo number_format($digital_subtotal, 2); ?></span>
        </div>
        <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
            <span>Shipping:</span>
            <span>£<?php echo number_format($shipping, 2); ?></span>
        </div>
        <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 1.2rem; border-top: 2px solid #333; padding-top: 10px;">
            <span>Total:</span>
            <span>£<?php echo number_format($total, 2); ?></span>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>