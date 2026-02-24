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

<div style="margin-bottom: 30px; border-bottom: 1px solid #e2e8f0; padding-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
    <h2 style="margin: 0;">Catalog Management</h2>
    <div style="display: flex; gap: 15px; align-items: center;">
        <a href="products.php<?php echo $show_all ? '' : '?show=all'; ?>" style="font-size: 0.85rem; color: #64748b;">
            <?php echo $show_all ? 'Show Active Only' : 'Show Archive'; ?>
        </a>
        <a href="product-add.php" class="btn" style="padding: 8px 16px; border-radius: 6px;">Add Product</a>
    </div>
</div>

<div style="overflow-x: auto; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px;">
    <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
        <thead>
            <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; text-align: left; color: #475569;">
                <th style="padding: 15px;">ID</th>
                <th style="padding: 15px;">Product</th>
                <th style="padding: 15px;">Category</th>
                <th style="padding: 15px;">Type</th>
                <th style="padding: 15px; text-align: right;">Price</th>
                <th style="padding: 15px; text-align: center;">Stock</th>
                <th style="padding: 15px; text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $p): ?>
            <tr style="border-bottom: 1px solid #f1f5f9; <?php echo $p['is_active'] ? '' : 'background: #fdf2f2;'; ?>">
                <td style="padding: 15px;"><?php echo $p['id']; ?></td>
                <td style="padding: 15px; font-weight: 600;"><?php echo h($p['name']); ?></td>
                <td style="padding: 15px; color: #64748b;"><?php echo h($p['category_name'] ?? '—'); ?></td>
                <td style="padding: 15px; font-size: 0.8rem; text-transform: uppercase; color: #94a3b8;"><?php echo $p['product_type']; ?></td>
                <td style="padding: 15px; text-align: right; font-weight: 600;">£<?php echo number_format($p['price'], 2); ?></td>
                <td style="padding: 15px; text-align: center;">
                    <?php if ($p['product_type'] === 'physical'): ?>
                        <span style="font-weight: 700; color: <?php echo $p['stock_qty'] <= 5 ? '#ef4444' : '#10b981'; ?>;">
                            <?php echo $p['stock_qty']; ?>
                        </span>
                    <?php else: ?>
                        <span style="color: #cbd5e1;">—</span>
                    <?php endif; ?>
                </td>
                <td style="padding: 15px; text-align: center;">
                    <div style="display: flex; gap: 8px; justify-content: center;">
                        <a href="product-edit.php?id=<?php echo $p['id']; ?>" class="btn" style="padding: 5px 12px; font-size: 0.8rem; border-radius: 4px; background: #fff; color: var(--primary-color); border: 1px solid #e2e8f0;">Edit</a>
                        <?php if ($p['is_active']): ?>
                        <form method="POST" action="" style="display: inline;">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="delete_product" value="1">
                            <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                            <button type="submit" class="btn" style="padding: 5px 12px; font-size: 0.8rem; border-radius: 4px; background: #fff; color: #ef4444; border: 1px solid #fee2e2;" onclick="return confirm('Deactivate product?');">Deactivate</button>
                        </form>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
