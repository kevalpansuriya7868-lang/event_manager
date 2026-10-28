<?php
/**
 * Edit Organizer
 * Event Management System
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

$pageTitle = 'Edit Organizer';
$errors = [];

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    set_flash('danger', 'Invalid organizer ID.');
    header('Location: ' . url('admin/organizers/index.php'));
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT * FROM organizer WHERE organizer_id = ?");
    $stmt->execute([$id]);
    $organizer = $stmt->fetch();

    if (!$organizer) {
        set_flash('danger', 'Organizer not found.');
        header('Location: ' . url('admin/organizers/index.php'));
        exit();
    }
} catch (PDOException $e) {
    die("Database error: " . htmlspecialchars($e->getMessage()));
}

$name = $organizer['organizer_name'];
$email = $organizer['email'];
$phone = $organizer['phone'];
$organization = $organizer['organization'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('Invalid security token.');
    }

    $name = trim($_POST['organizer_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $organization = trim($_POST['organization'] ?? '');

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

    // Check unique email and phone excluding current record
    if (empty($errors)) {
        try {
            $checkStmt = $pdo->prepare("SELECT email, phone FROM organizer WHERE (email = ? OR phone = ?) AND organizer_id != ? LIMIT 1");
            $checkStmt->execute([$email, $phone, $id]);
            $existing = $checkStmt->fetch();

            if ($existing) {
                if (strcasecmp($existing['email'], $email) === 0) {
                    $errors['email'] = 'Another organizer is already using this email.';
                }
                if ($existing['phone'] === $phone) {
                    $errors['phone'] = 'Another organizer is already using this phone number.';
                }
            }
        } catch (PDOException $e) {
            $errors['general'] = 'Database error: ' . $e->getMessage();
        }
    }

    if (empty($errors)) {
        try {
            $updateStmt = $pdo->prepare("
                UPDATE organizer 
                SET organizer_name = ?, email = ?, phone = ?, organization = ?
                WHERE organizer_id = ?
            ");
            $updateStmt->execute([$name, $email, $phone, $organization ?: null, $id]);

            set_flash('success', "Organizer '{$name}' updated successfully!");
            header('Location: ' . url('admin/organizers/index.php'));
            exit();
        } catch (PDOException $e) {
            $errors['general'] = 'Failed to update organizer: ' . $e->getMessage();
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
                <i class="fas fa-edit"></i> Edit Organizer #<?= $organizer['organizer_id'] ?>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-danger">
                    <div class="alert-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="alert-content"><?= sanitize($errors['general']) ?></div>
                </div>
            <?php endif; ?>

            <form action="<?= url('admin/organizers/edit.php?id=' . $id) ?>" method="POST" class="needs-validation" novalidate>
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="organizer_name" class="form-label">Full Name / Organizer Name <span class="required">*</span></label>
                    <input type="text" id="organizer_name" name="organizer_name" 
                           class="form-control <?= isset($errors['organizer_name']) ? 'is-invalid' : '' ?>" 
                           value="<?= sanitize($name) ?>" required>
                    <div class="form-error"><?= $errors['organizer_name'] ?? '' ?></div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address <span class="required">*</span></label>
                        <input type="email" id="email" name="email" 
                               class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                               value="<?= sanitize($email) ?>" required>
                        <div class="form-error"><?= $errors['email'] ?? '' ?></div>
                    </div>

                    <div class="form-group">
                        <label for="phone" class="form-label">Phone Number <span class="required">*</span></label>
                        <input type="text" id="phone" name="phone" 
                               class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" 
                               value="<?= sanitize($phone) ?>" required>
                        <div class="form-error"><?= $errors['phone'] ?? '' ?></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="organization" class="form-label">Organization / College Department</label>
                    <input type="text" id="organization" name="organization" 
                           class="form-control" 
                           value="<?= sanitize($organization) ?>">
                </div>

                <div style="display: flex; gap: 12px; margin-top: 28px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Changes
                    </button>
                    <a href="<?= url('admin/organizers/index.php') ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
