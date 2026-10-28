<?php
/**
 * Add New Venue
 * Event Management System
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

$pageTitle = 'Add New Venue';
$errors = [];

$venue_name = '';
$address = '';
$city = '';
$capacity = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('Invalid security token.');
    }

    $venue_name = trim($_POST['venue_name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $capacity = trim($_POST['capacity'] ?? '');

    // Server-side validation
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
            $stmt = $pdo->prepare("
                INSERT INTO venue (venue_name, address, city, capacity)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$venue_name, $address, $city, (int)$capacity]);

            set_flash('success', "Venue '{$venue_name}' added successfully!");
            header('Location: ' . url('admin/venues/index.php'));
            exit();
        } catch (PDOException $e) {
            $errors['general'] = 'Failed to add venue: ' . $e->getMessage();
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
                <i class="fas fa-landmark"></i> Create New Venue
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-danger">
                    <div class="alert-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="alert-content"><?= sanitize($errors['general']) ?></div>
                </div>
            <?php endif; ?>

            <form action="<?= url('admin/venues/add.php') ?>" method="POST" class="needs-validation" novalidate>
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="venue_name" class="form-label">Venue Name <span class="required">*</span></label>
                    <input type="text" id="venue_name" name="venue_name" 
                           class="form-control <?= isset($errors['venue_name']) ? 'is-invalid' : '' ?>" 
                           placeholder="e.g. Grand Auditorium Hall A" 
                           value="<?= sanitize($venue_name) ?>" required>
                    <div class="form-error"><?= $errors['venue_name'] ?? '' ?></div>
                </div>

                <div class="form-group">
                    <label for="address" class="form-label">Address / Campus Location <span class="required">*</span></label>
                    <input type="text" id="address" name="address" 
                           class="form-control <?= isset($errors['address']) ? 'is-invalid' : '' ?>" 
                           placeholder="e.g. Academic Block 1, Central Wing" 
                           value="<?= sanitize($address) ?>" required>
                    <div class="form-error"><?= $errors['address'] ?? '' ?></div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="city" class="form-label">City <span class="required">*</span></label>
                        <input type="text" id="city" name="city" 
                               class="form-control <?= isset($errors['city']) ? 'is-invalid' : '' ?>" 
                               placeholder="e.g. Ahmedabad" 
                               value="<?= sanitize($city) ?>" required>
                        <div class="form-error"><?= $errors['city'] ?? '' ?></div>
                    </div>

                    <div class="form-group">
                        <label for="capacity" class="form-label">Seating / Guest Capacity <span class="required">*</span></label>
                        <input type="number" id="capacity" name="capacity" min="1" 
                               class="form-control <?= isset($errors['capacity']) ? 'is-invalid' : '' ?>" 
                               placeholder="e.g. 1500" 
                               value="<?= sanitize($capacity) ?>" required>
                        <div class="form-error"><?= $errors['capacity'] ?? '' ?></div>
                    </div>
                </div>

                <div style="display: flex; gap: 12px; margin-top: 28px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Save Venue
                    </button>
                    <a href="<?= url('admin/venues/index.php') ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
