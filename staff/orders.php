<?php
require_once __DIR__ . '/../includes/header.php';
require_role('staff');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    $order_id = (int)$_POST['order_id'];
    $status = sanitize_input($_POST['status']);
    
    if (in_array($status, ['pending', 'processing', 'shipped', 'cancelled'])) {
        execute_query("UPDATE orders SET status = ? WHERE id = ?", "si", [$status, $order_id]);
        set_flash_message('success', 'Order status updated.');
    }
    header('Location: orders.php');
    exit;
}

$orders = fetch_all("
    SELECT o.*, u.full_name as username, u.email 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC
");
?>

<h2>Manage Orders (Staff)</h2>

<table style="width: 100%; border-collapse: collapse; background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
    <thead>
        <tr style="background: var(--primary-color); color: #fff;">
            <th style="padding: 10px; text-align: left;">ID</th>
            <th style="padding: 10px; text-align: left;">Customer</th>
            <th style="padding: 10px; text-align: left;">Date</th>
            <th style="padding: 10px; text-align: right;">Total</th>
            <th style="padding: 10px; text-align: center;">Status</th>
            <th style="padding: 10px; text-align: center;">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($orders as $o): ?>
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 15px;">#<?php echo $o['id']; ?></td>
                <td style="padding: 15px;"><?php echo h($o['username']); ?></td>
                <td style="padding: 15px;"><?php echo date('d M Y, H:i', strtotime($o['created_at'])); ?></td>
                <td style="padding: 15px; text-align: right;">£<?php echo number_format($o['total_amount'], 2); ?></td>
                <td style="padding: 15px; text-align: center;">
                    <span style="padding: 3px 8px; border-radius: 12px; font-size: 0.8rem; background: #e9ecef;"><?php echo ucfirst($o['status']); ?></span>
                </td>
                <td style="padding: 15px; text-align: center;">
                    <form method="POST" action="" style="display: flex; gap: 5px; justify-content: center;">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="update_status" value="1">
                        <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
                        <select name="status" style="padding: 5px;">
                            <option value="pending" <?php echo $o['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="processing" <?php echo $o['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                            <option value="shipped" <?php echo $o['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                            <option value="cancelled" <?php echo $o['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                        <button type="submit" class="btn" style="padding: 5px 10px; font-size: 0.8rem;">Update</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
