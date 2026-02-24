<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    
    if ($product_id > 0 && $quantity > 0) {
        $product = fetch_one("SELECT * FROM products WHERE id = ?", "i", [$product_id]);
        if ($product) {
            // For physical products, check stock
            if ($product['product_type'] === 'physical' && $quantity > $product['stock_qty']) {
                set_flash_message('error', 'Not enough stock available.');
            } else {
                if (!isset($_SESSION['cart'])) {
                    $_SESSION['cart'] = [];
                }
                
                // For digital products, force quantity to 1
                if ($product['product_type'] === 'digital') {
                    $_SESSION['cart'][$product_id] = [
                        'quantity' => 1
                    ];
                    set_flash_message('success', 'Digital product added to cart.');
                } else {
                    $current_qty = isset($_SESSION['cart'][$product_id]) ? $_SESSION['cart'][$product_id]['quantity'] : 0;
                    $new_qty = $current_qty + $quantity;
                    
                    if ($new_qty > $product['stock_qty']) {
                        set_flash_message('error', 'Cannot add more than available stock.');
                    } else {
                        $_SESSION['cart'][$product_id] = [
                            'quantity' => $new_qty
                        ];
                        set_flash_message('success', 'Product added to cart.');
                    }
                }
            }
        }
    }
}
header('Location: ' . $_SERVER['HTTP_REFERER']);
exit;