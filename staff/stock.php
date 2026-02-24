<?php
// staff/stock.php
require_once __DIR__ . '/../includes/header.php';
require_role('staff');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stock'])) {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    $product_id = (int)$_POST['product_id'];
    $stock = (int)$_POST['stock_qty'];
    
    if ($stock >= 0) {
        execute_query("UPDATE products SET stock_qty = ? WHERE id = ? AND product_type = 'physical'", "ii", [$stock, $product_id]);
        set_flash_message('success', 'Stock level modified.');
    }
    header('Location: stock.php');
    exit;
}

$physical_products = fetch_all("SELECT id, name, stock_qty FROM products WHERE product_type = 'physical' AND is_active = 1 ORDER BY name ASC");
?>

<div style="margin-bottom: 30px; border-bottom: 1px solid #e2e8f0; padding-bottom: 20px;">
    <h2 style="margin: 0;">Inventory Management</h2>
</div>

<div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
    <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
        <thead>
            <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; text-align: left; color: #475569;">
                <th style="padding: 15px;">Product ID</th>
                <th style="padding: 15px;">Item Name</th>
                <th style="padding: 15px; text-align: center;">Stock Level</th>
                <th style="padding: 15px; text-align: center;">Adjust</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($physical_products as $p): ?>
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 15px;"><?php echo $p['id']; ?></td>
                    <td style="padding: 15px; font-weight: 500;"><?php echo h($p['name']); ?></td>
                    <td style="padding: 15px; text-align: center; color: <?php echo $p['stock_qty'] <= 5 ? '#ef4444' : '#10b981'; ?>; font-weight: bold;">
                        <?php echo $p['stock_qty']; ?>
                    </td>
                    <td style="padding: 15px; text-align: center;">
                        <form method="POST" action="" style="display: flex; gap: 8px; justify-content: center; align-items: center;">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="update_stock" value="1">
                            <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                            <input type="number" name="stock_qty" value="<?php echo $p['stock_qty']; ?>" min="0" 
                                   style="width: 70px; padding: 6px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.85rem; text-align: center;">
                            <button type="submit" class="btn" style="padding: 6px 15px; font-size: 0.8rem; border-radius: 4px;">Update</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
