<?php
// admin/categories.php – add/delete categories with auto-slug
require_once __DIR__ . '/../includes/header.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');

    if (isset($_POST['add_category'])) {
        $name = sanitize_input($_POST['name'] ?? '');
        if (!empty($name)) {
            // Generate unique slug
            $base_slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $name), '-'));
            $slug = $base_slug;
            $n    = 1;
            while (fetch_one("SELECT id FROM categories WHERE slug = ?", "s", [$slug])) {
                $slug = $base_slug . '-' . (++$n);
            }
            execute_query(
                "INSERT INTO categories (name, slug) VALUES (?, ?)",
                "ss", [$name, $slug]
            );
            set_flash_message('success', 'Category added.');
        } else {
            set_flash_message('error', 'Name required.');
        }
    } elseif (isset($_POST['delete_category'])) {
        $cat_id = (int)$_POST['category_id'];
        $in_use = fetch_one("SELECT id FROM products WHERE category_id = ? AND is_active = 1 LIMIT 1", "i", [$cat_id]);
        if ($in_use) {
            set_flash_message('error', 'Cannot delete – category in use.');
        } else {
            execute_query("DELETE FROM categories WHERE id = ?", "i", [$cat_id]);
            set_flash_message('success', 'Category removed.');
        }
    }
    header('Location: categories.php');
    exit;
}

$categories = fetch_all("SELECT * FROM categories ORDER BY name ASC");
?>

<div style="margin-bottom: 30px; border-bottom: 1px solid #e2e8f0; padding-bottom: 20px;">
    <h2 style="margin: 0;">Category Management</h2>
</div>

<!-- Add form -->
<div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 25px; border-radius: 8px; margin-bottom: 30px;">
    <form method="POST" action="">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="add_category" value="1">
        <label style="display: block; font-size: 0.85rem; font-weight: 500; margin-bottom: 8px;">Create New Category</label>
        <div style="display: flex; gap: 10px;">
            <input type="text" name="name" placeholder="Category name" required
                   style="flex: 1; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem;">
            <button type="submit" class="btn" style="padding: 10px 20px; border-radius: 6px; font-weight: 600;">Add</button>
        </div>
    </form>
</div>

<!-- Table -->
<div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
    <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
        <thead>
            <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; text-align: left; color: #475569;">
                <th style="padding: 15px;">ID</th>
                <th style="padding: 15px;">Name</th>
                <th style="padding: 15px;">URL Slug</th>
                <th style="padding: 15px; text-align: center;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $cat): ?>
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 15px;"><?php echo $cat['id']; ?></td>
                <td style="padding: 15px; font-weight: 600;"><?php echo h($cat['name']); ?></td>
                <td style="padding: 15px; font-family: monospace; font-size: 0.8rem; color: #64748b;"><?php echo h($cat['slug']); ?></td>
                <td style="padding: 15px; text-align: center;">
                    <form method="POST" action="" style="display: inline;">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="delete_category" value="1">
                        <input type="hidden" name="category_id" value="<?php echo $cat['id']; ?>">
                        <button type="submit" class="btn" style="background: #fff; color: #ef4444; border: 1px solid #fee2e2; padding: 5px 12px; font-size: 0.8rem; border-radius: 4px;" onclick="return confirm('Delete category?');">Remove</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
