<?php
// staff/orders.php
require_once __DIR__ . '/../includes/header.php';
require_role('staff');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    $order_id = (int)$_POST['order_id'];
    $status   = sanitize_input($_POST['status']);

    $allowed_staff = ['pending', 'processing', 'shipped', 'cancelled'];
    if (in_array($status, $allowed_staff)) {
        execute_query("UPDATE orders SET status = ? WHERE id = ?", "si", [$status, $order_id]);
        set_flash_message('success', 'Order status modified.');
    }
    header('Location: orders.php');
    exit;
}

$orders = fetch_all("
    SELECT o.*, u.full_name AS username, u.email
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
");

$status_colors = [
    'pending'    => ['bg' => '#fef3c7', 'text' => '#92400e'],
    'paid'       => ['bg' => '#dcfce7', 'text' => '#166534'],
    'processing' => ['bg' => '#dbeafe', 'text' => '#1e40af'],
    'shipped'    => ['bg' => '#f0fdf4', 'text' => '#166534'],
    'completed'  => ['bg' => '#dcfce7', 'text' => '#15803d'],
    'cancelled'  => ['bg' => '#fee2e2', 'text' => '#991b1b'],
];
?>

<div style="margin-bottom: 30px; border-bottom: 1px solid #e2e8f0; padding-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
    <h2 style="margin: 0;">Order Processing</h2>
    <span style="color: #64748b; font-size: 0.9rem;"><?php echo count($orders); ?> Records Found</span>
</div>

<div style="overflow-x: auto; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px;">
    <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
        <thead>
            <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; text-align: left; color: #475569;">
                <th style="padding: 15px;">Order ID</th>
                <th style="padding: 15px;">Customer Detail</th>
                <th style="padding: 15px;">Order Date</th>
                <th style="padding: 15px; text-align: right;">Amount</th>
                <th style="padding: 15px; text-align: center;">Current Status</th>
                <th style="padding: 15px; text-align: center;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $o): 
                $sc = $status_colors[$o['status']] ?? ['bg' => '#f1f5f9', 'text' => '#475569'];
            ?>
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 15px; font-weight: bold;">#<?php echo $o['id']; ?></td>
                <td style="padding: 15px;">
                    <div style="font-weight: 500;"><?php echo h($o['username']); ?></div>
                    <div style="font-size: 0.75rem; color: #94a3b8;"><?php echo h($o['email']); ?></div>
                </td>
                <td style="padding: 15px; color: #64748b; font-size: 0.85rem;">
                    <?php echo date('d M Y, H:i', strtotime($o['created_at'])); ?>
                </td>
                <td style="padding: 15px; text-align: right; font-weight: 600;">
                    £<?php echo number_format($o['total_amount'], 2); ?>
                </td>
                <td style="padding: 15px; text-align: center;">
                    <span style="padding: 3px 10px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; background: <?php echo $sc['bg']; ?>; color: <?php echo $sc['text']; ?>;">
                        <?php echo ucfirst($o['status']); ?>
                    </span>
                </td>
                <td style="padding: 15px; text-align: center;">
                    <form method="POST" action="" style="display: flex; gap: 5px; justify-content: center;">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="update_status" value="1">
                        <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
                        <select name="status" style="padding: 6px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.8rem;">
                            <?php foreach (['pending','processing','shipped','cancelled'] as $s): ?>
                                <option value="<?php echo $s; ?>" <?php echo $o['status'] === $s ? 'selected' : ''; ?>>
                                    <?php echo ucfirst($s); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn" style="padding: 6px 12px; font-size: 0.8rem; border-radius: 4px;">Set</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
