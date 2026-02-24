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
    
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 5;
    $comment = sanitize_input($_POST['comment'] ?? '');
    $user_id = get_current_user_id();
    
    if ($rating < 1 || $rating > 5 || empty($comment)) {
        set_flash_message('error', 'Invalid rating or comment.');
        header('Location: ' . $base_url . '/pages/review-submit.php?product_id=' . $product_id);
        exit;
    }

    $sql = "SELECT oi.id FROM order_items oi JOIN orders o ON oi.order_id = o.id WHERE o.user_id = ? AND oi.product_id = ? AND o.status != 'cancelled' LIMIT 1";
    $has_purchased = fetch_one($sql, "ii", [$user_id, $product_id]);

    if (!$has_purchased) {
        set_flash_message('error', 'You must purchase this product to review it.');
        header('Location: ' . $base_url . '/pages/product.php?id=' . $product_id);
        exit;
    }

    $existing = fetch_one("SELECT id FROM reviews WHERE user_id = ? AND product_id = ?", "ii", [$user_id, $product_id]);
    if ($existing) {
        set_flash_message('error', 'You have already reviewed this product.');
        header('Location: ' . $base_url . '/pages/product.php?id=' . $product_id);
        exit;
    }

    $stmt = execute_query("INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)", "iiis", [$product_id, $user_id, $rating, $comment]);
    
    if ($stmt) {
        set_flash_message('success', 'Thank you for your review!');
    } else {
        set_flash_message('error', 'Failed to submit review.');
    }
}

header('Location: ' . $base_url . '/pages/product.php?id=' . $product_id);
exit;
