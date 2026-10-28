<?php
/**
 * Public Event Details Page
 * Event Management System
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    set_flash('danger', 'Invalid event requested.');
    header('Location: ' . url('events.php'));
    exit();
}

try {
    // 1. Fetch Event with Organizer and Venue
    $stmt = $pdo->prepare("
        SELECT e.*, 
               o.organizer_name, o.email as organizer_email, o.phone as organizer_phone, o.organization,
               v.venue_name, v.address as venue_address, v.city as venue_city, v.capacity as venue_capacity
        FROM event e
        LEFT JOIN organizer o ON e.organizer_id = o.organizer_id
        LEFT JOIN venue v ON e.venue_id = v.venue_id
        WHERE e.event_id = ?
    ");
    $stmt->execute([$id]);
    $event = $stmt->fetch();

    if (!$event) {
        set_flash('danger', 'Event not found.');
        header('Location: ' . url('events.php'));
        exit();
    }

    $pageTitle = $event['event_name'];

    // 2. Fetch Assigned Artists
    $artistsStmt = $pdo->prepare("
        SELECT ea.role, a.artist_name, a.genre, a.country, a.contact_email
        FROM event_artist ea
        JOIN artist a ON ea.artist_id = a.artist_id
        WHERE ea.event_id = ?
        ORDER BY a.artist_name ASC
    ");
    $artistsStmt->execute([$id]);
    $assignedArtists = $artistsStmt->fetchAll();

    // 3. Fetch Assigned Sponsors
    $sponsorsStmt = $pdo->prepare("
        SELECT es.sponsorship_type, s.sponsor_name, s.contact_person
        FROM event_sponsor es
        JOIN sponsor s ON es.sponsor_id = s.sponsor_id
        WHERE es.event_id = ?
        ORDER BY es.sponsorship_amount DESC
    ");
    $sponsorsStmt->execute([$id]);
    $assignedSponsors = $sponsorsStmt->fetchAll();

    // 4. Fetch Logistics Summary
    $staffCount = (int)$pdo->query("SELECT COUNT(*) FROM staff WHERE event_id = {$id}")->fetchColumn();
    $equipCount = (int)$pdo->query("SELECT COUNT(*) FROM equipment WHERE event_id = {$id}")->fetchColumn();

} catch (PDOException $e) {
    die("Database query error: " . htmlspecialchars($e->getMessage()));
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div style="margin-bottom: 24px;">
        <a href="<?= url('events.php') ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to All Events
        </a>
    </div>

    <!-- Main Header Card -->
    <div class="card" style="border-radius: var(--radius-lg); overflow: hidden; margin-bottom: 30px;">
        <div style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 60%, #4338ca 100%); color: #ffffff; padding: 40px;">
            <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 12px; flex-wrap: wrap;">
                <?= status_badge($event['status']) ?>
                <span class="badge badge-light" style="background: rgba(255,255,255,0.2); color: #ffffff;">
                    <i class="fas fa-location-dot"></i> <?= sanitize($event['venue_city'] ?? 'Metro') ?>
                </span>
            </div>

            <h1 style="font-size: 34px; font-weight: 800; line-height: 1.2; margin-bottom: 16px;">
                <?= sanitize($event['event_name']) ?>
            </h1>

            <div style="display: flex; gap: 24px; font-size: 15px; color: #cbd5e1; flex-wrap: wrap;">
                <div><i class="far fa-calendar-alt" style="color: #818cf8;"></i> <?= format_date($event['event_date']) ?></div>
                <div><i class="far fa-clock" style="color: #818cf8;"></i> <?= format_time($event['event_time']) ?></div>
                <div><i class="fas fa-building" style="color: #818cf8;"></i> <?= sanitize($event['venue_name'] ?? 'TBD') ?></div>
            </div>
        </div>

        <div class="card-body" style="padding: 32px;">
            <h3 style="font-size: 20px; font-weight: 700; margin-bottom: 12px; color: var(--text-main);">
                About This Event
            </h3>
            <p style="font-size: 15px; color: #475569; line-height: 1.8; margin-bottom: 24px;">
                <?= !empty($event['event_description']) ? nl2br(sanitize($event['event_description'])) : 'No extended description provided for this event.' ?>
            </p>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-top: 30px; border-top: 1px solid var(--border-color); padding-top: 24px;">
                <!-- Host Info -->
                <div>
                    <h4 style="font-size: 16px; font-weight: 700; margin-bottom: 10px; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-user-tie" style="color: var(--primary);"></i> Organized By
                    </h4>
                    <div style="font-size: 15px; font-weight: 600;"><?= sanitize($event['organizer_name'] ?? 'College Committee') ?></div>
                    <div style="font-size: 13px; color: var(--text-muted); margin-bottom: 6px;"><?= sanitize($event['organization'] ?: 'Event Coordinator') ?></div>
                    <div style="font-size: 13px; color: #64748b;">
                        Email: <a href="mailto:<?= sanitize($event['organizer_email']) ?>"><?= sanitize($event['organizer_email']) ?></a>
                    </div>
                </div>

                <!-- Venue Info -->
                <div>
                    <h4 style="font-size: 16px; font-weight: 700; margin-bottom: 10px; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-landmark" style="color: var(--primary);"></i> Venue & Location
                    </h4>
                    <div style="font-size: 15px; font-weight: 600;"><?= sanitize($event['venue_name'] ?? 'TBD') ?></div>
                    <div style="font-size: 13px; color: var(--text-muted); margin-bottom: 6px;"><?= sanitize($event['venue_address']) ?>, <?= sanitize($event['venue_city']) ?></div>
                    <div style="font-size: 13px; color: #64748b;">
                        Seating Capacity: <strong><?= number_format($event['venue_capacity']) ?></strong> attendees
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Featured Artists / Performers Section -->
    <div class="card" style="margin-bottom: 30px;">
        <div class="card-header">
            <div class="card-title">
                <i class="fas fa-microphone-lines"></i> Featured Artists, Performers & Keynote Speakers
            </div>
            <span class="badge badge-primary"><?= count($assignedArtists) ?> Lineup</span>
        </div>
        <div class="card-body">
            <?php if (empty($assignedArtists)): ?>
                <p style="color: var(--text-muted); font-size: 14px; margin: 0;">Artist lineup will be announced soon.</p>
            <?php else: ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">
                    <?php foreach ($assignedArtists as $art): ?>
                        <div style="padding: 18px; border-radius: var(--radius-sm); background: #f8fafc; border: 1px solid var(--border-color); display: flex; align-items: center; gap: 16px;">
                            <div style="width: 48px; height: 48px; border-radius: 50%; background: #e0e7ff; color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                                <i class="fas fa-music"></i>
                            </div>
                            <div>
                                <h4 style="font-size: 15px; font-weight: 700; margin-bottom: 2px;"><?= sanitize($art['artist_name']) ?></h4>
                                <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px;"><?= sanitize($art['genre'] ?: 'Artist') ?> &bull; <?= sanitize($art['country'] ?: 'Global') ?></div>
                                <span class="badge badge-info" style="font-size: 11px;"><?= sanitize($art['role'] ?: 'Performer') ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Official Sponsors Section -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fas fa-handshake"></i> Official Sponsors & Partners
            </div>
            <span class="badge badge-warning"><?= count($assignedSponsors) ?> Sponsors</span>
        </div>
        <div class="card-body">
            <?php if (empty($assignedSponsors)): ?>
                <p style="color: var(--text-muted); font-size: 14px; margin: 0;">Sponsorship packages currently open for partners.</p>
            <?php else: ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 16px;">
                    <?php foreach ($assignedSponsors as $sp): ?>
                        <div style="padding: 16px; border-radius: var(--radius-sm); background: #ffffff; border: 1px solid var(--border-color); text-align: center; box-shadow: var(--shadow-sm);">
                            <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: #d97706; margin-bottom: 6px; letter-spacing: 0.5px;">
                                <?= sanitize($sp['sponsorship_type']) ?>
                            </div>
                            <div style="font-size: 16px; font-weight: 800; color: var(--text-main); margin-bottom: 4px;">
                                <?= sanitize($sp['sponsor_name']) ?>
                            </div>
                            <?php if (!empty($sp['contact_person'])): ?>
                                <div style="font-size: 12px; color: var(--text-muted);">Rep: <?= sanitize($sp['contact_person']) ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
