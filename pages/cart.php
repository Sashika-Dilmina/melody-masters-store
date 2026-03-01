<?php
// pages/cart.php
require_once __DIR__ . '/../includes/header.php';

if (get_current_user_role() === 'admin') {
    set_flash_message('error', 'Administrators are redirected to the dashboard.');
    header('Location: ' . $base_url . '/admin/dashboard.php');
    exit;
}

$cart = $_SESSION['cart'] ?? [];
$cart_items = [];
$physical_subtotal = 0;
$digital_subtotal = 0;
$has_physical = false;

if (!empty($cart)) {
    $ids = array_map('intval', array_keys($cart));
    if (!empty($ids)) {
        $id_list = implode(',', $ids);
        $products = fetch_all("SELECT * FROM products WHERE id IN ($id_list) AND is_active = 1");
        
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
                'product' => $p,
                'quantity' => $qty,
                'subtotal' => $line_total
            ];
        }
    }
}

$subtotal = $physical_subtotal + $digital_subtotal;
$shipping = $has_physical ? calculate_shipping($physical_subtotal) : 0;
$total = $subtotal + $shipping;
?>

<div class="space-between mb-5 reveal">
    <h1 class="title" style="font-size: 2rem; margin: 0;">Shopping Cart</h1>
    <a href="<?php echo $base_url; ?>/pages/shop.php" class="btn btn-link">&larr; Continue Shopping</a>
</div>

<?php if (empty($cart_items)): ?>
    <div class="card text-center reveal" style="padding: 5rem 2rem;">
        <div style="width: 80px; height: 80px; background: var(--bg-soft); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="color: var(--text-muted);"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
        </div>
        <h2 class="mb-2">Your cart is empty</h2>
        <p class="muted mb-5">Looks like you haven't added any masterpieces to your collection yet.</p>
        <a href="shop.php" class="btn btn-primary" style="padding: 1rem 2.5rem;">Start Shopping</a>
    </div>
<?php else: ?>
    <div class="dashboard-layout reveal" style="grid-template-columns: 1fr 350px;">
        
        <!-- Cart Items -->
        <div class="stack">
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="text-center">Price</th>
                            <th class="text-center">Quantity</th>
                            <th class="text-right">Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td>
                                <div class="row" style="gap: 1rem;">
                                    <div style="width: 60px; height: 60px; background: var(--bg-soft); border-radius: 8px; overflow: hidden; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                                        <?php if ($item['product']['image_path']): ?>
                                            <img src="<?php echo $base_url . '/uploads/products/' . h($item['product']['image_path']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                        <?php else: ?>
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" style="color: #cbd5e1;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div style="font-weight: 700; color: var(--primary);"><a href="<?php echo $base_url; ?>/pages/product.php?id=<?php echo $item['product']['id']; ?>" style="color: inherit;"><?php echo h($item['product']['name']); ?></a></div>
                                        <span class="badge <?php echo strtoupper($item['product']['product_type']) === 'DIGITAL' ? 'badge-digital' : 'badge-physical'; ?>" style="font-size: 0.65rem;">
                                            <?php echo h($item['product']['product_type']); ?>
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center" style="font-weight: 600;">£<?php echo number_format($item['product']['price'], 2); ?></td>
                            <td class="text-center">
                                <form method="POST" action="<?php echo $base_url; ?>/api/cart-update.php" class="row" style="justify-content: center; gap: 0.5rem; flex-wrap: nowrap;">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="product_id" value="<?php echo $item['product']['id']; ?>">
                                    <?php if (strtoupper($item['product']['product_type']) === 'PHYSICAL'): ?>
                                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['product']['stock_qty']; ?>" class="input" style="width: 65px; padding: 0.4rem; text-align: center;">
                                    <?php else: ?>
                                        <input type="number" name="quantity" value="1" readonly class="input" style="width: 65px; padding: 0.4rem; text-align: center; background: var(--bg-soft); color: var(--text-muted); border-color: transparent;">
                                    <?php endif; ?>
                                    <button type="submit" class="btn btn-outline" style="padding: 0.4rem 0.75rem; font-size: 0.75rem; min-width: 45px;">Set</button>
                                </form>
                            </td>
                            <td class="text-right" style="font-weight: 700; color: var(--primary);">£<?php echo number_format($item['subtotal'], 2); ?></td>
                            <td class="text-right">
                                <form method="POST" action="<?php echo $base_url; ?>/api/cart-remove.php">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="product_id" value="<?php echo $item['product']['id']; ?>">
                                    <button type="submit" class="btn btn-link" style="color: var(--error); padding: 5px;" title="Remove Item">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Summary -->
        <div class="stack">
            <div class="card" style="position: sticky; top: 100px; padding: 2rem;">
                <h3 class="mb-4" style="font-size: 1.25rem;">Order Summary</h3>
                
                <?php if ($has_physical): ?>
                    <div class="mb-5">
                        <?php 
                            $percent = min(100, ($physical_subtotal / FREE_SHIPPING_THRESHOLD) * 100);
                            $remaining = FREE_SHIPPING_THRESHOLD - $physical_subtotal;
                        ?>
                        <div class="space-between mb-2">
                            <span class="text-sm" style="font-weight: 600; color: var(--text-main);">
                                <?php if ($remaining > 0): ?>
                                    £<?php echo number_format($remaining, 2); ?> to Free Shipping
                                <?php else: ?>
                                    <span style="color: var(--success);">Free shipping unlocked!</span>
                                <?php endif; ?>
                            </span>
                            <span class="text-xs muted"><?php echo round($percent); ?>%</span>
                        </div>
                        <div style="height: 8px; background: var(--bg-soft); border-radius: 10px; overflow: hidden; border: 1px solid var(--border);">
                            <div style="height: 100%; width: <?php echo $percent; ?>%; background: <?php echo $percent >= 100 ? 'var(--success)' : 'var(--accent)'; ?>; transition: width 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275);"></div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="stack mb-5" style="gap: 0.75rem;">
                    <div class="space-between">
                        <span class="muted">Subtotal</span>
                        <span style="font-weight: 600;">£<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <?php if ($has_physical): ?>
                    <div class="space-between">
                        <span class="muted">Shipping</span>
                        <span style="font-weight: 600;"><?php echo $shipping > 0 ? '£' . number_format($shipping, 2) : '<span style="color: var(--success);">Free</span>'; ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="divider" style="margin: 1.5rem 0;"></div>
                
                <div class="space-between mb-5">
                    <span style="font-weight: 700; font-size: 1.1rem;">Total Cost</span>
                    <span style="font-weight: 800; font-size: 1.5rem; color: var(--primary);">£<?php echo number_format($total, 2); ?></span>
                </div>
                
                <a href="checkout.php" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1rem;">Process Checkout</a>
                
                <div class="row" style="justify-content: center; gap: 1rem; margin-top: 1.5rem;">
                    <img src="https://img.icons8.com/color/48/000000/visa.png" width="30" alt="Visa">
                    <img src="https://img.icons8.com/color/48/000000/mastercard.png" width="30" alt="Mastercard">
                    <img src="https://img.icons8.com/color/48/000000/paypal.png" width="30" alt="PayPal">
                </div>
            </div>
        </div>

    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>