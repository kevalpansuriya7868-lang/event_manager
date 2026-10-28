<?php
/**
 * Public Layout Header
 * Event Management System
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? sanitize($pageTitle) . ' | EventHub' : 'EventHub - Event Management System' ?></title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body>

<header class="public-navbar">
    <div class="public-nav-container">
        <a href="<?= url('index.php') ?>" class="brand-logo">
            <i class="fas fa-calendar-check"></i>
            <span>Event<strong>Hub</strong></span>
        </a>

        <ul class="public-nav-links">
            <li><a href="<?= url('index.php') ?>" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">Home</a></li>
            <li><a href="<?= url('events.php') ?>" class="<?= $currentPage === 'events.php' || $currentPage === 'event-details.php' ? 'active' : '' ?>">Explore Events</a></li>
            <li>
                <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                    <a href="<?= url('admin/dashboard.php') ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-gauge-high"></i> Admin Dashboard
                    </a>
                <?php else: ?>
                    <a href="<?= url('login.php') ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-lock"></i> Admin Login
                    </a>
                <?php endif; ?>
            </li>
        </ul>
    </div>
</header>

<div class="container" style="padding-top: 20px; padding-bottom: 0;">
    <?php display_flash(); ?>
</div>
