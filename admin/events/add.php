<?php
/**
 * Create New Event
 * Event Management System
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

$pageTitle = 'Create New Event';
$errors = [];

// Fetch existing organizers and venues for the dropdowns
try {
    $organizers = $pdo->query("SELECT organizer_id, organizer_name, organization FROM organizer ORDER BY organizer_name ASC")->fetchAll();
    $venues = $pdo->query("SELECT venue_id, venue_name, city, capacity FROM venue ORDER BY venue_name ASC")->fetchAll();
} catch (PDOException $e) {
    die("Database error: " . htmlspecialchars($e->getMessage()));
}

$event_name = '';
$event_description = '';
$event_date = '';
$event_time = '';
$organizer_id = '';
$venue_id = '';
$status = 'Upcoming';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('Invalid security token.');
    }

    $event_name = trim($_POST['event_name'] ?? '');
    $event_description = trim($_POST['event_description'] ?? '');
    $event_date = trim($_POST['event_date'] ?? '');
    $event_time = trim($_POST['event_time'] ?? '');
    $organizer_id = trim($_POST['organizer_id'] ?? '');
    $venue_id = trim($_POST['venue_id'] ?? '');
    $status = trim($_POST['status'] ?? 'Upcoming');

    // Server-side validation
    if (empty($event_name)) {
        $errors['event_name'] = 'Event title is required.';
    }

    if (empty($event_date)) {
        $errors['event_date'] = 'Event date is required.';
    }

    if (empty($event_time)) {
        $errors['event_time'] = 'Event start time is required.';
    }

    if (empty($organizer_id) || !filter_var($organizer_id, FILTER_VALIDATE_INT)) {
        $errors['organizer_id'] = 'Please select a valid event organizer.';
    }

    if (empty($venue_id) || !filter_var($venue_id, FILTER_VALIDATE_INT)) {
        $errors['venue_id'] = 'Please select a valid venue location.';
    }

    $allowedStatuses = ['Upcoming', 'Ongoing', 'Completed', 'Cancelled'];
    if (!in_array($status, $allowedStatuses, true)) {
        $errors['status'] = 'Invalid status selected.';
    }

    if (empty($errors)) {
        try {
            $insertStmt = $pdo->prepare("
                INSERT INTO event (event_name, event_description, event_date, event_time, organizer_id, venue_id, status)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $insertStmt->execute([
                $event_name,
                $event_description ?: null,
                $event_date,
                $event_time,
                (int)$organizer_id,
                (int)$venue_id,
                $status
            ]);

            $newEventId = $pdo->lastInsertId();

            set_flash('success', "Event '{$event_name}' was created successfully! You can now assign artists and sponsors.");
            header('Location: ' . url('admin/events/view.php?id=' . $newEventId));
            exit();
        } catch (PDOException $e) {
            $errors['general'] = 'Failed to create event: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../../includes/admin_header.php';
?>

<div style="max-width: 850px; margin: 0 auto;">
    <div style="margin-bottom: 20px;">
        <a href="<?= url('admin/events/index.php') ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Events List
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fas fa-calendar-plus"></i> Schedule New Event
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-danger">
                    <div class="alert-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="alert-content"><?= sanitize($errors['general']) ?></div>
                </div>
            <?php endif; ?>

            <?php if (empty($organizers) || empty($venues)): ?>
                <div class="alert alert-warning">
                    <div class="alert-icon"><i class="fas fa-info-circle"></i></div>
                    <div class="alert-content">
                        <strong>Prerequisites Notice:</strong> You need at least one organizer and one venue to create an event.
                        <?php if (empty($organizers)): ?>
                            <a href="<?= url('admin/organizers/add.php') ?>" style="text-decoration: underline; font-weight: 700;">Add an Organizer</a>
                        <?php endif; ?>
                        <?php if (empty($venues)): ?>
                            <a href="<?= url('admin/venues/add.php') ?>" style="text-decoration: underline; font-weight: 700;">Add a Venue</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <form action="<?= url('admin/events/add.php') ?>" method="POST" class="needs-validation" novalidate>
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="event_name" class="form-label">Event Title / Name <span class="required">*</span></label>
                    <input type="text" id="event_name" name="event_name" 
                           class="form-control <?= isset($errors['event_name']) ? 'is-invalid' : '' ?>" 
                           placeholder="e.g. Annual Tech Conference 2026" 
                           value="<?= sanitize($event_name) ?>" required>
                    <div class="form-error"><?= $errors['event_name'] ?? '' ?></div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="event_date" class="form-label">Event Date <span class="required">*</span></label>
                        <input type="date" id="event_date" name="event_date" 
                               class="form-control <?= isset($errors['event_date']) ? 'is-invalid' : '' ?>" 
                               value="<?= sanitize($event_date) ?>" required>
                        <div class="form-error"><?= $errors['event_date'] ?? '' ?></div>
                    </div>

                    <div class="form-group">
                        <label for="event_time" class="form-label">Start Time <span class="required">*</span></label>
                        <input type="time" id="event_time" name="event_time" 
                               class="form-control <?= isset($errors['event_time']) ? 'is-invalid' : '' ?>" 
                               value="<?= sanitize($event_time) ?>" required>
                        <div class="form-error"><?= $errors['event_time'] ?? '' ?></div>
                    </div>

                    <div class="form-group">
                        <label for="status" class="form-label">Event Status <span class="required">*</span></label>
                        <select id="status" name="status" class="form-control <?= isset($errors['status']) ? 'is-invalid' : '' ?>" required>
                            <option value="Upcoming" <?= $status === 'Upcoming' ? 'selected' : '' ?>>Upcoming</option>
                            <option value="Ongoing" <?= $status === 'Ongoing' ? 'selected' : '' ?>>Ongoing</option>
                            <option value="Completed" <?= $status === 'Completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="Cancelled" <?= $status === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                        <div class="form-error"><?= $errors['status'] ?? '' ?></div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="organizer_id" class="form-label">Event Organizer <span class="required">*</span></label>
                        <select id="organizer_id" name="organizer_id" class="form-control <?= isset($errors['organizer_id']) ? 'is-invalid' : '' ?>" required>
                            <option value="">-- Choose Organizer --</option>
                            <?php foreach ($organizers as $org): ?>
                                <option value="<?= $org['organizer_id'] ?>" <?= (string)$organizer_id === (string)$org['organizer_id'] ? 'selected' : '' ?>>
                                    <?= sanitize($org['organizer_name']) ?> <?= $org['organization'] ? '(' . sanitize($org['organization']) . ')' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-error"><?= $errors['organizer_id'] ?? '' ?></div>
                    </div>

                    <div class="form-group">
                        <label for="venue_id" class="form-label">Hosting Venue <span class="required">*</span></label>
                        <select id="venue_id" name="venue_id" class="form-control <?= isset($errors['venue_id']) ? 'is-invalid' : '' ?>" required>
                            <option value="">-- Choose Venue --</option>
                            <?php foreach ($venues as $v): ?>
                                <option value="<?= $v['venue_id'] ?>" <?= (string)$venue_id === (string)$v['venue_id'] ? 'selected' : '' ?>>
                                    <?= sanitize($v['venue_name']) ?> - <?= sanitize($v['city']) ?> (Cap: <?= number_format($v['capacity']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-error"><?= $errors['venue_id'] ?? '' ?></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="event_description" class="form-label">Event Description & Program Schedule</label>
                    <textarea id="event_description" name="event_description" class="form-control" 
                              placeholder="Provide details about the event schedule, targets, audience, and keynote sessions..."><?= sanitize($event_description) ?></textarea>
                </div>

                <div style="display: flex; gap: 12px; margin-top: 28px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Save Event
                    </button>
                    <a href="<?= url('admin/events/index.php') ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
