<?php
require_once __DIR__ . '/../includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = fetch_one("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?", "i", [$id]);

if (!$product) {
    set_flash_message('error', 'Product not found.');
    header('Location: ' . $base_url . '/pages/shop.php');
    exit;
}
?>

<div style="display: flex; flex-wrap: wrap; gap: 30px; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
    <div style="flex: 1; min-width: 300px;">
        <?php if ($product['image_path']): ?>
            <img src="<?php echo $base_url . '/uploads/products/' . h($product['image_path']); ?>" alt="<?php echo h($product['name']); ?>" style="width: 100%; border-radius: 8px;">
        <?php else: ?>
            <div style="width: 100%; height: 300px; background: #ccc; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #666;">No Image</div>
        <?php endif; ?>
    </div>
    
    <div style="flex: 1; min-width: 300px;">
        <span style="color: #777; text-transform: uppercase; font-size: 0.9rem;"><?php echo h($product['category_name']); ?> | <?php echo ucfirst($product['product_type']); ?></span>
        <h2 style="margin: 10px 0;"><?php echo h($product['name']); ?></h2>
        <p style="font-size: 1.5rem; color: var(--secondary-color); font-weight: bold; margin-bottom: 20px;">£<?php echo number_format($product['price'], 2); ?></p>
        
        <div style="margin-bottom: 20px; line-height: 1.8;">
            <?php echo nl2br(h($product['description'])); ?>
        </div>
        
        <?php if ($product['product_type'] === 'physical'): ?>
            <p style="margin-bottom: 20px;"><strong>Stock Status:</strong> 
                <?php if ($product['stock_qty'] > 0): ?>
                    <span style="color: green;"><?php echo $product['stock_qty']; ?> in stock</span>
                <?php else: ?>
                    <span style="color: red;">Out of stock</span>
                <?php endif; ?>
            </p>
        <?php endif; ?>

        <?php if ($product['product_type'] === 'digital' || $product['stock_qty'] > 0): ?>
            <form method="POST" action="<?php echo $base_url; ?>/api/cart-add.php" style="display: flex; gap: 10px; align-items: center;">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                <label>Qty:</label>
                <?php if ($product['product_type'] === 'physical'): ?>
                    <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['stock_qty']; ?>" style="width: 60px; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                <?php else: ?>
                    <input type="number" name="quantity" value="1" min="1" max="1" readonly style="width: 60px; padding: 8px; border: 1px solid #ccc; border-radius: 4px; background: #eee;">
                <?php endif; ?>
                <button type="submit" class="btn" style="background: var(--secondary-color);">Add to Cart</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<div style="margin-top: 40px; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 20px;">
        <h3>Customer Reviews</h3>
        <?php
        $can_review = false;
        if (is_logged_in()) {
            $user_id = get_current_user_id();
            // Check purchased
            $sql = "SELECT oi.id FROM order_items oi JOIN orders o ON oi.order_id = o.id WHERE o.user_id = ? AND oi.product_id = ? AND o.status != 'cancelled' LIMIT 1";
            $has_purchased = fetch_one($sql, "ii", [$user_id, $id]);
            // Check not already reviewed
            $existing_review = fetch_one("SELECT id FROM reviews WHERE user_id = ? AND product_id = ?", "ii", [$user_id, $id]);
            
            if ($has_purchased && !$existing_review) {
                $can_review = true;
            }
        }
        ?>
        <?php if ($can_review): ?>
            <a href="<?php echo $base_url; ?>/pages/review-submit.php?product_id=<?php echo $id; ?>" class="btn">Write a Review</a>
        <?php endif; ?>
    </div>
    
    <?php
    $reviews = fetch_all("SELECT r.*, u.full_name as username FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = ? ORDER BY r.created_at DESC", "i", [$id]);
    ?>
    
    <?php if (empty($reviews)): ?>
        <p>No reviews yet. Be the first to review this product!</p>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <?php foreach ($reviews as $review): ?>
                <div style="border-bottom: 1px solid #f1f5f9; padding-bottom: 15px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <strong><?php echo h($review['username']); ?></strong>
                        <span style="color: #f59e0b;">
                            <?php for($i=1; $i<=5; $i++): ?>
                                <?php if($i <= $review['rating']): ?>
                                    <i class="fa-solid fa-star"></i>
                                <?php else: ?>
                                    <i class="fa-regular fa-star"></i>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </span>
                    </div>
                    <p style="color: #475569; font-size: 0.95rem; margin: 0; line-height: 1.5;"><?php echo nl2br(h($review['comment'])); ?></p>
                    <small style="color: #94a3b8; display: block; margin-top: 5px;"><?php echo date('F j, Y', strtotime($review['created_at'])); ?></small>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>