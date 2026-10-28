<?php
/**
 * Add New Artist / Speaker
 * Event Management System
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

$pageTitle = 'Add New Artist / Speaker';
$errors = [];

$artist_name = '';
$genre = '';
$contact_email = '';
$country = '';

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

    // Check unique email
    if (empty($errors)) {
        try {
            $checkStmt = $pdo->prepare("SELECT contact_email FROM artist WHERE contact_email = ? LIMIT 1");
            $checkStmt->execute([$contact_email]);
            if ($checkStmt->fetch()) {
                $errors['contact_email'] = 'An artist with this contact email already exists.';
            }
        } catch (PDOException $e) {
            $errors['general'] = 'Database check error: ' . $e->getMessage();
        }
    }

    if (empty($errors)) {
        try {
            $insertStmt = $pdo->prepare("
                INSERT INTO artist (artist_name, genre, contact_email, country)
                VALUES (?, ?, ?, ?)
            ");
            $insertStmt->execute([$artist_name, $genre ?: null, $contact_email, $country ?: null]);

            set_flash('success', "Artist '{$artist_name}' registered successfully!");
            header('Location: ' . url('admin/artists/index.php'));
            exit();
        } catch (PDOException $e) {
            $errors['general'] = 'Failed to add artist: ' . $e->getMessage();
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
                <i class="fas fa-user-plus"></i> Register New Artist or Guest Speaker
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-danger">
                    <div class="alert-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="alert-content"><?= sanitize($errors['general']) ?></div>
                </div>
            <?php endif; ?>

            <form action="<?= url('admin/artists/add.php') ?>" method="POST" class="needs-validation" novalidate>
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="artist_name" class="form-label">Artist / Speaker / Band Name <span class="required">*</span></label>
                    <input type="text" id="artist_name" name="artist_name" 
                           class="form-control <?= isset($errors['artist_name']) ? 'is-invalid' : '' ?>" 
                           placeholder="e.g. The Acoustic Waves or Dr. Alistair Finch" 
                           value="<?= sanitize($artist_name) ?>" required>
                    <div class="form-error"><?= $errors['artist_name'] ?? '' ?></div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="genre" class="form-label">Genre or Category</label>
                        <input type="text" id="genre" name="genre" 
                               class="form-control" 
                               placeholder="e.g. Indie Rock, Standup Comedy, AI Keynote" 
                               value="<?= sanitize($genre) ?>">
                    </div>

                    <div class="form-group">
                        <label for="country" class="form-label">Country / Origin</label>
                        <input type="text" id="country" name="country" 
                               class="form-control" 
                               placeholder="e.g. India, USA, United Kingdom" 
                               value="<?= sanitize($country) ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="contact_email" class="form-label">Contact / Management Email <span class="required">*</span></label>
                    <input type="email" id="contact_email" name="contact_email" 
                           class="form-control <?= isset($errors['contact_email']) ? 'is-invalid' : '' ?>" 
                           placeholder="e.g. booking@acousticwaves.com" 
                           value="<?= sanitize($contact_email) ?>" required>
                    <div class="form-error"><?= $errors['contact_email'] ?? '' ?></div>
                </div>

                <div style="display: flex; gap: 12px; margin-top: 28px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Register Artist
                    </button>
                    <a href="<?= url('admin/artists/index.php') ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
