<?php
/**
 * Global Helper Functions
 * Event Management System
 */

// Start session if not already active
function init_session() {
    if (session_status() === PHP_SESSION_NONE) {
        // Secure cookie settings
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        session_start();
    }
}

init_session();

/**
 * Sanitize output to prevent XSS attacks
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

/**
 * Format date for display (e.g. Nov 15, 2026)
 */
function format_date($dateStr) {
    if (empty($dateStr)) return '-';
    $timestamp = strtotime($dateStr);
    return date('M d, Y', $timestamp);
}

/**
 * Format time for display (e.g. 09:30 AM)
 */
function format_time($timeStr) {
    if (empty($timeStr)) return '-';
    $timestamp = strtotime($timeStr);
    return date('h:i A', $timestamp);
}

/**
 * Format currency
 */
function format_currency($amount) {
    return '₹ ' . number_format((float)$amount, 2);
}

/**
 * Return styled badge HTML for event status
 */
function status_badge($status) {
    $status = trim((string)$status);
    switch ($status) {
        case 'Upcoming':
            return '<span class="badge badge-info"><i class="fas fa-calendar-alt"></i> Upcoming</span>';
        case 'Ongoing':
            return '<span class="badge badge-success"><i class="fas fa-play-circle"></i> Ongoing</span>';
        case 'Completed':
            return '<span class="badge badge-secondary"><i class="fas fa-check-circle"></i> Completed</span>';
        case 'Cancelled':
            return '<span class="badge badge-danger"><i class="fas fa-times-circle"></i> Cancelled</span>';
        default:
            return '<span class="badge badge-light">' . sanitize($status) . '</span>';
    }
}

/**
 * Set a flash message for display on the next page
 * Types: success, danger, warning, info
 */
function set_flash($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Display flash message if one exists, then remove it
 */
function display_flash() {
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        $type = sanitize($flash['type']);
        $message = sanitize($flash['message']);
        
        $icon = 'fa-info-circle';
        if ($type === 'success') $icon = 'fa-check-circle';
        if ($type === 'danger') $icon = 'fa-exclamation-triangle';
        if ($type === 'warning') $icon = 'fa-exclamation-circle';

        echo "
        <div class='alert alert-{$type}' id='flashAlert'>
            <div class='alert-icon'><i class='fas {$icon}'></i></div>
            <div class='alert-content'>{$message}</div>
            <button type='button' class='alert-close' onclick=\"this.parentElement.style.display='none'\">&times;</button>
        </div>
        ";
        unset($_SESSION['flash_message']);
    }
}

/**
 * CSRF Protection
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Generate relative project link using BASE_URL
 */
function url($path = '') {
    $path = ltrim($path, '/');
    return BASE_URL . '/' . $path;
}
