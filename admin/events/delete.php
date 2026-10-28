<?php
/**
 * Delete Event Handler
 * Event Management System
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_flash('danger', 'Invalid request method for deletion.');
    header('Location: ' . url('admin/events/index.php'));
    exit();
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    set_flash('danger', 'Security validation failed (Invalid CSRF token).');
    header('Location: ' . url('admin/events/index.php'));
    exit();
}

$id = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
if (!$id) {
    set_flash('danger', 'Invalid event ID.');
    header('Location: ' . url('admin/events/index.php'));
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT event_name FROM event WHERE event_id = ?");
    $stmt->execute([$id]);
    $event = $stmt->fetch();

    if (!$event) {
        set_flash('danger', 'Event not found.');
        header('Location: ' . url('admin/events/index.php'));
        exit();
    }

    // Delete event (Foreign keys are set to ON DELETE CASCADE for relational child tables)
    $deleteStmt = $pdo->prepare("DELETE FROM event WHERE event_id = ?");
    $deleteStmt->execute([$id]);

    set_flash('success', "Event '{$event['event_name']}' and its related allocations were deleted.");
} catch (PDOException $e) {
    set_flash('danger', 'Database constraint error: ' . $e->getMessage());
}

header('Location: ' . url('admin/events/index.php'));
exit();
