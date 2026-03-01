<?php global $base_url; ?>
</div> <!-- End main content container -->

<footer class="section" style="background: var(--primary); color: var(--white); margin-top: auto; padding: 4rem 0 2rem;">
    <div class="container grid grid-4">
        <div>
            <h3 style="color: white; margin-bottom: 1.5rem;">Melody Masters</h3>
            <p class="muted" style="font-size: 0.9rem;">Elevating your musical journey with premium instruments and digital sheet music. Crafted for masters by masters.</p>
        </div>
        <div>
            <h4 style="color: white; margin-bottom: 1rem;">Shop</h4>
            <ul style="list-style: none; padding: 0;">
                <li class="mb-2"><a href="<?php echo $base_url; ?>/pages/shop.php" class="muted text-sm">All Products</a></li>
                <li class="mb-2"><a href="<?php echo $base_url; ?>/pages/shop.php?category=Instruments" class="muted text-sm">Instruments</a></li>
                <li class="mb-2"><a href="<?php echo $base_url; ?>/pages/shop.php?category=Sheet Music" class="muted text-sm">Digital Music</a></li>
            </ul>
        </div>
        <div>
            <h4 style="color: white; margin-bottom: 1rem;">Support</h4>
            <ul style="list-style: none; padding: 0;">
                <li class="mb-2"><a href="#" class="muted text-sm">Help Center</a></li>
                <li class="mb-2"><a href="#" class="muted text-sm">Terms of Service</a></li>
                <li class="mb-2"><a href="#" class="muted text-sm">Privacy Policy</a></li>
            </ul>
        </div>
        <div>
            <h4 style="color: white; margin-bottom: 1rem;">Contact</h4>
            <p class="muted text-sm">support@melodymasters.com</p>
            <p class="muted text-sm">+44 20 7946 0000</p>
        </div>
    </div>
    
    <div class="container" style="margin-top: 4rem; padding-top: 2rem; border-top: 1px solid rgba(255,255,255,0.1); text-align: center;">
        <p class="muted text-sm">
            &copy; <?php echo date('Y'); ?> Melody Masters Store. All Rights Reserved.
        </p>
    </div>
</footer>

<script src="<?php echo $base_url; ?>/assets/js/app.js"></script>
</body>
</html>