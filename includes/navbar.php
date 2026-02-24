<?php 
global $base_url; 
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar">
    <div class="container navbar-container">
        <a href="<?php echo $base_url; ?>/index.php" class="navbar-brand">
            Melody Masters
        </a>
        
        <ul class="navbar-nav">
            <li><a href="<?php echo $base_url; ?>/pages/shop.php" <?php echo $current_page === 'shop.php' ? 'style="color: var(--secondary-color);"' : ''; ?>>Shop</a></li>
            
            <?php if (is_logged_in()): ?>
                <?php if (get_current_user_role() !== 'admin'): ?>
                    <li><a href="<?php echo $base_url; ?>/pages/cart.php" <?php echo $current_page === 'cart.php' ? 'style="color: var(--secondary-color); font-weight: 700;"' : ''; ?>>
                        Cart<span class="badge"><?php echo get_cart_count(); ?></span>
                    </a></li>
                <?php endif; ?>
                
                <li><a href="<?php echo $base_url; ?>/pages/account.php" <?php echo ($current_page === 'account.php' || $current_page === 'orders.php' || $current_page === 'downloads.php') ? 'style="color: var(--secondary-color);"' : ''; ?>>Account</a></li>
                
                <?php if (get_current_user_role() === 'admin'): ?>
                    <li><a href="<?php echo $base_url; ?>/admin/dashboard.php" <?php echo (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) ? 'style="color: var(--secondary-color);"' : ''; ?>>Admin Panel</a></li>
                <?php elseif (get_current_user_role() === 'staff'): ?>
                    <li><a href="<?php echo $base_url; ?>/staff/dashboard.php" <?php echo (strpos($_SERVER['PHP_SELF'], '/staff/') !== false) ? 'style="color: var(--secondary-color);"' : ''; ?>>Staff Panel</a></li>
                <?php endif; ?>
                
                <li style="margin-left: 10px; border-left: 1px solid rgba(0,0,0,0.1); padding-left: 20px;">
                    <a href="<?php echo $base_url; ?>/pages/logout.php" style="color: #94a3b8; font-size: 0.85rem; font-weight: 500;">Sign Out</a>
                </li>
            <?php else: ?>
                <li><a href="<?php echo $base_url; ?>/pages/cart.php" <?php echo $current_page === 'cart.php' ? 'style="color: var(--secondary-color);"' : ''; ?>>Cart</a></li>
                <li><a href="<?php echo $base_url; ?>/pages/login.php" <?php echo $current_page === 'login.php' ? 'style="color: var(--secondary-color);"' : ''; ?>>Login</a></li>
                <li style="margin-left: 5px;">
                    <a href="<?php echo $base_url; ?>/pages/register.php" class="btn" style="padding: 9px 22px; border-radius: 6px; font-size: 0.85rem; font-weight: 700; background: #fff; color: var(--primary-color); border: 1px solid #e2e8f0; letter-spacing: 0.02em;">Get Started</a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>