<?php
/**
 * Add New Organizer
 * Event Management System
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

$pageTitle = 'Add New Organizer';
$errors = [];

$name = '';
$email = '';
$phone = '';
$organization = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF verification
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('Invalid security token. Please refresh and try again.');
    }

    $name = trim($_POST['organizer_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $organization = trim($_POST['organization'] ?? '');

    // Server-side validation
    if (empty($name)) {
        $errors['organizer_name'] = 'Organizer name is required.';
    } elseif (strlen($name) < 3) {
        $errors['organizer_name'] = 'Name must be at least 3 characters.';
    }

    if (empty($email)) {
        $errors['email'] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if (empty($phone)) {
        $errors['phone'] = 'Phone number is required.';
    } elseif (!preg_match('/^[0-9+\s\-()]{10,15}$/', $phone)) {
        $errors['phone'] = 'Please enter a valid 10-15 digit phone number.';
    }

    // Check unique email & phone
    if (empty($errors)) {
        try {
            $checkStmt = $pdo->prepare("SELECT email, phone FROM organizer WHERE email = ? OR phone = ? LIMIT 1");
            $checkStmt->execute([$email, $phone]);
            $existing = $checkStmt->fetch();

            if ($existing) {
                if (strcasecmp($existing['email'], $email) === 0) {
                    $errors['email'] = 'An organizer with this email address already exists.';
                }
                if ($existing['phone'] === $phone) {
                    $errors['phone'] = 'An organizer with this phone number already exists.';
                }
            }
        } catch (PDOException $e) {
            $errors['general'] = 'Database error: ' . $e->getMessage();
        }
    }

    // Insert if no errors
    if (empty($errors)) {
        try {
            $insertStmt = $pdo->prepare("
                INSERT INTO organizer (organizer_name, email, phone, organization)
                VALUES (?, ?, ?, ?)
            ");
            $insertStmt->execute([$name, $email, $phone, $organization ?: null]);

            set_flash('success', "Organizer '{$name}' created successfully!");
            header('Location: ' . url('admin/organizers/index.php'));
            exit();
        } catch (PDOException $e) {
            $errors['general'] = 'Failed to create organizer: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../../includes/admin_header.php';
?>

<div style="max-width: 750px; margin: 0 auto;">
    <div style="margin-bottom: 20px;">
        <a href="<?= url('admin/organizers/index.php') ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Organizers List
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fas fa-user-plus"></i> Create New Organizer
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-danger">
                    <div class="alert-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="alert-content"><?= sanitize($errors['general']) ?></div>
                </div>
            <?php endif; ?>

            <form action="<?= url('admin/organizers/add.php') ?>" method="POST" class="needs-validation" novalidate>
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="organizer_name" class="form-label">Full Name / Organizer Name <span class="required">*</span></label>
                    <input type="text" id="organizer_name" name="organizer_name" 
                           class="form-control <?= isset($errors['organizer_name']) ? 'is-invalid' : '' ?>" 
                           placeholder="e.g. Dr. Rajesh Sharma" 
                           value="<?= sanitize($name) ?>" required>
                    <div class="form-error"><?= $errors['organizer_name'] ?? '' ?></div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address <span class="required">*</span></label>
                        <input type="email" id="email" name="email" 
                               class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                               placeholder="e.g. rajesh@techfest.org" 
                               value="<?= sanitize($email) ?>" required>
                        <div class="form-error"><?= $errors['email'] ?? '' ?></div>
                    </div>

                    <div class="form-group">
                        <label for="phone" class="form-label">Phone Number <span class="required">*</span></label>
                        <input type="text" id="phone" name="phone" 
                               class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" 
                               placeholder="e.g. 9876543210" 
                               value="<?= sanitize($phone) ?>" required>
                        <div class="form-error"><?= $errors['phone'] ?? '' ?></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="organization" class="form-label">Organization / College Department</label>
                    <input type="text" id="organization" name="organization" 
                           class="form-control" 
                           placeholder="e.g. Department of Computer Engineering" 
                           value="<?= sanitize($organization) ?>">
                    <div class="form-text">Optional: Name of the club, department, or company they represent.</div>
                </div>

                <div style="display: flex; gap: 12px; margin-top: 28px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Save Organizer
                    </button>
                    <a href="<?= url('admin/organizers/index.php') ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
