<?php
// pages/product.php – product detail page
require_once __DIR__ . '/../includes/header.php';

$id      = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = fetch_one(
    "SELECT p.*, c.name AS category_name
     FROM products p
     LEFT JOIN categories c ON p.category_id = c.id
     WHERE p.id = ? AND p.is_active = 1",
    "i", [$id]
);

if (!$product) {
    set_flash_message('error', 'Product not found.');
    header('Location: ' . $base_url . '/pages/shop.php');
    exit;
}

// Average rating
$rating_row = fetch_one(
    "SELECT ROUND(AVG(rating),1) AS avg_rating, COUNT(*) AS total FROM reviews WHERE product_id = ?",
    "i", [$id]
);
$avg_rating  = (float)($rating_row['avg_rating'] ?? 0);
$total_revs  = (int)($rating_row['total'] ?? 0);
?>

<!-- Breadcrumb -->
<nav style="font-size:0.85rem; color:#94a3b8; margin-bottom:20px;">
    <a href="<?php echo $base_url; ?>/pages/shop.php" style="color:var(--primary-color);">Shop</a>
    <?php if ($product['category_name']): ?>
        &rsaquo;
        <a href="<?php echo $base_url; ?>/pages/shop.php?category_id=<?php echo $product['category_id']; ?>"
           style="color:var(--primary-color);"><?php echo h($product['category_name']); ?></a>
    <?php endif; ?>
    &rsaquo; <?php echo h($product['name']); ?>
</nav>

<!-- Main product card -->
<div style="display:flex; flex-wrap:wrap; gap:30px; background:#fff; padding:25px;
            border-radius:8px; border: 1px solid #e2e8f0; margin-bottom:30px;">

    <!-- Image -->
    <div style="flex:1; min-width:280px; background: #f8fafc; display: flex; align-items: center; justify-content: center; border-radius: 8px;">
        <?php if ($product['image_path']): ?>
            <img src="<?php echo $base_url . '/uploads/products/' . h($product['image_path']); ?>"
                 alt="<?php echo h($product['name']); ?>"
                 style="width:100%; border-radius:8px; object-fit:cover;">
        <?php else: ?>
            <div style="color:#cbd5e1; font-weight: 500;">No Image Available</div>
        <?php endif; ?>
    </div>

    <!-- Details -->
    <div style="flex:1; min-width:280px;">
        <span style="font-size:0.75rem; color:#94a3b8; text-transform:uppercase; letter-spacing:.05em;">
            <?php echo h($product['category_name'] ?? 'General'); ?>
            &bull; <?php echo ucfirst($product['product_type']); ?>
            <?php if ($product['brand']): ?>&bull; <?php echo h($product['brand']); ?><?php endif; ?>
        </span>

        <h1 style="margin:10px 0 5px; font-size:1.6rem;"><?php echo h($product['name']); ?></h1>

        <!-- Star rating -->
        <?php if ($total_revs > 0): ?>
        <div style="display:flex; align-items:center; gap:6px; margin-bottom:10px;">
            <span style="color:#f59e0b; font-size:1rem;">
                <?php for ($s = 1; $s <= 5; $s++): ?>
                    <?php echo $s <= round($avg_rating) ? '★' : '☆'; ?>
                <?php endfor; ?>
            </span>
            <span style="font-size:0.85rem; color:#64748b;">
                <?php echo number_format($avg_rating, 1); ?> / 5 (<?php echo $total_revs; ?> reviews)
            </span>
        </div>
        <?php endif; ?>

        <p style="font-size:1.8rem; color:var(--primary-color); font-weight:bold; margin:10px 0 15px;">
            £<?php echo number_format($product['price'], 2); ?>
        </p>

        <?php if ($product['short_description']): ?>
            <p style="color:#475569; margin-bottom:15px; line-height:1.6; font-size: 0.95rem;">
                <?php echo h($product['short_description']); ?>
            </p>
        <?php endif; ?>

        <div style="margin-bottom: 25px;">
            <?php if ($product['product_type'] === 'physical'): ?>
                <div style="margin-bottom: 8px; font-size: 0.9rem;">
                    <strong>Stock:</strong>
                    <?php if ($product['stock_qty'] > 0): ?>
                        <span style="color:#16a34a; font-weight:600;"><?php echo $product['stock_qty']; ?> in stock</span>
                    <?php else: ?>
                        <span style="color:#dc2626; font-weight:600;">Out of stock</span>
                    <?php endif; ?>
                </div>
                <div style="font-size: 0.85rem; color: #64748b;">
                    Free shipping on orders over £<?php echo number_format(FREE_SHIPPING_THRESHOLD, 0); ?>
                </div>
            <?php else: ?>
                <div style="font-size: 0.85rem; color: var(--secondary-color); font-weight: 500;">
                    Instant digital download available after purchase
                </div>
            <?php endif; ?>
        </div>

        <?php if (($product['product_type'] === 'digital' || $product['stock_qty'] > 0) && get_current_user_role() !== 'admin'): ?>
        <form method="POST" action="<?php echo $base_url; ?>/api/cart-add.php"
              style="display:flex; gap:10px; align-items:center;">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
            <?php if ($product['product_type'] === 'physical'): ?>
                <label style="font-size: 0.9rem;">Qty:</label>
                <input type="number" name="quantity" value="1" min="1"
                       max="<?php echo $product['stock_qty']; ?>"
                       style="width:60px; padding:8px; border:1px solid #cbd5e1; border-radius:4px;">
            <?php else: ?>
                <input type="hidden" name="quantity" value="1">
            <?php endif; ?>
            <button type="submit" class="btn" style="padding:10px 25px; border-radius: 4px;">
                Add to Cart
            </button>
        </form>
        <?php elseif (get_current_user_role() === 'admin'): ?>
            <div style="background: #f8fafc; padding: 15px; border-radius: 6px; border: 1px solid #e2e8f0; color: #64748b; font-size: 0.9rem;">
                Administrators viewing product details. Purchase capability is disabled for management roles.
            </div>
        <?php endif; ?>

        <!-- Full description -->
        <?php if ($product['description']): ?>
        <div style="margin-top:25px; padding-top:25px; border-top:1px solid #f1f5f9;
                    color:#475569; line-height:1.7; font-size:0.9rem;">
            <?php echo nl2br(h($product['description'])); ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Reviews -->
<div style="background:#fff; padding:25px; border-radius:8px; border: 1px solid #e2e8f0;">
    <div style="display:flex; justify-content:space-between; align-items:center;
                border-bottom:1px solid #f1f5f9; padding-bottom:15px; margin-bottom:20px;">
        <h2 style="margin:0; font-size:1.1rem;">Customer Reviews</h2>

        <?php
        $can_review = false;
        if (is_logged_in()) {
            $user_id = get_current_user_id();
            $purchased = fetch_one(
                "SELECT oi.id FROM order_items oi
                 JOIN orders o ON oi.order_id = o.id
                 WHERE o.user_id = ? AND oi.product_id = ? AND o.status != 'cancelled'
                 LIMIT 1",
                "ii", [$user_id, $id]
            );
            $already_reviewed = fetch_one(
                "SELECT id FROM reviews WHERE user_id = ? AND product_id = ?",
                "ii", [$user_id, $id]
            );
            $can_review = $purchased && !$already_reviewed;
        }
        ?>
        <?php if ($can_review): ?>
            <a href="<?php echo $base_url; ?>/pages/review-submit.php?product_id=<?php echo $id; ?>"
               class="btn" style="padding:6px 14px; font-size:0.85rem; border-radius: 4px;">Write a Review</a>
        <?php endif; ?>
    </div>

    <?php
    $reviews = fetch_all(
        "SELECT r.*, u.full_name AS username
         FROM reviews r
         JOIN users u ON r.user_id = u.id
         WHERE r.product_id = ?
         ORDER BY r.created_at DESC",
        "i", [$id]
    );
    ?>

    <?php if (empty($reviews)): ?>
        <p style="color:#94a3b8; text-align:center; padding:20px 0; font-size: 0.9rem;">
            No reviews for this product yet.
        </p>
    <?php else: ?>
        <div style="display:flex; flex-direction:column; gap:20px;">
            <?php foreach ($reviews as $review): ?>
            <div style="border-bottom:1px solid #f8fafc; padding-bottom:15px;">
                <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                    <strong style="font-size: 0.95rem;"><?php echo h($review['username']); ?></strong>
                    <span style="color:#f59e0b; font-size: 0.9rem;">
                        <?php for ($s = 1; $s <= 5; $s++): ?>
                            <?php echo $s <= $review['rating'] ? '★' : '☆'; ?>
                        <?php endfor; ?>
                    </span>
                </div>
                <p style="color:#475569; font-size:0.9rem; margin:0 0 5px; line-height:1.5;">
                    <?php echo nl2br(h($review['comment'])); ?>
                </p>
                <small style="color:#94a3b8; font-size: 0.75rem;">
                    <?php echo date('F j, Y', strtotime($review['created_at'])); ?>
                </small>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>