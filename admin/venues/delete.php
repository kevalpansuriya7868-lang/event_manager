<?php
/**
 * Delete Venue Handler
 * Event Management System
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_flash('danger', 'Invalid request method for deletion.');
    header('Location: ' . url('admin/venues/index.php'));
    exit();
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    set_flash('danger', 'Security validation failed (Invalid CSRF token).');
    header('Location: ' . url('admin/venues/index.php'));
    exit();
}

$id = filter_input(INPUT_POST, 'venue_id', FILTER_VALIDATE_INT);
if (!$id) {
    set_flash('danger', 'Invalid venue ID.');
    header('Location: ' . url('admin/venues/index.php'));
    exit();
}

try {
    // Check if venue is assigned to any events
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM event WHERE venue_id = ?");
    $checkStmt->execute([$id]);
    $eventCount = (int)$checkStmt->fetchColumn();

    if ($eventCount > 0) {
        set_flash('warning', "Cannot delete this venue: it is currently booked for {$eventCount} event(s). Please reassign those events first.");
        header('Location: ' . url('admin/venues/index.php'));
        exit();
    }

    $nameStmt = $pdo->prepare("SELECT venue_name FROM venue WHERE venue_id = ?");
    $nameStmt->execute([$id]);
    $venue = $nameStmt->fetch();

    if (!$venue) {
        set_flash('danger', 'Venue record not found.');
        header('Location: ' . url('admin/venues/index.php'));
        exit();
    }

    $deleteStmt = $pdo->prepare("DELETE FROM venue WHERE venue_id = ?");
    $deleteStmt->execute([$id]);

    set_flash('success', "Venue '{$venue['venue_name']}' was successfully deleted.");
} catch (PDOException $e) {
    set_flash('danger', 'Database constraint error: ' . $e->getMessage());
}

header('Location: ' . url('admin/venues/index.php'));
exit();
