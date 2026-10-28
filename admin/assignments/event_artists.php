<?php
/**
 * Event-Artist Many-to-Many Assignment Manager
 * Event Management System
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

$pageTitle = 'Assign Artists to Events';
$errors = [];

// Handle POST actions (Assign or Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('Invalid security token.');
    }

    $action = $_POST['action'] ?? 'add';

    if ($action === 'add') {
        $event_id = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
        $artist_id = filter_input(INPUT_POST, 'artist_id', FILTER_VALIDATE_INT);
        $role = trim($_POST['role'] ?? 'Performer');

        if (!$event_id) $errors['event_id'] = 'Please select a valid event.';
        if (!$artist_id) $errors['artist_id'] = 'Please select a valid artist or speaker.';

        if (empty($errors)) {
            try {
                // Check for duplicate composite key (event_id, artist_id)
                $checkStmt = $pdo->prepare("SELECT 1 FROM event_artist WHERE event_id = ? AND artist_id = ?");
                $checkStmt->execute([$event_id, $artist_id]);

                if ($checkStmt->fetch()) {
                    $errors['general'] = 'Duplicate Assignment: This artist is already assigned to the selected event!';
                } else {
                    $insertStmt = $pdo->prepare("INSERT INTO event_artist (event_id, artist_id, role) VALUES (?, ?, ?)");
                    $insertStmt->execute([$event_id, $artist_id, $role ?: 'Performer']);

                    set_flash('success', 'Artist successfully assigned to event!');
                    header('Location: ' . url('admin/assignments/event_artists.php?event_id=' . $event_id));
                    exit();
                }
            } catch (PDOException $e) {
                $errors['general'] = 'Database error: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'delete') {
        $event_id = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
        $artist_id = filter_input(INPUT_POST, 'artist_id', FILTER_VALIDATE_INT);

        if ($event_id && $artist_id) {
            try {
                $delStmt = $pdo->prepare("DELETE FROM event_artist WHERE event_id = ? AND artist_id = ?");
                $delStmt->execute([$event_id, $artist_id]);
                set_flash('success', 'Artist assignment removed successfully.');
            } catch (PDOException $e) {
                set_flash('danger', 'Error removing assignment: ' . $e->getMessage());
            }
        }
        header('Location: ' . url('admin/assignments/event_artists.php' . ($event_id ? '?event_id=' . $event_id : '')));
        exit();
    }
}

// Fetch lists for dropdowns and filter
try {
    $events = $pdo->query("SELECT event_id, event_name, event_date FROM event ORDER BY event_date DESC")->fetchAll();
    $artists = $pdo->query("SELECT artist_id, artist_name, genre FROM artist ORDER BY artist_name ASC")->fetchAll();

    $filterEventId = filter_input(INPUT_GET, 'event_id', FILTER_VALIDATE_INT);
    $filterArtistId = filter_input(INPUT_GET, 'artist_id', FILTER_VALIDATE_INT);

    $sql = "
        SELECT ea.event_id, ea.artist_id, ea.role,
               e.event_name, e.event_date,
               a.artist_name, a.genre, a.contact_email
        FROM event_artist ea
        JOIN event e ON ea.event_id = e.event_id
        JOIN artist a ON ea.artist_id = a.artist_id
        WHERE 1=1
    ";
    $params = [];

    if ($filterEventId) {
        $sql .= " AND ea.event_id = :event_id";
        $params[':event_id'] = $filterEventId;
    }

    if ($filterArtistId) {
        $sql .= " AND ea.artist_id = :artist_id";
        $params[':artist_id'] = $filterArtistId;
    }

    $sql .= " ORDER BY e.event_date DESC, a.artist_name ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $assignments = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Database error: " . htmlspecialchars($e->getMessage()));
}

require_once __DIR__ . '/../../includes/admin_header.php';
?>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 28px; align-items: start;">
    <!-- Add New Assignment Card -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fas fa-user-plus"></i> Assign Artist to Event
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-danger">
                    <div class="alert-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="alert-content"><?= sanitize($errors['general']) ?></div>
                </div>
            <?php endif; ?>

            <form action="<?= url('admin/assignments/event_artists.php') ?>" method="POST" class="needs-validation" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="add">

                <div class="form-group">
                    <label for="event_id" class="form-label">Select Event <span class="required">*</span></label>
                    <select id="event_id" name="event_id" class="form-control <?= isset($errors['event_id']) ? 'is-invalid' : '' ?>" required>
                        <option value="">-- Choose Event --</option>
                        <?php foreach ($events as $ev): ?>
                            <option value="<?= $ev['event_id'] ?>" <?= $filterEventId === $ev['event_id'] ? 'selected' : '' ?>>
                                <?= sanitize($ev['event_name']) ?> (<?= format_date($ev['event_date']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-error"><?= $errors['event_id'] ?? '' ?></div>
                </div>

                <div class="form-group">
                    <label for="artist_id" class="form-label">Select Artist / Speaker <span class="required">*</span></label>
                    <select id="artist_id" name="artist_id" class="form-control <?= isset($errors['artist_id']) ? 'is-invalid' : '' ?>" required>
                        <option value="">-- Choose Artist --</option>
                        <?php foreach ($artists as $art): ?>
                            <option value="<?= $art['artist_id'] ?>" <?= $filterArtistId === $art['artist_id'] ? 'selected' : '' ?>>
                                <?= sanitize($art['artist_name']) ?> <?= $art['genre'] ? '(' . sanitize($art['genre']) . ')' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-error"><?= $errors['artist_id'] ?? '' ?></div>
                </div>

                <div class="form-group">
                    <label for="role" class="form-label">Performance Role / Session Title</label>
                    <input type="text" id="role" name="role" class="form-control" 
                           placeholder="e.g. Keynote Speaker, Opening Act, Emcee" value="Performer">
                    <div class="form-text">Specific role the artist plays in this event.</div>
                </div>

                <div style="margin-top: 24px;">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-link"></i> Save Relationship
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Assignments Directory Table Card -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fas fa-list-check"></i> Current Artist Assignments (<?= count($assignments) ?>)
            </div>
            <?php if ($filterEventId || $filterArtistId): ?>
                <a href="<?= url('admin/assignments/event_artists.php') ?>" class="btn btn-secondary btn-sm">
                    <i class="fas fa-times"></i> Clear Filter
                </a>
            <?php endif; ?>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Event Name</th>
                            <th>Artist / Speaker</th>
                            <th>Role / Assignment</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($assignments)): ?>
                            <tr>
                                <td colspan="4">
                                    <div class="empty-state">
                                        <div class="empty-state-icon"><i class="fas fa-user-xmark"></i></div>
                                        <h3>No artist assignments</h3>
                                        <p>Use the form on the left to assign artists to events.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($assignments as $row): ?>
                                <tr>
                                    <td>
                                        <a href="<?= url('admin/events/view.php?id=' . $row['event_id']) ?>">
                                            <strong><?= sanitize($row['event_name']) ?></strong>
                                        </a>
                                        <div style="font-size: 12px; color: var(--text-muted);">
                                            <?= format_date($row['event_date']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <strong><?= sanitize($row['artist_name']) ?></strong>
                                        <div style="font-size: 12px; color: var(--text-muted);"><?= sanitize($row['genre'] ?: 'General') ?></div>
                                    </td>
                                    <td>
                                        <span class="badge badge-info"><?= sanitize($row['role'] ?: 'Performer') ?></span>
                                    </td>
                                    <td>
                                        <form action="<?= url('admin/assignments/event_artists.php') ?>" method="POST" style="display: inline;">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="event_id" value="<?= $row['event_id'] ?>">
                                            <input type="hidden" name="artist_id" value="<?= $row['artist_id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-icon confirm-delete" 
                                                    data-item="<?= sanitize($row['artist_name']) ?> from <?= sanitize($row['event_name']) ?>" 
                                                    title="Remove Assignment">
                                                <i class="fas fa-unlink"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
