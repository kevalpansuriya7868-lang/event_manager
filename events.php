<?php
/**
 * Public Events Directory & Search
 * Event Management System
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Explore Events';

$search = trim($_GET['search'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');
$cityFilter = trim($_GET['city'] ?? '');

try {
    // Fetch unique cities for filter dropdown
    $cities = $pdo->query("SELECT DISTINCT city FROM venue WHERE city IS NOT NULL AND city != '' ORDER BY city ASC")->fetchAll(PDO::FETCH_COLUMN);

    $sql = "
        SELECT e.*, o.organizer_name, v.venue_name, v.city, v.capacity,
               (SELECT COUNT(*) FROM event_artist ea WHERE ea.event_id = e.event_id) as artist_count
        FROM event e
        LEFT JOIN organizer o ON e.organizer_id = o.organizer_id
        LEFT JOIN venue v ON e.venue_id = v.venue_id
        WHERE 1=1
    ";
    $params = [];

    if (!empty($search)) {
        $sql .= " AND (e.event_name LIKE :search OR e.event_description LIKE :search OR o.organizer_name LIKE :search)";
        $params[':search'] = "%{$search}%";
    }

    if (!empty($statusFilter)) {
        $sql .= " AND e.status = :status";
        $params[':status'] = $statusFilter;
    }

    if (!empty($cityFilter)) {
        $sql .= " AND v.city = :city";
        $params[':city'] = $cityFilter;
    }

    $sql .= " ORDER BY e.event_date DESC, e.event_time DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $events = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Database error: " . htmlspecialchars($e->getMessage()));
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="section-header" style="margin-bottom: 24px;">
        <div>
            <h1 class="section-title">Discover All Campus & Public Events</h1>
            <p class="section-subtitle">Browse through upcoming summits, hackathons, concerts, and cultural gatherings.</p>
        </div>
        <div style="font-size: 14px; color: var(--text-muted);">
            Showing <strong><?= count($events) ?></strong> events
        </div>
    </div>

    <!-- Search & Filter Controls -->
    <div class="card" style="margin-bottom: 32px; background: #ffffff;">
        <div class="card-body">
            <form action="<?= url('events.php') ?>" method="GET" style="display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 14px; align-items: flex-end;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-size: 13px;">Search Keywords</label>
                    <input type="text" name="search" class="form-control" placeholder="Search event title, description, or host..." value="<?= sanitize($search) ?>">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-size: 13px;">Filter by Status</label>
                    <select name="status" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="Upcoming" <?= $statusFilter === 'Upcoming' ? 'selected' : '' ?>>Upcoming</option>
                        <option value="Ongoing" <?= $statusFilter === 'Ongoing' ? 'selected' : '' ?>>Ongoing</option>
                        <option value="Completed" <?= $statusFilter === 'Completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="Cancelled" <?= $statusFilter === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-size: 13px;">Filter by City</label>
                    <select name="city" class="form-control">
                        <option value="">All Cities</option>
                        <?php foreach ($cities as $city): ?>
                            <option value="<?= sanitize($city) ?>" <?= $cityFilter === $city ? 'selected' : '' ?>>
                                <?= sanitize($city) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="display: flex; gap: 8px;">
                    <button type="submit" class="btn btn-primary" style="height: 42px;">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <?php if (!empty($search) || !empty($statusFilter) || !empty($cityFilter)): ?>
                        <a href="<?= url('events.php') ?>" class="btn btn-secondary" style="height: 42px;" title="Reset Filters">
                            <i class="fas fa-times"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Events Cards Grid -->
    <?php if (empty($events)): ?>
        <div class="card">
            <div class="card-body">
                <div class="empty-state">
                    <div class="empty-state-icon"><i class="fas fa-search-minus"></i></div>
                    <h3>No events matched your search</h3>
                    <p>Try searching for different keywords or clear your active filters.</p>
                    <a href="<?= url('events.php') ?>" class="btn btn-primary btn-sm">View All Events</a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="events-grid">
            <?php foreach ($events as $ev): ?>
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
                                <?php if ($ev['artist_count'] > 0): ?>
                                    <li class="event-meta-item">
                                        <i class="fas fa-microphone"></i>
                                        <span><?= $ev['artist_count'] ?> Featured Artist(s) / Speakers</span>
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
