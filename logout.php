<?php
/**
 * Administrator Logout
 * Event Management System
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Unset all session variables
$_SESSION = [];

// Destroy session cookie if present
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Start fresh session just for the flash message
session_start();
set_flash('info', 'You have been successfully logged out.');
header('Location: ' . url('login.php'));
exit();
