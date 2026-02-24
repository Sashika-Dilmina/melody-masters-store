<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Melody Masters</title>
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/style.css">
</head>
<body>
<?php include __DIR__ . '/navbar.php'; ?>
<div class="container main-content">
<?php display_flash_messages(); ?>