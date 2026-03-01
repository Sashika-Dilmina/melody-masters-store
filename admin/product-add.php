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
    if ($category_id <= 0)  $errors[] = 'Selection of category group is required.';

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

<div class="space-between mb-5 reveal">
    <div>
        <h1 class="title" style="font-size: 2rem; margin: 0;">Add New Product</h1>
        <p class="muted">Expand your catalog with a new physical or digital musical asset.</p>
    </div>
    <a href="products.php" class="btn btn-outline" style="padding: 0.6rem 1.25rem; font-size: 0.85rem;">
        &larr; Return to Library
    </a>
</div>

<div class="reveal" style="animation-delay: 0.1s;">
    <form method="POST" action="" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        
        <div class="dashboard-layout" style="grid-template-columns: 1.5fr 1fr; align-items: start;">
            
            <div class="stack">
                <!-- Basic Info Card -->
                <div class="card" style="padding: 2.5rem;">
                    <h3 class="mb-5" style="font-size: 1.25rem;">Core Identification</h3>
                    
                    <div class="mb-4">
                        <label class="mb-2" style="font-weight: 600;">Product Name <span style="color: var(--error);">*</span></label>
                        <input type="text" name="name" required placeholder="e.g. Fender Stratocaster Custom" value="<?php echo h($_POST['name'] ?? ''); ?>" class="input">
                    </div>

                    <div class="mb-4">
                        <label class="mb-2" style="font-weight: 600;">Brief Summary (Short Description)</label>
                        <input type="text" name="short_description" maxlength="255" placeholder="One-line product overview..." value="<?php echo h($_POST['short_description'] ?? ''); ?>" class="input">
                    </div>

                    <div class="mb-0">
                        <label class="mb-2" style="font-weight: 600;">Full Product Description</label>
                        <textarea name="description" rows="8" placeholder="Detailed technical specifications, history, and features..." class="textarea"><?php echo h($_POST['description'] ?? ''); ?></textarea>
                    </div>
                </div>

                <!-- Media Upload Card -->
                <div class="card" style="padding: 2.5rem;">
                    <h3 class="mb-5" style="font-size: 1.25rem;">Visual & Distribution Assets</h3>
                    
                    <div class="mb-4">
                        <label class="mb-2" style="font-weight: 600;">Cover Image</label>
                        <div style="border: 2px dashed var(--border); padding: 2rem; border-radius: 12px; text-align: center; background: var(--bg-soft);">
                             <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--text-muted); margin-bottom: 1rem;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                             <input type="file" name="image" accept="image/*" class="text-sm muted">
                             <p class="text-xs muted mt-2">Recommended: 1200x800px JPG or WebP</p>
                        </div>
                    </div>

                    <div id="digital_file_field" style="display: none;">
                        <label class="mb-2" style="font-weight: 600; color: var(--accent);">Digital Source PDF <span style="color: var(--error);">*</span></label>
                        <div style="border: 2px dashed rgba(59, 130, 246, 0.3); padding: 2rem; border-radius: 12px; text-align: center; background: rgba(59, 130, 246, 0.02);">
                             <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--accent); margin-bottom: 1rem;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                             <input type="file" name="digital_file" class="text-sm muted">
                             <p class="text-xs muted mt-2">Maximum file size: 50MB</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="stack">
                <!-- Inventory & Classification Card -->
                <div class="card" style="padding: 2rem;">
                    <h3 class="mb-4" style="font-size: 1.1rem;">Classification</h3>
                    
                    <div class="mb-4">
                        <label class="mb-2" style="font-weight: 600;">Category Group <span style="color: var(--error);">*</span></label>
                        <select name="category_id" required class="select">
                            <option value="">-- Select Category --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo (($_POST['category_id'] ?? '') == $cat['id']) ? 'selected' : ''; ?>>
                                    <?php echo h($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="mb-2" style="font-weight: 600;">Distribution Type</label>
                        <select name="type" id="product_type" onchange="toggleFields()" class="select">
                            <option value="physical">Physical Asset</option>
                            <option value="digital">Digital Distribution</option>
                        </select>
                    </div>

                    <div class="mb-0">
                        <label class="mb-2" style="font-weight: 600;">Manufacturer / Brand</label>
                        <input type="text" name="brand" placeholder="e.g. Roland, Yamaha" value="<?php echo h($_POST['brand'] ?? ''); ?>" class="input">
                    </div>
                </div>

                <!-- Pricing & Stock Card -->
                <div class="card" style="padding: 2rem; border-top: 4px solid var(--accent);">
                    <h3 class="mb-4" style="font-size: 1.1rem;">Commercial Details</h3>
                    
                    <div class="mb-4">
                        <label class="mb-2" style="font-weight: 600;">List Price (GBP) <span style="color: var(--error);">*</span></label>
                        <div style="position: relative;">
                             <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); font-weight: 700; color: var(--text-muted);">£</span>
                             <input type="number" step="0.01" min="0.01" name="price" required value="<?php echo h($_POST['price'] ?? ''); ?>" class="input" style="padding-left: 2rem;">
                        </div>
                    </div>

                    <div class="mb-4" id="stock_field">
                        <label class="mb-2" style="font-weight: 600;">Fulfillment SKU</label>
                        <input type="text" name="sku" placeholder="MM-PROD-001" value="<?php echo h($_POST['sku'] ?? ''); ?>" class="input mb-4">
                        
                        <label class="mb-2" style="font-weight: 600;">Initial Stock Level</label>
                        <input type="number" name="stock_qty" min="0" value="<?php echo (int)($_POST['stock_qty'] ?? 0); ?>" class="input">
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem; margin-top: 1rem;">Publish to Storefront</button>
                    <a href="products.php" class="btn btn-outline" style="width: 100%; padding: 1rem; margin-top: 0.75rem;">Discard Draft</a>
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
