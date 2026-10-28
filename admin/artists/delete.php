<?php
/**
 * Delete Artist Handler
 * Event Management System
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_flash('danger', 'Invalid request method.');
    header('Location: ' . url('admin/artists/index.php'));
    exit();
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    set_flash('danger', 'Security validation failed (Invalid CSRF token).');
    header('Location: ' . url('admin/artists/index.php'));
    exit();
}

$id = filter_input(INPUT_POST, 'artist_id', FILTER_VALIDATE_INT);
if (!$id) {
    set_flash('danger', 'Invalid artist ID.');
    header('Location: ' . url('admin/artists/index.php'));
    exit();
}

try {
    // Check if artist is assigned to any events
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM event_artist WHERE artist_id = ?");
    $checkStmt->execute([$id]);
    $assignedCount = (int)$checkStmt->fetchColumn();

    if ($assignedCount > 0) {
        set_flash('warning', "Cannot delete artist: This artist is assigned to {$assignedCount} active event(s). Please unassign them from those events first.");
        header('Location: ' . url('admin/artists/index.php'));
        exit();
    }

    $nameStmt = $pdo->prepare("SELECT artist_name FROM artist WHERE artist_id = ?");
    $nameStmt->execute([$id]);
    $artist = $nameStmt->fetch();

    if (!$artist) {
        set_flash('danger', 'Artist record not found.');
        header('Location: ' . url('admin/artists/index.php'));
        exit();
    }

    $deleteStmt = $pdo->prepare("DELETE FROM artist WHERE artist_id = ?");
    $deleteStmt->execute([$id]);

    set_flash('success', "Artist '{$artist['artist_name']}' was successfully deleted.");
} catch (PDOException $e) {
    set_flash('danger', 'Database constraint error: ' . $e->getMessage());
}

header('Location: ' . url('admin/artists/index.php'));
exit();
