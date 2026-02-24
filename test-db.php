<?php
require_once __DIR__ . '/config/db.php';

echo "✅ DB Connected Successfully!<br><br>";

$stmt = $pdo->query("SELECT NOW() AS server_time");
$row = $stmt->fetch();

echo "MySQL Server Time: " . e($row['server_time']);