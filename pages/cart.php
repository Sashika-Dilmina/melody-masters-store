<?php
require_once __DIR__ . '/../includes/header.php';

$cart = $_SESSION['cart'] ?? [];
$cart_items = [];
$total = 0;

if (!empty($cart)) {
    $ids = array_map('intval', array_keys($cart));
    if (!empty($ids)) {
        $id_list = implode(',', $ids);
        $products = fetch_all("SELECT * FROM products WHERE id IN ($id_list)");
        
        foreach ($products as $p) {
            $qty = $cart[$p['id']]['quantity'];
            $subtotal = $p['price'] * $qty;
            $total += $subtotal;
            $cart_items[] = [
                'product' => $p,
                'quantity' => $qty,
                'subtotal' => $subtotal
            ];
        }
    }
}
?>

<h2>Shopping Cart</h2>

<?php if (empty($cart_items)): ?>
    <p>Your cart is empty. <a href="<?php echo $base_url; ?>/pages/shop.php">Browse our shop</a>.</p>
<?php else: ?>
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
        <thead>
            <tr style="background: var(--primary-color); color: #fff;">
                <th style="padding: 10px; text-align: left;">Product</th>
                <th style="padding: 10px; text-align: center;">Price</th>
                <th style="padding: 10px; text-align: center;">Quantity</th>
                <th style="padding: 10px; text-align: right;">Subtotal</th>
                <th style="padding: 10px; text-align: center;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cart_items as $item): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 15px;">
                        <a href="product.php?id=<?php echo $item['product']['id']; ?>" style="color: var(--primary-color); text-decoration: none; font-weight: bold;">
                            <?php echo h($item['product']['name']); ?>
                        </a>
                        <br><span style="font-size: 0.8rem; color: #777;"><?php echo ucfirst($item['product']['product_type']); ?></span>
                    </td>
                    <td style="padding: 15px; text-align: center;">£<?php echo number_format($item['product']['price'], 2); ?></td>
                    <td style="padding: 15px; text-align: center;">
                        <form method="POST" action="<?php echo $base_url; ?>/api/cart-update.php" style="display: flex; gap: 5px; justify-content: center;">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="product_id" value="<?php echo $item['product']['id']; ?>">
                            <?php if ($item['product']['product_type'] === 'physical'): ?>
                                <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['product']['stock_qty']; ?>" style="width: 60px; padding: 5px;">
                            <?php else: ?>
                                <input type="number" name="quantity" value="1" readonly style="width: 60px; padding: 5px; background: #eee;">
                            <?php endif; ?>
                            <button type="submit" class="btn" style="padding: 5px 10px; font-size: 0.8rem;">Update</button>
                        </form>
                    </td>
                    <td style="padding: 15px; text-align: right;">£<?php echo number_format($item['subtotal'], 2); ?></td>
                    <td style="padding: 15px; text-align: center;">
                        <form method="POST" action="<?php echo $base_url; ?>/api/cart-remove.php">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="product_id" value="<?php echo $item['product']['id']; ?>">
                            <button type="submit" class="btn" style="background: var(--secondary-color); padding: 5px 10px; font-size: 0.8rem;">Remove</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr style="background: #f9f9f9;">
                <td colspan="3" style="padding: 15px; text-align: right; font-weight: bold;">Cart Total:</td>
                <td style="padding: 15px; text-align: right; font-weight: bold; font-size: 1.2rem;">£<?php echo number_format($total, 2); ?></td>
                <td></td>
            </tr>
        </tfoot>
    </table>
    
    <div style="text-align: right;">
        <a href="<?php echo $base_url; ?>/pages/checkout.php" class="btn" style="font-size: 1.1rem; padding: 12px 24px; background: #27ae60;">Proceed to Checkout</a>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>