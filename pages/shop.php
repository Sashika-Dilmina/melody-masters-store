<?php
require_once __DIR__ . '/../includes/header.php';

$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$categories = fetch_all("SELECT * FROM categories ORDER BY name ASC");

$sql = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id";
$params = [];
$types = "";

if ($category_id > 0) {
    $sql .= " WHERE p.category_id = ?";
    $params[] = $category_id;
    $types = "i";
}
$sql .= " ORDER BY p.id DESC";
$products = fetch_all($sql, $types, $params);
?>

<h2>Shop</h2>
<div style="margin-bottom: 20px;">
    <strong>Filter by Category:</strong> 
    <a href="shop.php" style="margin-right: 10px; text-decoration: <?php echo $category_id === 0 ? 'underline' : 'none'; ?>">All</a>
    <?php foreach ($categories as $cat): ?>
        <a href="shop.php?category_id=<?php echo $cat['id']; ?>" style="margin-right: 10px; text-decoration: <?php echo $category_id === $cat['id'] ? 'underline' : 'none'; ?>"><?php echo h($cat['name']); ?></a>
    <?php endforeach; ?>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">
    <?php foreach ($products as $product): ?>
        <div style="background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.1); display: flex; flex-direction: column;">
            <?php if ($product['image_path']): ?>
                <img src="<?php echo $base_url . '/uploads/products/' . h($product['image_path']); ?>" alt="<?php echo h($product['name']); ?>" style="width: 100%; height: 200px; object-fit: cover;">
            <?php else: ?>
                <div style="width: 100%; height: 200px; background: #ccc; display: flex; align-items: center; justify-content: center; color: #666;">No Image</div>
            <?php endif; ?>
            <div style="padding: 15px; flex: 1; display: flex; flex-direction: column;">
                <span style="font-size: 0.8rem; color: #777; text-transform: uppercase;"><?php echo h($product['category_name']); ?></span>
                <h3 style="margin-bottom: 10px; font-size: 1.2rem;"><?php echo h($product['name']); ?></h3>
                <p style="color: var(--secondary-color); font-weight: bold; font-size: 1.1rem; margin-bottom: 15px;">£<?php echo number_format($product['price'], 2); ?></p>
                <div style="margin-top: auto; display: flex; gap: 10px;">
                    <a href="<?php echo $base_url; ?>/pages/product.php?id=<?php echo $product['id']; ?>" class="btn" style="flex: 1; text-align: center;">View</a>
                    <?php if ($product['product_type'] === 'digital' || $product['stock_qty'] > 0): ?>
                        <form method="POST" action="<?php echo $base_url; ?>/api/cart-add.php" style="flex: 1;">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="btn" style="width: 100%; background: var(--secondary-color);">Add</button>
                        </form>
                    <?php else: ?>
                        <span class="btn" style="flex: 1; text-align: center; background: #ccc; cursor: not-allowed;">Out of Stock</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>