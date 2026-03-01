<?php
// admin/orders.php
require_once __DIR__ . '/../includes/header.php';
require_role('admin');

// Status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    $order_id = (int)$_POST['order_id'];
    $status   = sanitize_input($_POST['status']);

    $allowed = ['pending', 'paid', 'processing', 'shipped', 'completed', 'cancelled'];
    if (in_array($status, $allowed)) {
        execute_query("UPDATE orders SET status = ? WHERE id = ?", "si", [$status, $order_id]);
        set_flash_message('success', "Order #$order_id status updated to $status.");
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
?>

<div class="space-between mb-5 reveal">
    <div>
        <h1 class="title" style="font-size: 2rem; margin: 0;">Order Management</h1>
        <p class="muted">Track and update the fulfillment status of store transactions.</p>
    </div>
    <div class="card card-hover" style="padding: 0.75rem 1.5rem; border-color: rgba(20, 184, 166, 0.1); background: rgba(20, 184, 166, 0.05);">
        <span style="font-weight: 800; color: var(--teal); font-size: 1.1rem;"><?php echo count($orders); ?></span>
        <span class="muted text-xs" style="text-transform: uppercase; font-weight: 700; margin-left: 8px;">Total Orders</span>
    </div>
</div>

<div class="card reveal" style="padding: 0; overflow: hidden; animation-delay: 0.1s;">
    <div class="table-container" style="border: none;">
        <table class="table">
            <thead>
                <tr>
                    <th style="padding-left: 2rem;">Order ID</th>
                    <th>Customer Details</th>
                    <th>Date & Time</th>
                    <th class="text-right">Total Owed</th>
                    <th class="text-center">Current Status</th>
                    <th class="text-right" style="padding-right: 2rem;">Status Control</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $o): ?>
                <tr>
                    <td style="padding-left: 2rem; font-weight: 800; color: var(--primary);">#<?php echo $o['id']; ?></td>
                    <td>
                        <div style="font-weight: 700; color: var(--primary);"><?php echo h($o['username']); ?></div>
                        <div class="muted text-xs"><?php echo h($o['email']); ?></div>
                    </td>
                    <td>
                        <div class="muted text-sm" style="font-weight: 500;"><?php echo date('d M Y', strtotime($o['created_at'])); ?></div>
                        <div class="muted text-xs"><?php echo date('H:i', strtotime($o['created_at'])); ?></div>
                    </td>
                    <td class="text-right" style="font-weight: 700; color: var(--accent);">
                        £<?php echo number_format($o['total_amount'], 2); ?>
                    </td>
                    <td class="text-center">
                        <span class="badge badge-status <?php echo strtolower($o['status']); ?>" style="padding: 0.4rem 1rem;">
                            <?php echo ucfirst($o['status']); ?>
                        </span>
                    </td>
                    <td class="text-right" style="padding-right: 2rem;">
                        <form method="POST" action="" class="row" style="justify-content: flex-end; gap: 0.75rem;">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="update_status" value="1">
                            <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
                            <select name="status" class="select" style="max-width: 140px; padding: 0.4rem 0.75rem; font-size: 0.85rem;">
                                <?php foreach (['pending','paid','processing','shipped','completed','cancelled'] as $s): ?>
                                    <option value="<?php echo $s; ?>" <?php echo $o['status'] === $s ? 'selected' : ''; ?>>
                                        <?php echo ucfirst($s); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-outline" style="padding: 0.45rem 1rem; font-size: 0.85rem;">
                                Set
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
