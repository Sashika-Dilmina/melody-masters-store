<?php
// config/config.php

// Site settings
define('SITE_NAME', 'Melody Masters Instrument Shop');
define('CURRENCY', '£');

// Business rules
define('FREE_SHIPPING_THRESHOLD', 100.00); // Free shipping if subtotal >= 100
define('FLAT_SHIPPING_FEE', 7.50);         // Shipping fee if not free (you can change)

// Security (used later)
define('APP_DEBUG', true); // set false when submitting

// Session (used later)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simple helper to safely print (for later pages)
function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}