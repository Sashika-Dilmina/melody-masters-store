<?php
// admin/products.php
require_once __DIR__ . '/../includes/header.php';
require_role('admin');

// Handle deactivation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    $id = (int)$_POST['product_id'];
    execute_query("UPDATE products SET is_active = 0 WHERE id = ?", "i", [$id]);
    set_flash_message('success', 'Product deactivated.');
    header('Location: products.php');
    exit;
}

$show_all  = isset($_GET['show']) && $_GET['show'] === 'all';
$where     = $show_all ? '' : "WHERE p.is_active = 1";
$products  = fetch_all("
    SELECT p.*, c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    $where
    ORDER BY p.id DESC
");
?>

<div class="space-between mb-5 reveal">
    <div>
        <h1 class="title" style="font-size: 2rem; margin: 0;">Catalog Management</h1>
        <p class="muted">Manage your store's physical instruments and digital assets.</p>
    </div>
    <div class="row">
        <a href="products.php<?php echo $show_all ? '' : '?show=all'; ?>" class="btn btn-outline" style="font-size: 0.85rem;">
            <?php echo $show_all ? 'Show Active Only' : 'Show Archived'; ?>
        </a>
        <a href="product-add.php" class="btn btn-primary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Add New Product
        </a>
    </div>
</div>

<div class="card reveal" style="padding: 0; overflow: hidden;">
    <div class="table-container" style="border: none;">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Product Details</th>
                    <th>Category</th>
                    <th class="text-right">Price</th>
                    <th class="text-center">Stock</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                <tr style="<?php echo $p['is_active'] ? '' : 'background: #fdf2f2; opacity: 0.8;'; ?>">
                    <td class="muted" style="font-size: 0.8rem;">#<?php echo $p['id']; ?></td>
                    <td>
                        <div class="row">
                            <div style="width: 40px; height: 40px; background: var(--bg-soft); border-radius: 8px; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                                <?php if ($p['image_path']): ?>
                                    <img src="<?php echo $base_url . '/uploads/products/' . h($p['image_path']); ?>" style="width: 100%; height: 100%; object-fit: cover;" alt="Product">
                                <?php else: ?>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #cbd5e1;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                                <?php endif; ?>
                            </div>
                            <div>
                                <div style="font-weight: 700; color: var(--primary);"><?php echo h($p['name']); ?></div>
                                <span class="badge <?php echo strtoupper($p['product_type']) === 'DIGITAL' ? 'badge-digital' : 'badge-physical'; ?>" style="font-size: 0.6rem; padding: 0.15rem 0.5rem;"><?php echo h($p['product_type']); ?></span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="muted text-sm"><?php echo h($p['category_name'] ?? 'Uncategorized'); ?></span>
                    </td>
                    <td class="text-right" style="font-weight: 700;">£<?php echo number_format($p['price'], 2); ?></td>
                    <td class="text-center">
                        <?php if (strtoupper($p['product_type']) === 'PHYSICAL'): ?>
                            <span style="font-weight: 800; color: <?php echo $p['stock_qty'] <= 5 ? 'var(--error)' : 'var(--success)'; ?>;">
                                <?php echo $p['stock_qty']; ?>
                            </span>
                        <?php else: ?>
                            <span class="muted" style="font-size: 1.25rem;">∞</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-right">
                        <div class="row" style="justify-content: flex-end; gap: 0.5rem;">
                            <a href="product-edit.php?id=<?php echo $p['id']; ?>" class="btn btn-outline" style="padding: 0.4rem 0.75rem; font-size: 0.75rem; min-width: 60px;">Edit</a>
                            <?php if ($p['is_active']): ?>
                            <form method="POST" action="" style="display: inline;">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="delete_product" value="1">
                                <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                                <button type="submit" class="btn btn-outline" style="padding: 0.4rem 0.75rem; font-size: 0.75rem; color: var(--error); border-color: rgba(239, 68, 68, 0.2); min-width: 80px;" onclick="return confirm('Archive this product?');">Archive</button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
