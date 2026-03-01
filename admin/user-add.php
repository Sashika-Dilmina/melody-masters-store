<?php
// admin/user-add.php
require_once __DIR__ . '/../includes/header.php';
require_role('admin');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');

    $full_name = sanitize_input($_POST['full_name'] ?? '');
    $email     = strtolower(trim($_POST['email'] ?? ''));
    $password  = $_POST['password'] ?? '';
    $role      = $_POST['role'] ?? 'customer';
    
    // Validation
    if (empty($full_name) || empty($email) || empty($password)) {
        $errors[] = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email address provided.';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    } elseif (!in_array($role, ['admin', 'staff', 'customer'])) {
        $errors[] = 'Invalid role selected.';
    } else {
        $existing = fetch_one("SELECT id FROM users WHERE email = ?", "s", [$email]);
        if ($existing) {
             $errors[] = 'Email already exists in our system.';
        } else {
             $hashed_password = password_hash($password, PASSWORD_DEFAULT);
             $stmt = execute_query(
                 "INSERT INTO users (full_name, email, password_hash, role, is_active) VALUES (?, ?, ?, ?, 1)", 
                 "ssss", [$full_name, $email, $hashed_password, $role]
             );
             if ($stmt) {
                 set_flash_message('success', "Account for " . h($full_name) . " created successfully as " . h($role) . ".");
                 header('Location: users.php');
                 exit;
             } else {
                 $errors[] = 'Database error occurred. Please try again.';
             }
        }
    }
    
    if (!empty($errors)) {
        set_flash_message('error', implode(' ', $errors));
    }
}
?>

<div class="space-between mb-5 reveal">
    <div>
        <h1 class="title" style="font-size: 2rem; margin: 0;">Add New Member</h1>
        <p class="muted">Provision a new account with specific administrative or customer privileges.</p>
    </div>
    <a href="users.php" class="btn btn-outline" style="padding: 0.6rem 1.25rem; font-size: 0.85rem;">
        &larr; Back to Member List
    </a>
</div>

<div class="reveal" style="max-width: 800px; margin: 0 auto; animation-delay: 0.1s;">
    <form method="POST" action="">
        <?php echo csrf_field(); ?>
        
        <div class="card" style="padding: 3rem;">
            <h3 class="mb-5" style="font-size: 1.25rem;">Account Credentials</h3>
            
            <div class="grid grid-2 mb-4">
                <div>
                    <label class="mb-2" style="font-weight: 600;">Full Legal Name <span style="color: var(--error);">*</span></label>
                    <input type="text" name="full_name" required placeholder="John Doe" value="<?php echo h($_POST['full_name'] ?? ''); ?>" class="input">
                </div>
                <div>
                    <label class="mb-2" style="font-weight: 600;">Email Address <span style="color: var(--error);">*</span></label>
                    <input type="email" name="email" required placeholder="john@example.com" value="<?php echo h($_POST['email'] ?? ''); ?>" class="input">
                </div>
            </div>

            <div class="grid grid-2 mb-5">
                <div>
                    <label class="mb-2" style="font-weight: 600;">System Password <span style="color: var(--error);">*</span></label>
                    <input type="password" name="password" required placeholder="••••••••" class="input">
                    <p class="text-xs muted mt-1">Minimum 8 characters required.</p>
                </div>
                <div>
                    <label class="mb-2" style="font-weight: 600;">Access Level / Role <span style="color: var(--error);">*</span></label>
                    <select name="role" required class="select">
                        <option value="customer" <?php echo (($_POST['role'] ?? '') === 'customer') ? 'selected' : ''; ?>>Customer (Default)</option>
                        <option value="staff" <?php echo (($_POST['role'] ?? '') === 'staff') ? 'selected' : ''; ?>>Internal Staff</option>
                        <option value="admin" <?php echo (($_POST['role'] ?? '') === 'admin') ? 'selected' : ''; ?>>Full Administrator</option>
                    </select>
                </div>
            </div>

            <div class="row" style="gap: 1.5rem; justify-content: flex-end; padding-top: 2rem; border-top: 1px solid var(--border);">
                <a href="users.php" class="btn btn-outline" style="padding: 1rem 2rem;">Cancel</a>
                <button type="submit" class="btn btn-primary" style="padding: 1rem 2.5rem; font-weight: 800;">CREATE MEMBER ACCOUNT</button>
            </div>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
