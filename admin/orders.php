<?php
require_once __DIR__ . '/../includes/header.php';
require_role('admin');

$orders = fetch_all("
    SELECT o.*, u.full_name as username, u.email 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC
");
?>

<h2>All Orders (Admin View)</h2>

<table style="width: 100%; border-collapse: collapse; background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
    <thead>
        <tr style="background: var(--primary-color); color: #fff;">
            <th style="padding: 10px; text-align: left;">ID</th>
            <th style="padding: 10px; text-align: left;">Customer</th>
            <th style="padding: 10px; text-align: left;">Date</th>
            <th style="padding: 10px; text-align: center;">Status</th>
            <th style="padding: 10px; text-align: right;">Total</th>
            <th style="padding: 10px; text-align: center;">View</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($orders as $o): ?>
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 15px;">#<?php echo $o['id']; ?></td>
                <td style="padding: 15px;"><?php echo h($o['username']); ?><br><span style="font-size: 0.8rem; color: #777;"><?php echo h($o['email']); ?></span></td>
                <td style="padding: 15px;"><?php echo date('d M Y, H:i', strtotime($o['created_at'])); ?></td>
                <td style="padding: 15px; text-align: center;">
                    <span style="padding: 3px 8px; border-radius: 12px; font-size: 0.8rem; background: #e9ecef;"><?php echo ucfirst($o['status']); ?></span>
                </td>
                <td style="padding: 15px; text-align: right;">£<?php echo number_format($o['total_amount'], 2); ?></td>
                <td style="padding: 15px; text-align: center;">
                    <a href="<?php echo $base_url; ?>/staff/orders.php" class="btn" style="padding: 5px 10px; font-size: 0.8rem;">Manage in Staff</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
