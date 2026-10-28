<?php
/**
 * Admin Sidebar Navigation
 * Event Management System
 */

$currentUri = $_SERVER['REQUEST_URI'] ?? '';
?>
<aside class="admin-sidebar">
    <div class="sidebar-header">
        <i class="fas fa-layer-group"></i>
        <span>Event<strong>Hub</strong> Admin</span>
    </div>

    <ul class="sidebar-menu">
        <li class="sidebar-category">Overview</li>
        <li class="sidebar-item">
            <a href="<?= url('admin/dashboard.php') ?>" class="sidebar-link <?= strpos($currentUri, 'dashboard.php') !== false ? 'active' : '' ?>">
                <i class="fas fa-chart-pie"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li class="sidebar-category">Core Management</li>
        <li class="sidebar-item">
            <a href="<?= url('admin/events/index.php') ?>" class="sidebar-link <?= strpos($currentUri, '/events/') !== false ? 'active' : '' ?>">
                <i class="fas fa-calendar-star"></i>
                <span>Events</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?= url('admin/organizers/index.php') ?>" class="sidebar-link <?= strpos($currentUri, '/organizers/') !== false ? 'active' : '' ?>">
                <i class="fas fa-user-tie"></i>
                <span>Organizers</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?= url('admin/venues/index.php') ?>" class="sidebar-link <?= strpos($currentUri, '/venues/') !== false ? 'active' : '' ?>">
                <i class="fas fa-landmark"></i>
                <span>Venues</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?= url('admin/artists/index.php') ?>" class="sidebar-link <?= strpos($currentUri, '/artists/') !== false ? 'active' : '' ?>">
                <i class="fas fa-microphone-lines"></i>
                <span>Artists & Speakers</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?= url('admin/sponsors/index.php') ?>" class="sidebar-link <?= strpos($currentUri, '/sponsors/') !== false ? 'active' : '' ?>">
                <i class="fas fa-handshake"></i>
                <span>Sponsors</span>
            </a>
        </li>

        <li class="sidebar-category">Resources & Logistics</li>
        <li class="sidebar-item">
            <a href="<?= url('admin/staff/index.php') ?>" class="sidebar-link <?= strpos($currentUri, '/staff/') !== false ? 'active' : '' ?>">
                <i class="fas fa-users-gear"></i>
                <span>Staff Allocation</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?= url('admin/equipment/index.php') ?>" class="sidebar-link <?= strpos($currentUri, '/equipment/') !== false ? 'active' : '' ?>">
                <i class="fas fa-boxes-stacked"></i>
                <span>Equipment</span>
            </a>
        </li>

        <li class="sidebar-category">Relational Assignments</li>
        <li class="sidebar-item">
            <a href="<?= url('admin/assignments/event_artists.php') ?>" class="sidebar-link <?= strpos($currentUri, 'event_artists.php') !== false ? 'active' : '' ?>">
                <i class="fas fa-user-plus"></i>
                <span>Assign Artists</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?= url('admin/assignments/event_sponsors.php') ?>" class="sidebar-link <?= strpos($currentUri, 'event_sponsors.php') !== false ? 'active' : '' ?>">
                <i class="fas fa-circle-dollar-to-slot"></i>
                <span>Assign Sponsors</span>
            </a>
        </li>

        <li class="sidebar-category">Quick Shortcuts</li>
        <li class="sidebar-item">
            <a href="<?= url('index.php') ?>" target="_blank" class="sidebar-link">
                <i class="fas fa-arrow-up-right-from-square"></i>
                <span>View Public Website</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?= url('logout.php') ?>" class="sidebar-link" style="color: #f87171;">
                <i class="fas fa-arrow-right-from-bracket"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>

    <div class="sidebar-footer">
        <div class="admin-user-pill">
            <div class="admin-avatar">
                <?= strtoupper(substr($_SESSION['admin_username'] ?? 'A', 0, 1)) ?>
            </div>
            <div class="admin-user-info">
                <div class="admin-user-name"><?= sanitize($_SESSION['admin_username'] ?? 'Admin') ?></div>
                <div class="admin-user-role"><?= sanitize($_SESSION['admin_email'] ?? 'Administrator') ?></div>
            </div>
        </div>
    </div>
</aside>
