<?php
// ── Secure logout ─────────────────────────────────────────────────────────────
// Must start session to destroy it, then redirect via $base_url (not hardcoded).
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Wipe all session data
$_SESSION = [];

// Delete the session cookie if it exists
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(
        session_name(), '', time() - 3600,
        $p['path'], $p['domain'],
        $p['secure'], $p['httponly']
    );
}

session_destroy();

// Restart a fresh session just for the flash message
session_start();
set_flash_message('success', 'You have been logged out.');
header('Location: ' . $base_url . '/pages/login.php');
exit;