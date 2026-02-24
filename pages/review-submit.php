<?php
// pages/review-submit.php
require_once __DIR__ . '/../includes/header.php';
require_login();

$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
$user_id = get_current_user_id();

$product = fetch_one("SELECT * FROM products WHERE id = ?", "i", [$product_id]);
if (!$product) {
    set_flash_message('error', 'Product not found.');
    header('Location: ' . $base_url . '/pages/shop.php');
    exit;
}

// Verify if user bought the product
$sql = "SELECT oi.id FROM order_items oi JOIN orders o ON oi.order_id = o.id WHERE o.user_id = ? AND oi.product_id = ? AND o.status != 'cancelled' LIMIT 1";
$has_purchased = fetch_one($sql, "ii", [$user_id, $product_id]);

if (!$has_purchased) {
    set_flash_message('error', 'You can only review products you have purchased.');
    header('Location: ' . $base_url . '/pages/product.php?id=' . $product_id);
    exit;
}

// Check if already reviewed
$existing_review = fetch_one("SELECT * FROM reviews WHERE user_id = ? AND product_id = ?", "ii", [$user_id, $product_id]);

if ($existing_review) {
    set_flash_message('error', 'You have already reviewed this product.');
    header('Location: ' . $base_url . '/pages/product.php?id=' . $product_id);
    exit;
}
?>

<div style="max-width: 600px; margin: 0 auto; padding-top: 20px;">
    <div style="margin-bottom: 30px; border-bottom: 1px solid #e2e8f0; padding-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
        <h2 style="margin: 0;">Submit Review</h2>
        <a href="product.php?id=<?php echo $product_id; ?>" style="font-size: 0.9rem; color: #64748b;">Return to Product</a>
    </div>

    <div style="background: #fff; padding: 30px; border: 1px solid #e2e8f0; border-radius: 8px;">
        <p style="margin-bottom: 25px; color: #475569;">You are reviewing: <strong><?php echo h($product['name']); ?></strong></p>
        
        <form method="POST" action="<?php echo $base_url; ?>/api/review-save.php">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.9rem; font-weight: 500; margin-bottom: 8px;">Rating Quality</label>
                <select name="rating" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem;">
                    <option value="5">5 - Excellent</option>
                    <option value="4">4 - Very Good</option>
                    <option value="3">3 - Average</option>
                    <option value="2">2 - Poor</option>
                    <option value="1">1 - Unsatisfactory</option>
                </select>
            </div>
            
            <div style="margin-bottom: 25px;">
                <label style="display: block; font-size: 0.9rem; font-weight: 500; margin-bottom: 8px;">Written Feedback</label>
                <textarea name="comment" required placeholder="Describe your experience with this item..."
                          style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; height: 120px; font-size: 0.9rem; font-family: inherit;"></textarea>
            </div>
            
            <button type="submit" class="btn" style="width: 100%; padding: 12px; border-radius: 6px; font-weight: bold;">Post Review</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
