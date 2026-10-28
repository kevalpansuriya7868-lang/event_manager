<?php
/**
 * Venues Listing & Search
 * Event Management System
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

$pageTitle = 'Manage Venues';
$search = trim($_GET['search'] ?? '');

try {
    if (!empty($search)) {
        $stmt = $pdo->prepare("
            SELECT v.*, COUNT(e.event_id) as event_count
            FROM venue v
            LEFT JOIN event e ON v.venue_id = e.venue_id
            WHERE v.venue_name LIKE :search 
               OR v.city LIKE :search 
               OR v.address LIKE :search
            GROUP BY v.venue_id
            ORDER BY v.venue_id DESC
        ");
        $stmt->execute([':search' => "%{$search}%"]);
    } else {
        $stmt = $pdo->query("
            SELECT v.*, COUNT(e.event_id) as event_count
            FROM venue v
            LEFT JOIN event e ON v.venue_id = e.venue_id
            GROUP BY v.venue_id
            ORDER BY v.venue_id DESC
        ");
    }
    $venues = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database error: " . htmlspecialchars($e->getMessage()));
}

require_once __DIR__ . '/../../includes/admin_header.php';
?>

<div class="toolbar-container">
    <form action="<?= url('admin/venues/index.php') ?>" method="GET" class="search-form">
        <input type="text" name="search" class="form-control" placeholder="Search by venue name, city, or address..." value="<?= sanitize($search) ?>">
        <button type="submit" class="btn btn-secondary">
            <i class="fas fa-search"></i> Search
        </button>
        <?php if (!empty($search)): ?>
            <a href="<?= url('admin/venues/index.php') ?>" class="btn btn-secondary" title="Clear Search">
                <i class="fas fa-times"></i>
            </a>
        <?php endif; ?>
    </form>

    <div>
        <a href="<?= url('admin/venues/add.php') ?>" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Add Venue
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-landmark"></i> Venues Directory (<?= count($venues) ?>)
        </div>
        <div style="font-size: 13px; color: var(--text-muted);">
            Manage event halls, auditoriums, and open grounds
        </div>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Venue Name</th>
                        <th>Address</th>
                        <th>City</th>
                        <th>Capacity</th>
                        <th>Hosted Events</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($venues)): ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i class="fas fa-building-circle-xmark"></i></div>
                                    <h3>No venues found</h3>
                                    <p>Try refining your search keyword or add a new venue location.</p>
                                    <a href="<?= url('admin/venues/add.php') ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus"></i> Add New Venue
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($venues as $v): ?>
                            <tr>
                                <td><span class="badge badge-secondary">#<?= $v['venue_id'] ?></span></td>
                                <td><strong><?= sanitize($v['venue_name']) ?></strong></td>
                                <td><?= sanitize($v['address']) ?></td>
                                <td><span class="badge badge-light" style="font-size: 13px; border: 1px solid #cbd5e1;"><?= sanitize($v['city']) ?></span></td>
                                <td>
                                    <strong><?= number_format($v['capacity']) ?></strong> <span style="font-size: 12px; color: var(--text-muted);">seats</span>
                                </td>
                                <td>
                                    <span class="badge badge-info"><?= $v['event_count'] ?> Events</span>
                                </td>
                                <td>
                                    <div class="actions-cell">
                                        <a href="<?= url('admin/venues/edit.php?id=' . $v['venue_id']) ?>" class="btn btn-secondary btn-icon" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="<?= url('admin/venues/delete.php') ?>" method="POST" style="display: inline;">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="venue_id" value="<?= $v['venue_id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-icon confirm-delete" data-item="<?= sanitize($v['venue_name']) ?>" title="Delete">
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
