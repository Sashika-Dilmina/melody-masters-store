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
<nav class="mb-4 reveal" style="font-size: 0.9rem; font-weight: 500;">
    <a href="<?php echo $base_url; ?>/index.php" class="muted">Home</a>
    <span class="muted" style="margin: 0 8px;">/</span>
    <a href="<?php echo $base_url; ?>/pages/shop.php" class="muted">Shop</a>
    <?php if ($product['category_name']): ?>
        <span class="muted" style="margin: 0 8px;">/</span>
        <a href="<?php echo $base_url; ?>/pages/shop.php?category_id=<?php echo $product['category_id']; ?>" class="muted"><?php echo h($product['category_name']); ?></a>
    <?php endif; ?>
    <span class="muted" style="margin: 0 8px;">/</span>
    <span style="color: var(--primary); font-weight: 600;"><?php echo h($product['name']); ?></span>
</nav>

<div class="grid grid-2 reveal" style="gap: 4rem; align-items: start; margin-bottom: 4rem;">
    <!-- Product Gallery/Image -->
    <div class="card" style="padding: 1rem; border-radius: var(--radius-lg); background: var(--bg-soft); border: none;">
        <div style="aspect-ratio: 1/1; display: flex; align-items: center; justify-content: center; overflow: hidden; border-radius: var(--radius);">
            <?php if ($product['image_path']): ?>
                <img src="<?php echo $base_url . '/uploads/products/' . h($product['image_path']); ?>"
                     alt="<?php echo h($product['name']); ?>"
                     style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease;"
                     onmouseover="this.style.transform='scale(1.05)'"
                     onmouseout="this.style.transform='scale(1)'">
            <?php else: ?>
                <div class="text-center">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" style="color: #cbd5e1; margin-bottom: 1rem;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                    <p class="muted">No preview image available</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Product Info -->
    <div>
        <div class="mb-4">
            <span class="badge <?php echo strtoupper($product['product_type']) === 'DIGITAL' ? 'badge-digital' : 'badge-physical'; ?> mb-3">
                <?php echo h($product['product_type']); ?>
            </span>
            <h1 class="title mb-2" style="font-size: 2.5rem;"><?php echo h($product['name']); ?></h1>
            <p class="text-lg muted mb-3"><?php echo h($product['brand'] ?? 'Premium Collection'); ?></p>
            
            <?php if ($total_revs > 0): ?>
            <div class="row" style="gap: 8px;">
                <div style="color: #f59e0b; display: flex; gap: 2px;">
                    <?php for ($s = 1; $s <= 5; $s++): ?>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="<?php echo $s <= round($avg_rating) ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                    <?php endfor; ?>
                </div>
                <span class="text-sm" style="font-weight: 600;"><?php echo number_format($avg_rating, 1); ?></span>
                <span class="text-sm muted">(<?php echo $total_revs; ?> verified reviews)</span>
            </div>
            <?php endif; ?>
        </div>

        <div class="mb-5">
            <h2 style="font-size: 2.25rem; font-weight: 800; color: var(--accent); margin-bottom: 1.5rem;">£<?php echo number_format($product['price'], 2); ?></h2>
            
            <p class="text-lg" style="color: var(--primary-light); line-height: 1.6; margin-bottom: 2rem;">
                <?php echo h($product['short_description']); ?>
            </p>

            <div class="card" style="background: var(--bg-soft); border: none; padding: 1.25rem; margin-bottom: 2rem;">
                <div class="row" style="align-items: flex-start; gap: 1rem;">
                    <div style="width: 40px; height: 40px; background: var(--white); border-radius: 10px; display: flex; align-items: center; justify-content: center; box-shadow: var(--shadow-sm);">
                        <?php if (strtoupper($product['product_type']) === 'DIGITAL'): ?>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--accent);"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        <?php else: ?>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--success);"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h4 class="mb-1" style="font-size: 0.95rem;">
                            <?php echo strtoupper($product['product_type']) === 'DIGITAL' ? 'Instant Access' : 'Quick Delivery'; ?>
                        </h4>
                        <p class="muted text-sm">
                            <?php echo strtoupper($product['product_type']) === 'DIGITAL' 
                                ? 'Download your files immediately after payment confirmation.' 
                                : ($product['stock_qty'] > 0 ? 'In stock and ready to ship worldwide.' : 'Currently out of stock. Contact us for availability.'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <?php if ((strtoupper($product['product_type']) === 'DIGITAL' || $product['stock_qty'] > 0) && get_current_user_role() !== 'admin'): ?>
        <form method="POST" action="<?php echo $base_url; ?>/api/cart-add.php" class="row" style="gap: 1rem;">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
            <?php if (strtoupper($product['product_type']) === 'PHYSICAL'): ?>
                <div style="flex: 0 0 100px;">
                    <label class="text-xs mb-1">Quantity</label>
                    <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['stock_qty']; ?>" class="input">
                </div>
            <?php else: ?>
                <input type="hidden" name="quantity" value="1">
            <?php endif; ?>
            <div style="flex: 1; padding-top: 20px;">
                <button type="submit" class="btn btn-primary" style="width: 100%; height: 50px; font-size: 1rem;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                    Add to Cart
                </button>
            </div>
        </form>
        <?php elseif (get_current_user_role() === 'admin'): ?>
            <div class="alert alert-info">
                Purchase capability is disabled for administrator roles.
            </div>
        <?php else: ?>
            <button class="btn btn-outline" disabled style="width: 100%; height: 50px; opacity: 0.6; cursor: not-allowed;">Currently Out of Stock</button>
        <?php endif; ?>
    </div>
</div>

<div class="divider"></div>

<div class="grid grid-2 reveal" style="gap: 4rem; margin-bottom: 5rem;">
    <!-- Description -->
    <div>
        <h3 class="mb-4">Description</h3>
        <div class="muted" style="line-height: 1.8; font-size: 1.05rem;">
            <?php echo nl2br(h($product['description'])); ?>
        </div>
    </div>

    <!-- Reviews Section -->
    <div>
        <div class="space-between mb-4">
            <h3 class="m-0">Customer Reviews</h3>
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
                   class="btn btn-outline" style="padding: 0.5rem 1rem; font-size: 0.85rem;">Write a Review</a>
            <?php endif; ?>
        </div>

        <?php
        $reviews = fetch_all(
            "SELECT r.*, u.full_name AS username
             FROM reviews r
             JOIN users u ON r.user_id = u.id
             WHERE r.product_id = ? AND r.is_approved = 1
             ORDER BY r.created_at DESC",
            "i", [$id]
        );
        ?>

        <?php if (empty($reviews)): ?>
            <div class="card text-center" style="padding: 2.5rem; background: var(--bg-soft); border: none;">
                <p class="muted m-0">No reviews yet for this masterpiece.</p>
            </div>
        <?php else: ?>
            <div class="stack">
                <?php foreach ($reviews as $review): ?>
                <div class="card card-hover" style="padding: 1.25rem;">
                    <div class="space-between mb-2">
                        <strong style="font-size: 1rem; color: var(--primary);"><?php echo h($review['username']); ?></strong>
                        <div style="color: #f59e0b; display: flex; gap: 1px;">
                            <?php for ($s = 1; $s <= 5; $s++): ?>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="<?php echo $s <= $review['rating'] ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <p class="muted text-sm mb-3" style="line-height: 1.6;">
                        <?php echo nl2br(h($review['comment'])); ?>
                    </p>
                    <small class="text-xs muted" style="text-transform: uppercase; font-weight: 600;">
                        <?php echo date('F j, Y', strtotime($review['created_at'])); ?>
                    </small>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>