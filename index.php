<?php
// index.php – Home Page
require_once __DIR__ . '/includes/header.php';

// Only show active products for "New Arrivals"
$featured_products = fetch_all("
    SELECT p.*, c.name AS category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.is_active = 1 
    ORDER BY p.id DESC 
    LIMIT 4
");
?>

<div style="background: #f1f5f9; padding: 60px 40px; border-radius: 10px; margin-bottom: 50px; text-align: center;">
    <h1 style="font-size: 2.5rem; margin-bottom: 10px;">Melody Masters Store</h1>
    <p style="font-size: 1.1rem; color: #64748b; max-width: 600px; margin: 0 auto 25px;">
        Premium musical instruments and digital sheet music.
    </p>
    <a href="<?php echo $base_url; ?>/pages/shop.php" class="btn" style="padding: 12px 25px; border-radius: 4px;">Browse Collection</a>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px; margin-bottom: 60px;">
    <div style="text-align: center; border: 1px solid #e2e8f0; padding: 30px; border-radius: 8px;">
        <h3 style="font-size: 1.1rem; margin-bottom: 10px;">Free Shipping</h3>
        <p style="color: #64748b; font-size: 0.9rem;">On physical orders over £<?php echo number_format(FREE_SHIPPING_THRESHOLD, 0); ?></p>
    </div>
    <div style="text-align: center; border: 1px solid #e2e8f0; padding: 30px; border-radius: 8px;">
        <h3 style="font-size: 1.1rem; margin-bottom: 10px;">Digital Delivery</h3>
        <p style="color: #64748b; font-size: 0.9rem;">Instant access to your digital sheet music library.</p>
    </div>
    <div style="text-align: center; border: 1px solid #e2e8f0; padding: 30px; border-radius: 8px;">
        <h3 style="font-size: 1.1rem; margin-bottom: 10px;">Secure Payments</h3>
        <p style="color: #64748b; font-size: 0.9rem;">Safe and encrypted transactions for all customers.</p>
    </div>
</div>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <h2 style="font-size: 1.5rem;">New Arrivals</h2>
    <a href="pages/shop.php" style="color: var(--secondary-color); font-weight: 500; font-size: 0.9rem;">View All &rarr;</a>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 25px;">
    <?php foreach ($featured_products as $product): ?>
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; display: flex; flex-direction: column;">
            <div style="height: 180px; background: #f8fafc; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: center;">
                <?php if ($product['image_path']): ?>
                    <img src="<?php echo $base_url . '/uploads/products/' . h($product['image_path']); ?>" 
                         style="width: 100%; height: 100%; object-fit: cover;">
                <?php else: ?>
                    <span style="color: #cbd5e1; font-weight: 500;">No Image</span>
                <?php endif; ?>
            </div>
            
            <div style="padding: 20px; flex: 1; display: flex; flex-direction: column;">
                <h3 style="font-size: 1rem; margin-bottom: 10px; line-height: 1.2;">
                    <a href="pages/product.php?id=<?php echo $product['id']; ?>" style="color: inherit; text-decoration: none;">
                        <?php echo h($product['name']); ?>
                    </a>
                </h3>
                <div style="margin-top: auto; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-weight: bold; color: var(--primary-color);">£<?php echo number_format($product['price'], 2); ?></span>
                    <span style="font-size: 0.75rem; color: #94a3b8; text-transform: uppercase;"><?php echo $product['product_type']; ?></span>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div style="margin: 60px 0; background: #0f172a; padding: 40px; border-radius: 10px; text-align: center; color: #fff;">
    <h2 style="font-size: 1.8rem; margin-bottom: 10px;">Assignment Submission</h2>
    <p style="color: #94a3b8; margin-bottom: 25px;">Ready to showcase the platform? Use the buttons below to navigate.</p>
    <div style="display: flex; gap: 15px; justify-content: center;">
        <?php if (!is_logged_in()): ?>
            <a href="pages/register.php" class="btn" style="background: #fff; color: #0f172a; border-radius: 4px;">Register Account</a>
        <?php endif; ?>
        <a href="pages/shop.php" class="btn" style="border-radius: 4px;">Explore Shop</a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>