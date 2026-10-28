<?php
/**
 * Delete Equipment Handler
 * Event Management System
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_flash('danger', 'Invalid request method.');
    header('Location: ' . url('admin/equipment/index.php'));
    exit();
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    set_flash('danger', 'Security validation failed (Invalid CSRF token).');
    header('Location: ' . url('admin/equipment/index.php'));
    exit();
}

$id = filter_input(INPUT_POST, 'equipment_id', FILTER_VALIDATE_INT);
$redirectEventId = filter_input(INPUT_POST, 'redirect_event_id', FILTER_VALIDATE_INT);

if (!$id) {
    set_flash('danger', 'Invalid equipment ID.');
    header('Location: ' . url('admin/equipment/index.php'));
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT equipment_name FROM equipment WHERE equipment_id = ?");
    $stmt->execute([$id]);
    $equipment = $stmt->fetch();

    if ($equipment) {
        $deleteStmt = $pdo->prepare("DELETE FROM equipment WHERE equipment_id = ?");
        $deleteStmt->execute([$id]);
        set_flash('success', "Equipment '{$equipment['equipment_name']}' was deleted.");
    } else {
        set_flash('danger', 'Equipment record not found.');
    }
} catch (PDOException $e) {
    set_flash('danger', 'Database error: ' . $e->getMessage());
}

if ($redirectEventId) {
    header('Location: ' . url('admin/events/view.php?id=' . $redirectEventId));
} else {
    header('Location: ' . url('admin/equipment/index.php'));
}
exit();
