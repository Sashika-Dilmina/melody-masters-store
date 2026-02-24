<?php
require_once __DIR__ . '/../includes/header.php';
require_login();

$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
$user_id = get_current_user_id();

$product = fetch_one("SELECT * FROM products WHERE id = ?", "i", [$product_id]);
if (!$product) {
    die('Product not found.');
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

<h2>Write a Review</h2>
<div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); max-width: 600px; margin: 0 auto;">
    <h3 style="margin-bottom: 20px;">Reviewing: <?php echo h($product['name']); ?></h3>
    <form method="POST" action="<?php echo $base_url; ?>/api/review-save.php">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
        
        <div style="margin-bottom: 15px;">
            <label>Rating (1 to 5)</label><br>
            <select name="rating" required style="width: 100%; padding: 8px;">
                <option value="5">5 - Excellent</option>
                <option value="4">4 - Good</option>
                <option value="3">3 - Average</option>
                <option value="2">2 - Poor</option>
                <option value="1">1 - Terrible</option>
            </select>
        </div>
        
        <div style="margin-bottom: 15px;">
            <label>Comment</label><br>
            <textarea name="comment" required style="width: 100%; padding: 8px; height: 100px;"></textarea>
        </div>
        
        <button type="submit" class="btn" style="width: 100%; background: var(--secondary-color);">Submit Review</button>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
