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

        if (strtoupper($p['product_type']) === 'PHYSICAL') {
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
$shipping = $has_physical ? calculate_shipping($physical_subtotal) : 0;
$total     = $subtotal + $shipping;
?>

<div class="mb-5 reveal">
    <h1 class="title" style="font-size: 2rem;">Secure Checkout</h1>
    <p class="muted">Please provide your details to complete the order.</p>
</div>

<div class="dashboard-layout reveal" style="grid-template-columns: 1.5fr 1fr;">

    <!-- Information Form -->
    <div class="card">
        <h3 class="mb-5" style="font-size: 1.25rem;">Delivery Information</h3>
        
        <form method="POST" action="<?php echo $base_url; ?>/api/checkout-place-order.php" id="checkout-form">
            <?php echo csrf_field(); ?>

            <div class="grid grid-2 mb-4">
                <div>
                    <label>Recipient Name</label>
                    <input type="text" name="ship_name" required placeholder="Full name" class="input">
                </div>
                <div>
                    <label>Contact Phone</label>
                    <input type="text" name="ship_phone" placeholder="e.g. +44..." class="input">
                </div>
            </div>

            <?php if ($has_physical): ?>
                <div class="mb-4">
                    <label>Delivery Address</label>
                    <input type="text" name="ship_address1" required placeholder="Street and house number" class="input mb-2">
                    <input type="text" name="ship_address2" placeholder="Apartment, suite, unit (optional)" class="input">
                </div>

                <div class="grid grid-2 mb-5">
                    <div>
                        <label>City</label>
                        <input type="text" name="ship_city" required placeholder="City name" class="input">
                    </div>
                    <div>
                        <label>Postcode / ZIP</label>
                        <input type="text" name="ship_country" required placeholder="Region or Country" class="input">
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info mb-5">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                    <span>Digital-only order detected. No physical delivery required. Your masterpieces will be available in your library instantly.</span>
                </div>
            <?php endif; ?>

            <div style="background: rgba(59, 130, 246, 0.05); padding: 1.5rem; border-radius: 12px; border: 1px dashed var(--accent); margin-bottom: 2rem;">
                <h4 style="font-size: 1rem; margin-bottom: 0.5rem; color: var(--accent);">Payment Method</h4>
                <p class="muted text-sm mb-3">Your transaction is secured with 256-bit SSL encryption.</p>
                <div class="row" style="gap: 1rem;">
                     <div class="card" style="flex: 1; padding: 1rem; border: 1.5px solid var(--accent); background: var(--white); cursor: pointer; text-align: center;">
                         <img src="https://img.icons8.com/color/48/000000/visa.png" width="30" alt="Visa">
                         <img src="https://img.icons8.com/color/48/000000/mastercard.png" width="30" alt="MasterCard">
                         <p class="text-xs" style="font-weight: 700; margin-top: 5px; color: var(--accent);">Credit / Debit Card</p>
                     </div>
                     <div class="card" style="flex: 1; padding: 1rem; border: 1.5px solid var(--border); background: var(--bg-soft); cursor: pointer; text-align: center; opacity: 0.6;">
                         <img src="https://img.icons8.com/color/48/000000/paypal.png" width="30" alt="PayPal">
                         <p class="text-xs" style="font-weight: 700; margin-top: 5px;">PayPal</p>
                     </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1.25rem; font-size: 1.125rem; border-radius: 12px;">
                Complete Payment & Place Order
            </button>
        </form>
    </div>

    <!-- Summary -->
    <div class="stack">
        <div class="card" style="position: sticky; top: 100px;">
            <h3 class="mb-4" style="font-size: 1.1rem;">Order Items</h3>
            
            <div class="stack mb-4" style="gap: 1rem;">
                <?php foreach ($cart_items as $item): ?>
                <div class="space-between" style="padding-bottom: 0.75rem; border-bottom: 1px dashed var(--border);">
                    <div style="flex: 1; margin-right: 10px;">
                        <p style="font-weight: 600; font-size: 0.95rem; margin-bottom: 2px;"><?php echo h($item['product']['name']); ?></p>
                        <p class="text-xs muted" style="font-weight: 600; text-transform: uppercase;">Qty: <?php echo $item['quantity']; ?></p>
                    </div>
                    <span style="font-weight: 700; color: var(--primary);">£<?php echo number_format($item['subtotal'], 2); ?></span>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="stack mb-4" style="gap: 0.75rem; font-size: 0.95rem;">
                <div class="space-between">
                    <span class="muted">Subtotal</span>
                    <span style="font-weight: 600;">£<?php echo number_format($subtotal, 2); ?></span>
                </div>
                <div class="space-between">
                    <span class="muted">Shipping</span>
                    <span style="font-weight: 600;"><?php echo $shipping > 0 ? '£' . number_format($shipping, 2) : '<span style="color: var(--success);">Free</span>'; ?></span>
                </div>
            </div>
            
            <div class="divider"></div>
            
            <div class="space-between">
                <span style="font-weight: 700; font-size: 1rem;">Total to Pay</span>
                <span style="font-weight: 800; font-size: 1.5rem; color: var(--accent);">£<?php echo number_format($total, 2); ?></span>
            </div>
            
            <div style="margin-top: 2rem; padding: 1rem; background: var(--bg-soft); border-radius: 10px; display: flex; align-items: center; gap: 10px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--success); flex-shrink: 0;"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                <p class="text-xs muted">Your payment is encrypted and handled by a secure gateway. We do not store your credit card details.</p>
            </div>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>