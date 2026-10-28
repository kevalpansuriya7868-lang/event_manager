<?php
/**
 * Database Configuration & Connection
 * Event Management System
 * Supports both Local (127.0.0.1 / localhost) and Global Cloud (Railway / Environment Variables)
 */

// 1. Detect Database Credentials from Environment Variables (Railway / Cloud) or fallback to Local 127.0.0.1 (XAMPP)
$dbHost = getenv('MYSQLHOST') ?: getenv('DB_HOST') ?: '127.0.0.1';
$dbPort = getenv('MYSQLPORT') ?: getenv('DB_PORT') ?: '3306';
$dbName = getenv('MYSQLDATABASE') ?: getenv('DB_NAME') ?: 'event_management_db';
$dbUser = getenv('MYSQLUSER') ?: getenv('DB_USER') ?: 'root';
$dbPass = getenv('MYSQLPASSWORD') !== false ? getenv('MYSQLPASSWORD') : (getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
$dbCharset = 'utf8mb4';

// Check if a single connection URL is provided (DATABASE_URL or MYSQL_URL)
$dbUrl = getenv('DATABASE_URL') ?: getenv('MYSQL_URL');
if ($dbUrl) {
    $parsedUrl = parse_url($dbUrl);
    if (!empty($parsedUrl['host'])) {
        $dbHost = $parsedUrl['host'];
    }
    if (!empty($parsedUrl['port'])) {
        $dbPort = (string)$parsedUrl['port'];
    }
    if (!empty($parsedUrl['user'])) {
        $dbUser = urldecode($parsedUrl['user']);
    }
    if (isset($parsedUrl['pass'])) {
        $dbPass = urldecode($parsedUrl['pass']);
    }
    if (!empty($parsedUrl['path'])) {
        $dbName = ltrim($parsedUrl['path'], '/');
    }
}

define('DB_HOST', $dbHost);
define('DB_PORT', $dbPort);
define('DB_NAME', $dbName);
define('DB_USER', $dbUser);
define('DB_PASS', $dbPass);
define('DB_CHARSET', $dbCharset);

// 2. Dynamic Base URL Detection
$scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
$scriptDir = str_replace('\\', '/', $scriptDir);

if (preg_match('#^(.*?/event-management)#i', $scriptDir, $matches)) {
    // When placed in an 'event-management' subdirectory (e.g., XAMPP htdocs)
    $baseUrl = $matches[1];
} elseif ($scriptDir === '/' || $scriptDir === '' || $scriptDir === '.') {
    // Root-level deployment (e.g. Railway, PHP built-in server, production domains)
    $baseUrl = '';
} else {
    // If inside /admin or other subdirectories at root level
    $parts = array_values(array_filter(explode('/', $scriptDir)));
    if (!empty($parts) && $parts[0] !== 'admin') {
        $baseUrl = '/' . $parts[0];
    } else {
        $baseUrl = '';
    }
}
define('BASE_URL', rtrim($baseUrl, '/'));

// 3. Establish PDO Database Connection with Automatic Fallback & Auto-Seeding
$pdo = null;
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => true,
    PDO::ATTR_TIMEOUT            => 5,
];

try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        // If database does not exist yet (code 1049: Unknown database), try to auto-create it
        if ($e->getCode() == 1049 || stripos($e->getMessage(), 'Unknown database') !== false) {
            $rootDsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=" . DB_CHARSET;
            $tempPdo = new PDO($rootDsn, DB_USER, DB_PASS, $options);
            $tempPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            unset($tempPdo);
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } elseif (DB_HOST === '127.0.0.1' && empty(getenv('MYSQLHOST')) && empty(getenv('DATABASE_URL'))) {
            // Local fallback attempt: try 'localhost' if 127.0.0.1 socket gave refusal
            $localDsn = "mysql:host=localhost;port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $pdo = new PDO($localDsn, DB_USER, DB_PASS, $options);
        } else {
            throw $e;
        }
    }

    // Auto-seed schema and initial sample records if tables don't exist yet (works automatically both on Local & Railway)
    try {
        $checkStmt = $pdo->query("SHOW TABLES LIKE 'event'");
        if ($checkStmt && !$checkStmt->fetch()) {
            $sqlFile = __DIR__ . '/../database/event_management.sql';
            if (file_exists($sqlFile)) {
                $sqlContent = file_get_contents($sqlFile);
                // Strip raw CREATE DATABASE and USE statements so queries run inside the active connected database
                $sqlContent = preg_replace('/^\s*CREATE\s+DATABASE.*?;/mi', '', $sqlContent);
                $sqlContent = preg_replace('/^\s*USE\s+.*?;/mi', '', $sqlContent);
                $pdo->exec($sqlContent);
            }
        }
    } catch (Throwable $seedError) {
        // Non-fatal, let application proceed
    }

} catch (Throwable $e) {
    $isCloud = getenv('RAILWAY_ENVIRONMENT') || getenv('MYSQLHOST') || getenv('DATABASE_URL');
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Database Setup Required - EventHub</title>
        <style>
            * { box-sizing: border-box; }
            body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #0f172a; color: #f8fafc; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; }
            .setup-card { background: #1e293b; border-radius: 12px; max-width: 680px; width: 100%; padding: 32px; border: 1px solid #334155; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.5); }
            h1 { color: #f59e0b; font-size: 22px; margin: 0 0 10px 0; }
            p { color: #94a3b8; line-height: 1.6; font-size: 14px; margin: 8px 0; }
            ol { color: #cbd5e1; line-height: 1.8; padding-left: 20px; font-size: 14px; }
            code { background: #0b1120; color: #38bdf8; padding: 2px 6px; border-radius: 4px; font-family: monospace; font-size: 13px; }
            .err { background: #451a1a; color: #fca5a5; padding: 12px; border-radius: 6px; font-size: 13px; margin: 16px 0; border: 1px solid #7f1d1d; word-break: break-all; }
        </style>
    </head>
    <body>
        <div class="setup-card">
            <h1>Database Connection Notice</h1>
            <p>Could not connect to the database <code><?= htmlspecialchars(DB_NAME) ?></code> on <code><?= htmlspecialchars(DB_HOST . ':' . DB_PORT) ?></code>.</p>
            <div class="err"><strong>Error:</strong> <?= htmlspecialchars($e->getMessage()) ?></div>

            <?php if ($isCloud): ?>
                <h3>Steps to connect MySQL in Railway:</h3>
                <ol>
                    <li>In your Railway Project Dashboard, click the <strong>+</strong> (Add) button.</li>
                    <li>Select <strong>Database</strong> &rarr; <strong>Add MySQL</strong>.</li>
                    <li>Railway will generate the database and EventHub will automatically connect and seed all tables!</li>
                </ol>
            <?php else: ?>
                <h3>Quick 2-Minute Setup Steps for XAMPP:</h3>
                <ol>
                    <li>Open your <strong>XAMPP Control Panel</strong> and click <strong>Start</strong> on both <strong>Apache</strong> and <strong>MySQL</strong>.</li>
                    <li>Open your browser and navigate to <a href="http://localhost:8080/phpmyadmin" target="_blank" style="color: #60a5fa;">http://localhost:8080/phpmyadmin</a> (or <a href="http://localhost/phpmyadmin" target="_blank" style="color: #60a5fa;">http://localhost/phpmyadmin</a>).</li>
                    <li>Click on the <strong>Import</strong> tab at the top.</li>
                    <li>Click <strong>Choose File</strong> and select <code>database/event_management.sql</code> from this project folder.</li>
                    <li>Scroll down and click <strong>Import</strong> / <strong>Go</strong>.</li>
                    <li>Refresh this page!</li>
                </ol>
            <?php endif; ?>
        </div>
    </body>
    </html>
    <?php
    exit();
}
