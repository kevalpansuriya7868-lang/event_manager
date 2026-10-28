<?php
/**
 * Sponsors Listing & Search
 * Event Management System
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

$pageTitle = 'Manage Sponsors';
$search = trim($_GET['search'] ?? '');

try {
    if (!empty($search)) {
        $stmt = $pdo->prepare("
            SELECT s.*, 
                   COUNT(es.event_id) as event_count,
                   COALESCE(SUM(es.sponsorship_amount), 0) as total_sponsored
            FROM sponsor s
            LEFT JOIN event_sponsor es ON s.sponsor_id = es.sponsor_id
            WHERE s.sponsor_name LIKE :search 
               OR s.contact_person LIKE :search 
               OR s.email LIKE :search 
               OR s.phone LIKE :search
            GROUP BY s.sponsor_id
            ORDER BY s.sponsor_id DESC
        ");
        $stmt->execute([':search' => "%{$search}%"]);
    } else {
        $stmt = $pdo->query("
            SELECT s.*, 
                   COUNT(es.event_id) as event_count,
                   COALESCE(SUM(es.sponsorship_amount), 0) as total_sponsored
            FROM sponsor s
            LEFT JOIN event_sponsor es ON s.sponsor_id = es.sponsor_id
            GROUP BY s.sponsor_id
            ORDER BY s.sponsor_id DESC
        ");
    }
    $sponsors = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database error: " . htmlspecialchars($e->getMessage()));
}

require_once __DIR__ . '/../../includes/admin_header.php';
?>

<div class="toolbar-container">
    <form action="<?= url('admin/sponsors/index.php') ?>" method="GET" class="search-form">
        <input type="text" name="search" class="form-control" placeholder="Search by sponsor name, contact person, email..." value="<?= sanitize($search) ?>">
        <button type="submit" class="btn btn-secondary">
            <i class="fas fa-search"></i> Search
        </button>
        <?php if (!empty($search)): ?>
            <a href="<?= url('admin/sponsors/index.php') ?>" class="btn btn-secondary" title="Clear Search">
                <i class="fas fa-times"></i>
            </a>
        <?php endif; ?>
    </form>

    <div>
        <a href="<?= url('admin/sponsors/add.php') ?>" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Add Sponsor
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-handshake"></i> Corporate Sponsors (<?= count($sponsors) ?>)
        </div>
        <div style="font-size: 13px; color: var(--text-muted);">
            Manage brand partners, title sponsors, and event funding
        </div>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Sponsor / Company</th>
                        <th>Contact Person</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Sponsored Events</th>
                        <th>Total Contribution</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($sponsors)): ?>
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i class="fas fa-building-circle-exclamation"></i></div>
                                    <h3>No sponsors found</h3>
                                    <p>Register companies, title sponsors, and academic event partners.</p>
                                    <a href="<?= url('admin/sponsors/add.php') ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus"></i> Add New Sponsor
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($sponsors as $sp): ?>
                            <tr>
                                <td><span class="badge badge-secondary">#<?= $sp['sponsor_id'] ?></span></td>
                                <td><strong><?= sanitize($sp['sponsor_name']) ?></strong></td>
                                <td><?= sanitize($sp['contact_person'] ?: '-') ?></td>
                                <td><a href="mailto:<?= sanitize($sp['email']) ?>"><?= sanitize($sp['email']) ?></a></td>
                                <td><?= sanitize($sp['phone'] ?: '-') ?></td>
                                <td>
                                    <a href="<?= url('admin/assignments/event_sponsors.php?sponsor_id=' . $sp['sponsor_id']) ?>" class="badge badge-info">
                                        <?= $sp['event_count'] ?> Events
                                    </a>
                                </td>
                                <td>
                                    <strong><?= format_currency($sp['total_sponsored']) ?></strong>
                                </td>
                                <td>
                                    <div class="actions-cell">
                                        <a href="<?= url('admin/sponsors/edit.php?id=' . $sp['sponsor_id']) ?>" class="btn btn-secondary btn-icon" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="<?= url('admin/sponsors/delete.php') ?>" method="POST" style="display: inline;">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="sponsor_id" value="<?= $sp['sponsor_id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-icon confirm-delete" data-item="<?= sanitize($sp['sponsor_name']) ?>" title="Delete">
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
