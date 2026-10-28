<?php
/**
 * Detailed Event Overview & Allocation Manager (Admin)
 * Event Management System
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    set_flash('danger', 'Invalid event ID.');
    header('Location: ' . url('admin/events/index.php'));
    exit();
}

try {
    // 1. Fetch Event Details with Organizer and Venue
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
        set_flash('danger', 'Event record not found.');
        header('Location: ' . url('admin/events/index.php'));
        exit();
    }

    $pageTitle = 'Event: ' . $event['event_name'];

    // 2. Fetch Assigned Artists
    $artistsStmt = $pdo->prepare("
        SELECT ea.role, a.artist_id, a.artist_name, a.genre, a.contact_email, a.country
        FROM event_artist ea
        JOIN artist a ON ea.artist_id = a.artist_id
        WHERE ea.event_id = ?
        ORDER BY a.artist_name ASC
    ");
    $artistsStmt->execute([$id]);
    $assignedArtists = $artistsStmt->fetchAll();

    // 3. Fetch Assigned Sponsors
    $sponsorsStmt = $pdo->prepare("
        SELECT es.sponsorship_amount, es.sponsorship_type, s.sponsor_id, s.sponsor_name, s.contact_person, s.email, s.phone
        FROM event_sponsor es
        JOIN sponsor s ON es.sponsor_id = s.sponsor_id
        WHERE es.event_id = ?
        ORDER BY es.sponsorship_amount DESC
    ");
    $sponsorsStmt->execute([$id]);
    $assignedSponsors = $sponsorsStmt->fetchAll();

    // 4. Fetch Staff Allocated
    $staffStmt = $pdo->prepare("SELECT * FROM staff WHERE event_id = ? ORDER BY staff_name ASC");
    $staffStmt->execute([$id]);
    $allocatedStaff = $staffStmt->fetchAll();

    // 5. Fetch Equipment Allocated
    $equipmentStmt = $pdo->prepare("SELECT * FROM equipment WHERE event_id = ? ORDER BY equipment_name ASC");
    $equipmentStmt->execute([$id]);
    $allocatedEquipment = $equipmentStmt->fetchAll();

} catch (PDOException $e) {
    die("Database error: " . htmlspecialchars($e->getMessage()));
}

require_once __DIR__ . '/../../includes/admin_header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
    <div>
        <a href="<?= url('admin/events/index.php') ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Events
        </a>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="<?= url('event-details.php?id=' . $event['event_id']) ?>" target="_blank" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-up-right-from-square"></i> View Public Page
        </a>
        <a href="<?= url('admin/events/edit.php?id=' . $event['event_id']) ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-edit"></i> Edit Event
        </a>
    </div>
</div>

<!-- Main Event Summary Banner -->
<div class="card" style="margin-bottom: 28px;">
    <div class="card-body">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
            <div>
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                    <h1 style="font-size: 26px; font-weight: 800; color: var(--text-main); margin: 0;">
                        <?= sanitize($event['event_name']) ?>
                    </h1>
                    <?= status_badge($event['status']) ?>
                </div>
                <div style="display: flex; gap: 20px; font-size: 14px; color: var(--text-muted); flex-wrap: wrap;">
                    <span><i class="far fa-calendar-alt" style="color: var(--primary);"></i> <strong><?= format_date($event['event_date']) ?></strong></span>
                    <span><i class="far fa-clock" style="color: var(--primary);"></i> <strong><?= format_time($event['event_time']) ?></strong></span>
                    <span><i class="fas fa-location-dot" style="color: var(--primary);"></i> <?= sanitize($event['venue_name'] ?? 'N/A') ?>, <?= sanitize($event['venue_city'] ?? '') ?></span>
                </div>
            </div>
            <div style="text-align: right;">
                <span class="badge badge-secondary" style="font-size: 13px;">Event ID: #<?= $event['event_id'] ?></span>
            </div>
        </div>

        <?php if (!empty($event['event_description'])): ?>
            <div style="margin-top: 20px; padding: 16px; background: #f8fafc; border-radius: var(--radius-sm); border-left: 4px solid var(--primary); font-size: 14px; line-height: 1.6; color: #334155;">
                <?= nl2br(sanitize($event['event_description'])) ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Organizer & Venue Details Row -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 28px;">
    <!-- Organizer Details Card -->
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header">
            <div class="card-title">
                <i class="fas fa-user-tie"></i> Event Organizer
            </div>
            <?php if (!empty($event['organizer_id'])): ?>
                <a href="<?= url('admin/organizers/edit.php?id=' . $event['organizer_id']) ?>" class="btn btn-secondary btn-sm">Edit</a>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <?php if (!empty($event['organizer_name'])): ?>
                <h3 style="font-size: 17px; font-weight: 700; margin-bottom: 4px;"><?= sanitize($event['organizer_name']) ?></h3>
                <div style="font-size: 13px; color: var(--text-muted); margin-bottom: 14px;"><?= sanitize($event['organization'] ?: 'Independent Host') ?></div>
                <div style="font-size: 14px; display: flex; flex-direction: column; gap: 6px;">
                    <div><i class="far fa-envelope" style="width: 20px; color: var(--primary);"></i> <a href="mailto:<?= sanitize($event['organizer_email']) ?>"><?= sanitize($event['organizer_email']) ?></a></div>
                    <div><i class="fas fa-phone" style="width: 20px; color: var(--primary);"></i> <?= sanitize($event['organizer_phone']) ?></div>
                </div>
            <?php else: ?>
                <p style="color: var(--text-muted);">No organizer assigned.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Venue Details Card -->
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header">
            <div class="card-title">
                <i class="fas fa-landmark"></i> Venue Information
            </div>
            <?php if (!empty($event['venue_id'])): ?>
                <a href="<?= url('admin/venues/edit.php?id=' . $event['venue_id']) ?>" class="btn btn-secondary btn-sm">Edit</a>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <?php if (!empty($event['venue_name'])): ?>
                <h3 style="font-size: 17px; font-weight: 700; margin-bottom: 4px;"><?= sanitize($event['venue_name']) ?></h3>
                <div style="font-size: 13px; color: var(--text-muted); margin-bottom: 14px;"><?= sanitize($event['venue_address']) ?>, <?= sanitize($event['venue_city']) ?></div>
                <div style="font-size: 14px;">
                    <i class="fas fa-users" style="width: 20px; color: var(--primary);"></i> 
                    Total Seating Capacity: <strong><?= number_format($event['venue_capacity']) ?></strong> attendees
                </div>
            <?php else: ?>
                <p style="color: var(--text-muted);">No venue assigned.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Section 1: Assigned Artists & Performers -->
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-microphone-lines"></i> Assigned Artists & Speakers (<?= count($assignedArtists) ?>)
        </div>
        <a href="<?= url('admin/assignments/event_artists.php?event_id=' . $event['event_id']) ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Assign Artist
        </a>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Artist / Speaker Name</th>
                        <th>Genre / Category</th>
                        <th>Country</th>
                        <th>Performance Role</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($assignedArtists)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 24px; color: var(--text-muted);">
                                No artists assigned to this event yet. 
                                <a href="<?= url('admin/assignments/event_artists.php?event_id=' . $event['event_id']) ?>">Assign one now</a>.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($assignedArtists as $art): ?>
                            <tr>
                                <td><strong><?= sanitize($art['artist_name']) ?></strong></td>
                                <td><span class="badge badge-primary"><?= sanitize($art['genre'] ?: 'General') ?></span></td>
                                <td><?= sanitize($art['country'] ?: '-') ?></td>
                                <td><span class="badge badge-info"><?= sanitize($art['role'] ?: 'Performer') ?></span></td>
                                <td>
                                    <form action="<?= url('admin/assignments/event_artists.php') ?>" method="POST" style="display: inline;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="event_id" value="<?= $event['event_id'] ?>">
                                        <input type="hidden" name="artist_id" value="<?= $art['artist_id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-icon confirm-delete" data-item="<?= sanitize($art['artist_name']) ?>" title="Remove Assignment">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Section 2: Assigned Sponsors & Partners -->
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-handshake"></i> Assigned Sponsors & Brand Partners (<?= count($assignedSponsors) ?>)
        </div>
        <a href="<?= url('admin/assignments/event_sponsors.php?event_id=' . $event['event_id']) ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Assign Sponsor
        </a>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Sponsor / Brand</th>
                        <th>Contact Person</th>
                        <th>Sponsorship Type</th>
                        <th>Amount Sponsored</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($assignedSponsors)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 24px; color: var(--text-muted);">
                                No sponsors assigned to this event yet. 
                                <a href="<?= url('admin/assignments/event_sponsors.php?event_id=' . $event['event_id']) ?>">Add sponsorship</a>.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($assignedSponsors as $sp): ?>
                            <tr>
                                <td><strong><?= sanitize($sp['sponsor_name']) ?></strong></td>
                                <td><?= sanitize($sp['contact_person'] ?: '-') ?></td>
                                <td><span class="badge badge-warning"><?= sanitize($sp['sponsorship_type'] ?: 'Sponsor') ?></span></td>
                                <td><strong><?= format_currency($sp['sponsorship_amount']) ?></strong></td>
                                <td>
                                    <form action="<?= url('admin/assignments/event_sponsors.php') ?>" method="POST" style="display: inline;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="event_id" value="<?= $event['event_id'] ?>">
                                        <input type="hidden" name="sponsor_id" value="<?= $sp['sponsor_id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-icon confirm-delete" data-item="<?= sanitize($sp['sponsor_name']) ?>" title="Remove Sponsorship">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Section 3 & 4: Staff & Equipment Allocation Row -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
    <!-- Staff Card -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fas fa-users-gear"></i> Staff Members (<?= count($allocatedStaff) ?>)
            </div>
            <a href="<?= url('admin/staff/add.php?event_id=' . $event['event_id']) ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Allocate Staff
            </a>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Staff Name</th>
                            <th>Designated Role</th>
                            <th>Phone</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($allocatedStaff)): ?>
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 20px; color: var(--text-muted);">
                                    No staff allocated yet.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($allocatedStaff as $st): ?>
                                <tr>
                                    <td><strong><?= sanitize($st['staff_name']) ?></strong></td>
                                    <td><span class="badge badge-info"><?= sanitize($st['role']) ?></span></td>
                                    <td><?= sanitize($st['phone'] ?: '-') ?></td>
                                    <td>
                                        <form action="<?= url('admin/staff/delete.php') ?>" method="POST" style="display: inline;">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="staff_id" value="<?= $st['staff_id'] ?>">
                                            <input type="hidden" name="redirect_event_id" value="<?= $event['event_id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-icon confirm-delete" data-item="<?= sanitize($st['staff_name']) ?>" title="Delete Staff">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Equipment Card -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fas fa-boxes-stacked"></i> Allocated Equipment (<?= count($allocatedEquipment) ?>)
            </div>
            <a href="<?= url('admin/equipment/add.php?event_id=' . $event['event_id']) ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add Equipment
            </a>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Equipment Name</th>
                            <th>Qty</th>
                            <th>Condition</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($allocatedEquipment)): ?>
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 20px; color: var(--text-muted);">
                                    No equipment assigned yet.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($allocatedEquipment as $eq): ?>
                                <tr>
                                    <td><strong><?= sanitize($eq['equipment_name']) ?></strong></td>
                                    <td><?= $eq['quantity'] ?> units</td>
                                    <td><span class="badge badge-success"><?= sanitize($eq['condition_status'] ?: 'Good') ?></span></td>
                                    <td>
                                        <form action="<?= url('admin/equipment/delete.php') ?>" method="POST" style="display: inline;">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="equipment_id" value="<?= $eq['equipment_id'] ?>">
                                            <input type="hidden" name="redirect_event_id" value="<?= $event['event_id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-icon confirm-delete" data-item="<?= sanitize($eq['equipment_name']) ?>" title="Delete Equipment">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
