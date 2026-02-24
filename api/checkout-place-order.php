<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $base_url . '/index.php');
    exit;
}

verify_csrf_token($_POST['csrf_token'] ?? '');

$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    set_flash_message('error', 'Your cart is empty.');
    header('Location: ' . $base_url . '/pages/cart.php');
    exit;
}

// ── Collect shipping fields ──────────────────────────────────────────────────
$ship_name     = sanitize_input($_POST['ship_name']     ?? '');
$ship_phone    = sanitize_input($_POST['ship_phone']    ?? '');
$ship_address1 = sanitize_input($_POST['ship_address1'] ?? '');
$ship_address2 = sanitize_input($_POST['ship_address2'] ?? '');
$ship_city     = sanitize_input($_POST['ship_city']     ?? '');
$ship_postal   = sanitize_input($_POST['ship_postal']   ?? '');
$ship_country  = sanitize_input($_POST['ship_country']  ?? '');

// ── Load cart products from DB ───────────────────────────────────────────────
$physical_subtotal = 0;
$digital_subtotal  = 0;
$has_physical      = false;

$ids      = array_map('intval', array_keys($cart));
$id_list  = implode(',', $ids);
$products = fetch_all("SELECT * FROM products WHERE id IN ($id_list)");

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

// ── Validate required fields ─────────────────────────────────────────────────
if (empty($ship_name)) {
    set_flash_message('error', 'Full name is required.');
    header('Location: ' . $base_url . '/pages/checkout.php');
    exit;
}

if ($has_physical && (empty($ship_address1) || empty($ship_city) || empty($ship_country))) {
    set_flash_message('error', 'Please fill in all required shipping fields.');
    header('Location: ' . $base_url . '/pages/checkout.php');
    exit;
}

// ── Calculate totals ─────────────────────────────────────────────────────────
$subtotal        = $physical_subtotal + $digital_subtotal;
$shipping_amount = $has_physical ? calculate_shipping($subtotal) : 0;
$total_amount    = $subtotal + $shipping_amount;
$user_id         = get_current_user_id();

// ── Database transaction ─────────────────────────────────────────────────────
global $conn;
$conn->begin_transaction();

try {
    // Generate a unique order number
    $order_number = 'MM-' . strtoupper(bin2hex(random_bytes(4)));

    // 1. Insert into orders using actual DB column names
    $stmt = $conn->prepare("
        INSERT INTO orders
            (user_id, order_number, ship_name, ship_phone, ship_address1, ship_address2,
             ship_city, ship_postal, ship_country,
             subtotal, shipping_amount, total_amount, status)
        VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
    ");
    // i=int  s=string  s=string  s=string  s=string  s=string  s=string  s=string  s=string d=decimal d=decimal d=decimal
    $stmt->bind_param(
        "issssssssddd",
        $user_id,
        $order_number,
        $ship_name, $ship_phone, $ship_address1, $ship_address2,
        $ship_city,  $ship_postal, $ship_country,
        $subtotal, $shipping_amount, $total_amount
    );
    $stmt->execute();
    $order_id = $stmt->insert_id;
    $stmt->close();

    // 2. Insert order_items using actual DB column names:
    //    order_id, product_id, product_name, product_type, unit_price, quantity, line_total
    $stmt_item = $conn->prepare("
        INSERT INTO order_items
            (order_id, product_id, product_name, product_type, unit_price, quantity, line_total)
        VALUES
            (?, ?, ?, ?, ?, ?, ?)
    ");
    // i=int  i=int  s=string  s=string  d=decimal  i=int  d=decimal
    $stmt_stock = $conn->prepare("
        UPDATE products SET stock_qty = stock_qty - ? WHERE id = ? AND product_type = 'physical'
    ");

    foreach ($products as $p) {
        $qty        = (int)$cart[$p['id']]['quantity'];
        $unit_price = (float)$p['price'];
        $line_total = $unit_price * $qty;
        $prod_name  = $p['name'];
        $prod_type  = $p['product_type'];

        $stmt_item->bind_param(
            "iissdid",
            $order_id, $p['id'], $prod_name, $prod_type, $unit_price, $qty, $line_total
        );
        $stmt_item->execute();

        if ($p['product_type'] === 'physical') {
            $stmt_stock->bind_param("ii", $qty, $p['id']);
            $stmt_stock->execute();
        }
    }

    $stmt_item->close();
    $stmt_stock->close();

    $conn->commit();

    // Clear cart and redirect to success page
    unset($_SESSION['cart']);
    header('Location: ' . $base_url . '/pages/order-success.php?id=' . $order_id);
    exit;

} catch (Exception $e) {
    $conn->rollback();
    set_flash_message('error', 'Order placement failed: ' . $e->getMessage());
    header('Location: ' . $base_url . '/pages/checkout.php');
    exit;
}