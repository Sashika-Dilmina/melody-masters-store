<?php
require_once __DIR__ . '/includes/header.php';

$featured_products = fetch_all("SELECT * FROM products ORDER BY id DESC LIMIT 4");
?>
<div style="text-align: center; margin-bottom: 40px;">
    <h1>Welcome to Melody Masters</h1>
    <p>Your one-stop shop for high-quality instruments and digital sheet music.</p>
    <a href="<?php echo $base_url; ?>/pages/shop.php" class="btn" style="margin-top: 15px; font-size: 1.1rem; padding: 12px 24px;">Browse Our Shop</a>
</div>

<h2 style="margin-bottom: 20px; border-bottom: 2px solid var(--primary-color); padding-bottom: 10px;">New Arrivals</h2>
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">
    <?php foreach ($featured_products as $product): ?>
        <div style="background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.1); display: flex; flex-direction: column;">
            <?php if ($product['image_path']): ?>
                <img src="<?php echo $base_url . '/uploads/products/' . h($product['image_path']); ?>" alt="<?php echo h($product['name']); ?>" style="width: 100%; height: 200px; object-fit: cover;">
            <?php else: ?>
                <div style="width: 100%; height: 200px; background: #ccc; display: flex; align-items: center; justify-content: center; color: #666;">No Image</div>
            <?php endif; ?>
            <div style="padding: 15px; flex: 1; display: flex; flex-direction: column;">
                <h3 style="margin-bottom: 10px; font-size: 1.2rem;"><?php echo h($product['name']); ?></h3>
                <p style="color: var(--secondary-color); font-weight: bold; font-size: 1.1rem; margin-bottom: 15px;">£<?php echo number_format($product['price'], 2); ?></p>
                <a href="<?php echo $base_url; ?>/pages/product.php?id=<?php echo $product['id']; ?>" class="btn" style="margin-top: auto; text-align: center;">View Details</a>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>