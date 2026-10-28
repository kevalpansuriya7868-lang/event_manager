<?php
/**
 * Public Landing Page
 * Event Management System
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Home';

try {
    // Fetch upcoming and featured events
    $stmt = $pdo->query("
        SELECT e.*, o.organizer_name, v.venue_name, v.city, v.capacity
        FROM event e
        LEFT JOIN organizer o ON e.organizer_id = o.organizer_id
        LEFT JOIN venue v ON e.venue_id = v.venue_id
        WHERE e.status IN ('Upcoming', 'Ongoing')
        ORDER BY e.event_date ASC
        LIMIT 6
    ");
    $featuredEvents = $stmt->fetchAll();

    // Key platform statistics
    $eventCount = (int)$pdo->query("SELECT COUNT(*) FROM event")->fetchColumn();
    $venueCount = (int)$pdo->query("SELECT COUNT(*) FROM venue")->fetchColumn();
    $artistCount = (int)$pdo->query("SELECT COUNT(*) FROM artist")->fetchColumn();
    $sponsorCount = (int)$pdo->query("SELECT COUNT(*) FROM sponsor")->fetchColumn();

} catch (PDOException $e) {
    die("Database connection error: " . htmlspecialchars($e->getMessage()));
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-content">
        <span class="hero-badge"><i class="fas fa-sparkles"></i> Open-Ended Project &bull; Full-Stack PHP & MySQL</span>
        <h1 class="hero-title">Plan, Organize & Celebrate Memorable Events</h1>
        <p class="hero-subtitle">
            Welcome to EventHub &mdash; a centralized event management platform for college festivals, technical conferences, musical concerts, seminars, and exhibitions.
        </p>
        <div class="hero-cta">
            <a href="<?= url('events.php') ?>" class="btn btn-primary" style="padding: 12px 28px; font-size: 15px;">
                <i class="fas fa-calendar-check"></i> Explore All Events
            </a>
            <a href="<?= url('login.php') ?>" class="btn btn-secondary" style="padding: 12px 28px; font-size: 15px; background: rgba(255,255,255,0.15); color: #ffffff; border-color: rgba(255,255,255,0.3);">
                <i class="fas fa-lock"></i> Administrator Login
            </a>
        </div>
    </div>
</section>

<!-- Public Stats Bar -->
<div class="container" style="padding-top: 0; transform: translateY(-40px); margin-bottom: -10px;">
    <div class="stats-grid" style="margin-bottom: 0;">
        <div class="stat-card" style="box-shadow: var(--shadow-lg);">
            <div class="stat-icon indigo"><i class="fas fa-calendar-star"></i></div>
            <div class="stat-details">
                <div class="stat-title">Events Hosted</div>
                <div class="stat-value"><?= $eventCount ?></div>
            </div>
        </div>

        <div class="stat-card" style="box-shadow: var(--shadow-lg);">
            <div class="stat-icon purple"><i class="fas fa-landmark"></i></div>
            <div class="stat-details">
                <div class="stat-title">Campus Venues</div>
                <div class="stat-value"><?= $venueCount ?></div>
            </div>
        </div>

        <div class="stat-card" style="box-shadow: var(--shadow-lg);">
            <div class="stat-icon rose"><i class="fas fa-microphone-lines"></i></div>
            <div class="stat-details">
                <div class="stat-title">Artists & Speakers</div>
                <div class="stat-value"><?= $artistCount ?></div>
            </div>
        </div>

        <div class="stat-card" style="box-shadow: var(--shadow-lg);">
            <div class="stat-icon amber"><i class="fas fa-handshake"></i></div>
            <div class="stat-details">
                <div class="stat-title">Sponsors & Partners</div>
                <div class="stat-value"><?= $sponsorCount ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Featured Events Section -->
<div class="container">
    <div class="section-header">
        <div>
            <h2 class="section-title">Upcoming & Ongoing Events</h2>
            <p class="section-subtitle">Discover exciting college festivals, tech conferences, and music performances.</p>
        </div>
        <a href="<?= url('events.php') ?>" class="btn btn-secondary">
            View All Events <i class="fas fa-arrow-right"></i>
        </a>
    </div>

    <?php if (empty($featuredEvents)): ?>
        <div class="card">
            <div class="card-body">
                <div class="empty-state">
                    <div class="empty-state-icon"><i class="far fa-calendar-times"></i></div>
                    <h3>No upcoming events scheduled</h3>
                    <p>Log in as administrator to create new events.</p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="events-grid">
            <?php foreach ($featuredEvents as $ev): ?>
                <div class="event-card">
                    <div class="event-card-header">
                        <div class="event-card-date">
                            <i class="far fa-calendar-alt"></i> <?= format_date($ev['event_date']) ?>
                        </div>
                        <h3 class="event-card-title"><?= sanitize($ev['event_name']) ?></h3>
                        <div style="font-size: 13px; opacity: 0.9;">
                            <i class="far fa-clock"></i> <?= format_time($ev['event_time']) ?>
                        </div>
                    </div>
                    <div class="event-card-body">
                        <div>
                            <ul class="event-meta-list">
                                <li class="event-meta-item">
                                    <i class="fas fa-location-dot"></i>
                                    <span><?= sanitize($ev['venue_name'] ?? 'TBD') ?> (<?= sanitize($ev['city'] ?? '-') ?>)</span>
                                </li>
                                <li class="event-meta-item">
                                    <i class="fas fa-user-tie"></i>
                                    <span><?= sanitize($ev['organizer_name'] ?? 'College Committee') ?></span>
                                </li>
                                <?php if (!empty($ev['capacity'])): ?>
                                    <li class="event-meta-item">
                                        <i class="fas fa-users"></i>
                                        <span>Capacity: <?= number_format($ev['capacity']) ?> seats</span>
                                    </li>
                                <?php endif; ?>
                            </ul>

                            <?php if (!empty($ev['event_description'])): ?>
                                <p style="font-size: 13px; color: var(--text-muted); line-height: 1.5; margin-bottom: 16px;">
                                    <?= sanitize(substr($ev['event_description'], 0, 110)) ?>...
                                </p>
                            <?php endif; ?>
                        </div>

                        <div class="event-card-footer">
                            <?= status_badge($ev['status']) ?>
                            <a href="<?= url('event-details.php?id=' . $ev['event_id']) ?>" class="btn btn-primary btn-sm">
                                View Details <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- About System Features Section -->
<div style="background: #ffffff; border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); padding: 60px 0;">
    <div class="container" style="padding-top: 0; padding-bottom: 0;">
        <div style="text-align: center; max-width: 700px; margin: 0 auto 50px;">
            <h2 style="font-size: 28px; font-weight: 800; margin-bottom: 10px;">End-to-End Event Coordination</h2>
            <p style="color: var(--text-muted); font-size: 15px;">Designed with a strict relational database schema to model every essential element of large-scale event organization.</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 30px;">
            <div style="padding: 24px; border-radius: var(--radius-md); background: #f8fafc; border: 1px solid var(--border-color);">
                <div style="width: 48px; height: 48px; border-radius: 10px; background: #e0e7ff; color: #4f46e5; display: flex; align-items: center; justify-content: center; font-size: 20px; margin-bottom: 16px;">
                    <i class="fas fa-people-roof"></i>
                </div>
                <h4 style="font-size: 18px; font-weight: 700; margin-bottom: 8px;">Organizers & Venues</h4>
                <p style="font-size: 14px; color: var(--text-muted); line-height: 1.6;">Maintain detailed department profiles and track auditorium capacities, acoustics, and ground layouts.</p>
            </div>

            <div style="padding: 24px; border-radius: var(--radius-md); background: #f8fafc; border: 1px solid var(--border-color);">
                <div style="width: 48px; height: 48px; border-radius: 10px; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 20px; margin-bottom: 16px;">
                    <i class="fas fa-microphone"></i>
                </div>
                <h4 style="font-size: 18px; font-weight: 700; margin-bottom: 8px;">Artists & Keynotes</h4>
                <p style="font-size: 14px; color: var(--text-muted); line-height: 1.6;">Model complex many-to-many performance relationships, role assignments, and multi-artist schedules.</p>
            </div>

            <div style="padding: 24px; border-radius: var(--radius-md); background: #f8fafc; border: 1px solid var(--border-color);">
                <div style="width: 48px; height: 48px; border-radius: 10px; background: #d1fae5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 20px; margin-bottom: 16px;">
                    <i class="fas fa-hand-holding-dollar"></i>
                </div>
                <h4 style="font-size: 18px; font-weight: 700; margin-bottom: 8px;">Corporate Sponsorships</h4>
                <p style="font-size: 14px; color: var(--text-muted); line-height: 1.6;">Record sponsorship funding agreements, track brand tiers (Title, Platinum, Gold), and partner obligations.</p>
            </div>

            <div style="padding: 24px; border-radius: var(--radius-md); background: #f8fafc; border: 1px solid var(--border-color);">
                <div style="width: 48px; height: 48px; border-radius: 10px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 20px; margin-bottom: 16px;">
                    <i class="fas fa-sliders"></i>
                </div>
                <h4 style="font-size: 18px; font-weight: 700; margin-bottom: 8px;">Logistics & Crew</h4>
                <p style="font-size: 14px; color: var(--text-muted); line-height: 1.6;">Allocate on-ground staff, technical supervisors, 4K projectors, mixing consoles, and sound lights per event.</p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
