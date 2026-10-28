<?php
/**
 * Edit Artist / Speaker
 * Event Management System
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

$pageTitle = 'Edit Artist / Speaker';
$errors = [];

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    set_flash('danger', 'Invalid artist ID.');
    header('Location: ' . url('admin/artists/index.php'));
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT * FROM artist WHERE artist_id = ?");
    $stmt->execute([$id]);
    $artist = $stmt->fetch();

    if (!$artist) {
        set_flash('danger', 'Artist record not found.');
        header('Location: ' . url('admin/artists/index.php'));
        exit();
    }
} catch (PDOException $e) {
    die("Database error: " . htmlspecialchars($e->getMessage()));
}

$artist_name = $artist['artist_name'];
$genre = $artist['genre'];
$contact_email = $artist['contact_email'];
$country = $artist['country'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('Invalid security token.');
    }

    $artist_name = trim($_POST['artist_name'] ?? '');
    $genre = trim($_POST['genre'] ?? '');
    $contact_email = trim($_POST['contact_email'] ?? '');
    $country = trim($_POST['country'] ?? '');

    if (empty($artist_name)) {
        $errors['artist_name'] = 'Artist name is required.';
    }

    if (empty($contact_email)) {
        $errors['contact_email'] = 'Contact email is required.';
    } elseif (!filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
        $errors['contact_email'] = 'Please enter a valid email address.';
    }

    if (empty($errors)) {
        try {
            $checkStmt = $pdo->prepare("SELECT contact_email FROM artist WHERE contact_email = ? AND artist_id != ? LIMIT 1");
            $checkStmt->execute([$contact_email, $id]);
            if ($checkStmt->fetch()) {
                $errors['contact_email'] = 'Another artist is already using this contact email.';
            }
        } catch (PDOException $e) {
            $errors['general'] = 'Database check error: ' . $e->getMessage();
        }
    }

    if (empty($errors)) {
        try {
            $updateStmt = $pdo->prepare("
                UPDATE artist 
                SET artist_name = ?, genre = ?, contact_email = ?, country = ?
                WHERE artist_id = ?
            ");
            $updateStmt->execute([$artist_name, $genre ?: null, $contact_email, $country ?: null, $id]);

            set_flash('success', "Artist '{$artist_name}' updated successfully!");
            header('Location: ' . url('admin/artists/index.php'));
            exit();
        } catch (PDOException $e) {
            $errors['general'] = 'Failed to update artist: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../../includes/admin_header.php';
?>

<div style="max-width: 750px; margin: 0 auto;">
    <div style="margin-bottom: 20px;">
        <a href="<?= url('admin/artists/index.php') ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Artists List
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fas fa-edit"></i> Edit Artist #<?= $artist['artist_id'] ?>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-danger">
                    <div class="alert-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="alert-content"><?= sanitize($errors['general']) ?></div>
                </div>
            <?php endif; ?>

            <form action="<?= url('admin/artists/edit.php?id=' . $id) ?>" method="POST" class="needs-validation" novalidate>
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="artist_name" class="form-label">Artist / Speaker Name <span class="required">*</span></label>
                    <input type="text" id="artist_name" name="artist_name" 
                           class="form-control <?= isset($errors['artist_name']) ? 'is-invalid' : '' ?>" 
                           value="<?= sanitize($artist_name) ?>" required>
                    <div class="form-error"><?= $errors['artist_name'] ?? '' ?></div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="genre" class="form-label">Genre or Category</label>
                        <input type="text" id="genre" name="genre" 
                               class="form-control" 
                               value="<?= sanitize($genre) ?>">
                    </div>

                    <div class="form-group">
                        <label for="country" class="form-label">Country</label>
                        <input type="text" id="country" name="country" 
                               class="form-control" 
                               value="<?= sanitize($country) ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="contact_email" class="form-label">Contact / Management Email <span class="required">*</span></label>
                    <input type="email" id="contact_email" name="contact_email" 
                           class="form-control <?= isset($errors['contact_email']) ? 'is-invalid' : '' ?>" 
                           value="<?= sanitize($contact_email) ?>" required>
                    <div class="form-error"><?= $errors['contact_email'] ?? '' ?></div>
                </div>

                <div style="display: flex; gap: 12px; margin-top: 28px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Artist
                    </button>
                    <a href="<?= url('admin/artists/index.php') ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
