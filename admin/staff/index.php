<?php
/**
 * Staff Allocations Listing & Search
 * Event Management System
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

$pageTitle = 'Manage Staff Allocations';
$search = trim($_GET['search'] ?? '');
$eventFilter = filter_input(INPUT_GET, 'event_id', FILTER_VALIDATE_INT);

try {
    $events = $pdo->query("SELECT event_id, event_name FROM event ORDER BY event_name ASC")->fetchAll();

    $sql = "
        SELECT st.*, e.event_name, e.event_date
        FROM staff st
        LEFT JOIN event e ON st.event_id = e.event_id
        WHERE 1=1
    ";
    $params = [];

    if (!empty($search)) {
        $sql .= " AND (st.staff_name LIKE :search OR st.role LIKE :search OR st.phone LIKE :search OR e.event_name LIKE :search)";
        $params[':search'] = "%{$search}%";
    }

    if ($eventFilter) {
        $sql .= " AND st.event_id = :event_id";
        $params[':event_id'] = $eventFilter;
    }

    $sql .= " ORDER BY st.staff_id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $staffMembers = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Database error: " . htmlspecialchars($e->getMessage()));
}

require_once __DIR__ . '/../../includes/admin_header.php';
?>

<div class="toolbar-container">
    <form action="<?= url('admin/staff/index.php') ?>" method="GET" class="search-form" style="max-width: 650px;">
        <input type="text" name="search" class="form-control" placeholder="Search staff name, role, phone, or event..." value="<?= sanitize($search) ?>">
        
        <select name="event_id" class="form-control" style="max-width: 180px;">
            <option value="">All Events</option>
            <?php foreach ($events as $ev): ?>
                <option value="<?= $ev['event_id'] ?>" <?= $eventFilter === $ev['event_id'] ? 'selected' : '' ?>>
                    <?= sanitize($ev['event_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="btn btn-secondary">
            <i class="fas fa-search"></i> Search
        </button>

        <?php if (!empty($search) || $eventFilter): ?>
            <a href="<?= url('admin/staff/index.php') ?>" class="btn btn-secondary" title="Clear Filters">
                <i class="fas fa-times"></i>
            </a>
        <?php endif; ?>
    </form>

    <div>
        <a href="<?= url('admin/staff/add.php' . ($eventFilter ? '?event_id=' . $eventFilter : '')) ?>" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Allocate New Staff
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-users-gear"></i> Event Staff Allocations (<?= count($staffMembers) ?>)
        </div>
        <div style="font-size: 13px; color: var(--text-muted);">
            Manage on-ground crew, sound engineers, volunteers, and security leads
        </div>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Staff Name</th>
                        <th>Designated Role</th>
                        <th>Phone Number</th>
                        <th>Assigned Event</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($staffMembers)): ?>
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i class="fas fa-user-xmark"></i></div>
                                    <h3>No staff allocated</h3>
                                    <p>Allocate technical supervisors, crowd managers, and backstage crew.</p>
                                    <a href="<?= url('admin/staff/add.php') ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus"></i> Allocate Staff
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($staffMembers as $st): ?>
                            <tr>
                                <td><span class="badge badge-secondary">#<?= $st['staff_id'] ?></span></td>
                                <td><strong><?= sanitize($st['staff_name']) ?></strong></td>
                                <td><span class="badge badge-info"><?= sanitize($st['role']) ?></span></td>
                                <td><?= sanitize($st['phone'] ?: '-') ?></td>
                                <td>
                                    <?php if (!empty($st['event_name'])): ?>
                                        <a href="<?= url('admin/events/view.php?id=' . $st['event_id']) ?>">
                                            <i class="fas fa-calendar-day" style="color: var(--primary);"></i> <?= sanitize($st['event_name']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted);">Unassigned</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="actions-cell">
                                        <a href="<?= url('admin/staff/edit.php?id=' . $st['staff_id']) ?>" class="btn btn-secondary btn-icon" title="Edit Staff Record">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="<?= url('admin/staff/delete.php') ?>" method="POST" style="display: inline;">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="staff_id" value="<?= $st['staff_id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-icon confirm-delete" data-item="<?= sanitize($st['staff_name']) ?>" title="Remove Staff">
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
