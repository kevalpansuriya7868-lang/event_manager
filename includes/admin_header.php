<?php
/**
 * Admin Layout Header
 * Event Management System
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

$pageTitle = $pageTitle ?? 'Admin Panel';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitize($pageTitle) ?> | EventHub Admin</title>
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

<div class="admin-wrapper">
    <!-- Sidebar Navigation -->
    <?php require_once __DIR__ . '/admin_sidebar.php'; ?>

    <!-- Main Content Area -->
    <main class="admin-main">
        <!-- Topbar -->
        <header class="admin-topbar">
            <div class="topbar-left">
                <div class="breadcrumbs">
                    <a href="<?= url('admin/dashboard.php') ?>"><i class="fas fa-home"></i></a>
                    <span>/</span>
                    <span><?= sanitize($pageTitle) ?></span>
                </div>
            </div>

            <div class="topbar-right">
                <span style="font-size: 13px; color: var(--text-muted);">
                    <i class="far fa-calendar"></i> <?= date('l, M d, Y') ?>
                </span>
                <a href="<?= url('index.php') ?>" target="_blank" class="btn btn-secondary btn-sm" title="Open Public Website">
                    <i class="fas fa-external-link-alt"></i> Public Site
                </a>
                <a href="<?= url('logout.php') ?>" class="btn btn-danger btn-sm" title="Logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </header>

        <!-- Page Dynamic Content Container -->
        <div class="admin-content">
            <?php display_flash(); ?>
