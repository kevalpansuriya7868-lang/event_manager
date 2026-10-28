<?php
/**
 * Delete Staff Handler
 * Event Management System
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_flash('danger', 'Invalid request method.');
    header('Location: ' . url('admin/staff/index.php'));
    exit();
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    set_flash('danger', 'Security validation failed (Invalid CSRF token).');
    header('Location: ' . url('admin/staff/index.php'));
    exit();
}

$id = filter_input(INPUT_POST, 'staff_id', FILTER_VALIDATE_INT);
$redirectEventId = filter_input(INPUT_POST, 'redirect_event_id', FILTER_VALIDATE_INT);

if (!$id) {
    set_flash('danger', 'Invalid staff ID.');
    header('Location: ' . url('admin/staff/index.php'));
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT staff_name FROM staff WHERE staff_id = ?");
    $stmt->execute([$id]);
    $staff = $stmt->fetch();

    if ($staff) {
        $deleteStmt = $pdo->prepare("DELETE FROM staff WHERE staff_id = ?");
        $deleteStmt->execute([$id]);
        set_flash('success', "Staff member '{$staff['staff_name']}' was removed.");
    } else {
        set_flash('danger', 'Staff record not found.');
    }
} catch (PDOException $e) {
    set_flash('danger', 'Database error: ' . $e->getMessage());
}

if ($redirectEventId) {
    header('Location: ' . url('admin/events/view.php?id=' . $redirectEventId));
} else {
    header('Location: ' . url('admin/staff/index.php'));
}
exit();
