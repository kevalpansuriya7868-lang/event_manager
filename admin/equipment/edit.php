<?php
/**
 * Edit Equipment Allocation
 * Event Management System
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

$pageTitle = 'Edit Equipment Allocation';
$errors = [];

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    set_flash('danger', 'Invalid equipment ID.');
    header('Location: ' . url('admin/equipment/index.php'));
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT * FROM equipment WHERE equipment_id = ?");
    $stmt->execute([$id]);
    $equipment = $stmt->fetch();

    if (!$equipment) {
        set_flash('danger', 'Equipment record not found.');
        header('Location: ' . url('admin/equipment/index.php'));
        exit();
    }

    $events = $pdo->query("SELECT event_id, event_name FROM event ORDER BY event_date ASC")->fetchAll();
} catch (PDOException $e) {
    die("Database error: " . htmlspecialchars($e->getMessage()));
}

$equipment_name = $equipment['equipment_name'];
$quantity = $equipment['quantity'];
$condition_status = $equipment['condition_status'];
$event_id = $equipment['event_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('Invalid security token.');
    }

    $equipment_name = trim($_POST['equipment_name'] ?? '');
    $quantity = trim($_POST['quantity'] ?? '');
    $condition_status = trim($_POST['condition_status'] ?? 'Good');
    $event_id = trim($_POST['event_id'] ?? '');

    if (empty($equipment_name)) {
        $errors['equipment_name'] = 'Equipment item name is required.';
    }

    if ($quantity === '' || !filter_var($quantity, FILTER_VALIDATE_INT) || (int)$quantity <= 0) {
        $errors['quantity'] = 'Quantity must be a positive whole number greater than 0.';
    }

    if (empty($event_id) || !filter_var($event_id, FILTER_VALIDATE_INT)) {
        $errors['event_id'] = 'Please select a valid event.';
    }

    if (empty($errors)) {
        try {
            $updateStmt = $pdo->prepare("
                UPDATE equipment 
                SET equipment_name = ?, quantity = ?, condition_status = ?, event_id = ?
                WHERE equipment_id = ?
            ");
            $updateStmt->execute([$equipment_name, (int)$quantity, $condition_status, (int)$event_id, $id]);

            set_flash('success', "Equipment '{$equipment_name}' updated successfully!");
            header('Location: ' . url('admin/equipment/index.php'));
            exit();
        } catch (PDOException $e) {
            $errors['general'] = 'Failed to update equipment record: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../../includes/admin_header.php';
?>

<div style="max-width: 750px; margin: 0 auto;">
    <div style="margin-bottom: 20px;">
        <a href="<?= url('admin/equipment/index.php') ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Equipment List
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fas fa-edit"></i> Edit Equipment #<?= $equipment['equipment_id'] ?>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-danger">
                    <div class="alert-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="alert-content"><?= sanitize($errors['general']) ?></div>
                </div>
            <?php endif; ?>

            <form action="<?= url('admin/equipment/edit.php?id=' . $id) ?>" method="POST" class="needs-validation" novalidate>
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="event_id" class="form-label">Allocated Event <span class="required">*</span></label>
                    <select id="event_id" name="event_id" class="form-control <?= isset($errors['event_id']) ? 'is-invalid' : '' ?>" required>
                        <?php foreach ($events as $ev): ?>
                            <option value="<?= $ev['event_id'] ?>" <?= (string)$event_id === (string)$ev['event_id'] ? 'selected' : '' ?>>
                                <?= sanitize($ev['event_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-error"><?= $errors['event_id'] ?? '' ?></div>
                </div>

                <div class="form-group">
                    <label for="equipment_name" class="form-label">Equipment Name / Description <span class="required">*</span></label>
                    <input type="text" id="equipment_name" name="equipment_name" 
                           class="form-control <?= isset($errors['equipment_name']) ? 'is-invalid' : '' ?>" 
                           value="<?= sanitize($equipment_name) ?>" required>
                    <div class="form-error"><?= $errors['equipment_name'] ?? '' ?></div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="quantity" class="form-label">Quantity <span class="required">*</span></label>
                        <input type="number" id="quantity" name="quantity" min="1" 
                               class="form-control <?= isset($errors['quantity']) ? 'is-invalid' : '' ?>" 
                               value="<?= sanitize($quantity) ?>" required>
                        <div class="form-error"><?= $errors['quantity'] ?? '' ?></div>
                    </div>

                    <div class="form-group">
                        <label for="condition_status" class="form-label">Condition Status</label>
                        <select id="condition_status" name="condition_status" class="form-control">
                            <option value="Excellent" <?= $condition_status === 'Excellent' ? 'selected' : '' ?>>Excellent</option>
                            <option value="Good" <?= $condition_status === 'Good' ? 'selected' : '' ?>>Good</option>
                            <option value="Fair" <?= $condition_status === 'Fair' ? 'selected' : '' ?>>Fair</option>
                            <option value="Needs Maintenance" <?= $condition_status === 'Needs Maintenance' ? 'selected' : '' ?>>Needs Maintenance</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; gap: 12px; margin-top: 28px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Equipment
                    </button>
                    <a href="<?= url('admin/equipment/index.php') ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
