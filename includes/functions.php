<?php
// includes/functions.php

// ── Base URL ──────────────────────────────────────────────────────────────────
$is_dev_server = (php_sapi_name() === 'cli-server');
$base_url = $is_dev_server ? '' : '/melody-masters-store';

// ── Output sanitisation ───────────────────────────────────────────────────────
/**
 * Escape a string for safe HTML output.
 */
function h($str): string {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

/**
 * Alias kept for back-compat with any code using e().
 */
function e(string $value): string {
    return h($value);
}

/**
 * Sanitise text input (strip extra whitespace, HTML entities & slashes).
 * Use h() for output; use sanitize_input() only when storing plain text.
 */
function sanitize_input($data): string {
    return htmlspecialchars(stripslashes(trim((string)$data)), ENT_QUOTES, 'UTF-8');
}

// ── Flash messages ────────────────────────────────────────────────────────────
function set_flash_message(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash_message(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function display_flash_messages(): void {
    $flash = get_flash_message();
    if ($flash) {
        $class = ($flash['type'] === 'error') ? 'alert-danger' : 'alert-success';
        echo '<div class="alert ' . $class . '">' . h($flash['message']) . '</div>';
    }
}

// ── Cart helpers ──────────────────────────────────────────────────────────────
function get_cart_count(): int {
    $count = 0;
    if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $count += (int)($item['quantity'] ?? 0);
        }
    }
    return $count;
}

/**
 * Returns the shipping cost for a given subtotal if physical goods are present.
 * The threshold check is performed against the provided subtotal (which can include digital).
 */
function calculate_shipping(float $subtotal): float {
    if ($subtotal <= 0) {
        return 0.00;
    }
    if ($subtotal >= FREE_SHIPPING_THRESHOLD) {
        return 0.00;          // free shipping
    }
    return (float)FLAT_SHIPPING_FEE;
}

// ── Database helpers ──────────────────────────────────────────────────────────
/**
 * Execute a prepared statement and return the statement object.
 * Throws a RuntimeException on failure so the caller can catch it.
 */
function execute_query(string $sql, string $types = '', array $params = []): \mysqli_stmt {
    global $conn;
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new \RuntimeException('Query prepare error: ' . $conn->error);
    }
    if ($types && $params) {
        $stmt->bind_param($types, ...$params);
    }
    if (!$stmt->execute()) {
        throw new \RuntimeException('Query execute error: ' . $stmt->error);
    }
    return $stmt;
}

/**
 * Fetch all rows from a prepared query.
 */
function fetch_all(string $sql, string $types = '', array $params = []): array {
    $stmt   = execute_query($sql, $types, $params);
    $result = $stmt->get_result();
    $data   = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    $stmt->close();
    return $data;
}

/**
 * Fetch a single row from a prepared query, or null.
 */
function fetch_one(string $sql, string $types = '', array $params = []): ?array {
    $stmt   = execute_query($sql, $types, $params);
    $result = $stmt->get_result();
    $row    = $result ? $result->fetch_assoc() : null;
    $stmt->close();
    return $row ?: null;
}
