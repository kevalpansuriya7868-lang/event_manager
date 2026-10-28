<?php
/**
 * Edit Sponsor
 * Event Management System
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

$pageTitle = 'Edit Sponsor';
$errors = [];

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    set_flash('danger', 'Invalid sponsor ID.');
    header('Location: ' . url('admin/sponsors/index.php'));
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT * FROM sponsor WHERE sponsor_id = ?");
    $stmt->execute([$id]);
    $sponsor = $stmt->fetch();

    if (!$sponsor) {
        set_flash('danger', 'Sponsor record not found.');
        header('Location: ' . url('admin/sponsors/index.php'));
        exit();
    }
} catch (PDOException $e) {
    die("Database error: " . htmlspecialchars($e->getMessage()));
}

$sponsor_name = $sponsor['sponsor_name'];
$contact_person = $sponsor['contact_person'];
$email = $sponsor['email'];
$phone = $sponsor['phone'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('Invalid security token.');
    }

    $sponsor_name = trim($_POST['sponsor_name'] ?? '');
    $contact_person = trim($_POST['contact_person'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if (empty($sponsor_name)) {
        $errors['sponsor_name'] = 'Sponsor name is required.';
    }

    if (empty($email)) {
        $errors['email'] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if (!empty($phone) && !preg_match('/^[0-9+\s\-()]{10,15}$/', $phone)) {
        $errors['phone'] = 'Please enter a valid 10-15 digit phone number.';
    }

    if (empty($errors)) {
        try {
            $checkStmt = $pdo->prepare("SELECT email FROM sponsor WHERE email = ? AND sponsor_id != ? LIMIT 1");
            $checkStmt->execute([$email, $id]);
            if ($checkStmt->fetch()) {
                $errors['email'] = 'Another sponsor is already registered with this email.';
            }
        } catch (PDOException $e) {
            $errors['general'] = 'Database check error: ' . $e->getMessage();
        }
    }

    if (empty($errors)) {
        try {
            $updateStmt = $pdo->prepare("
                UPDATE sponsor 
                SET sponsor_name = ?, contact_person = ?, email = ?, phone = ?
                WHERE sponsor_id = ?
            ");
            $updateStmt->execute([$sponsor_name, $contact_person ?: null, $email, $phone ?: null, $id]);

            set_flash('success', "Sponsor '{$sponsor_name}' updated successfully!");
            header('Location: ' . url('admin/sponsors/index.php'));
            exit();
        } catch (PDOException $e) {
            $errors['general'] = 'Failed to update sponsor: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../../includes/admin_header.php';
?>

<div style="max-width: 750px; margin: 0 auto;">
    <div style="margin-bottom: 20px;">
        <a href="<?= url('admin/sponsors/index.php') ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Sponsors List
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fas fa-edit"></i> Edit Sponsor #<?= $sponsor['sponsor_id'] ?>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-danger">
                    <div class="alert-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="alert-content"><?= sanitize($errors['general']) ?></div>
                </div>
            <?php endif; ?>

            <form action="<?= url('admin/sponsors/edit.php?id=' . $id) ?>" method="POST" class="needs-validation" novalidate>
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="sponsor_name" class="form-label">Sponsor / Company Name <span class="required">*</span></label>
                    <input type="text" id="sponsor_name" name="sponsor_name" 
                           class="form-control <?= isset($errors['sponsor_name']) ? 'is-invalid' : '' ?>" 
                           value="<?= sanitize($sponsor_name) ?>" required>
                    <div class="form-error"><?= $errors['sponsor_name'] ?? '' ?></div>
                </div>

                <div class="form-group">
                    <label for="contact_person" class="form-label">Contact Person</label>
                    <input type="text" id="contact_person" name="contact_person" 
                           class="form-control" 
                           value="<?= sanitize($contact_person) ?>">
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
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="text" id="phone" name="phone" 
                               class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" 
                               value="<?= sanitize($phone) ?>">
                        <div class="form-error"><?= $errors['phone'] ?? '' ?></div>
                    </div>
                </div>

                <div style="display: flex; gap: 12px; margin-top: 28px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Sponsor
                    </button>
                    <a href="<?= url('admin/sponsors/index.php') ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
