<?php
/**
 * Equipment Allocation Listing & Search
 * Event Management System
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

$pageTitle = 'Manage Equipment Allocations';
$search = trim($_GET['search'] ?? '');
$eventFilter = filter_input(INPUT_GET, 'event_id', FILTER_VALIDATE_INT);

try {
    $events = $pdo->query("SELECT event_id, event_name FROM event ORDER BY event_name ASC")->fetchAll();

    $sql = "
        SELECT eq.*, e.event_name, e.event_date
        FROM equipment eq
        LEFT JOIN event e ON eq.event_id = e.event_id
        WHERE 1=1
    ";
    $params = [];

    if (!empty($search)) {
        $sql .= " AND (eq.equipment_name LIKE :search OR eq.condition_status LIKE :search OR e.event_name LIKE :search)";
        $params[':search'] = "%{$search}%";
    }

    if ($eventFilter) {
        $sql .= " AND eq.event_id = :event_id";
        $params[':event_id'] = $eventFilter;
    }

    $sql .= " ORDER BY eq.equipment_id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $equipmentList = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Database error: " . htmlspecialchars($e->getMessage()));
}

require_once __DIR__ . '/../../includes/admin_header.php';
?>

<div class="toolbar-container">
    <form action="<?= url('admin/equipment/index.php') ?>" method="GET" class="search-form" style="max-width: 650px;">
        <input type="text" name="search" class="form-control" placeholder="Search equipment name, condition, or event..." value="<?= sanitize($search) ?>">
        
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
            <a href="<?= url('admin/equipment/index.php') ?>" class="btn btn-secondary" title="Clear Filters">
                <i class="fas fa-times"></i>
            </a>
        <?php endif; ?>
    </form>

    <div>
        <a href="<?= url('admin/equipment/add.php' . ($eventFilter ? '?event_id=' . $eventFilter : '')) ?>" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Allocate Equipment
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-boxes-stacked"></i> Equipment Inventory & Allocations (<?= count($equipmentList) ?>)
        </div>
        <div style="font-size: 13px; color: var(--text-muted);">
            Manage audio systems, visual projectors, stage lights, and microphones
        </div>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Equipment Item</th>
                        <th>Quantity</th>
                        <th>Condition Status</th>
                        <th>Allocated Event</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($equipmentList)): ?>
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i class="fas fa-box-open"></i></div>
                                    <h3>No equipment items allocated</h3>
                                    <p>Allocate projectors, audio consoles, and stage equipment to scheduled events.</p>
                                    <a href="<?= url('admin/equipment/add.php') ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus"></i> Allocate Equipment
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($equipmentList as $eq): ?>
                            <tr>
                                <td><span class="badge badge-secondary">#<?= $eq['equipment_id'] ?></span></td>
                                <td><strong><?= sanitize($eq['equipment_name']) ?></strong></td>
                                <td><strong><?= (int)$eq['quantity'] ?></strong> <span style="font-size: 12px; color: var(--text-muted);">units</span></td>
                                <td>
                                    <?php
                                    $cond = $eq['condition_status'] ?? 'Good';
                                    $badgeClass = 'badge-success';
                                    if ($cond === 'Fair') $badgeClass = 'badge-warning';
                                    if ($cond === 'Needs Maintenance') $badgeClass = 'badge-danger';
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= sanitize($cond) ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($eq['event_name'])): ?>
                                        <a href="<?= url('admin/events/view.php?id=' . $eq['event_id']) ?>">
                                            <i class="fas fa-calendar-day" style="color: var(--primary);"></i> <?= sanitize($eq['event_name']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted);">Unassigned</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="actions-cell">
                                        <a href="<?= url('admin/equipment/edit.php?id=' . $eq['equipment_id']) ?>" class="btn btn-secondary btn-icon" title="Edit Equipment">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="<?= url('admin/equipment/delete.php') ?>" method="POST" style="display: inline;">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="equipment_id" value="<?= $eq['equipment_id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-icon confirm-delete" data-item="<?= sanitize($eq['equipment_name']) ?>" title="Delete Equipment">
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
