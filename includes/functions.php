<?php
$base_url = '/melody-masters-store';

function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)), ENT_QUOTES, 'UTF-8');
}

function h($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

function set_flash_message($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash_message() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function display_flash_messages() {
    $flash = get_flash_message();
    if ($flash) {
        $class = ($flash['type'] === 'error') ? 'alert-danger' : 'alert-success';
        echo '<div class="alert ' . $class . '">' . h($flash['message']) . '</div>';
    }
}

function get_cart_count() {
    $count = 0;
    if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $count += (int)$item['quantity'];
        }
    }
    return $count;
}

function calculate_shipping($physical_subtotal) {
    if ($physical_subtotal == 0) return 0.00;
    if ($physical_subtotal > 100) return 0.00;
    return 10.00; 
}

function execute_query($sql, $types = "", $params = []) {
    global $conn;
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Query error: " . $conn->error);
    }
    if ($types && $params) {
        $stmt->bind_param($types, ...$params);
    }
    if (!$stmt->execute()) {
        die("Execute error: " . $stmt->error);
    }
    return $stmt;
}

function fetch_all($sql, $types = "", $params = []) {
    $stmt = execute_query($sql, $types, $params);
    $result = $stmt->get_result();
    $data = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    $stmt->close();
    return $data;
}

function fetch_one($sql, $types = "", $params = []) {
    $stmt = execute_query($sql, $types, $params);
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();
    return $row;
}
