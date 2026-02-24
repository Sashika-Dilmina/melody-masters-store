<?php
require_once __DIR__ . '/../includes/header.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    $id = (int)$_POST['user_id'];
    $role = sanitize_input($_POST['role']);
    
    if (in_array($role, ['admin', 'staff', 'customer'])) {
        execute_query("UPDATE users SET role = ? WHERE id = ?", "si", [$role, $id]);
        set_flash_message('success', 'User role updated.');
    }
    header('Location: users.php');
    exit;
}

$users = fetch_all("SELECT * FROM users ORDER BY id DESC");
?>

<h2>Manage Users</h2>

<table style="width: 100%; border-collapse: collapse; background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
    <thead>
        <tr style="background: var(--primary-color); color: #fff;">
            <th style="padding: 10px; text-align: left;">ID</th>
            <th style="padding: 10px; text-align: left;">Username</th>
            <th style="padding: 10px; text-align: left;">Email</th>
            <th style="padding: 10px; text-align: left;">Role</th>
            <th style="padding: 10px; text-align: center;">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $u): ?>
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 15px;"><?php echo $u['id']; ?></td>
                <td style="padding: 15px;"><?php echo h($u['full_name']); ?></td>
                <td style="padding: 15px;"><?php echo h($u['email']); ?></td>
                <td style="padding: 15px;">
                    <span style="padding: 3px 8px; border-radius: 12px; font-size: 0.8rem; background: #e9ecef;">
                        <?php echo h($u['role']); ?>
                    </span>
                </td>
                <td style="padding: 15px; text-align: center;">
                    <form method="POST" action="" style="display: flex; gap: 5px; justify-content: center;">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="update_role" value="1">
                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                        <select name="role" style="padding: 5px;">
                            <option value="customer" <?php echo $u['role'] === 'customer' ? 'selected' : ''; ?>>Customer</option>
                            <option value="staff" <?php echo $u['role'] === 'staff' ? 'selected' : ''; ?>>Staff</option>
                            <option value="admin" <?php echo $u['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        </select>
                        <button type="submit" class="btn" style="padding: 5px 10px; font-size: 0.8rem;">Update</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
