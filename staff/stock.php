<?php
require_once __DIR__ . '/../includes/header.php';
require_role('staff');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stock'])) {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    $product_id = (int)$_POST['product_id'];
    $stock = (int)$_POST['stock_quantity'];
    
    if ($stock >= 0) {
        execute_query("UPDATE products SET stock_qty = ? WHERE id = ? AND product_type = 'physical'", "ii", [$stock, $product_id]);
        set_flash_message('success', 'Stock updated.');
    }
    header('Location: stock.php');
    exit;
}

$physical_products = fetch_all("SELECT id, name, stock_qty FROM products WHERE product_type = 'physical' ORDER BY name ASC");
?>

<h2>Manage Physical Stock</h2>

<table style="width: 100%; border-collapse: collapse; background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
    <thead>
        <tr style="background: var(--primary-color); color: #fff;">
            <th style="padding: 10px; text-align: left;">Product ID</th>
            <th style="padding: 10px; text-align: left;">Name</th>
            <th style="padding: 10px; text-align: center;">Current Stock</th>
            <th style="padding: 10px; text-align: center;">Update Stock</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($physical_products as $p): ?>
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 15px;"><?php echo $p['id']; ?></td>
                <td style="padding: 15px;"><?php echo h($p['name']); ?></td>
                <td style="padding: 15px; text-align: center; color: <?php echo $p['stock_qty'] <= 5 ? 'red' : 'green'; ?>; font-weight: bold;">
                    <?php echo $p['stock_qty']; ?>
                </td>
                <td style="padding: 15px; text-align: center;">
                    <form method="POST" action="" style="display: flex; gap: 5px; justify-content: center;">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="update_stock" value="1">
                        <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                        <input type="number" name="stock_qty" value="<?php echo $p['stock_qty']; ?>" min="0" style="width: 80px; padding: 5px;">
                        <button type="submit" class="btn" style="padding: 5px 10px; font-size: 0.8rem;">Save</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
