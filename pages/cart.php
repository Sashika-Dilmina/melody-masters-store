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
            
            if ($p['product_type'] === 'physical') {
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
$shipping = $has_physical ? calculate_shipping($subtotal) : 0;
$total = $subtotal + $shipping;
?>

<div style="margin-bottom: 30px; border-bottom: 1px solid #e2e8f0; padding-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
    <h2 style="margin: 0;">Shopping Cart</h2>
    <a href="<?php echo $base_url; ?>/pages/shop.php" style="color: var(--secondary-color); text-decoration: none; font-size: 0.9rem;">&larr; Back to Shop</a>
</div>

<?php if (empty($cart_items)): ?>
    <div style="background: #fff; border: 1px solid #e2e8f0; padding: 40px; border-radius: 8px; text-align: center;">
        <p style="color: #64748b; margin-bottom: 20px;">Your cart is currently empty.</p>
        <a href="shop.php" class="btn">Browse Shop</a>
    </div>
<?php else: ?>
    <div style="display: grid; grid-template-columns: 1fr 320px; gap: 30px; align-items: start;">
        
        <!-- Cart Table -->
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; text-align: left; font-size: 0.8rem; color: #64748b; text-transform: uppercase;">
                        <th style="padding: 15px;">Item</th>
                        <th style="padding: 15px; text-align: center;">Price</th>
                        <th style="padding: 15px; text-align: center;">Qty</th>
                        <th style="padding: 15px; text-align: right;">Total</th>
                        <th style="padding: 15px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                    <tr style="border-bottom: 1px solid #f1f5f9; font-size: 0.95rem;">
                        <td style="padding: 15px;">
                            <div style="font-weight: 600;"><?php echo h($item['product']['name']); ?></div>
                            <div style="font-size: 0.75rem; color: #94a3b8;"><?php echo ucfirst($item['product']['product_type']); ?></div>
                        </td>
                        <td style="padding: 15px; text-align: center;">£<?php echo number_format($item['product']['price'], 2); ?></td>
                        <td style="padding: 15px; text-align: center;">
                            <form method="POST" action="<?php echo $base_url; ?>/api/cart-update.php" style="display: flex; gap: 5px; justify-content: center;">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="product_id" value="<?php echo $item['product']['id']; ?>">
                                <?php if ($item['product']['product_type'] === 'physical'): ?>
                                    <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['product']['stock_qty']; ?>" 
                                           style="width: 50px; padding: 4px; border: 1px solid #cbd5e1; border-radius: 4px; text-align: center;">
                                <?php else: ?>
                                    <input type="number" name="quantity" value="1" readonly 
                                           style="width: 50px; padding: 4px; border: 1px solid #f1f5f9; border-radius: 4px; text-align: center; background: #f8fafc; color: #94a3b8;">
                                <?php endif; ?>
                                <button type="submit" style="background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 4px; padding: 4px 8px; font-size: 0.7rem; cursor: pointer;">Set</button>
                            </form>
                        </td>
                        <td style="padding: 15px; text-align: right; font-weight: bold;">£<?php echo number_format($item['subtotal'], 2); ?></td>
                        <td style="padding: 15px; text-align: center;">
                            <form method="POST" action="<?php echo $base_url; ?>/api/cart-remove.php">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="product_id" value="<?php echo $item['product']['id']; ?>">
                                <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer; font-size: 0.9rem;" title="Remove Item">Remove</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Summary -->
        <div style="background: #fff; border: 1px solid #e2e8f0; padding: 25px; border-radius: 12px; position: sticky; top: 100px;">
            <h3 style="margin: 0 0 20px; font-size: 1.1rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;">Summary</h3>
            
            <?php if ($has_physical): ?>
                <div style="margin-bottom: 25px;">
                    <?php 
                        $percent = min(100, ($subtotal / FREE_SHIPPING_THRESHOLD) * 100);
                        $remaining = FREE_SHIPPING_THRESHOLD - $subtotal;
                    ?>
                    <div style="display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 6px;">
                        <span style="font-weight: 600; color: #475569;">
                            <?php if ($remaining > 0): ?>
                                £<?php echo number_format($remaining, 2); ?> away from free shipping
                            <?php else: ?>
                                <span style="color: #10b981;">&check; Free shipping unlocked</span>
                            <?php endif; ?>
                        </span>
                        <span style="color: #94a3b8;"><?php echo round($percent); ?>%</span>
                    </div>
                    <div style="height: 6px; background: #f1f5f9; border-radius: 10px; overflow: hidden;">
                        <div style="height: 100%; width: <?php echo $percent; ?>%; background: <?php echo $percent >= 100 ? '#10b981' : 'var(--secondary-color)'; ?>; transition: width 0.4s ease;"></div>
                    </div>
                </div>
            <?php endif; ?>

            <div style="display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 0.95rem;">
                <span style="color: #64748b;">Subtotal</span>
                <span style="font-weight: 500;">£<?php echo number_format($subtotal, 2); ?></span>
            </div>
            
            <div style="display: flex; justify-content: space-between; margin-bottom: 25px; font-size: 0.95rem;">
                <span style="color: #64748b;">Shipping</span>
                <span style="font-weight: 500;"><?php echo $shipping > 0 ? '£' . number_format($shipping, 2) : '<span style="color: #10b981;">Free</span>'; ?></span>
            </div>
            
            <div style="border-top: 1px solid #f1f5f9; padding-top: 15px; display: flex; justify-content: space-between; font-weight: 800; font-size: 1.25rem; margin-bottom: 30px;">
                <span>Total</span>
                <span style="color: var(--primary-color);">£<?php echo number_format($total, 2); ?></span>
            </div>
            
            <a href="checkout.php" class="btn" style="display: block; width: 100%; text-align: center; padding: 14px; border-radius: 8px; font-weight: 700; font-size: 1rem;">Checkout</a>
        </div>

    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>