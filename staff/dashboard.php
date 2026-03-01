<?php
// staff/dashboard.php
require_once __DIR__ . '/../includes/header.php';
require_role('staff');

$stats_pending_orders = fetch_one("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'")['count'];
$low_stock_products = fetch_one("SELECT COUNT(*) as count FROM products WHERE stock_qty <= 5 AND product_type = 'physical' AND is_active = 1")['count'];
?>

<div class="space-between mb-5 reveal">
    <div>
        <h1 class="title" style="font-size: 2rem; margin: 0;">Staff Operations Hub</h1>
        <p class="muted">Internal dashboard for order fulfillment and inventory monitoring.</p>
    </div>
    <div class="card card-hover" style="padding: 0.75rem 1.5rem; border-color: rgba(59, 130, 246, 0.1); background: rgba(59, 130, 246, 0.05);">
        <span class="muted text-xs" style="text-transform: uppercase; font-weight: 700;">Role:</span>
        <span style="font-weight: 800; color: var(--accent); font-size: 0.9rem; margin-left: 5px;">Internal Staff</span>
    </div>
</div>

<div class="grid grid-2 mb-5 reveal" style="animation-delay: 0.1s;">
    <div class="card card-hover" style="padding: 2.5rem; border-left: 4px solid var(--accent);">
         <div style="width: 40px; height: 40px; background: rgba(59, 130, 246, 0.1); color: var(--accent); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
        </div>
        <h4 class="text-xs muted mb-1" style="text-transform: uppercase; font-weight: 700;">Pending Fulfillment</h4>
        <p style="font-size: 2.5rem; font-weight: 800; color: var(--primary);"><?php echo $stats_pending_orders; ?></p>
        <a href="orders.php" class="btn btn-link mt-3" style="padding: 0;">Fulfill Orders &rarr;</a>
    </div>

    <div class="card card-hover" style="padding: 2.5rem; border-left: 4px solid var(--error);">
         <div style="width: 40px; height: 40px; background: rgba(239, 68, 68, 0.1); color: var(--error); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path></svg>
        </div>
        <h4 class="text-xs muted mb-1" style="text-transform: uppercase; font-weight: 700;">Critical Stock Alerts</h4>
        <p style="font-size: 2.5rem; font-weight: 800; color: var(--error);"><?php echo $low_stock_products; ?></p>
        <a href="stock.php" class="btn btn-link mt-3" style="padding: 0; color: var(--error);">Update Inventory &rarr;</a>
    </div>
</div>

<div class="card mb-5 reveal" style="padding: 3rem; background: var(--bg-soft); border: 2px dashed var(--border); animation-delay: 0.2s;">
    <h3 class="mb-5 text-center" style="font-size: 1.25rem;">Internal Navigation Hub</h3>
    <div class="row" style="justify-content: center; gap: 1.5rem; flex-wrap: wrap;">
        <a href="orders.php" class="btn btn-primary" style="padding: 1rem 2rem; min-width: 200px;">
             <svg style="margin-right: 8px;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
             Fulfillment Center
        </a>
        <a href="stock.php" class="btn btn-primary" style="padding: 1rem 2rem; min-width: 200px; background: var(--primary);">
             <svg style="margin-right: 8px;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
             Inventory Control
        </a>
        <a href="<?php echo $base_url; ?>/pages/shop.php" target="_blank" class="btn btn-outline" style="padding: 1rem 2rem; min-width: 200px;">
             <svg style="margin-right: 8px;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
             View Storefrontend
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
