<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

echo "Database Connected Successfully.<br><br>";

$result = $conn->query("SELECT NOW() AS server_time");
$row = $result->fetch_assoc();

echo "MySQL Server Time: " . h($row['server_time']);