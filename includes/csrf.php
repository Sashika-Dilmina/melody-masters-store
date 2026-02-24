<?php
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        die('CSRF token validation failed.');
    }
    return true;
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . h(generate_csrf_token()) . '">';
}
