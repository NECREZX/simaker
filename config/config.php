<?php
/**
 * SIMAKER Configuration File
 * Sistem Informasi Monitoring Aktivitas Kerja
 */

// Application Settings
define('APP_NAME', 'SIMAKER');
define('APP_FULL_NAME', 'Sistem Informasi Monitoring Aktivitas Kerja');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/simaker');
define('APP_SECRET', 'your-secret-key-' . md5(__DIR__));


// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'simaker');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
session_save_path(sys_get_temp_dir());

// Session timeout (30 minutes)
define('SESSION_TIMEOUT', 1800);

// File Upload Settings
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB in bytes
define('UPLOAD_ALLOWED_TYPES', ['jpg', 'jpeg', 'png', 'pdf']);
define('UPLOAD_PATH', __DIR__ . '/../uploads/logbook/');

// Pagination
define('RECORDS_PER_PAGE', 10);

// Emerald Color Scheme
define('COLOR_PRIMARY', '#10b98162');
define('COLOR_PRIMARY_DARK', '#10b98162');
define('COLOR_PRIMARY_DARKER', '#0478573e');
define('COLOR_SECONDARY', '#6b7280');

// Path Constants
define('BASE_PATH', dirname(__DIR__));
define('ASSETS_PATH', APP_URL . '/assets');
define('UPLOADS_URL', APP_URL . '/uploads');

// Error Reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    
    // Check session timeout
    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > SESSION_TIMEOUT)) {
        session_unset();
        session_destroy();
        header('Location: ' . APP_URL . '/index.php?timeout=1');
        exit();
    }
    $_SESSION['LAST_ACTIVITY'] = time();
}

// CSRF Token Helper
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
