<?php
// admin/users.php
require_once __DIR__ . '/../includes/header.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    $id = (int)$_POST['user_id'];
    $role = sanitize_input($_POST['role']);
    
    if (in_array($role, ['admin', 'staff', 'customer'])) {
        execute_query("UPDATE users SET role = ? WHERE id = ?", "si", [$role, $id]);
        set_flash_message('success', 'User role modified.');
    }
    header('Location: users.php');
    exit;
}

$users = fetch_all("SELECT * FROM users ORDER BY id DESC");
?>

<div style="margin-bottom: 30px; border-bottom: 1px solid #e2e8f0; padding-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
    <h2 style="margin: 0;">User Management</h2>
    <span style="color: #64748b; font-size: 0.9rem;"><?php echo count($users); ?> Users Registered</span>
</div>

<div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
    <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
        <thead>
            <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; text-align: left; color: #475569;">
                <th style="padding: 15px;">ID</th>
                <th style="padding: 15px;">User</th>
                <th style="padding: 15px;">Email</th>
                <th style="padding: 15px;">Role Status</th>
                <th style="padding: 15px; text-align: center;">Change Role</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 15px;"><?php echo $u['id']; ?></td>
                <td style="padding: 15px; font-weight: 600;"><?php echo h($u['full_name']); ?></td>
                <td style="padding: 15px; color: #64748b; font-size: 0.85rem;"><?php echo h($u['email']); ?></td>
                <td style="padding: 15px;">
                    <span style="padding: 3px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; background: #f1f5f9; color: #475569;">
                        <?php echo h($u['role']); ?>
                    </span>
                </td>
                <td style="padding: 15px; text-align: center;">
                    <form method="POST" action="" style="display: flex; gap: 8px; justify-content: center;">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="update_role" value="1">
                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                        <select name="role" style="padding: 5px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.8rem;">
                            <option value="customer" <?php echo $u['role'] === 'customer' ? 'selected' : ''; ?>>Customer</option>
                            <option value="staff" <?php echo $u['role'] === 'staff' ? 'selected' : ''; ?>>Staff</option>
                            <option value="admin" <?php echo $u['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        </select>
                        <button type="submit" class="btn" style="padding: 6px 12px; font-size: 0.8rem; border-radius: 4px;">Update</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
