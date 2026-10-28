<?php
/**
 * Allocate New Staff Member
 * Event Management System
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

$pageTitle = 'Allocate Staff Member';
$errors = [];

try {
    $events = $pdo->query("SELECT event_id, event_name FROM event ORDER BY event_date ASC")->fetchAll();
} catch (PDOException $e) {
    die("Database error: " . htmlspecialchars($e->getMessage()));
}

$defaultEventId = filter_input(INPUT_GET, 'event_id', FILTER_VALIDATE_INT) ?? '';

$staff_name = '';
$role = '';
$phone = '';
$event_id = $defaultEventId;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('Invalid security token.');
    }

    $staff_name = trim($_POST['staff_name'] ?? '');
    $role = trim($_POST['role'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $event_id = trim($_POST['event_id'] ?? '');

    if (empty($staff_name)) {
        $errors['staff_name'] = 'Staff member name is required.';
    }

    if (empty($role)) {
        $errors['role'] = 'Staff role or designation is required.';
    }

    if (!empty($phone) && !preg_match('/^[0-9+\s\-()]{10,15}$/', $phone)) {
        $errors['phone'] = 'Please enter a valid phone number.';
    }

    if (empty($event_id) || !filter_var($event_id, FILTER_VALIDATE_INT)) {
        $errors['event_id'] = 'Please select a valid event to assign this staff member.';
    }

    if (empty($errors)) {
        try {
            $insertStmt = $pdo->prepare("
                INSERT INTO staff (staff_name, role, phone, event_id)
                VALUES (?, ?, ?, ?)
            ");
            $insertStmt->execute([$staff_name, $role, $phone ?: null, (int)$event_id]);

            set_flash('success', "Staff '{$staff_name}' successfully allocated!");

            if (!empty($_POST['redirect_event_id'])) {
                header('Location: ' . url('admin/events/view.php?id=' . (int)$_POST['redirect_event_id']));
            } else {
                header('Location: ' . url('admin/staff/index.php'));
            }
            exit();
        } catch (PDOException $e) {
            $errors['general'] = 'Failed to allocate staff: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../../includes/admin_header.php';
?>

<div style="max-width: 750px; margin: 0 auto;">
    <div style="margin-bottom: 20px;">
        <a href="<?= !empty($defaultEventId) ? url('admin/events/view.php?id=' . $defaultEventId) : url('admin/staff/index.php') ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fas fa-user-plus"></i> Allocate Staff to Event
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-danger">
                    <div class="alert-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="alert-content"><?= sanitize($errors['general']) ?></div>
                </div>
            <?php endif; ?>

            <form action="<?= url('admin/staff/add.php') ?>" method="POST" class="needs-validation" novalidate>
                <?= csrf_field() ?>
                <?php if (!empty($defaultEventId)): ?>
                    <input type="hidden" name="redirect_event_id" value="<?= (int)$defaultEventId ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="event_id" class="form-label">Assign to Event <span class="required">*</span></label>
                    <select id="event_id" name="event_id" class="form-control <?= isset($errors['event_id']) ? 'is-invalid' : '' ?>" required>
                        <option value="">-- Choose Target Event --</option>
                        <?php foreach ($events as $ev): ?>
                            <option value="<?= $ev['event_id'] ?>" <?= (string)$event_id === (string)$ev['event_id'] ? 'selected' : '' ?>>
                                <?= sanitize($ev['event_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-error"><?= $errors['event_id'] ?? '' ?></div>
                </div>

                <div class="form-group">
                    <label for="staff_name" class="form-label">Staff Member Full Name <span class="required">*</span></label>
                    <input type="text" id="staff_name" name="staff_name" 
                           class="form-control <?= isset($errors['staff_name']) ? 'is-invalid' : '' ?>" 
                           placeholder="e.g. Aarav Patel" 
                           value="<?= sanitize($staff_name) ?>" required>
                    <div class="form-error"><?= $errors['staff_name'] ?? '' ?></div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="role" class="form-label">Designated Role / Function <span class="required">*</span></label>
                        <input type="text" id="role" name="role" 
                               class="form-control <?= isset($errors['role']) ? 'is-invalid' : '' ?>" 
                               placeholder="e.g. Stage Manager, Sound Supervisor, Security Lead" 
                               value="<?= sanitize($role) ?>" required>
                        <div class="form-error"><?= $errors['role'] ?? '' ?></div>
                    </div>

                    <div class="form-group">
                        <label for="phone" class="form-label">Contact Phone</label>
                        <input type="text" id="phone" name="phone" 
                               class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" 
                               placeholder="e.g. 9876543210" 
                               value="<?= sanitize($phone) ?>">
                        <div class="form-error"><?= $errors['phone'] ?? '' ?></div>
                    </div>
                </div>

                <div style="display: flex; gap: 12px; margin-top: 28px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Allocate Staff
                    </button>
                    <a href="<?= url('admin/staff/index.php') ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
