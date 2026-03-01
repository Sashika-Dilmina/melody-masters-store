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

<div class="mb-5 reveal text-center">
    <h1 class="title" style="font-size: 2rem;">Product Experience Review</h1>
    <p class="muted">Share your musical journey with <strong><?php echo h($product['name']); ?></strong>.</p>
</div>

<div class="reveal" style="max-width: 600px; margin: 0 auto; animation-delay: 0.1s;">
    <div class="card" style="padding: 3rem;">
        <form method="POST" action="<?php echo $base_url; ?>/api/review-save.php">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
            
            <div class="mb-5">
                <label class="mb-3" style="display: block; font-weight: 700; color: var(--primary);">1. How would you rate this item?</label>
                <select name="rating" required class="select" style="font-size: 1rem; padding: 1rem;">
                    <option value="5">★★★★★ - Masterpiece (5/5)</option>
                    <option value="4">★★★★☆ - Very Good (4/5)</option>
                    <option value="3">★★★☆☆ - Average (3/5)</option>
                    <option value="2">★★☆☆☆ - Poor (2/5)</option>
                    <option value="1">★☆☆☆☆ - Unsatisfactory (1/5)</option>
                </select>
            </div>
            
            <div class="mb-5">
                <label class="mb-3" style="display: block; font-weight: 700; color: var(--primary);">2. Detailed Feedback</label>
                <textarea name="comment" required placeholder="What did you like? How was the sound quality or manufacturing?"
                          class="textarea" style="height: 180px; padding: 1rem;"></textarea>
                <p class="text-xs muted mt-2">Your review will be public and attribute to your display name.</p>
            </div>
            
            <div class="stack">
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1.25rem; font-weight: 800; letter-spacing: 0.5px;">PUBLISH REVIEW</button>
                <a href="product.php?id=<?php echo $product_id; ?>" class="btn btn-outline" style="width: 100%; padding: 1rem; margin-top: 1rem;">Back to Product</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
