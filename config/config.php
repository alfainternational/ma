<?php
/**
 * General Application Configuration
 * Marketing AI System
 */

// Prevent direct access
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// Load database config
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/config/constants.php';

// Application Settings
define('APP_NAME', 'Marketing AI System');
define('APP_NAME_AR', 'نظام الذكاء الاصطناعي التسويقي');
define('APP_VERSION', '1.0.0');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost/ma');
define('APP_ENV', getenv('APP_ENV') ?: 'development');
define('APP_DEBUG', getenv('APP_DEBUG') === 'true');
define('APP_LANG', 'ar');
define('APP_DIR', 'rtl');

// Session Configuration
define('SESSION_LIFETIME', 7200); // 2 hours
define('SESSION_NAME', 'mai_session');

// Security
define('CSRF_TOKEN_NAME', '_csrf_token');
define('PASSWORD_COST', 12);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 900); // 15 minutes
define('JWT_SECRET', getenv('JWT_SECRET') ?: 'change-this-secret-key');

// Upload Settings
define('UPLOAD_DIR', BASE_PATH . '/assets/uploads/');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf']);

// AI System Settings
define('CONFIDENCE_THRESHOLD', 0.75);
define('AUTO_INFERENCE', true);
define('ADAPTIVE_QUESTIONING', true);
define('REAL_TIME_ALERTS', true);

// Timezone
date_default_timezone_set('Asia/Riyadh');

// Error Reporting
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// Start Session
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_samesite', 'Strict');
    session_name(SESSION_NAME);
    session_start();
}

// Security Headers
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Autoload classes
spl_autoload_register(function ($class) {
    $paths = [
        BASE_PATH . '/classes/',
        BASE_PATH . '/classes/ai-engine/',
        BASE_PATH . '/classes/ai-engine/experts/',
        BASE_PATH . '/classes/helpers/',
    ];

    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});
