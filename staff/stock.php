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
        set_flash_message('success', "Stock level for product #$product_id updated successfully.");
    }
    header('Location: stock.php');
    exit;
}

$physical_products = fetch_all("SELECT id, name, stock_qty, sku FROM products WHERE product_type = 'physical' AND is_active = 1 ORDER BY name ASC");
?>

<div class="space-between mb-5 reveal">
    <div>
        <h1 class="title" style="font-size: 2rem; margin: 0;">Inventory Management</h1>
        <p class="muted">Monitor and adjust stock levels for physical store assets.</p>
    </div>
    <div class="card card-hover" style="padding: 0.75rem 1.5rem; border-color: rgba(59, 130, 246, 0.1); background: rgba(59, 130, 246, 0.05);">
        <span style="font-weight: 800; color: var(--accent); font-size: 1.1rem;"><?php echo count($physical_products); ?></span>
        <span class="muted text-xs" style="text-transform: uppercase; font-weight: 700; margin-left: 8px;">Physical SKUs</span>
    </div>
</div>

<div class="card reveal" style="padding: 0; overflow: hidden; animation-delay: 0.1s;">
    <div class="table-container" style="border: none;">
        <table class="table">
            <thead>
                <tr>
                    <th style="padding-left: 2rem;">Product ID</th>
                    <th>Item Information</th>
                    <th class="text-center">Current Stock</th>
                    <th class="text-right" style="padding-right: 2rem;">Inventory Adjustment</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($physical_products as $p): ?>
                <tr>
                    <td class="muted" style="padding-left: 2rem;">#<?php echo $p['id']; ?></td>
                    <td>
                        <div style="font-weight: 700; color: var(--primary);"><?php echo h($p['name']); ?></div>
                        <div class="muted text-xs" style="font-family: 'DM Mono', monospace;"><?php echo h($p['sku'] ?: 'NO-SKU'); ?></div>
                    </td>
                    <td class="text-center">
                        <span class="badge" style="font-size: 0.9rem; padding: 0.4rem 1rem; width: 60px; text-align: center; <?php echo $p['stock_qty'] <= 5 ? 'background: var(--error); color: white;' : 'background: var(--bg-soft); color: var(--success); border: 1px solid rgba(20, 184, 166, 0.2);'; ?>">
                            <?php echo $p['stock_qty']; ?>
                        </span>
                    </td>
                    <td class="text-right" style="padding-right: 2rem;">
                        <form method="POST" action="" class="row" style="justify-content: flex-end; gap: 0.75rem;">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="update_stock" value="1">
                            <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                            <input type="number" name="stock_qty" value="<?php echo $p['stock_qty']; ?>" min="0" class="input" style="width: 100px; text-align: center; padding: 0.45rem;">
                            <button type="submit" class="btn btn-outline" style="padding: 0.45rem 1rem; font-size: 0.85rem;">
                                Update
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
