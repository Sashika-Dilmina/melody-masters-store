<?php
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function require_login() {
    global $base_url;
    if (!is_logged_in()) {
        set_flash_message('error', 'Please log in to access this page.');
        header('Location: ' . $base_url . '/pages/login.php');
        exit;
    }
}

function get_current_user_role() {
    return $_SESSION['user_role'] ?? null;
}

function get_current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

function require_role($role) {
    global $base_url;
    require_login();
    if (get_current_user_role() !== $role) {
        set_flash_message('error', 'Unauthorized access.');
        header('Location: ' . $base_url . '/index.php');
        exit;
    }
}