<?php
/**
 * Authentication Gatekeeper
 * Event Management System
 * Enforces session check on all administration routes
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    set_flash('danger', 'Access denied. Please log in to view the administrator panel.');
    header('Location: ' . url('login.php'));
    exit();
}

/**
 * Returns currently logged-in administrator information
 */
function current_admin() {
    return [
        'id'       => $_SESSION['admin_id'] ?? null,
        'username' => $_SESSION['admin_username'] ?? 'Administrator',
        'email'    => $_SESSION['admin_email'] ?? ''
    ];
}
