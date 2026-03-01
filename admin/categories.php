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
            set_flash_message('error', 'Category name is required.');
        }
    } elseif (isset($_POST['delete_category'])) {
        $cat_id = (int)$_POST['category_id'];
        $in_use = fetch_one("SELECT id FROM products WHERE category_id = ? AND is_active = 1 LIMIT 1", "i", [$cat_id]);
        if ($in_use) {
            set_flash_message('error', 'Cannot delete – category is currently in use by active products.');
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

<div class="mb-5 reveal">
    <h1 class="title" style="font-size: 2rem;">Category Management</h1>
    <p class="muted">Organize your musical catalog into logical collections.</p>
</div>

<div class="dashboard-layout reveal" style="grid-template-columns: 1fr 2fr; animation-delay: 0.1s;">
    <!-- Add form (Sidebar-like Sidebar) -->
    <div class="stack">
        <div class="card" style="padding: 2rem;">
            <div class="row mb-4" style="gap: 12px;">
                <div style="width: 32px; height: 32px; background: rgba(59, 130, 246, 0.1); color: var(--accent); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                </div>
                <h3 class="m-0" style="font-size: 1.1rem;">New Category</h3>
            </div>
            
            <form method="POST" action="">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="add_category" value="1">
                <div class="mb-4">
                    <label class="mb-2" style="font-weight: 600;">Category Name</label>
                    <input type="text" name="name" placeholder="e.g. Acoustic Guitars" required class="input">
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.85rem;">Create Category</button>
            </form>
        </div>

        <div class="card" style="background: var(--bg-soft); border: 2px dashed var(--border); padding: 1.5rem;">
            <p class="text-xs muted" style="line-height: 1.6;">
                <strong>Note:</strong> Slugs are automatically generated for search engine optimization. Categories currently linked to products cannot be deleted.
            </p>
        </div>
    </div>

    <!-- Table -->
    <div class="card" style="padding: 0; overflow: hidden;">
        <div class="table-container" style="border: none;">
            <table class="table">
                <thead>
                    <tr>
                        <th style="padding-left: 2rem;">ID</th>
                        <th>Category Branding</th>
                        <th>Identifier (Slug)</th>
                        <th class="text-right" style="padding-right: 2rem;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="4" class="text-center muted" style="padding: 4rem;">No categories defined yet.</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td class="muted" style="padding-left: 2rem;">#<?php echo $cat['id']; ?></td>
                            <td>
                                <div style="font-weight: 700; color: var(--primary);"><?php echo h($cat['name']); ?></div>
                            </td>
                            <td>
                                <span class="badge" style="background: var(--bg-soft); color: var(--text-muted); font-family: 'DM Mono', monospace; font-size: 0.75rem; padding: 0.25rem 0.6rem;">
                                    <?php echo h($cat['slug']); ?>
                                </span>
                            </td>
                            <td class="text-right" style="padding-right: 2rem;">
                                <form method="POST" action="" style="display: inline;">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="delete_category" value="1">
                                    <input type="hidden" name="category_id" value="<?php echo $cat['id']; ?>">
                                    <button type="submit" class="btn btn-outline" style="padding: 0.4rem 0.75rem; font-size: 0.75rem; color: var(--error); border-color: rgba(239, 68, 68, 0.2);" onclick="return confirm('Remove this category and all its metadata?');">
                                        Remove
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
