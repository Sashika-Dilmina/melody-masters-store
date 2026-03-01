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
            <li><a href="<?php echo $base_url; ?>/pages/shop.php" class="nav-link <?php echo $current_page === 'shop.php' ? 'active' : ''; ?>">Shop</a></li>
            
            <?php if (is_logged_in()): ?>
                <?php if (get_current_user_role() !== 'admin'): ?>
                    <li><a href="<?php echo $base_url; ?>/pages/cart.php" class="nav-link <?php echo $current_page === 'cart.php' ? 'active' : ''; ?>">
                        Cart <span class="badge badge-status" style="margin-left: 4px;"><?php echo get_cart_count(); ?></span>
                    </a></li>
                <?php endif; ?>
                
                <li><a href="<?php echo $base_url; ?>/pages/account.php" class="nav-link <?php echo ($current_page === 'account.php' || $current_page === 'orders.php' || $current_page === 'downloads.php') ? 'active' : ''; ?>">Account</a></li>
                
                <?php if (get_current_user_role() === 'admin'): ?>
                    <li><a href="<?php echo $base_url; ?>/admin/dashboard.php" class="nav-link <?php echo (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) ? 'active' : ''; ?>">Admin</a></li>
                <?php elseif (get_current_user_role() === 'staff'): ?>
                    <li><a href="<?php echo $base_url; ?>/staff/dashboard.php" class="nav-link <?php echo (strpos($_SERVER['PHP_SELF'], '/staff/') !== false) ? 'active' : ''; ?>">Staff</a></li>
                <?php endif; ?>
                
                <li>
                    <a href="<?php echo $base_url; ?>/pages/logout.php" class="btn btn-outline" style="padding: 0.5rem 1rem;">Sign Out</a>
                </li>
            <?php else: ?>
                <li><a href="<?php echo $base_url; ?>/pages/cart.php" class="nav-link <?php echo $current_page === 'cart.php' ? 'active' : ''; ?>">Cart</a></li>
                <li><a href="<?php echo $base_url; ?>/pages/login.php" class="nav-link <?php echo $current_page === 'login.php' ? 'active' : ''; ?>">Login</a></li>
                <li>
                    <a href="<?php echo $base_url; ?>/pages/register.php" class="btn btn-primary" style="padding: 0.5rem 1.25rem;">Get Started</a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>