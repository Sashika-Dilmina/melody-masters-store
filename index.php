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

<header class="section reveal" style="background: var(--white); background-image: radial-gradient(circle at 10% 20%, rgba(59, 130, 246, 0.05) 0%, rgba(255, 255, 255, 1) 90%); border-radius: var(--radius-lg); margin-bottom: 3rem; position: relative; overflow: hidden; border: 1px solid var(--border);">
    <div class="row container" style="min-height: 400px; padding: 2rem;">
        <div style="flex: 1; text-align: left;">
            <span class="badge badge-digital mb-3">New Collection 2026</span>
            <h1 class="title mb-3">Unlock Your Musical Potential</h1>
            <p class="subtitle mb-5" style="max-width: 500px;">
                Experience the finest selection of premium instruments and digital sheet music. Crafted for every level of mastery.
            </p>
            <div class="row">
                <a href="<?php echo $base_url; ?>/pages/shop.php" class="btn btn-primary btn-lg" style="padding: 1rem 2rem;">Browse Collection</a>
                <a href="<?php echo $base_url; ?>/pages/register.php" class="btn btn-outline" style="padding: 1rem 2rem;">Join Community</a>
            </div>
        </div>
        <div style="flex: 1; display: flex; justify-content: center; align-items: center;" class="hide-mobile">
            <!-- Hero Illustration or SVG could go here -->
            <div style="width: 300px; height: 300px; background: var(--bg-soft); border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: var(--shadow-lg);">
                 <svg width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="color: var(--accent);"><path d="M9 18V5l12-2v13"></path><circle cx="6" cy="18" r="3"></circle><circle cx="18" cy="16" r="3"></circle></svg>
            </div>
        </div>
    </div>
</header>

<section class="mb-5 grid grid-3">
    <div class="card card-hover text-center reveal" style="animation-delay: 0.1s;">
        <div style="width: 50px; height: 50px; background: rgba(59, 130, 246, 0.1); color: var(--accent); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
        </div>
        <h3 class="mb-2">Free Shipping</h3>
        <p class="muted text-sm">On physical orders over £<?php echo number_format(FREE_SHIPPING_THRESHOLD, 0); ?></p>
    </div>
    <div class="card card-hover text-center reveal" style="animation-delay: 0.2s;">
        <div style="width: 50px; height: 50px; background: rgba(20, 184, 166, 0.1); color: var(--teal); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
        </div>
        <h3 class="mb-2">Digital Delivery</h3>
        <p class="muted text-sm">Instant access to your digital sheet music library.</p>
    </div>
    <div class="card card-hover text-center reveal" style="animation-delay: 0.3s;">
        <div style="width: 50px; height: 50px; background: rgba(11, 18, 32, 0.1); color: var(--primary); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
        </div>
        <h3 class="mb-2">Secure Payments</h3>
        <p class="muted text-sm">Safe and encrypted transactions for all customers.</p>
    </div>
</section>

<div class="space-between mb-4">
    <h2 class="title" style="font-size: 1.75rem;">New Arrivals</h2>
    <a href="pages/shop.php" class="btn btn-link">View All Store &rarr;</a>
</div>

<div class="grid grid-4 mb-5">
    <?php foreach ($featured_products as $index => $product): ?>
        <div class="card card-hover reveal" style="padding: 0; overflow: hidden; animation-delay: <?php echo 0.1 * ($index + 1); ?>s;">
            <div style="height: 220px; background: #f8fafc; display: flex; align-items: center; justify-content: center; position: relative;">
                <?php if ($product['image_path']): ?>
                    <img src="<?php echo $base_url . '/uploads/products/' . h($product['image_path']); ?>" 
                         style="width: 100%; height: 100%; object-fit: cover;">
                <?php else: ?>
                    <span style="color: #cbd5e1; font-weight: 500;">No Image</span>
                <?php endif; ?>
                <div style="position: absolute; top: 12px; right: 12px;">
                    <span class="badge <?php echo $product['product_type'] === 'DIGITAL' ? 'badge-digital' : 'badge-physical'; ?>">
                        <?php echo $product['product_type']; ?>
                    </span>
                </div>
            </div>
            
            <div style="padding: 1.25rem;">
                <p class="text-sm muted mb-1"><?php echo h($product['category_name']); ?></p>
                <h3 style="font-size: 1.1rem; margin-bottom: 1rem; height: 2.4em; overflow: hidden;">
                    <a href="pages/product.php?id=<?php echo $product['id']; ?>" style="color: inherit;">
                        <?php echo h($product['name']); ?>
                    </a>
                </h3>
                <div class="space-between">
                    <span style="font-weight: 800; font-size: 1.25rem; color: var(--primary);">£<?php echo number_format($product['price'], 2); ?></span>
                    <a href="pages/product.php?id=<?php echo $product['id']; ?>" class="btn btn-outline" style="padding: 0.5rem 1rem; border-radius: 8px;">View</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="section reveal" style="background: var(--primary); padding: 5rem; border-radius: var(--radius-lg); text-align: center; color: var(--white); margin-bottom: 4rem; background-image: linear-gradient(135deg, rgba(11, 18, 32, 1) 0%, rgba(30, 41, 59, 1) 100%);">
    <h2 style="color: white; font-size: 2.25rem; margin-bottom: 1rem;">Unleash Your Inner Maestro</h2>
    <p class="muted mb-5" style="font-size: 1.1rem; max-width: 600px; margin-left: auto; margin-right: auto; color: #94a3b8;">Join thousands of musicians worldwide who trust Melody Masters for their professional gear and digital library.</p>
    <div class="row" style="justify-content: center;">
        <?php if (!is_logged_in()): ?>
            <a href="pages/register.php" class="btn btn-primary" style="background: var(--white); color: var(--primary); padding: 1rem 2.5rem;">Create Free Account</a>
        <?php endif; ?>
        <a href="pages/shop.php" class="btn btn-outline" style="border-color: rgba(255,255,255,0.2); color: white; padding: 1rem 2.5rem;">Explore Full Shop</a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>