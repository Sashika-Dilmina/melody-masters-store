<?php
// api/download-file.php – secure download gate for digital products
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_login();

$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
$user_id    = get_current_user_id();

if ($product_id <= 0) {
    http_response_code(400);
    die('Invalid product.');
}

// Verify the current user actually purchased this digital product
// (and the order has not been cancelled)
$sql = "
    SELECT p.file_url
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    JOIN products p ON oi.product_id = p.id
    WHERE o.user_id      = ?
      AND oi.product_id  = ?
      AND p.product_type = 'digital'
      AND o.status      != 'cancelled'
    LIMIT 1
";
$product = fetch_one($sql, "ii", [$user_id, $product_id]);

if (!$product || empty($product['file_url'])) {
    set_flash_message('error', 'You do not have access to this download.');
    header('Location: ' . $base_url . '/pages/downloads.php');
    exit;
}

// Resolve the file path – basename() prevents directory traversal
$filepath = __DIR__ . '/../uploads/digital/' . basename($product['file_url']);

if (!file_exists($filepath) || !is_readable($filepath)) {
    http_response_code(404);
    die('File not found on server. Please contact support.');
}

// Stream the file securely
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . filesize($filepath));
ob_clean();
flush();
readfile($filepath);
exit;
