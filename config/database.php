<?php
/**
 * Database Configuration & Connection
 * Event Management System
 */

// Database credentials for XAMPP
define('DB_HOST', 'localhost');
define('DB_NAME', 'event_management_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Dynamic base path determination so assets and links work in any folder
$scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
$scriptDir = str_replace('\\', '/', $scriptDir);

// Detect if we are inside admin or a subfolder
if (preg_match('#^(.*?/event-management)#i', $scriptDir, $matches)) {
    $baseUrl = $matches[1];
} elseif ($scriptDir === '/' || $scriptDir === '') {
    $baseUrl = '';
} else {
    // If placed in root or custom folder name, find base
    $parts = explode('/', trim($scriptDir, '/'));
    $baseUrl = (!empty($parts[0])) ? '/' . $parts[0] : '';
}
define('BASE_URL', rtrim($baseUrl, '/'));

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => true,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // If the database does not exist yet, show a user-friendly setup screen
    $errorCode = $e->getCode();
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Database Setup Required - Event Management System</title>
        <style>
            body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #0f172a; color: #f8fafc; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; box-sizing: border-box; }
            .setup-card { background: #1e293b; border-radius: 12px; max-width: 650px; width: 100%; padding: 32px; border: 1px solid #334155; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.5); }
            h1 { color: #f59e0b; font-size: 24px; margin-top: 0; }
            p { color: #94a3b8; line-height: 1.6; }
            ol { color: #cbd5e1; line-height: 1.8; padding-left: 20px; }
            code { background: #0f172a; color: #38bdf8; padding: 2px 6px; border-radius: 4px; font-family: monospace; }
            .err { background: #451a1a; color: #f87171; padding: 12px; border-radius: 6px; font-size: 13px; margin: 15px 0; word-break: break-all; }
        </style>
    </head>
    <body>
        <div class="setup-card">
            <h1>Database Connection Notice</h1>
            <p>Could not connect to the database <code><?= htmlspecialchars(DB_NAME) ?></code>. This usually means either MySQL isn't running yet in XAMPP or the database hasn't been imported yet.</p>
            <div class="err"><strong>MySQL Error:</strong> <?= htmlspecialchars($e->getMessage()) ?></div>
            <h3>Quick 2-Minute Setup Steps:</h3>
            <ol>
                <li>Open your <strong>XAMPP Control Panel</strong> and click <strong>Start</strong> on both <strong>Apache</strong> and <strong>MySQL</strong>.</li>
                <li>Open your browser and navigate to <a href="http://localhost/phpmyadmin" target="_blank" style="color: #60a5fa;">http://localhost/phpmyadmin</a>.</li>
                <li>Click on the <strong>Import</strong> tab at the top.</li>
                <li>Click <strong>Choose File</strong> and select <code>database/event_management.sql</code> from this project folder.</li>
                <li>Scroll down and click <strong>Import</strong> / <strong>Go</strong>.</li>
                <li>Refresh this page!</li>
            </ol>
        </div>
    </body>
    </html>
    <?php
    exit();
}
