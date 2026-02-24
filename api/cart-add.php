<?php
// api/cart-add.php
// Security: CSRF checked, product validated against DB, stock enforced.
// No auth required – guests can have a cart too.
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $base_url . '/pages/shop.php');
    exit;
}

if (get_current_user_role() === 'admin') {
    set_flash_message('error', 'Administrators cannot add items to the cart.');
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? ($base_url . '/pages/shop.php')));
    exit;
}

verify_csrf_token($_POST['csrf_token'] ?? '');

$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity   = isset($_POST['quantity'])   ? (int)$_POST['quantity']   : 1;

if ($product_id <= 0 || $quantity <= 0) {
    set_flash_message('error', 'Invalid request.');
    header('Location: ' . $base_url . '/pages/shop.php');
    exit;
}

// Validate product exists and is active in the DB
$product = fetch_one(
    "SELECT id, name, product_type, stock_qty, is_active FROM products WHERE id = ? AND is_active = 1",
    "i", [$product_id]
);

if (!$product) {
    set_flash_message('error', 'Product not found or unavailable.');
    header('Location: ' . ($base_url . '/pages/shop.php'));
    exit;
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($product['product_type'] === 'digital') {
    // Digital: always quantity 1, no stock concerns
    $_SESSION['cart'][$product_id] = ['quantity' => 1];
    set_flash_message('success', h($product['name']) . ' added to cart.');
} else {
    // Physical: enforce stock limit
    $current_qty = $_SESSION['cart'][$product_id]['quantity'] ?? 0;
    $new_qty     = $current_qty + $quantity;

    if ($new_qty > $product['stock_qty']) {
        set_flash_message('error', 'Cannot add more than available stock (' . $product['stock_qty'] . ' left).');
    } else {
        $_SESSION['cart'][$product_id] = ['quantity' => $new_qty];
        set_flash_message('success', h($product['name']) . ' added to cart.');
    }
}

// Safe redirect: use referer only if it's from our own site, else fallback
$referer  = $_SERVER['HTTP_REFERER'] ?? '';
$own_host = $_SERVER['HTTP_HOST']    ?? '';
if ($referer && parse_url($referer, PHP_URL_HOST) === $own_host) {
    header('Location: ' . $referer);
} else {
    header('Location: ' . $base_url . '/pages/cart.php');
}
exit;