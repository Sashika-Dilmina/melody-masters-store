<?php
// admin/product-add.php
require_once __DIR__ . '/../includes/header.php';
require_role('admin');

$categories = fetch_all("SELECT id, name FROM categories ORDER BY name ASC");

function make_product_slug(string $name, int $exclude_id = 0): string {
    global $conn;
    $base = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $name), '-'));
    $slug = $base;
    $n    = 1;
    while (true) {
        $row = fetch_one(
            "SELECT id FROM products WHERE slug = ?" . ($exclude_id ? " AND id != $exclude_id" : ""),
            "s", [$slug]
        );
        if (!$row) break;
        $slug = $base . '-' . (++$n);
    }
    return $slug;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');

    $name          = sanitize_input($_POST['name']          ?? '');
    $description   = sanitize_input($_POST['description']   ?? '');
    $short_desc    = sanitize_input($_POST['short_description'] ?? '');
    $brand         = sanitize_input($_POST['brand']         ?? '');
    $price         = (float)($_POST['price']                ?? 0);
    $category_id   = (int)($_POST['category_id']            ?? 0);
    $product_type  = ($_POST['type'] ?? '') === 'digital' ? 'digital' : 'physical';
    $stock_qty     = $product_type === 'physical' ? max(0, (int)($_POST['stock_qty'] ?? 0)) : 0;
    $sku           = sanitize_input($_POST['sku']            ?? '');

    if (empty($name))       $errors[] = 'Product identification name required.';
    if ($price <= 0)        $errors[] = 'Unit price must be positive.';
    if ($category_id <= 0)  $errors[] = 'Selection of category category is required.';

    $image_path = null;
    $file_url   = null;

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_img = ['jpg','jpeg','png','gif','webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed_img)) {
            $filename = uniqid('prod_') . '.' . $ext;
            $dest     = __DIR__ . '/../uploads/products/' . $filename;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                $image_path = $filename;
            }
        }
    }

    if ($product_type === 'digital' && isset($_FILES['digital_file']) && $_FILES['digital_file']['error'] === UPLOAD_ERR_OK) {
        $filename = uniqid('dig_') . '_' . basename($_FILES['digital_file']['name']);
        $dest     = __DIR__ . '/../uploads/digital/' . $filename;
        if (move_uploaded_file($_FILES['digital_file']['tmp_name'], $dest)) {
            $file_url = $filename;
        }
    }

    if (empty($errors)) {
        $slug = make_product_slug($name);
        execute_query(
            "INSERT INTO products
                (category_id, name, slug, brand, product_type, price, stock_qty, sku,
                 short_description, description, image_path, file_url, is_active)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)",
            "isssssdissss",
            [$category_id, $name, $slug, $brand, $product_type, $price, $stock_qty,
             $sku, $short_desc, $description, $image_path, $file_url]
        );
        set_flash_message('success', 'New product record created.');
        header('Location: products.php');
        exit;
    } else {
        set_flash_message('error', implode(' ', $errors));
    }
}
?>

<div style="margin-bottom: 30px; border-bottom: 1px solid #e2e8f0; padding-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
    <h2 style="margin: 0;">Add New Product Record</h2>
    <a href="products.php" style="font-size: 0.9rem; color: #64748b;">Return to List</a>
</div>

<div style="background: #fff; padding: 30px; border: 1px solid #e2e8f0; border-radius: 8px; max-width: 800px;">
    <form method="POST" action="" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">

            <div style="grid-column: 1 / -1;">
                <label style="display: block; font-size: 0.85rem; font-weight: 500; margin-bottom: 8px;">Product Identification Name</label>
                <input type="text" name="name" required value="<?php echo h($_POST['name'] ?? ''); ?>"
                       style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 500; margin-bottom: 8px;">Category Group</label>
                <select name="category_id" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                    <option value="">-- Choose Category --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo (($_POST['category_id'] ?? '') == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo h($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 500; margin-bottom: 8px;">Manufacturer/Brand</label>
                <input type="text" name="brand" value="<?php echo h($_POST['brand'] ?? ''); ?>"
                       style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 500; margin-bottom: 8px;">Unit Price (GBP)</label>
                <input type="number" step="0.01" min="0.01" name="price" required value="<?php echo h($_POST['price'] ?? ''); ?>"
                       style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 500; margin-bottom: 8px;">Product Type</label>
                <select name="type" id="product_type" onchange="toggleFields()" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                    <option value="physical">Physical Asset</option>
                    <option value="digital">Digital Distribution</option>
                </select>
            </div>

            <div id="stock_field">
                <label style="display: block; font-size: 0.85rem; font-weight: 500; margin-bottom: 8px;">Initial Inventory Level</label>
                <input type="number" name="stock_qty" min="0" value="<?php echo (int)($_POST['stock_qty'] ?? 0); ?>"
                       style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 500; margin-bottom: 8px;">Product Code (SKU)</label>
                <input type="text" name="sku" value="<?php echo h($_POST['sku'] ?? ''); ?>"
                       style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
            </div>

            <div style="grid-column: 1 / -1;">
                <label style="display: block; font-size: 0.85rem; font-weight: 500; margin-bottom: 8px;">Brief Summary</label>
                <input type="text" name="short_description" maxlength="255" value="<?php echo h($_POST['short_description'] ?? ''); ?>"
                       style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
            </div>

            <div style="grid-column: 1 / -1;">
                <label style="display: block; font-size: 0.85rem; font-weight: 500; margin-bottom: 8px;">Complete Description</label>
                <textarea name="description" rows="4" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-family: inherit; resize: vertical;"><?php echo h($_POST['description'] ?? ''); ?></textarea>
            </div>

            <div style="grid-column: 1 / -1;">
                <label style="display: block; font-size: 0.85rem; font-weight: 500; margin-bottom: 8px;">Product Asset Image (jpg/png)</label>
                <input type="file" name="image" accept="image/*" style="font-size: 0.85rem;">
            </div>

            <div style="grid-column: 1 / -1; display: none;" id="digital_file_field">
                <label style="display: block; font-size: 0.85rem; font-weight: 500; margin-bottom: 8px;">Digital Source File (Upload)</label>
                <input type="file" name="digital_file" style="font-size: 0.85rem; margin-bottom: 5px;">
                <div style="font-size: 0.75rem; color: #64748b;">This asset represents the downloadable content for the customer.</div>
            </div>

        </div>

        <div style="margin-top: 30px; display: flex; gap: 10px; padding-top: 20px; border-top: 1px solid #f1f5f9;">
            <button type="submit" class="btn" style="flex: 1; padding: 12px; border-radius: 4px; font-weight: bold;">Create Product Record</button>
            <a href="products.php" class="btn" style="flex: 1; background: #fff; color: #64748b; border: 1px solid #cbd5e1; padding: 11px; border-radius: 4px; text-align: center;">Cancel</a>
        </div>
    </form>
</div>

<script>
function toggleFields() {
    const type = document.getElementById('product_type').value;
    document.getElementById('stock_field').style.display = type === 'digital' ? 'none' : 'block';
    document.getElementById('digital_file_field').style.display = type === 'digital' ? 'block' : 'none';
}
document.addEventListener('DOMContentLoaded', toggleFields);
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
