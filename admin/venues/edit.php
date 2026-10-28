<?php
/**
 * Edit Venue
 * Event Management System
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

$pageTitle = 'Edit Venue';
$errors = [];

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    set_flash('danger', 'Invalid venue ID.');
    header('Location: ' . url('admin/venues/index.php'));
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT * FROM venue WHERE venue_id = ?");
    $stmt->execute([$id]);
    $venue = $stmt->fetch();

    if (!$venue) {
        set_flash('danger', 'Venue not found.');
        header('Location: ' . url('admin/venues/index.php'));
        exit();
    }
} catch (PDOException $e) {
    die("Database error: " . htmlspecialchars($e->getMessage()));
}

$venue_name = $venue['venue_name'];
$address = $venue['address'];
$city = $venue['city'];
$capacity = $venue['capacity'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('Invalid security token.');
    }

    $venue_name = trim($_POST['venue_name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $capacity = trim($_POST['capacity'] ?? '');

    if (empty($venue_name)) {
        $errors['venue_name'] = 'Venue name is required.';
    }

    if (empty($address)) {
        $errors['address'] = 'Physical address is required.';
    }

    if (empty($city)) {
        $errors['city'] = 'City is required.';
    }

    if ($capacity === '') {
        $errors['capacity'] = 'Audience capacity is required.';
    } elseif (!filter_var($capacity, FILTER_VALIDATE_INT) || (int)$capacity <= 0) {
        $errors['capacity'] = 'Capacity must be a positive number greater than 0.';
    }

    if (empty($errors)) {
        try {
            $updateStmt = $pdo->prepare("
                UPDATE venue 
                SET venue_name = ?, address = ?, city = ?, capacity = ?
                WHERE venue_id = ?
            ");
            $updateStmt->execute([$venue_name, $address, $city, (int)$capacity, $id]);

            set_flash('success', "Venue '{$venue_name}' updated successfully!");
            header('Location: ' . url('admin/venues/index.php'));
            exit();
        } catch (PDOException $e) {
            $errors['general'] = 'Failed to update venue: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../../includes/admin_header.php';
?>

<div style="max-width: 750px; margin: 0 auto;">
    <div style="margin-bottom: 20px;">
        <a href="<?= url('admin/venues/index.php') ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Venues List
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fas fa-edit"></i> Edit Venue #<?= $venue['venue_id'] ?>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-danger">
                    <div class="alert-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="alert-content"><?= sanitize($errors['general']) ?></div>
                </div>
            <?php endif; ?>

            <form action="<?= url('admin/venues/edit.php?id=' . $id) ?>" method="POST" class="needs-validation" novalidate>
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="venue_name" class="form-label">Venue Name <span class="required">*</span></label>
                    <input type="text" id="venue_name" name="venue_name" 
                           class="form-control <?= isset($errors['venue_name']) ? 'is-invalid' : '' ?>" 
                           value="<?= sanitize($venue_name) ?>" required>
                    <div class="form-error"><?= $errors['venue_name'] ?? '' ?></div>
                </div>

                <div class="form-group">
                    <label for="address" class="form-label">Address / Location <span class="required">*</span></label>
                    <input type="text" id="address" name="address" 
                           class="form-control <?= isset($errors['address']) ? 'is-invalid' : '' ?>" 
                           value="<?= sanitize($address) ?>" required>
                    <div class="form-error"><?= $errors['address'] ?? '' ?></div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="city" class="form-label">City <span class="required">*</span></label>
                        <input type="text" id="city" name="city" 
                               class="form-control <?= isset($errors['city']) ? 'is-invalid' : '' ?>" 
                               value="<?= sanitize($city) ?>" required>
                        <div class="form-error"><?= $errors['city'] ?? '' ?></div>
                    </div>

                    <div class="form-group">
                        <label for="capacity" class="form-label">Seating / Guest Capacity <span class="required">*</span></label>
                        <input type="number" id="capacity" name="capacity" min="1" 
                               class="form-control <?= isset($errors['capacity']) ? 'is-invalid' : '' ?>" 
                               value="<?= sanitize($capacity) ?>" required>
                        <div class="form-error"><?= $errors['capacity'] ?? '' ?></div>
                    </div>
                </div>

                <div style="display: flex; gap: 12px; margin-top: 28px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Venue
                    </button>
                    <a href="<?= url('admin/venues/index.php') ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
