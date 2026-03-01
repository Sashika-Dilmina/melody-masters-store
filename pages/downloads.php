<?php
// pages/downloads.php – customer's digital download library
require_once __DIR__ . '/../includes/header.php';
require_login();

$user_id = get_current_user_id();

$sql = "
    SELECT
        p.id   AS product_id,
        p.name,
        p.file_url,
        p.image_path,
        MIN(o.created_at) AS purchased_at
    FROM order_items oi
    JOIN orders   o ON oi.order_id   = o.id
    JOIN products p ON oi.product_id = p.id
    WHERE o.user_id       = ?
      AND p.product_type  = 'digital'
      AND o.status       != 'cancelled'
      AND p.file_url IS NOT NULL
    GROUP BY p.id, p.name, p.file_url, p.image_path
    ORDER BY purchased_at DESC
";
$downloads = fetch_all($sql, "i", [$user_id]);
?>

<div class="mb-5 reveal">
    <h1 class="title" style="font-size: 2rem;">Digital Library</h1>
    <p class="muted">Access and download your purchased digital sheet music.</p>
</div>

<?php if (empty($downloads)): ?>
    <div class="card text-center reveal" style="padding: 5rem 2rem;">
        <div style="width: 80px; height: 80px; background: var(--bg-soft); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: var(--text-muted); border: 1px solid var(--border);">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
        </div>
        <h2 class="mb-2">Your library is empty</h2>
        <p class="muted mb-5">You haven't purchased any digital masterpieces yet.</p>
        <a href="shop.php" class="btn btn-primary" style="padding: 1rem 2.5rem;">Browse Digital Collection</a>
    </div>
<?php else: ?>
    <div class="grid grid-4 reveal">
        <?php foreach ($downloads as $index => $item): ?>
        <div class="card card-hover reveal" style="padding: 0; overflow: hidden; animation-delay: <?php echo 0.05 * $index; ?>s;">
            <div style="height: 180px; background: #f8fafc; display: flex; align-items: center; justify-content: center;">
                <?php if ($item['image_path']): ?>
                    <img src="<?php echo $base_url . '/uploads/products/' . h($item['image_path']); ?>"
                         style="width: 100%; height: 100%; object-fit: cover;">
                <?php else: ?>
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" style="color: #cbd5e1;"><path d="m16 6 4 14H4L8 6Z"></path><path d="M12 6v14"></path><path d="M8 10h8"></path><path d="M8 14h8"></path></svg>
                <?php endif; ?>
            </div>
            
            <div style="padding: 1.5rem;">
                <h3 style="margin-bottom: 0.5rem; font-size: 1.05rem; line-height: 1.4; height: 2.8em; overflow: hidden;"><?php echo h($item['name']); ?></h3>
                <p class="muted text-xs mb-4" style="font-weight: 600; text-transform: uppercase;">
                    Acquired: <?php echo date('d M Y', strtotime($item['purchased_at'])); ?>
                </p>
                
                <a href="<?php echo $base_url; ?>/api/download-file.php?product_id=<?php echo $item['product_id']; ?>"
                   class="btn btn-primary" style="width: 100%; padding: 0.75rem; font-size: 0.85rem;">
                   <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 4px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    Download PDF Asset
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
