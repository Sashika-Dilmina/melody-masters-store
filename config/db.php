<?php
// config/db.php
require_once __DIR__ . '/config.php';

$dbHost = 'localhost';
$dbName = 'melody_masters';
$dbUser = 'root';
$dbPass = ''; 

$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

if ($conn->connect_error) {
    if (defined('APP_DEBUG') && APP_DEBUG) {
        die("Database connection failed: " . $conn->connect_error);
    }
    die("Database connection failed. Please try again later.");
}

$conn->set_charset("utf8mb4");