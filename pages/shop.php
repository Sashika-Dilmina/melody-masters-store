<?php
// pages/shop.php
require_once __DIR__ . '/../includes/header.php';

$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$search      = sanitize_input($_GET['q'] ?? '');

$categories  = fetch_all("SELECT id, name FROM categories ORDER BY name ASC");

// Build query – only active products
$sql    = "SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_active = 1";
$params = [];
$types  = '';

if ($category_id > 0) {
    $sql    .= " AND p.category_id = ?";
    $params[] = $category_id;
    $types   .= "i";
}
if (!empty($search)) {
    $sql    .= " AND (p.name LIKE ? OR p.short_description LIKE ?)";
    $like     = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $types   .= "ss";
}
$sql .= " ORDER BY p.id DESC";
$products = fetch_all($sql, $types, $params);
?>

<div class="space-between mb-5 reveal">
    <h1 class="title" style="font-size: 2rem; margin: 0;">Our Collection</h1>
    
    <form method="GET" action="" class="row">
        <div style="position: relative;">
            <input type="text" name="q" placeholder="Search instruments..." value="<?php echo h($search); ?>" class="input" style="min-width: 280px; padding-left: 2.5rem;">
            <svg style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted);" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        </div>
        <?php if ($category_id): ?>
            <input type="hidden" name="category_id" value="<?php echo $category_id; ?>">
        <?php endif; ?>
        <button type="submit" class="btn btn-primary">Search</button>
        <?php if ($search): ?>
            <a href="shop.php<?php echo $category_id ? '?category_id='.$category_id : ''; ?>" class="btn btn-outline">Clear</a>
        <?php endif; ?>
    </form>
</div>

<div class="alert alert-info mb-5 reveal" style="animation-delay: 0.1s;">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
    <span>Free shipping on orders over <strong>£<?php echo number_format(FREE_SHIPPING_THRESHOLD, 0); ?></strong>. Standard rate £<?php echo number_format(FLAT_SHIPPING_FEE, 2); ?>.</span>
</div>

<div class="mb-5 reveal" style="animation-delay: 0.2s;">
    <div class="row" style="flex-wrap: wrap; gap: 0.75rem;">
        <span class="muted text-sm" style="font-weight: 600; text-transform: uppercase;">Filter by:</span>
        <a href="shop.php<?php echo $search ? '?q='.urlencode($search) : ''; ?>"
           class="btn <?php echo $category_id === 0 ? 'btn-primary' : 'btn-outline'; ?>" style="padding: 0.5rem 1.25rem; font-size: 0.85rem; border-radius: 999px;">All Products</a>
        <?php foreach ($categories as $cat): ?>
            <a href="shop.php?category_id=<?php echo $cat['id']; ?><?php echo $search ? '&q='.urlencode($search) : ''; ?>"
               class="btn <?php echo $category_id === $cat['id'] ? 'btn-primary' : 'btn-outline'; ?>" style="padding: 0.5rem 1.25rem; font-size: 0.85rem; border-radius: 999px;">
                <?php echo h($cat['name']); ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<?php if (empty($products)): ?>
    <div class="card text-center reveal" style="padding: 5rem 2rem;">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" style="color: var(--text-muted); margin-bottom: 1.5rem;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        <p class="muted mb-4">No products found matching your current selection.</p>
        <a href="shop.php" class="btn btn-primary">Clear all filters</a>
    </div>
<?php else: ?>
<div class="grid grid-4 mb-5">
    <?php foreach ($products as $index => $product): ?>
    <div class="card card-hover reveal" style="padding: 0; overflow: hidden; animation-delay: <?php echo 0.05 * $index; ?>s;">
        <a href="<?php echo $base_url; ?>/pages/product.php?id=<?php echo $product['id']; ?>" style="display: block; background: #f8fafc; height: 220px; position: relative;">
            <?php if ($product['image_path']): ?>
                <img src="<?php echo $base_url . '/uploads/products/' . h($product['image_path']); ?>"
                        alt="<?php echo h($product['name']); ?>"
                        style="width: 100%; height: 100%; object-fit: cover;">
            <?php else: ?>
                <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #cbd5e1; font-weight: 500;">No Image</div>
            <?php endif; ?>
            
            <div style="position: absolute; top: 12px; right: 12px;">
                <span class="badge <?php echo strtoupper($product['product_type']) === 'DIGITAL' ? 'badge-digital' : 'badge-physical'; ?>">
                    <?php echo h($product['product_type']); ?>
                </span>
            </div>
        </a>

        <div style="padding: 1.25rem; display: flex; flex-direction: column; flex: 1;">
            <p class="text-xs muted mb-1" style="font-size: 0.75rem; text-transform: uppercase; font-weight: 600;"><?php echo h($product['category_name'] ?? 'General'); ?></p>
            <h3 style="margin-bottom: 0.75rem; font-size: 1.05rem; line-height: 1.4; height: 2.8em; overflow: hidden;">
                <a href="<?php echo $base_url; ?>/pages/product.php?id=<?php echo $product['id']; ?>" style="color: inherit;">
                    <?php echo h($product['name']); ?>
                </a>
            </h3>
            
            <p style="font-weight: 800; font-size: 1.25rem; color: var(--primary); margin-bottom: 1.25rem;">
                £<?php echo number_format($product['price'], 2); ?>
            </p>

            <?php if (get_current_user_role() !== 'admin'): ?>
            <div class="row" style="margin-top: auto; gap: 0.5rem;">
                <?php if ((strtoupper($product['product_type']) === 'PHYSICAL' && $product['stock_qty'] > 0) || strtoupper($product['product_type']) === 'DIGITAL'): ?>
                    <form method="POST" action="<?php echo $base_url; ?>/api/cart-add.php" style="flex: 1;">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.6rem; font-size: 0.85rem;">
                            Add to Cart
                        </button>
                    </form>
                <?php else: ?>
                    <button class="btn btn-outline" disabled style="flex: 1; opacity: 0.6; cursor: not-allowed; font-size: 0.85rem;">Sold Out</button>
                <?php endif; ?>
                <a href="<?php echo $base_url; ?>/pages/product.php?id=<?php echo $product['id']; ?>"
                   class="btn btn-outline" style="padding: 0.6rem; font-size: 0.85rem;">Details</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>