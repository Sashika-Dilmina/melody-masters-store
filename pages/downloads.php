<?php
require_once __DIR__ . '/../includes/header.php';
require_login();

$user_id = get_current_user_id();

$sql = "
    SELECT p.id as product_id, p.name, p.file_url, oi.order_id, o.created_at
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.user_id = ? AND p.product_type = 'digital' AND o.status != 'cancelled'
    ORDER BY o.created_at DESC
";
$downloads = fetch_all($sql, "i", [$user_id]);

// Remove duplicates if they bought the same digital item multiple times
$unique_downloads = [];
foreach ($downloads as $d) {
    if (!isset($unique_downloads[$d['product_id']])) {
        $unique_downloads[$d['product_id']] = $d;
    }
}
?>

<h2>My Digital Downloads</h2>

<?php if (empty($unique_downloads)): ?>
    <p>You have no digital downloads available. <a href="<?php echo $base_url; ?>/pages/shop.php">Browse our digital products</a>.</p>
<?php else: ?>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
        <?php foreach ($unique_downloads as $item): ?>
            <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); border-left: 4px solid var(--secondary-color);">
                <h3 style="margin-bottom: 10px;"><?php echo h($item['name']); ?></h3>
                <p style="font-size: 0.9rem; color: #666; margin-bottom: 15px;">Purchased on: <?php echo date('d M Y', strtotime($item['created_at'])); ?></p>
                <a href="<?php echo $base_url; ?>/api/download-file.php?product_id=<?php echo $item['product_id']; ?>" class="btn" style="background: var(--primary-color);">Download File</a>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
