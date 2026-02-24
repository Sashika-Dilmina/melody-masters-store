<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    
    if ($product_id > 0 && isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
        set_flash_message('success', 'Item removed from cart.');
    }
}
header('Location: ' . $base_url . '/pages/cart.php');
exit;