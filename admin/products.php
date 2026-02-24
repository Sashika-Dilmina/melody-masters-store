<?php
require_once __DIR__ . '/../includes/header.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    $id = (int)$_POST['product_id'];
    execute_query("DELETE FROM products WHERE id = ?", "i", [$id]);
    set_flash_message('success', 'Product deleted.');
    header('Location: products.php');
    exit;
}

$products = fetch_all("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC");
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>Manage Products</h2>
    <a href="product-add.php" class="btn" style="background: #27ae60;">+ Add New Product</a>
</div>

<table style="width: 100%; border-collapse: collapse; background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
    <thead>
        <tr style="background: var(--primary-color); color: #fff;">
            <th style="padding: 10px; text-align: left;">ID</th>
            <th style="padding: 10px; text-align: left;">Name</th>
            <th style="padding: 10px; text-align: left;">Category</th>
            <th style="padding: 10px; text-align: left;">Type</th>
            <th style="padding: 10px; text-align: right;">Price</th>
            <th style="padding: 10px; text-align: center;">Stock</th>
            <th style="padding: 10px; text-align: center;">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($products as $p): ?>
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 15px;"><?php echo $p['id']; ?></td>
                <td style="padding: 15px;"><?php echo h($p['name']); ?></td>
                <td style="padding: 15px;"><?php echo h($p['category_name']); ?></td>
                <td style="padding: 15px;"><?php echo ucfirst($p['product_type']); ?></td>
                <td style="padding: 15px; text-align: right;">£<?php echo number_format($p['price'], 2); ?></td>
                <td style="padding: 15px; text-align: center;">
                    <?php echo $p['product_type'] === 'physical' ? $p['stock_qty'] : '-'; ?>
                </td>
                <td style="padding: 15px; text-align: center;">
                    <a href="product-edit.php?id=<?php echo $p['id']; ?>" class="btn" style="padding: 5px 10px; font-size: 0.8rem; text-decoration: none; color: white;">Edit</a>
                    <form method="POST" action="" style="display: inline;">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="delete_product" value="1">
                        <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                        <button type="submit" class="btn" style="background: var(--secondary-color); padding: 5px 10px; font-size: 0.8rem;" onclick="return confirm('Delete this product?');">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
