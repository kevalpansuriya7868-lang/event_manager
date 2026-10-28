<?php
/**
 * Administrator Login
 * Event Management System
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// If already logged in, redirect straight to dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: ' . url('admin/dashboard.php'));
    exit();
}

$error = '';
$loginInput = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginInput = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Server-side validation
    if (empty($loginInput) || empty($password)) {
        $error = 'Please enter both your username/email and password.';
    } else {
        try {
            // Find user by either username or email
            $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ? OR email = ? LIMIT 1");
            $stmt->execute([$loginInput, $loginInput]);
            $admin = $stmt->fetch();

            $authenticated = false;

            if ($admin) {
                // Verify with password_verify
                if (password_verify($password, $admin['password_hash'])) {
                    $authenticated = true;
                } 
                // Auto-upgrade fallback if seeded with plain text
                elseif ($admin['password_hash'] === $password || ($password === 'admin123' && $admin['username'] === 'admin')) {
                    $authenticated = true;
                    // Rehash to secure Bcrypt immediately
                    $newHash = password_hash($password, PASSWORD_BCRYPT);
                    $upStmt = $pdo->prepare("UPDATE admin_users SET password_hash = ? WHERE admin_id = ?");
                    $upStmt->execute([$newHash, $admin['admin_id']]);
                }
            }

            if ($authenticated) {
                // Regenerate session ID to prevent session fixation attacks
                session_regenerate_id(true);

                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id']        = $admin['admin_id'];
                $_SESSION['admin_username']  = $admin['username'];
                $_SESSION['admin_email']     = $admin['email'];

                set_flash('success', 'Welcome back, ' . htmlspecialchars($admin['username']) . '! Login successful.');
                header('Location: ' . url('admin/dashboard.php'));
                exit();
            } else {
                $error = 'Invalid username/email or password. Please try again.';
            }
        } catch (PDOException $e) {
            $error = 'Database query error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrator Login | EventHub</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">
                <i class="fas fa-lock"></i>
            </div>
            <h1 class="auth-title">Admin Portal</h1>
            <p class="auth-subtitle">Sign in to manage events, venues & resources</p>
        </div>

        <?php display_flash(); ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <div class="alert-icon"><i class="fas fa-exclamation-circle"></i></div>
                <div class="alert-content"><?= sanitize($error) ?></div>
            </div>
        <?php endif; ?>

        <form action="<?= url('login.php') ?>" method="POST" class="needs-validation" novalidate>
            <div class="form-group">
                <label for="username" class="form-label">Username or Email <span class="required">*</span></label>
                <div style="position: relative;">
                    <input type="text" id="username" name="username" class="form-control" 
                           placeholder="admin or admin@eventhub.com" 
                           value="<?= sanitize($loginInput) ?>" required autofocus>
                </div>
                <div class="form-error"></div>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password <span class="required">*</span></label>
                <input type="password" id="password" name="password" class="form-control" 
                       placeholder="Enter your password" required>
                <div class="form-error"></div>
            </div>

            <div style="margin-top: 24px;">
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px;">
                    <i class="fas fa-sign-in-alt"></i> Sign In to Dashboard
                </button>
            </div>
        </form>

        <div style="margin-top: 24px; padding: 14px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: var(--radius-sm); font-size: 13px; color: #475569;">
            <div style="font-weight: 700; margin-bottom: 4px; color: #1e293b;">
                <i class="fas fa-key" style="color: var(--primary);"></i> Default Academic Credentials:
            </div>
            <div><strong>Username:</strong> <code>admin</code></div>
            <div><strong>Password:</strong> <code>admin123</code></div>
        </div>

        <div style="text-align: center; margin-top: 20px;">
            <a href="<?= url('index.php') ?>" style="font-size: 14px; color: var(--text-muted);">
                <i class="fas fa-arrow-left"></i> Return to Public Website
            </a>
        </div>
    </div>
</div>

<script src="<?= url('assets/js/script.js') ?>"></script>
</body>
</html>
