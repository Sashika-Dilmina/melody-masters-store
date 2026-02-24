<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_login();

$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
$user_id = get_current_user_id();

if ($product_id <= 0) {
    die('Invalid product.');
}

// Check ownership
$sql = "
    SELECT p.file_url 
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    JOIN products p ON oi.product_id = p.id
    WHERE o.user_id = ? AND oi.product_id = ? AND p.type = 'digital' AND o.status != 'cancelled'
    LIMIT 1
";
$product = fetch_one($sql, "ii", [$user_id, $product_id]);

if (!$product || empty($product['file_url'])) {
    set_flash_message('error', 'You do not have access to this download.');
    header('Location: ' . $base_url . '/pages/downloads.php');
    exit;
}

$filepath = __DIR__ . '/../uploads/digital/' . basename($product['file_url']);

if (!file_exists($filepath)) {
    die('File not found on server.');
}

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="'.basename($filepath).'"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filepath));
readfile($filepath);
exit;
