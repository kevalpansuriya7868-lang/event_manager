<?php
/**
 * Artists & Speakers Listing
 * Event Management System
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

$pageTitle = 'Manage Artists & Speakers';
$search = trim($_GET['search'] ?? '');

try {
    if (!empty($search)) {
        $stmt = $pdo->prepare("
            SELECT a.*, COUNT(ea.event_id) as event_count
            FROM artist a
            LEFT JOIN event_artist ea ON a.artist_id = ea.artist_id
            WHERE a.artist_name LIKE :search 
               OR a.genre LIKE :search 
               OR a.contact_email LIKE :search 
               OR a.country LIKE :search
            GROUP BY a.artist_id
            ORDER BY a.artist_id DESC
        ");
        $stmt->execute([':search' => "%{$search}%"]);
    } else {
        $stmt = $pdo->query("
            SELECT a.*, COUNT(ea.event_id) as event_count
            FROM artist a
            LEFT JOIN event_artist ea ON a.artist_id = ea.artist_id
            GROUP BY a.artist_id
            ORDER BY a.artist_id DESC
        ");
    }
    $artists = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database error: " . htmlspecialchars($e->getMessage()));
}

require_once __DIR__ . '/../../includes/admin_header.php';
?>

<div class="toolbar-container">
    <form action="<?= url('admin/artists/index.php') ?>" method="GET" class="search-form">
        <input type="text" name="search" class="form-control" placeholder="Search by artist name, genre, email, country..." value="<?= sanitize($search) ?>">
        <button type="submit" class="btn btn-secondary">
            <i class="fas fa-search"></i> Search
        </button>
        <?php if (!empty($search)): ?>
            <a href="<?= url('admin/artists/index.php') ?>" class="btn btn-secondary" title="Clear Search">
                <i class="fas fa-times"></i>
            </a>
        <?php endif; ?>
    </form>

    <div>
        <a href="<?= url('admin/artists/add.php') ?>" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Add Artist / Speaker
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-microphone-lines"></i> Artists & Guest Speakers (<?= count($artists) ?>)
        </div>
        <div style="font-size: 13px; color: var(--text-muted);">
            Manage performers, bands, DJs, and conference keynote speakers
        </div>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Genre / Category</th>
                        <th>Contact Email</th>
                        <th>Country</th>
                        <th>Assigned Events</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($artists)): ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i class="fas fa-music"></i></div>
                                    <h3>No artists found</h3>
                                    <p>Add singers, keynote speakers, or bands to your roster.</p>
                                    <a href="<?= url('admin/artists/add.php') ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus"></i> Add New Artist
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($artists as $art): ?>
                            <tr>
                                <td><span class="badge badge-secondary">#<?= $art['artist_id'] ?></span></td>
                                <td><strong><?= sanitize($art['artist_name']) ?></strong></td>
                                <td><span class="badge badge-primary"><?= sanitize($art['genre'] ?: 'General') ?></span></td>
                                <td><a href="mailto:<?= sanitize($art['contact_email']) ?>"><?= sanitize($art['contact_email']) ?></a></td>
                                <td><?= sanitize($art['country'] ?: '-') ?></td>
                                <td>
                                    <a href="<?= url('admin/assignments/event_artists.php?artist_id=' . $art['artist_id']) ?>" class="badge badge-info">
                                        <?= $art['event_count'] ?> Events
                                    </a>
                                </td>
                                <td>
                                    <div class="actions-cell">
                                        <a href="<?= url('admin/artists/edit.php?id=' . $art['artist_id']) ?>" class="btn btn-secondary btn-icon" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="<?= url('admin/artists/delete.php') ?>" method="POST" style="display: inline;">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="artist_id" value="<?= $art['artist_id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-icon confirm-delete" data-item="<?= sanitize($art['artist_name']) ?>" title="Delete">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
