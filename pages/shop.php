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

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:10px;">
    <h2 style="margin:0;">Shop</h2>
    <!-- Search bar -->
    <form method="GET" action="" style="display:flex; gap:8px;">
        <input type="text" name="q" placeholder="Search products…" value="<?php echo h($search); ?>"
               style="padding:8px 12px; border:1px solid #ccc; border-radius:4px; min-width:220px;">
        <?php if ($category_id): ?>
            <input type="hidden" name="category_id" value="<?php echo $category_id; ?>">
        <?php endif; ?>
        <button type="submit" class="btn" style="padding:8px 14px;">Search</button>
        <?php if ($search): ?>
            <a href="shop.php<?php echo $category_id ? '?category_id='.$category_id : ''; ?>" class="btn"
               style="background:#94a3b8; padding:8px 14px;">Clear</a>
        <?php endif; ?>
    </form>
</div>

<!-- Shipping notice -->
<div style="background:#f1f5f9; border: 1px solid #e2e8f0; padding:12px 15px; border-radius:4px; margin-bottom:20px; font-size:0.9rem; color:#475569;">
    Free shipping on orders over £<?php echo number_format(FREE_SHIPPING_THRESHOLD, 0); ?>. 
    Standard flat rate £<?php echo number_format(FLAT_SHIPPING_FEE, 2); ?>.
</div>

<!-- Category filter -->
<div style="margin-bottom:20px; display:flex; flex-wrap:wrap; gap:8px; align-items:center;">
    <strong style="margin-right:4px; font-size: 0.9rem;">Categories:</strong>
    <a href="shop.php<?php echo $search ? '?q='.urlencode($search) : ''; ?>"
       class="btn" style="padding:4px 12px; font-size:0.85rem; border-radius: 4px;
           background:<?php echo $category_id === 0 ? 'var(--primary-color)' : '#e2e8f0'; ?>;
           color:<?php echo $category_id === 0 ? '#fff' : '#475569'; ?>;">All</a>
    <?php foreach ($categories as $cat): ?>
        <a href="shop.php?category_id=<?php echo $cat['id']; ?><?php echo $search ? '&q='.urlencode($search) : ''; ?>"
           class="btn" style="padding:4px 12px; font-size:0.85rem; border-radius: 4px;
               background:<?php echo $category_id === $cat['id'] ? 'var(--primary-color)' : '#e2e8f0'; ?>;
               color:<?php echo $category_id === $cat['id'] ? '#fff' : '#475569'; ?>;">
            <?php echo h($cat['name']); ?>
        </a>
    <?php endforeach; ?>
</div>

<?php if (empty($products)): ?>
    <div style="text-align:center; padding:60px 20px; background:#fff; border: 1px solid #e2e8f0; border-radius:8px;">
        <p style="color:#64748b; font-size:1rem; margin:0;">No products match your criteria. <a href="shop.php">View all products</a>.</p>
    </div>
<?php else: ?>
<div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(240px,1fr)); gap:20px;">
    <?php foreach ($products as $product): ?>
    <div style="background:#fff; border: 1px solid #e2e8f0; border-radius:8px; overflow:hidden; display:flex; flex-direction:column;">

        <!-- Product image -->
        <a href="<?php echo $base_url; ?>/pages/product.php?id=<?php echo $product['id']; ?>" style="display: block; background: #f8fafc; height: 200px;">
            <?php if ($product['image_path']): ?>
                <img src="<?php echo $base_url . '/uploads/products/' . h($product['image_path']); ?>"
                        alt="<?php echo h($product['name']); ?>"
                        style="width:100%; height:100%; object-fit:cover; display:block;">
            <?php else: ?>
                <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:#cbd5e1; font-weight: 500;">No Image</div>
            <?php endif; ?>
        </a>

        <div style="padding:15px; flex:1; display:flex; flex-direction:column;">
            <span style="font-size:0.7rem; color:#94a3b8; text-transform:uppercase; letter-spacing:.05em;">
                <?php echo h($product['category_name'] ?? 'Music'); ?>
                <?php if ($product['product_type'] === 'digital'): ?>
                    &bull; Digital
                <?php endif; ?>
            </span>
            <h3 style="margin:6px 0 8px; font-size:1rem; line-height:1.3;">
                <a href="<?php echo $base_url; ?>/pages/product.php?id=<?php echo $product['id']; ?>"
                   style="color:inherit; text-decoration:none;">
                    <?php echo h($product['name']); ?>
                </a>
            </h3>
            
            <p style="color:var(--primary-color); font-weight:bold; font-size:1.1rem; margin-bottom:12px;">
                £<?php echo number_format($product['price'], 2); ?>
            </p>

            <?php if (get_current_user_role() !== 'admin'): ?>
            <div style="display:flex; gap:8px; margin-top:auto;">
                <a href="<?php echo $base_url; ?>/pages/product.php?id=<?php echo $product['id']; ?>"
                   class="btn" style="flex:1; text-align:center; padding:8px; font-size:0.85rem; background: #fff; color: var(--primary-color); border: 1px solid #e2e8f0; border-radius: 4px;">Detail</a>

                <?php if ($product['product_type'] === 'digital' || $product['stock_qty'] > 0): ?>
                    <form method="POST" action="<?php echo $base_url; ?>/api/cart-add.php" style="flex:1;">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="btn"
                                style="width:100%; padding:8px; font-size:0.85rem; border-radius: 4px;">
                            Add to Cart
                        </button>
                    </form>
                <?php else: ?>
                    <span style="flex:1; text-align:center; padding:8px; background:#f1f5f9;
                          color:#94a3b8; border-radius: 4px; font-size:0.85rem;">Sold Out</span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>