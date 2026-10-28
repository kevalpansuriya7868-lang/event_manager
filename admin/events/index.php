<?php
/**
 * Events Management & Listing
 * Event Management System
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

$pageTitle = 'Manage Events';

$search = trim($_GET['search'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');

try {
    $sql = "
        SELECT e.*, 
               o.organizer_name, 
               v.venue_name, 
               v.city,
               (SELECT COUNT(*) FROM event_artist ea WHERE ea.event_id = e.event_id) as artist_count,
               (SELECT COUNT(*) FROM event_sponsor es WHERE es.event_id = e.event_id) as sponsor_count,
               (SELECT COUNT(*) FROM staff st WHERE st.event_id = e.event_id) as staff_count,
               (SELECT COUNT(*) FROM equipment eq WHERE eq.event_id = e.event_id) as equipment_count
        FROM event e
        LEFT JOIN organizer o ON e.organizer_id = o.organizer_id
        LEFT JOIN venue v ON e.venue_id = v.venue_id
        WHERE 1=1
    ";

    $params = [];

    if (!empty($search)) {
        $sql .= " AND (e.event_name LIKE :search OR o.organizer_name LIKE :search OR v.venue_name LIKE :search OR v.city LIKE :search)";
        $params[':search'] = "%{$search}%";
    }

    if (!empty($statusFilter)) {
        $sql .= " AND e.status = :status";
        $params[':status'] = $statusFilter;
    }

    $sql .= " ORDER BY e.event_date DESC, e.event_time DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $events = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Database query error: " . htmlspecialchars($e->getMessage()));
}

require_once __DIR__ . '/../../includes/admin_header.php';
?>

<div class="toolbar-container">
    <form action="<?= url('admin/events/index.php') ?>" method="GET" class="search-form" style="max-width: 650px;">
        <input type="text" name="search" class="form-control" placeholder="Search by event title, organizer, or venue..." value="<?= sanitize($search) ?>">
        
        <select name="status" class="form-control" style="max-width: 160px;">
            <option value="">All Statuses</option>
            <option value="Upcoming" <?= $statusFilter === 'Upcoming' ? 'selected' : '' ?>>Upcoming</option>
            <option value="Ongoing" <?= $statusFilter === 'Ongoing' ? 'selected' : '' ?>>Ongoing</option>
            <option value="Completed" <?= $statusFilter === 'Completed' ? 'selected' : '' ?>>Completed</option>
            <option value="Cancelled" <?= $statusFilter === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
        </select>

        <button type="submit" class="btn btn-secondary">
            <i class="fas fa-filter"></i> Filter
        </button>

        <?php if (!empty($search) || !empty($statusFilter)): ?>
            <a href="<?= url('admin/events/index.php') ?>" class="btn btn-secondary" title="Clear Filters">
                <i class="fas fa-times"></i>
            </a>
        <?php endif; ?>
    </form>

    <div>
        <a href="<?= url('admin/events/add.php') ?>" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Create New Event
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-calendar-star"></i> All Events Directory (<?= count($events) ?>)
        </div>
        <div style="font-size: 13px; color: var(--text-muted);">
            Manage schedules, venues, performers, sponsors, and status
        </div>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Event Name</th>
                        <th>Schedule</th>
                        <th>Venue & City</th>
                        <th>Organizer</th>
                        <th>Status</th>
                        <th>Allocations</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($events)): ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i class="fas fa-calendar-times"></i></div>
                                    <h3>No events found</h3>
                                    <p>Try adjusting your search criteria or create a brand new event.</p>
                                    <a href="<?= url('admin/events/add.php') ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus"></i> Create New Event
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($events as $ev): ?>
                            <tr>
                                <td>
                                    <strong><a href="<?= url('admin/events/view.php?id=' . $ev['event_id']) ?>"><?= sanitize($ev['event_name']) ?></a></strong>
                                    <div style="font-size: 12px; color: var(--text-muted);">ID: #<?= $ev['event_id'] ?></div>
                                </td>
                                <td>
                                    <div><i class="far fa-calendar-alt" style="color: var(--primary);"></i> <?= format_date($ev['event_date']) ?></div>
                                    <div style="font-size: 12px; color: var(--text-muted);"><i class="far fa-clock"></i> <?= format_time($ev['event_time']) ?></div>
                                </td>
                                <td>
                                    <div><?= sanitize($ev['venue_name'] ?? 'Not Assigned') ?></div>
                                    <div style="font-size: 12px; color: var(--text-muted);"><i class="fas fa-location-dot"></i> <?= sanitize($ev['city'] ?? '-') ?></div>
                                </td>
                                <td>
                                    <?= sanitize($ev['organizer_name'] ?? 'Not Assigned') ?>
                                </td>
                                <td>
                                    <?= status_badge($ev['status']) ?>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                                        <span class="badge badge-light" title="Artists" style="border: 1px solid #cbd5e1;">
                                            <i class="fas fa-microphone" style="color: var(--primary);"></i> <?= $ev['artist_count'] ?>
                                        </span>
                                        <span class="badge badge-light" title="Sponsors" style="border: 1px solid #cbd5e1;">
                                            <i class="fas fa-handshake" style="color: #d97706;"></i> <?= $ev['sponsor_count'] ?>
                                        </span>
                                        <span class="badge badge-light" title="Staff" style="border: 1px solid #cbd5e1;">
                                            <i class="fas fa-user-gear" style="color: #059669;"></i> <?= $ev['staff_count'] ?>
                                        </span>
                                        <span class="badge badge-light" title="Equipment" style="border: 1px solid #cbd5e1;">
                                            <i class="fas fa-box" style="color: #0284c7;"></i> <?= $ev['equipment_count'] ?>
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="actions-cell">
                                        <a href="<?= url('admin/events/view.php?id=' . $ev['event_id']) ?>" class="btn btn-secondary btn-icon" title="View Full Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= url('admin/events/edit.php?id=' . $ev['event_id']) ?>" class="btn btn-secondary btn-icon" title="Edit Event">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="<?= url('admin/events/delete.php') ?>" method="POST" style="display: inline;">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="event_id" value="<?= $ev['event_id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-icon confirm-delete" data-item="<?= sanitize($ev['event_name']) ?>" title="Delete Event">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
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

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
