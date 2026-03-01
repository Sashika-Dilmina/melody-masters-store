<?php
// admin/product-edit.php
require_once __DIR__ . '/../includes/header.php';
require_role('admin');

$id      = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = fetch_one("SELECT * FROM products WHERE id = ?", "i", [$id]);

if (!$product) {
    set_flash_message('error', 'Item record not found in database.');
    header('Location: products.php');
    exit;
}

$categories = fetch_all("SELECT id, name FROM categories ORDER BY name ASC");

function make_product_slug_edit(string $name, int $exclude_id): string {
    global $conn;
    $base = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $name), '-'));
    $slug = $base;
    $n    = 1;
    while (true) {
        $row = fetch_one("SELECT id FROM products WHERE slug = ? AND id != $exclude_id", "s", [$slug]);
        if (!$row) break;
        $slug = $base . '-' . (++$n);
    }
    return $slug;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');

    $name         = sanitize_input($_POST['name']               ?? '');
    $description  = sanitize_input($_POST['description']        ?? '');
    $short_desc   = sanitize_input($_POST['short_description']  ?? '');
    $brand        = sanitize_input($_POST['brand']              ?? '');
    $price        = (float)($_POST['price']                     ?? 0);
    $category_id  = (int)($_POST['category_id']                 ?? 0);
    $product_type = ($_POST['type'] ?? '') === 'digital' ? 'digital' : 'physical';
    $stock_qty    = $product_type === 'physical' ? max(0, (int)($_POST['stock_qty'] ?? 0)) : 0;
    $sku          = sanitize_input($_POST['sku']                ?? '');
    $is_active    = isset($_POST['is_active']) ? 1 : 0;

    if (empty($name))      $errors[] = 'Product title is required.';
    if ($price <= 0)       $errors[] = 'Unit price must be positive.';
    if ($category_id <= 0) $errors[] = 'Category classification is required.';

    $image_path = $product['image_path'];
    $file_url   = $product['file_url'];

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
        $slug = ($name !== $product['name']) ? make_product_slug_edit($name, $id) : $product['slug'];

        execute_query(
            "UPDATE products
             SET category_id=?, name=?, slug=?, brand=?, product_type=?, price=?,
                 stock_qty=?, sku=?, short_description=?, description=?,
                 image_path=?, file_url=?, is_active=?
             WHERE id=?",
            "isssssdissssii",
            [$category_id, $name, $slug, $brand, $product_type, $price,
             $stock_qty, $sku, $short_desc, $description,
             $image_path, $file_url, $is_active, $id]
        );
        set_flash_message('success', 'Changes committed successfully.');
        header('Location: products.php');
        exit;
    } else {
        set_flash_message('error', implode(' ', $errors));
    }
}
?>

<div class="space-between mb-5 reveal">
    <div>
        <h1 class="title" style="font-size: 2rem; margin: 0;">Edit Product: <?php echo h($product['name']); ?></h1>
        <p class="muted">Modify technical specifications, pricing, and distribution assets.</p>
    </div>
    <a href="products.php" class="btn btn-outline" style="padding: 0.6rem 1.25rem; font-size: 0.85rem;">
        &larr; Back to Catalog
    </a>
</div>

<div class="reveal" style="animation-delay: 0.1s;">
    <form method="POST" action="" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        
        <div class="dashboard-layout" style="grid-template-columns: 1.5fr 1fr; align-items: start;">
            
            <div class="stack">
                <!-- Core Info Card -->
                <div class="card" style="padding: 2.5rem;">
                    <div class="space-between mb-5">
                        <h3 class="m-0" style="font-size: 1.25rem;">Core Identification</h3>
                        <div class="row" style="gap: 10px;">
                             <input type="checkbox" name="is_active" id="is_active" value="1" <?php echo $product['is_active'] ? 'checked' : ''; ?>>
                             <label for="is_active" class="text-xs" style="font-weight: 700; color: var(--primary);">ACTIVE ON STOREFRONT</label>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="mb-2" style="font-weight: 600;">Product Name <span style="color: var(--error);">*</span></label>
                        <input type="text" name="name" required value="<?php echo h($product['name']); ?>" class="input">
                    </div>

                    <div class="mb-4">
                        <label class="mb-2" style="font-weight: 600;">Brief Summary (Short Description)</label>
                        <input type="text" name="short_description" maxlength="255" value="<?php echo h($product['short_description']); ?>" class="input">
                    </div>

                    <div class="mb-0">
                        <label class="mb-2" style="font-weight: 600;">Full Product Description</label>
                        <textarea name="description" rows="10" class="textarea"><?php echo h($product['description']); ?></textarea>
                    </div>
                </div>

                <!-- Media Assets Card -->
                <div class="card" style="padding: 2.5rem;">
                    <h3 class="mb-5" style="font-size: 1.25rem;">Distribution Assets</h3>
                    
                    <div class="mb-5">
                        <label class="mb-2" style="font-weight: 600;">Primary Image</label>
                        <div class="row mb-3" style="gap: 1.5rem; align-items: center;">
                            <div style="width: 100px; height: 100px; background: var(--bg-soft); border-radius: 12px; overflow: hidden; border: 1px solid var(--border); display: flex; align-items: center; justify-content: center;">
                                <?php if ($product['image_path']): ?>
                                    <img src="<?php echo $base_url . '/uploads/products/' . h($product['image_path']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #cbd5e1;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                                <?php endif; ?>
                            </div>
                            <div style="flex: 1;">
                                <input type="file" name="image" accept="image/*" class="text-sm muted">
                                <p class="text-xs muted mt-1">Upload a new image to overwrite the current one.</p>
                            </div>
                        </div>
                    </div>

                    <div id="digital_file_field" style="<?php echo $product['product_type'] === 'digital' ? '' : 'display: none;'; ?>">
                        <label class="mb-2" style="font-weight: 600; color: var(--accent);">Digital Source PDF</label>
                        <div style="background: rgba(59, 130, 246, 0.05); padding: 1.5rem; border-radius: 12px; border: 1px dashed rgba(59, 130, 246, 0.2);">
                            <?php if ($product['file_url']): ?>
                                <div class="row mb-3" style="gap: 8px;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="color: var(--success);"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    <span class="text-xs muted">Current: <strong><?php echo h($product['file_url']); ?></strong></span>
                                </div>
                            <?php endif; ?>
                            <input type="file" name="digital_file" class="text-sm muted">
                        </div>
                    </div>
                </div>
            </div>

            <div class="stack">
                <!-- Classification Card -->
                <div class="card" style="padding: 2rem;">
                    <h3 class="mb-4" style="font-size: 1.1rem;">Classification</h3>
                    
                    <div class="mb-4">
                        <label class="mb-2" style="font-weight: 600;">Category Group <span style="color: var(--error);">*</span></label>
                        <select name="category_id" required class="select">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $cat['id'] == $product['category_id'] ? 'selected' : ''; ?>>
                                    <?php echo h($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="mb-2" style="font-weight: 600;">Distribution Type</label>
                        <select name="type" id="product_type" onchange="toggleFields()" class="select">
                            <option value="physical" <?php echo $product['product_type'] === 'physical' ? 'selected' : ''; ?>>Physical Asset</option>
                            <option value="digital" <?php echo $product['product_type'] === 'digital' ? 'selected' : ''; ?>>Digital Distribution</option>
                        </select>
                    </div>

                    <div class="mb-0">
                        <label class="mb-2" style="font-weight: 600;">Manufacturer / Brand</label>
                        <input type="text" name="brand" value="<?php echo h($product['brand'] ?? ''); ?>" class="input">
                    </div>
                </div>

                <!-- Commercial Card -->
                <div class="card" style="padding: 2rem; border-top: 4px solid var(--accent);">
                    <h3 class="mb-4" style="font-size: 1.1rem;">Commercial Details</h3>
                    
                    <div class="mb-4">
                        <label class="mb-2" style="font-weight: 600;">Retail Price (GBP) <span style="color: var(--error);">*</span></label>
                        <div style="position: relative;">
                             <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); font-weight: 700; color: var(--text-muted);">£</span>
                             <input type="number" step="0.01" min="0.01" name="price" required value="<?php echo h($product['price']); ?>" class="input" style="padding-left: 2rem;">
                        </div>
                    </div>

                    <div id="stock_field" style="<?php echo $product['product_type'] === 'digital' ? 'display: none;' : ''; ?>">
                        <label class="mb-2" style="font-weight: 600;">Fulfillment SKU</label>
                        <input type="text" name="sku" value="<?php echo h($product['sku'] ?? ''); ?>" class="input mb-4">
                        
                        <label class="mb-2" style="font-weight: 600;">Inventory Level</label>
                        <input type="number" name="stock_qty" min="0" value="<?php echo (int)$product['stock_qty']; ?>" class="input">
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem; margin-top: 1rem;">Commit Changes</button>
                    <a href="products.php" class="btn btn-outline" style="width: 100%; padding: 1rem; margin-top: 0.75rem;">Revert & Cancel</a>
                </div>
            </div>

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
