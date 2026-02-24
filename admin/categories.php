<?php
require_once __DIR__ . '/../includes/header.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    if (isset($_POST['add_category'])) {
        $name = sanitize_input($_POST['name']);
        if (!empty($name)) {
            execute_query("INSERT INTO categories (name) VALUES (?)", "s", [$name]);
            set_flash_message('success', 'Category added.');
        }
    } elseif (isset($_POST['delete_category'])) {
        $id = (int)$_POST['category_id'];
        execute_query("DELETE FROM categories WHERE id = ?", "i", [$id]);
        set_flash_message('success', 'Category deleted.');
    }
    header('Location: categories.php');
    exit;
}

$categories = fetch_all("SELECT * FROM categories ORDER BY name ASC");
?>

<h2>Manage Categories</h2>

<form method="POST" action="" style="margin-bottom: 20px; background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="add_category" value="1">
    <div style="display: flex; gap: 10px;">
        <input type="text" name="name" placeholder="New Category Name" required style="flex: 1; padding: 8px;">
        <button type="submit" class="btn">Add Category</button>
    </div>
</form>

<table style="width: 100%; border-collapse: collapse; background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
    <thead>
        <tr style="background: var(--primary-color); color: #fff;">
            <th style="padding: 10px; text-align: left;">ID</th>
            <th style="padding: 10px; text-align: left;">Name</th>
            <th style="padding: 10px; text-align: center;">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($categories as $cat): ?>
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 15px;"><?php echo $cat['id']; ?></td>
                <td style="padding: 15px;"><?php echo h($cat['name']); ?></td>
                <td style="padding: 15px; text-align: center;">
                    <form method="POST" action="" style="display: inline;">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="delete_category" value="1">
                        <input type="hidden" name="category_id" value="<?php echo $cat['id']; ?>">
                        <button type="submit" class="btn" style="background: var(--secondary-color); padding: 5px 10px; font-size: 0.8rem;" onclick="return confirm('Are you sure?');">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
