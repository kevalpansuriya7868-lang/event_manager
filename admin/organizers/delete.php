<?php
/**
 * Delete Organizer Handler
 * Event Management System
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_flash('danger', 'Invalid request method for deletion.');
    header('Location: ' . url('admin/organizers/index.php'));
    exit();
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    set_flash('danger', 'Security validation failed (Invalid CSRF token).');
    header('Location: ' . url('admin/organizers/index.php'));
    exit();
}

$id = filter_input(INPUT_POST, 'organizer_id', FILTER_VALIDATE_INT);
if (!$id) {
    set_flash('danger', 'Invalid organizer ID.');
    header('Location: ' . url('admin/organizers/index.php'));
    exit();
}

try {
    // 1. Check if the organizer is referenced by any events (Referential Integrity Check)
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM event WHERE organizer_id = ?");
    $checkStmt->execute([$id]);
    $eventCount = (int)$checkStmt->fetchColumn();

    if ($eventCount > 0) {
        set_flash('warning', "Cannot delete this organizer: they are currently assigned to {$eventCount} active event(s). Please delete or reassign those events first.");
        header('Location: ' . url('admin/organizers/index.php'));
        exit();
    }

    // 2. Fetch name for notification
    $nameStmt = $pdo->prepare("SELECT organizer_name FROM organizer WHERE organizer_id = ?");
    $nameStmt->execute([$id]);
    $organizer = $nameStmt->fetch();

    if (!$organizer) {
        set_flash('danger', 'Organizer record not found.');
        header('Location: ' . url('admin/organizers/index.php'));
        exit();
    }

    // 3. Delete record
    $deleteStmt = $pdo->prepare("DELETE FROM organizer WHERE organizer_id = ?");
    $deleteStmt->execute([$id]);

    set_flash('success', "Organizer '{$organizer['organizer_name']}' was successfully deleted.");
} catch (PDOException $e) {
    set_flash('danger', 'Database constraint error: ' . $e->getMessage());
}

header('Location: ' . url('admin/organizers/index.php'));
exit();
