<?php
/**
 * Administrator Dashboard
 * Event Management System
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

$pageTitle = 'Dashboard Overview';

try {
    // 1. Calculate dynamic statistics using real MySQL aggregate queries
    $totalEvents = (int)$pdo->query("SELECT COUNT(*) FROM event")->fetchColumn();
    $totalOrganizers = (int)$pdo->query("SELECT COUNT(*) FROM organizer")->fetchColumn();
    $totalVenues = (int)$pdo->query("SELECT COUNT(*) FROM venue")->fetchColumn();
    $totalArtists = (int)$pdo->query("SELECT COUNT(*) FROM artist")->fetchColumn();
    $totalSponsors = (int)$pdo->query("SELECT COUNT(*) FROM sponsor")->fetchColumn();
    $totalStaff = (int)$pdo->query("SELECT COUNT(*) FROM staff")->fetchColumn();
    $totalEquipment = (int)$pdo->query("SELECT COUNT(*) FROM equipment")->fetchColumn();
    $upcomingEventsCount = (int)$pdo->query("SELECT COUNT(*) FROM event WHERE status = 'Upcoming'")->fetchColumn();

    // 2. Fetch recent events with organizer and venue details
    $recentEventsStmt = $pdo->query("
        SELECT e.*, o.organizer_name, v.venue_name, v.city
        FROM event e
        LEFT JOIN organizer o ON e.organizer_id = o.organizer_id
        LEFT JOIN venue v ON e.venue_id = v.venue_id
        ORDER BY e.event_date DESC, e.event_time DESC
        LIMIT 6
    ");
    $recentEvents = $recentEventsStmt->fetchAll();

    // 3. Fetch upcoming events
    $upcomingEventsStmt = $pdo->query("
        SELECT e.*, v.venue_name, v.city
        FROM event e
        LEFT JOIN venue v ON e.venue_id = v.venue_id
        WHERE e.status = 'Upcoming'
        ORDER BY e.event_date ASC
        LIMIT 4
    ");
    $upcomingEvents = $upcomingEventsStmt->fetchAll();

} catch (PDOException $e) {
    die("Database error loading dashboard: " . htmlspecialchars($e->getMessage()));
}

require_once __DIR__ . '/../includes/admin_header.php';
?>

<!-- Welcome Banner -->
<div style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%); color: #ffffff; padding: 28px; border-radius: var(--radius-md); margin-bottom: 28px; box-shadow: var(--shadow-sm); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
    <div>
        <h2 style="font-size: 24px; font-weight: 800; margin-bottom: 6px;">
            Welcome back, <?= sanitize($_SESSION['admin_username'] ?? 'Administrator') ?>!
        </h2>
        <p style="color: #cbd5e1; font-size: 14px;">
            Here is the live operational summary of all events, venues, performers, sponsors, and logistics.
        </p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="<?= url('admin/events/add.php') ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-plus-circle"></i> Create New Event
        </a>
        <a href="<?= url('admin/assignments/event_artists.php') ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-user-plus"></i> Assign Artist
        </a>
    </div>
</div>

<!-- 8 Key Dynamic Metrics -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon indigo">
            <i class="fas fa-calendar-star"></i>
        </div>
        <div class="stat-details">
            <div class="stat-title">Total Events</div>
            <div class="stat-value"><?= $totalEvents ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-details">
            <div class="stat-title">Upcoming Events</div>
            <div class="stat-value"><?= $upcomingEventsCount ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon green">
            <i class="fas fa-user-tie"></i>
        </div>
        <div class="stat-details">
            <div class="stat-title">Organizers</div>
            <div class="stat-value"><?= $totalOrganizers ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon purple">
            <i class="fas fa-landmark"></i>
        </div>
        <div class="stat-details">
            <div class="stat-title">Venues</div>
            <div class="stat-value"><?= $totalVenues ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon rose">
            <i class="fas fa-microphone-lines"></i>
        </div>
        <div class="stat-details">
            <div class="stat-title">Artists & Speakers</div>
            <div class="stat-value"><?= $totalArtists ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon amber">
            <i class="fas fa-handshake"></i>
        </div>
        <div class="stat-details">
            <div class="stat-title">Sponsors</div>
            <div class="stat-value"><?= $totalSponsors ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon teal">
            <i class="fas fa-users-gear"></i>
        </div>
        <div class="stat-details">
            <div class="stat-title">Staff Members</div>
            <div class="stat-value"><?= $totalStaff ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon indigo">
            <i class="fas fa-boxes-stacked"></i>
        </div>
        <div class="stat-details">
            <div class="stat-title">Equipment Records</div>
            <div class="stat-value"><?= $totalEquipment ?></div>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; align-items: start;">
    <!-- Recent Events Management Table -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fas fa-list"></i> Recent Events
            </div>
            <a href="<?= url('admin/events/index.php') ?>" class="btn btn-secondary btn-sm">
                View All Events <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Event Name</th>
                            <th>Date & Time</th>
                            <th>Venue</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentEvents)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 30px; color: var(--text-muted);">
                                    No events recorded yet. <a href="<?= url('admin/events/add.php') ?>">Create your first event</a>.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentEvents as $ev): ?>
                                <tr>
                                    <td>
                                        <strong><?= sanitize($ev['event_name']) ?></strong>
                                        <div style="font-size: 12px; color: var(--text-muted);">
                                            Org: <?= sanitize($ev['organizer_name'] ?? 'N/A') ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div><i class="far fa-calendar-alt" style="color: var(--primary);"></i> <?= format_date($ev['event_date']) ?></div>
                                        <div style="font-size: 12px; color: var(--text-muted);"><i class="far fa-clock"></i> <?= format_time($ev['event_time']) ?></div>
                                    </td>
                                    <td>
                                        <?= sanitize($ev['venue_name'] ?? 'N/A') ?>
                                        <div style="font-size: 12px; color: var(--text-muted);"><?= sanitize($ev['city'] ?? '') ?></div>
                                    </td>
                                    <td>
                                        <?= status_badge($ev['status']) ?>
                                    </td>
                                    <td>
                                        <div class="actions-cell">
                                            <a href="<?= url('admin/events/view.php?id=' . $ev['event_id']) ?>" class="btn btn-secondary btn-icon" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= url('admin/events/edit.php?id=' . $ev['event_id']) ?>" class="btn btn-secondary btn-icon" title="Edit Event">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Quick Operations & Upcoming Events Column -->
    <div>
        <!-- Quick Actions Card -->
        <div class="card" style="margin-bottom: 24px;">
            <div class="card-header">
                <div class="card-title">
                    <i class="fas fa-bolt"></i> Quick Actions
                </div>
            </div>
            <div class="card-body" style="display: flex; flex-direction: column; gap: 10px;">
                <a href="<?= url('admin/events/add.php') ?>" class="btn btn-primary" style="justify-content: flex-start;">
                    <i class="fas fa-plus"></i> Add New Event
                </a>
                <a href="<?= url('admin/organizers/add.php') ?>" class="btn btn-secondary" style="justify-content: flex-start;">
                    <i class="fas fa-user-tie"></i> Add New Organizer
                </a>
                <a href="<?= url('admin/venues/add.php') ?>" class="btn btn-secondary" style="justify-content: flex-start;">
                    <i class="fas fa-landmark"></i> Add New Venue
                </a>
                <a href="<?= url('admin/assignments/event_artists.php') ?>" class="btn btn-secondary" style="justify-content: flex-start;">
                    <i class="fas fa-microphone-lines"></i> Assign Artist to Event
                </a>
                <a href="<?= url('admin/assignments/event_sponsors.php') ?>" class="btn btn-secondary" style="justify-content: flex-start;">
                    <i class="fas fa-handshake"></i> Assign Sponsor to Event
                </a>
            </div>
        </div>

        <!-- Upcoming Schedule Card -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fas fa-calendar-check"></i> Upcoming Schedule
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($upcomingEvents)): ?>
                    <p style="color: var(--text-muted); font-size: 14px;">No upcoming events scheduled.</p>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 14px;">
                        <?php foreach ($upcomingEvents as $ue): ?>
                            <div style="padding: 12px; background: #f8fafc; border-radius: var(--radius-sm); border-left: 4px solid var(--primary);">
                                <div style="font-weight: 700; font-size: 14px; color: var(--text-main);">
                                    <?= sanitize($ue['event_name']) ?>
                                </div>
                                <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">
                                    <i class="far fa-calendar"></i> <?= format_date($ue['event_date']) ?> at <?= format_time($ue['event_time']) ?>
                                </div>
                                <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                                    <i class="fas fa-location-dot"></i> <?= sanitize($ue['venue_name'] ?? 'TBD') ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
