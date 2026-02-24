<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    
    $cart = $_SESSION['cart'] ?? [];
    if (empty($cart)) {
        set_flash_message('error', 'Your cart is empty.');
        header('Location: ' . $base_url . '/pages/cart.php');
        exit;
    }
    
    $shipping_name = sanitize_input($_POST['shipping_name'] ?? '');
    $shipping_address = sanitize_input($_POST['shipping_address'] ?? '');
    
    $physical_subtotal = 0;
    $digital_subtotal = 0;
    $has_physical = false;
    
    $ids = array_map('intval', array_keys($cart));
    $id_list = implode(',', $ids);
    $products = fetch_all("SELECT * FROM products WHERE id IN ($id_list)");
    
    // Validate stock and calculate totals
    foreach ($products as $p) {
        $qty = $cart[$p['id']]['quantity'];
        
        if ($p['product_type'] === 'physical') {
            if ($qty > $p['stock_qty']) {
                set_flash_message('error', 'Not enough stock for ' . h($p['name']));
                header('Location: ' . $base_url . '/pages/cart.php');
                exit;
            }
            $physical_subtotal += $p['price'] * $qty;
            $has_physical = true;
        } else {
            $digital_subtotal += $p['price'] * $qty;
        }
    }
    
    if ($has_physical && empty($shipping_address)) {
        set_flash_message('error', 'Shipping address is required for physical items.');
        header('Location: ' . $base_url . '/pages/checkout.php');
        exit;
    }
    
    $shipping = calculate_shipping($physical_subtotal);
    $total_amount = $physical_subtotal + $digital_subtotal + $shipping;
    $user_id = get_current_user_id();
    
    global $conn;
    $conn->begin_transaction();
    
    try {
        // Insert order
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, shipping_address, status) VALUES (?, ?, ?, 'pending')");
        $stmt->bind_param("ids", $user_id, $total_amount, $shipping_address);
        $stmt->execute();
        $order_id = $stmt->insert_id;
        $stmt->close();
        
        // Insert order items and update stock
        $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt_stock = $conn->prepare("UPDATE products SET stock_qty = stock_qty - ? WHERE id = ? AND product_type = 'physical'");
        
        foreach ($products as $p) {
            $qty = $cart[$p['id']]['quantity'];
            $price = $p['price'];
            
            $stmt_item->bind_param("iiid", $order_id, $p['id'], $qty, $price);
            $stmt_item->execute();
            
            if ($p['product_type'] === 'physical') {
                $stmt_stock->bind_param("ii", $qty, $p['id']);
                $stmt_stock->execute();
            }
        }
        
        $stmt_item->close();
        $stmt_stock->close();
        
        $conn->commit();
        
        // Clear cart
        unset($_SESSION['cart']);
        
        header('Location: ' . $base_url . '/pages/order-success.php?id=' . $order_id);
        exit;
        
    } catch (Exception $e) {
        $conn->rollback();
        set_flash_message('error', 'Order placement failed: ' . $e->getMessage());
        header('Location: ' . $base_url . '/pages/checkout.php');
        exit;
    }
}
header('Location: ' . $base_url . '/index.php');
exit;