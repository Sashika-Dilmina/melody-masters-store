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
        set_flash_message('success', "Role for user #$id updated to $role.");
    }
    header('Location: users.php');
    exit;
}

$users = fetch_all("SELECT * FROM users ORDER BY id DESC");
?>

<div class="space-between mb-5 reveal">
    <div>
        <h1 class="title" style="font-size: 2rem; margin: 0;">User Management</h1>
        <p class="muted">Monitor accounts and manage administrative access levels.</p>
    </div>
    <div class="row" style="gap: 1.5rem;">
        <div class="card card-hover" style="padding: 0.75rem 1.5rem; border-color: rgba(59, 130, 246, 0.1); background: rgba(59, 130, 246, 0.05); margin: 0;">
            <span style="font-weight: 800; color: var(--accent); font-size: 1.1rem;"><?php echo count($users); ?></span>
            <span class="muted text-xs" style="text-transform: uppercase; font-weight: 700; margin-left: 8px;">Members</span>
        </div>
        <a href="user-add.php" class="btn btn-primary" style="padding: 0.75rem 1.5rem; display: flex; align-items: center; gap: 8px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
            Add New Member
        </a>
    </div>
</div>

<div class="card reveal" style="padding: 0; overflow: hidden; animation-delay: 0.1s;">
    <div class="table-container" style="border: none;">
        <table class="table">
            <thead>
                <tr>
                    <th style="padding-left: 2rem;">ID</th>
                    <th>Full Name & Email</th>
                    <th>Account Role</th>
                    <th class="text-right" style="padding-right: 2rem;">Administrative Control</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td class="muted" style="padding-left: 2rem;">#<?php echo $u['id']; ?></td>
                    <td>
                        <div class="row">
                            <div style="width: 36px; height: 36px; background: var(--bg-soft); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; color: var(--accent); font-size: 0.85rem; border: 1px solid var(--border);">
                                <?php echo strtoupper(substr($u['full_name'], 0, 1)); ?>
                            </div>
                            <div>
                                <div style="font-weight: 700; color: var(--primary);"><?php echo h($u['full_name']); ?></div>
                                <div class="muted text-xs"><?php echo h($u['email']); ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <?php 
                        $role_style = 'background: #F1F5F9; color: #475569;';
                        if ($u['role'] === 'admin') $role_style = 'background: #FEE2E2; color: #991B1B;';
                        elseif ($u['role'] === 'staff') $role_style = 'background: #FEF3C7; color: #92400E;';
                        ?>
                        <span class="badge" style="<?php echo $role_style; ?> font-size: 0.7rem; padding: 0.25rem 0.6rem; font-weight: 700;">
                            <?php echo strtoupper($u['role']); ?>
                        </span>
                    </td>
                    <td class="text-right" style="padding-right: 2rem;">
                        <form method="POST" action="" class="row" style="justify-content: flex-end; gap: 0.75rem;">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="update_role" value="1">
                            <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                            <select name="role" class="select" style="max-width: 140px; padding: 0.4rem 0.75rem; font-size: 0.85rem;">
                                <option value="customer" <?php echo $u['role'] === 'customer' ? 'selected' : ''; ?>>Customer</option>
                                <option value="staff" <?php echo $u['role'] === 'staff' ? 'selected' : ''; ?>>Staff Member</option>
                                <option value="admin" <?php echo $u['role'] === 'admin' ? 'selected' : ''; ?>>Store Admin</option>
                            </select>
                            <button type="submit" class="btn btn-outline" style="padding: 0.45rem 1rem; font-size: 0.85rem;">
                                Update
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
