<?php
/**
 * Event-Sponsor Many-to-Many Assignment & Sponsorship Manager
 * Event Management System
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

$pageTitle = 'Assign Sponsors to Events';
$errors = [];

// Handle POST actions (Add, Update, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('Invalid security token.');
    }

    $action = $_POST['action'] ?? 'add';

    if ($action === 'add') {
        $event_id = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
        $sponsor_id = filter_input(INPUT_POST, 'sponsor_id', FILTER_VALIDATE_INT);
        $amount = trim($_POST['sponsorship_amount'] ?? '');
        $type = trim($_POST['sponsorship_type'] ?? 'Gold Sponsor');

        if (!$event_id) $errors['event_id'] = 'Please select a valid event.';
        if (!$sponsor_id) $errors['sponsor_id'] = 'Please select a valid sponsor company.';

        if ($amount === '' || !is_numeric($amount) || (float)$amount <= 0) {
            $errors['sponsorship_amount'] = 'Sponsorship amount must be greater than 0.';
        }

        if (empty($errors)) {
            try {
                // Check duplicate composite key (event_id, sponsor_id)
                $checkStmt = $pdo->prepare("SELECT 1 FROM event_sponsor WHERE event_id = ? AND sponsor_id = ?");
                $checkStmt->execute([$event_id, $sponsor_id]);

                if ($checkStmt->fetch()) {
                    $errors['general'] = 'Duplicate Entry: This sponsor is already assigned to this event! You can update their amount below.';
                } else {
                    $insertStmt = $pdo->prepare("
                        INSERT INTO event_sponsor (event_id, sponsor_id, sponsorship_amount, sponsorship_type)
                        VALUES (?, ?, ?, ?)
                    ");
                    $insertStmt->execute([$event_id, $sponsor_id, (float)$amount, $type]);

                    set_flash('success', 'Sponsorship successfully assigned!');
                    header('Location: ' . url('admin/assignments/event_sponsors.php?event_id=' . $event_id));
                    exit();
                }
            } catch (PDOException $e) {
                $errors['general'] = 'Database error: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'update') {
        $event_id = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
        $sponsor_id = filter_input(INPUT_POST, 'sponsor_id', FILTER_VALIDATE_INT);
        $amount = trim($_POST['sponsorship_amount'] ?? '');
        $type = trim($_POST['sponsorship_type'] ?? 'Gold Sponsor');

        if ($amount === '' || !is_numeric($amount) || (float)$amount <= 0) {
            set_flash('danger', 'Update failed: Sponsorship amount must be greater than 0.');
        } else {
            try {
                $upStmt = $pdo->prepare("
                    UPDATE event_sponsor 
                    SET sponsorship_amount = ?, sponsorship_type = ?
                    WHERE event_id = ? AND sponsor_id = ?
                ");
                $upStmt->execute([(float)$amount, $type, $event_id, $sponsor_id]);
                set_flash('success', 'Sponsorship agreement updated successfully!');
            } catch (PDOException $e) {
                set_flash('danger', 'Database error: ' . $e->getMessage());
            }
        }
        header('Location: ' . url('admin/assignments/event_sponsors.php' . ($event_id ? '?event_id=' . $event_id : '')));
        exit();
    } elseif ($action === 'delete') {
        $event_id = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
        $sponsor_id = filter_input(INPUT_POST, 'sponsor_id', FILTER_VALIDATE_INT);

        if ($event_id && $sponsor_id) {
            try {
                $delStmt = $pdo->prepare("DELETE FROM event_sponsor WHERE event_id = ? AND sponsor_id = ?");
                $delStmt->execute([$event_id, $sponsor_id]);
                set_flash('success', 'Sponsorship removed successfully.');
            } catch (PDOException $e) {
                set_flash('danger', 'Error removing sponsorship: ' . $e->getMessage());
            }
        }
        header('Location: ' . url('admin/assignments/event_sponsors.php' . ($event_id ? '?event_id=' . $event_id : '')));
        exit();
    }
}

// Fetch lists for dropdowns and filter
try {
    $events = $pdo->query("SELECT event_id, event_name, event_date FROM event ORDER BY event_date DESC")->fetchAll();
    $sponsors = $pdo->query("SELECT sponsor_id, sponsor_name FROM sponsor ORDER BY sponsor_name ASC")->fetchAll();

    $filterEventId = filter_input(INPUT_GET, 'event_id', FILTER_VALIDATE_INT);
    $filterSponsorId = filter_input(INPUT_GET, 'sponsor_id', FILTER_VALIDATE_INT);

    $sql = "
        SELECT es.event_id, es.sponsor_id, es.sponsorship_amount, es.sponsorship_type,
               e.event_name, e.event_date,
               s.sponsor_name, s.contact_person
        FROM event_sponsor es
        JOIN event e ON es.event_id = e.event_id
        JOIN sponsor s ON es.sponsor_id = s.sponsor_id
        WHERE 1=1
    ";
    $params = [];

    if ($filterEventId) {
        $sql .= " AND es.event_id = :event_id";
        $params[':event_id'] = $filterEventId;
    }

    if ($filterSponsorId) {
        $sql .= " AND es.sponsor_id = :sponsor_id";
        $params[':sponsor_id'] = $filterSponsorId;
    }

    $sql .= " ORDER BY es.sponsorship_amount DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $sponsorships = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Database error: " . htmlspecialchars($e->getMessage()));
}

require_once __DIR__ . '/../../includes/admin_header.php';
?>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 28px; align-items: start;">
    <!-- Add Sponsorship Form -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fas fa-handshake"></i> Assign Sponsor to Event
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-danger">
                    <div class="alert-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="alert-content"><?= sanitize($errors['general']) ?></div>
                </div>
            <?php endif; ?>

            <form action="<?= url('admin/assignments/event_sponsors.php') ?>" method="POST" class="needs-validation" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="add">

                <div class="form-group">
                    <label for="event_id" class="form-label">Target Event <span class="required">*</span></label>
                    <select id="event_id" name="event_id" class="form-control <?= isset($errors['event_id']) ? 'is-invalid' : '' ?>" required>
                        <option value="">-- Choose Event --</option>
                        <?php foreach ($events as $ev): ?>
                            <option value="<?= $ev['event_id'] ?>" <?= $filterEventId === $ev['event_id'] ? 'selected' : '' ?>>
                                <?= sanitize($ev['event_name']) ?> (<?= format_date($ev['event_date']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-error"><?= $errors['event_id'] ?? '' ?></div>
                </div>

                <div class="form-group">
                    <label for="sponsor_id" class="form-label">Sponsor Partner <span class="required">*</span></label>
                    <select id="sponsor_id" name="sponsor_id" class="form-control <?= isset($errors['sponsor_id']) ? 'is-invalid' : '' ?>" required>
                        <option value="">-- Choose Sponsor Company --</option>
                        <?php foreach ($sponsors as $sp): ?>
                            <option value="<?= $sp['sponsor_id'] ?>" <?= $filterSponsorId === $sp['sponsor_id'] ? 'selected' : '' ?>>
                                <?= sanitize($sp['sponsor_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-error"><?= $errors['sponsor_id'] ?? '' ?></div>
                </div>

                <div class="form-group">
                    <label for="sponsorship_amount" class="form-label">Sponsorship Amount (₹) <span class="required">*</span></label>
                    <input type="number" step="0.01" min="1" id="sponsorship_amount" name="sponsorship_amount" 
                           class="form-control <?= isset($errors['sponsorship_amount']) ? 'is-invalid' : '' ?>" 
                           placeholder="e.g. 50000" required>
                    <div class="form-error"><?= $errors['sponsorship_amount'] ?? '' ?></div>
                </div>

                <div class="form-group">
                    <label for="sponsorship_type" class="form-label">Sponsorship Tier / Type</label>
                    <select id="sponsorship_type" name="sponsorship_type" class="form-control">
                        <option value="Title Sponsor">Title Sponsor</option>
                        <option value="Platinum Partner">Platinum Partner</option>
                        <option value="Gold Sponsor" selected>Gold Sponsor</option>
                        <option value="Silver Sponsor">Silver Sponsor</option>
                        <option value="Youth Energy Partner">Youth Energy Partner</option>
                        <option value="Beverage Partner">Beverage Partner</option>
                        <option value="Media Partner">Media Partner</option>
                    </select>
                </div>

                <div style="margin-top: 24px;">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-file-contract"></i> Record Sponsorship
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Current Sponsorships Table -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fas fa-circle-dollar-to-slot"></i> Active Event Sponsorships (<?= count($sponsorships) ?>)
            </div>
            <?php if ($filterEventId || $filterSponsorId): ?>
                <a href="<?= url('admin/assignments/event_sponsors.php') ?>" class="btn btn-secondary btn-sm">
                    <i class="fas fa-times"></i> Clear Filter
                </a>
            <?php endif; ?>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Sponsor</th>
                            <th>Amount</th>
                            <th>Tier / Type</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($sponsorships)): ?>
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <div class="empty-state-icon"><i class="fas fa-hand-holding-dollar"></i></div>
                                        <h3>No sponsorships recorded</h3>
                                        <p>Assign sponsor funding and partnerships to events.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($sponsorships as $row): ?>
                                <tr>
                                    <td>
                                        <a href="<?= url('admin/events/view.php?id=' . $row['event_id']) ?>">
                                            <strong><?= sanitize($row['event_name']) ?></strong>
                                        </a>
                                        <div style="font-size: 12px; color: var(--text-muted);"><?= format_date($row['event_date']) ?></div>
                                    </td>
                                    <td>
                                        <strong><?= sanitize($row['sponsor_name']) ?></strong>
                                        <div style="font-size: 12px; color: var(--text-muted);"><?= sanitize($row['contact_person'] ?: '-') ?></div>
                                    </td>
                                    <td>
                                        <strong><?= format_currency($row['sponsorship_amount']) ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-warning"><?= sanitize($row['sponsorship_type']) ?></span>
                                    </td>
                                    <td>
                                        <div class="actions-cell">
                                            <!-- Inline Quick Edit Modal/Form Toggle -->
                                            <button type="button" class="btn btn-secondary btn-icon" 
                                                    onclick="toggleEditSponsor(<?= $row['event_id'] ?>, <?= $row['sponsor_id'] ?>)" 
                                                    title="Edit Tier or Amount">
                                                <i class="fas fa-pencil"></i>
                                            </button>

                                            <!-- Delete Form -->
                                            <form action="<?= url('admin/assignments/event_sponsors.php') ?>" method="POST" style="display: inline;">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="event_id" value="<?= $row['event_id'] ?>">
                                                <input type="hidden" name="sponsor_id" value="<?= $row['sponsor_id'] ?>">
                                                <button type="submit" class="btn btn-danger btn-icon confirm-delete" 
                                                        data-item="<?= sanitize($row['sponsor_name']) ?> sponsorship" 
                                                        title="Remove Sponsorship">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Hidden inline editor row -->
                                <tr id="edit_row_<?= $row['event_id'] ?>_<?= $row['sponsor_id'] ?>" style="display: none; background: #f8fafc;">
                                    <td colspan="5" style="padding: 16px 20px;">
                                        <form action="<?= url('admin/assignments/event_sponsors.php') ?>" method="POST" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="event_id" value="<?= $row['event_id'] ?>">
                                            <input type="hidden" name="sponsor_id" value="<?= $row['sponsor_id'] ?>">

                                            <span style="font-weight: 600; font-size: 13px;">Update Amount:</span>
                                            <input type="number" step="0.01" min="1" name="sponsorship_amount" class="form-control" style="width: 140px;" value="<?= $row['sponsorship_amount'] ?>" required>

                                            <span style="font-weight: 600; font-size: 13px;">Tier:</span>
                                            <input type="text" name="sponsorship_type" class="form-control" style="width: 180px;" value="<?= sanitize($row['sponsorship_type']) ?>" required>

                                            <button type="submit" class="btn btn-primary btn-sm">Save</button>
                                            <button type="button" class="btn btn-secondary btn-sm" onclick="toggleEditSponsor(<?= $row['event_id'] ?>, <?= $row['sponsor_id'] ?>)">Cancel</button>
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

<script>
function toggleEditSponsor(eventId, sponsorId) {
    const row = document.getElementById('edit_row_' + eventId + '_' + sponsorId);
    if (row) {
        row.style.display = (row.style.display === 'none' || row.style.display === '') ? 'table-row' : 'none';
    }
}
</script>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
