<?php
// pages/checkout.php
require_once __DIR__ . '/../includes/header.php';
require_login();

if (get_current_user_role() === 'admin') {
    set_flash_message('error', 'Administrators are redirected to the dashboard.');
    header('Location: ' . $base_url . '/admin/dashboard.php');
    exit;
}

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
        $line_total = $p['price'] * $qty;

        if ($p['product_type'] === 'physical') {
            $physical_subtotal += $line_total;
            $has_physical = true;
        } else {
            $digital_subtotal += $line_total;
        }

        $cart_items[] = [
            'product'  => $p,
            'quantity' => $qty,
            'subtotal' => $line_total
        ];
    }
}

$subtotal  = $physical_subtotal + $digital_subtotal;
$shipping = $has_physical ? calculate_shipping($subtotal) : 0;
$total     = $subtotal + $shipping;
?>

<div style="margin-bottom: 30px; border-bottom: 1px solid #e2e8f0; padding-bottom: 20px;">
    <h2 style="margin: 0;">Checkout</h2>
</div>

<div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 30px; align-items: start;">

    <!-- Information Form -->
    <div class="responsive-box" style="background: #fff; padding: 30px; border-radius: 8px; border: 1px solid #e2e8f0;">
        <h3 style="margin: 0 0 25px; font-size: 1.1rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 15px;">Shipping & Billing</h3>
        
        <form method="POST" action="<?php echo $base_url; ?>/api/checkout-place-order.php" id="checkout-form">
            <?php echo csrf_field(); ?>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 500; margin-bottom: 8px;">Order Name (Recipient)</label>
                <input type="text" name="ship_name" required placeholder="John Doe"
                       style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 500; margin-bottom: 8px;">Contact Phone</label>
                <input type="text" name="ship_phone" placeholder="+1..."
                       style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
            </div>

            <?php if ($has_physical): ?>
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 500; margin-bottom: 8px;">Delivery Address</label>
                    <input type="text" name="ship_address1" required placeholder="Street address"
                           style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; margin-bottom: 10px;">
                    <input type="text" name="ship_address2" placeholder="Apartment, suite, etc. (optional)"
                           style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 30px;">
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 500; margin-bottom: 5px;">City</label>
                        <input type="text" name="ship_city" required
                               style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 500; margin-bottom: 5px;">Country</label>
                        <input type="text" name="ship_country" required
                               style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                    </div>
                </div>
            <?php else: ?>
                <div style="background: #f8fafc; padding: 15px; border-radius: 6px; margin-bottom: 25px; border: 1px solid #e2e8f0; font-size: 0.85rem; color: #64748b;">
                    Only digital products in cart. Delivery address is not required.
                </div>
            <?php endif; ?>

            <button type="submit" class="btn" style="width: 100%; padding: 14px; border-radius: 6px; font-weight: bold; font-size: 1rem;">
                Confirm & Place Order
            </button>
        </form>
    </div>

    <!-- Summary -->
    <div class="responsive-box" style="background: #fff; padding: 30px; border-radius: 8px; border: 1px solid #e2e8f0;">
        <h3 style="margin: 0 0 20px; font-size: 1rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;">Order Summary</h3>
        
        <div style="margin-bottom: 20px;">
            <?php foreach ($cart_items as $item): ?>
            <div style="display: flex; justify-content: space-between; font-size: 0.9rem; padding: 6px 0; border-bottom: 1px dashed #f1f5f9;">
                <span style="color: #475569;"><?php echo h($item['product']['name']); ?> (x<?php echo $item['quantity']; ?>)</span>
                <span style="font-weight: 500;">£<?php echo number_format($item['subtotal'], 2); ?></span>
            </div>
            <?php endforeach; ?>
        </div>

        <div style="display: flex; justify-content: space-between; font-size: 0.9rem; margin-bottom: 10px;">
            <span style="color: #64748b;">Subtotal</span>
            <span>£<?php echo number_format($subtotal, 2); ?></span>
        </div>
        <div style="display: flex; justify-content: space-between; font-size: 0.9rem; margin-bottom: 20px;">
            <span style="color: #64748b;">Shipping</span>
            <span>£<?php echo number_format($shipping, 2); ?></span>
        </div>
        
        <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 1.25rem; border-top: 2px solid #f1f5f9; padding-top: 15px; color: var(--primary-color);">
            <span>Order Total</span>
            <span>£<?php echo number_format($total, 2); ?></span>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>