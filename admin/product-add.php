<?php
require_once __DIR__ . '/../includes/header.php';
require_role('admin');

$categories = fetch_all("SELECT * FROM categories ORDER BY name ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    
    $name = sanitize_input($_POST['name']);
    $description = sanitize_input($_POST['description']);
    $price = (float)$_POST['price'];
    $category_id = (int)$_POST['category_id'];
    $product_type = $_POST['type'] === 'digital' ? 'digital' : 'physical';
    $stock_qty = $product_type === 'physical' ? (int)$_POST['stock_qty'] : 0;
    
    $image_path = null;
    $file_url = null;
    
    // Upload standard image
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $ext;
        $dest = __DIR__ . '/../uploads/products/' . $filename;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
            $image_path = $filename;
        }
    }
    
    // Upload digital file if digital product
    if ($product_type === 'digital' && isset($_FILES['digital_file']) && $_FILES['digital_file']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['digital_file']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('dig_') . '.' . $ext;
        $dest = __DIR__ . '/../uploads/digital/' . $filename;
        if (move_uploaded_file($_FILES['digital_file']['tmp_name'], $dest)) {
            $file_url = $filename;
        }
    }
    
    if (empty($name) || $price <= 0 || $category_id <= 0) {
        set_flash_message('error', 'Name, positive price, and category are required.');
    } else {
        $sql = "INSERT INTO products (name, description, price, category_id, product_type, stock_qty, image_path, file_url) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        execute_query($sql, "ssdisiss", [
            $name, $description, $price, $category_id, $product_type, $stock_qty, $image_path, $file_url
        ]);
        set_flash_message('success', 'Product added successfully.');
        header('Location: products.php');
        exit;
    }
}
?>

<h2>Add New Product</h2>

<div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); max-width: 600px;">
    <form method="POST" action="" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        
        <div style="margin-bottom: 15px;">
            <label>Name</label><br>
            <input type="text" name="name" required style="width: 100%; padding: 8px;">
        </div>
        
        <div style="margin-bottom: 15px;">
            <label>Category</label><br>
            <select name="category_id" required style="width: 100%; padding: 8px;">
                <option value="">-- Select Category --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>"><?php echo h($cat['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div style="margin-bottom: 15px;">
            <label>Description</label><br>
            <textarea name="description" style="width: 100%; padding: 8px; height: 100px;"></textarea>
        </div>
        
        <div style="margin-bottom: 15px;">
            <label>Price (£)</label><br>
            <input type="number" step="0.01" name="price" required style="width: 100%; padding: 8px;">
        </div>
        
        <div style="margin-bottom: 15px;">
            <label>Type</label><br>
            <select name="type" id="product_type" style="width: 100%; padding: 8px;" onchange="toggleFields()">
                <option value="physical">Physical</option>
                <option value="digital">Digital</option>
            </select>
        </div>
        
        <div style="margin-bottom: 15px;" id="stock_field">
            <label>Initial Stock</label><br>
            <input type="number" name="stock_qty" value="0" min="0" style="width: 100%; padding: 8px;">
        </div>
        
        <div style="margin-bottom: 15px;">
            <label>Product Image</label><br>
            <input type="file" name="image" accept="image/*" style="width: 100%; padding: 8px;">
        </div>
        
        <div style="margin-bottom: 15px; display: none;" id="digital_file_field">
            <label>Digital Download File</label><br>
            <input type="file" name="digital_file" style="width: 100%; padding: 8px;">
        </div>
        
        <button type="submit" class="btn" style="width: 100%;">Save Product</button>
        <a href="products.php" style="display: block; text-align: center; margin-top: 15px; text-decoration: none; color: #666;">Cancel</a>
    </form>
</div>

<script>
function toggleFields() {
    const type = document.getElementById('product_type').value;
    const stockField = document.getElementById('stock_field');
    const digitalFileField = document.getElementById('digital_file_field');
    
    if (type === 'digital') {
        stockField.style.display = 'none';
        digitalFileField.style.display = 'block';
    } else {
        stockField.style.display = 'block';
        digitalFileField.style.display = 'none';
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
