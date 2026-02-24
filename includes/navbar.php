<?php global $base_url; ?>
<nav class="navbar">
    <div class="container navbar-container">
        <a href="<?php echo $base_url; ?>/index.php" class="navbar-brand">Melody Masters</a>
        <ul class="navbar-nav">
            <li><a href="<?php echo $base_url; ?>/pages/shop.php">Shop</a></li>
            <li><a href="<?php echo $base_url; ?>/pages/cart.php">Cart <span class="badge"><?php echo get_cart_count(); ?></span></a></li>
            <?php if (is_logged_in()): ?>
                <li><a href="<?php echo $base_url; ?>/pages/account.php">Account</a></li>
                <?php if (get_current_user_role() === 'admin'): ?>
                    <li><a href="<?php echo $base_url; ?>/admin/dashboard.php">Admin Dashboard</a></li>
                <?php elseif (get_current_user_role() === 'staff'): ?>
                    <li><a href="<?php echo $base_url; ?>/staff/dashboard.php">Staff Dashboard</a></li>
                <?php endif; ?>
                <li><a href="<?php echo $base_url; ?>/pages/logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="<?php echo $base_url; ?>/pages/login.php">Login</a></li>
                <li><a href="<?php echo $base_url; ?>/pages/register.php">Register</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>