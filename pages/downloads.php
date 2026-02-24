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

<div style="margin-bottom: 30px; border-bottom: 1px solid #e2e8f0; padding-bottom: 20px;">
    <h2 style="margin: 0;">Digital Downloads</h2>
</div>

<?php if (empty($downloads)): ?>
    <div style="text-align:center; padding:60px 20px; background:#fff; border: 1px solid #e2e8f0; border-radius:8px;">
        <p style="color:#64748b; font-size:1rem; margin-bottom: 20px;">
            You haven't purchased any digital items yet.
        </p>
        <a href="shop.php?category_id=digital" class="btn">Explore Sheet Music</a>
    </div>
<?php else: ?>
    <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(250px,1fr)); gap:25px;">
        <?php foreach ($downloads as $item): ?>
        <div style="background:#fff; padding:20px; border: 1px solid #e2e8f0; border-radius:8px; display:flex; flex-direction:column; gap:12px;">
            <div style="height: 120px; background: #f8fafc; border-radius: 4px; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                <?php if ($item['image_path']): ?>
                    <img src="<?php echo $base_url . '/uploads/products/' . h($item['image_path']); ?>"
                         style="width:100%; height:100%; object-fit:cover;">
                <?php else: ?>
                    <span style="color: #cbd5e1; font-size: 0.8rem;">Digital Item</span>
                <?php endif; ?>
            </div>
            
            <h3 style="margin:0; font-size:1rem;"><?php echo h($item['name']); ?></h3>
            <p style="font-size:0.75rem; color:#94a3b8; margin:0;">
                Purchased on <?php echo date('d M Y', strtotime($item['purchased_at'])); ?>
            </p>
            
            <a href="<?php echo $base_url; ?>/api/download-file.php?product_id=<?php echo $item['product_id']; ?>"
               class="btn" style="text-align:center; margin-top:10px; padding: 10px; border-radius: 4px; font-size: 0.85rem;">
                Download Asset
            </a>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
