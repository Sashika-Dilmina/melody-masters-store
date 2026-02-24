<?php
$pdo = new PDO('mysql:host=localhost;dbname=melody_masters', 'root', '');
$stmt = $pdo->query("SHOW CREATE TABLE products");
$res = $stmt->fetch(PDO::FETCH_ASSOC);
file_put_contents(__DIR__ . '/schema.txt', $res['Create Table']);
echo "Schema written";
