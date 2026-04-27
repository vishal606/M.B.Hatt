<?php
// MBHaat.com — Main Configuration

define('ROOT_PATH', dirname(__DIR__));
define('APP_VERSION', '1.0.0');

// Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'mbhaat');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// App URL (change to your domain)
define('APP_URL', 'http://localhost/mbhaat');
define('ASSETS_URL', APP_URL . '/assets');
define('UPLOADS_URL', APP_URL . '/uploads');

// Security
define('SECRET_KEY', 'mbhaat_secret_2026_change_this_in_production');
define('HASH_ALGO', PASSWORD_BCRYPT);

// Session
define('SESSION_NAME', 'mbhaat_session');
define('SESSION_LIFETIME', 86400 * 30); // 30 days

// File upload limits
define('MAX_FILE_SIZE', 524288000); // 500 MB
define('MAX_IMAGE_SIZE', 5242880);  // 5 MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);

// Paths
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('PRODUCT_UPLOAD_PATH', UPLOAD_PATH . '/products');
define('AVATAR_UPLOAD_PATH', UPLOAD_PATH . '/avatars');
define('SCREENSHOT_UPLOAD_PATH', UPLOAD_PATH . '/screenshots');

// Error display (set false in production)
define('DEBUG_MODE', true);
