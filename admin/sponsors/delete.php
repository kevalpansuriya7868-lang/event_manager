<?php
/**
 * Delete Sponsor Handler
 * Event Management System
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_flash('danger', 'Invalid request method.');
    header('Location: ' . url('admin/sponsors/index.php'));
    exit();
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    set_flash('danger', 'Security validation failed (Invalid CSRF token).');
    header('Location: ' . url('admin/sponsors/index.php'));
    exit();
}

$id = filter_input(INPUT_POST, 'sponsor_id', FILTER_VALIDATE_INT);
if (!$id) {
    set_flash('danger', 'Invalid sponsor ID.');
    header('Location: ' . url('admin/sponsors/index.php'));
    exit();
}

try {
    // Check if sponsor is assigned to any events
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM event_sponsor WHERE sponsor_id = ?");
    $checkStmt->execute([$id]);
    $assignedCount = (int)$checkStmt->fetchColumn();

    if ($assignedCount > 0) {
        set_flash('warning', "Cannot delete sponsor: This sponsor has active sponsorships in {$assignedCount} event(s). Please remove their sponsorship agreements first.");
        header('Location: ' . url('admin/sponsors/index.php'));
        exit();
    }

    $nameStmt = $pdo->prepare("SELECT sponsor_name FROM sponsor WHERE sponsor_id = ?");
    $nameStmt->execute([$id]);
    $sponsor = $nameStmt->fetch();

    if (!$sponsor) {
        set_flash('danger', 'Sponsor record not found.');
        header('Location: ' . url('admin/sponsors/index.php'));
        exit();
    }

    $deleteStmt = $pdo->prepare("DELETE FROM sponsor WHERE sponsor_id = ?");
    $deleteStmt->execute([$id]);

    set_flash('success', "Sponsor '{$sponsor['sponsor_name']}' was successfully deleted.");
} catch (PDOException $e) {
    set_flash('danger', 'Database constraint error: ' . $e->getMessage());
}

header('Location: ' . url('admin/sponsors/index.php'));
exit();
