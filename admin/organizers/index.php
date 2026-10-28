<?php
/**
 * Organizers Listing & Search
 * Event Management System
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

$pageTitle = 'Manage Organizers';

$search = trim($_GET['search'] ?? '');

try {
    if (!empty($search)) {
        $stmt = $pdo->prepare("
            SELECT o.*, COUNT(e.event_id) as event_count
            FROM organizer o
            LEFT JOIN event e ON o.organizer_id = e.organizer_id
            WHERE o.organizer_name LIKE :search 
               OR o.email LIKE :search 
               OR o.phone LIKE :search 
               OR o.organization LIKE :search
            GROUP BY o.organizer_id
            ORDER BY o.organizer_id DESC
        ");
        $stmt->execute([':search' => "%{$search}%"]);
    } else {
        $stmt = $pdo->query("
            SELECT o.*, COUNT(e.event_id) as event_count
            FROM organizer o
            LEFT JOIN event e ON o.organizer_id = e.organizer_id
            GROUP BY o.organizer_id
            ORDER BY o.organizer_id DESC
        ");
    }
    $organizers = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database error: " . htmlspecialchars($e->getMessage()));
}

require_once __DIR__ . '/../../includes/admin_header.php';
?>

<div class="toolbar-container">
    <form action="<?= url('admin/organizers/index.php') ?>" method="GET" class="search-form">
        <input type="text" name="search" class="form-control" placeholder="Search by name, email, phone, or organization..." value="<?= sanitize($search) ?>">
        <button type="submit" class="btn btn-secondary">
            <i class="fas fa-search"></i> Search
        </button>
        <?php if (!empty($search)): ?>
            <a href="<?= url('admin/organizers/index.php') ?>" class="btn btn-secondary" title="Clear Search">
                <i class="fas fa-times"></i>
            </a>
        <?php endif; ?>
    </form>

    <div>
        <a href="<?= url('admin/organizers/add.php') ?>" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Add Organizer
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-user-tie"></i> Organizers Directory (<?= count($organizers) ?>)
        </div>
        <div style="font-size: 13px; color: var(--text-muted);">
            Manage event coordinators and departments
        </div>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Organizer Name</th>
                        <th>Organization / Department</th>
                        <th>Contact Email</th>
                        <th>Phone</th>
                        <th>Total Events</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($organizers)): ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i class="fas fa-user-slash"></i></div>
                                    <h3>No organizers found</h3>
                                    <p>Try refining your search keyword or add a new organizer.</p>
                                    <a href="<?= url('admin/organizers/add.php') ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus"></i> Add New Organizer
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($organizers as $org): ?>
                            <tr>
                                <td><span class="badge badge-secondary">#<?= $org['organizer_id'] ?></span></td>
                                <td><strong><?= sanitize($org['organizer_name']) ?></strong></td>
                                <td><?= sanitize($org['organization'] ?: '-') ?></td>
                                <td><a href="mailto:<?= sanitize($org['email']) ?>"><?= sanitize($org['email']) ?></a></td>
                                <td><?= sanitize($org['phone']) ?></td>
                                <td>
                                    <span class="badge badge-info"><?= $org['event_count'] ?> Events</span>
                                </td>
                                <td>
                                    <div class="actions-cell">
                                        <a href="<?= url('admin/organizers/edit.php?id=' . $org['organizer_id']) ?>" class="btn btn-secondary btn-icon" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="<?= url('admin/organizers/delete.php') ?>" method="POST" style="display: inline;">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="organizer_id" value="<?= $org['organizer_id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-icon confirm-delete" data-item="<?= sanitize($org['organizer_name']) ?>" title="Delete">
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
