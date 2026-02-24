<?php
session_start();
require_once __DIR__ . '/../config/config.php';
session_destroy();
session_start();
$_SESSION['flash'] = ['type' => 'success', 'message' => 'You have been logged out.'];
header('Location: /melody-masters-store/index.php');
exit;