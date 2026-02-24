<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
    
    if ($product_id > 0 && isset($_SESSION['cart'][$product_id])) {
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$product_id]);
            set_flash_message('success', 'Item removed from cart.');
        } else {
            $product = fetch_one("SELECT * FROM products WHERE id = ?", "i", [$product_id]);
            if ($product) {
                if ($product['product_type'] === 'digital') {
                     // Keep at 1
                     $_SESSION['cart'][$product_id]['quantity'] = 1;
                } else {
                    if ($quantity > $product['stock_qty']) {
                        set_flash_message('error', 'Requested quantity exceeds stock.');
                        $_SESSION['cart'][$product_id]['quantity'] = $product['stock_qty'];
                    } else {
                        $_SESSION['cart'][$product_id]['quantity'] = $quantity;
                        set_flash_message('success', 'Cart updated.');
                    }
                }
            }
        }
    }
}
header('Location: ' . $base_url . '/pages/cart.php');
exit;