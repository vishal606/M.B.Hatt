<?php
/**
 * MBHaat.com - Configuration File
 * Digital Product Selling Platform
 */

// Error Reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Configuration
$DB_HOST = 'localhost';
$DB_NAME = 'mbhaat_db';
$DB_USER = 'root';
$DB_PASS = '';
$DB_CHARSET = 'utf8mb4';

// Application Configuration
define('APP_NAME', 'MBHaat.com');
define('APP_URL', 'http://localhost/M.B>Hatt');
define('APP_VERSION', '1.0.0');
define('APP_TIMEZONE', 'Asia/Dhaka');

// Set timezone
date_default_timezone_set(APP_TIMEZONE);

// Brand Colors
define('COLOR_BEIGE', '#F7F6E5');
define('COLOR_BLUE', '#76D2DB');
define('COLOR_RED', '#DA4848');
define('COLOR_PURPLE', '#36064D');

// Session Configuration
ini_set('session.cookie_path', '/');
ini_set('session.cookie_secure', false);
ini_set('session.cookie_httponly', true);
session_start();

// Database Connection
try {
    $pdo = new PDO(
        "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=$DB_CHARSET",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Helper Functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getUserName() {
    return $_SESSION['user_name'] ?? 'Guest';
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function generateToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

function formatPrice($price) {
    return number_format($price, 2) . ' BDT';
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function showAlert($message, $type = 'info') {
    $colors = [
        'success' => 'alert-success',
        'error' => 'alert-danger',
        'warning' => 'alert-warning',
        'info' => 'alert-primary'
    ];
    $color = $colors[$type] ?? $colors['info'];
    
    return "<div class='alert $color alert-dismissible fade show' role='alert'>
            $message
            <button type='button' class='btn-close' data-mdb-dismiss='alert'></button>
          </div>";
}

function getSettings($key = null) {
    global $pdo;
    
    if ($key) {
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        return $result ? $result['setting_value'] : null;
    }
    
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
    return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
}

// Cart Functions
function getCart() {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    return $_SESSION['cart'];
}

function addToCart($productId, $quantity = 1) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] += $quantity;
    } else {
        $_SESSION['cart'][$productId] = $quantity;
    }
}

function removeFromCart($productId) {
    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
    }
}

function clearCart() {
    $_SESSION['cart'] = [];
}

function getCartCount() {
    $cart = getCart();
    return array_sum($cart);
}

function getCartTotal() {
    global $pdo;
    $cart = getCart();
    $total = 0;
    
    foreach ($cart as $productId => $quantity) {
        $stmt = $pdo->prepare("SELECT price FROM products WHERE id = ? AND status = 'active'");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
        if ($product) {
            $total += $product['price'] * $quantity;
        }
    }
    
    return $total;
}

// Security Functions
function csrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = generateToken();
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function requireAuth() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        redirect(APP_URL . '/pages/login.php');
    }
}

function requireAdmin() {
    if (!isLoggedIn()) {
        redirect(APP_URL . '/admin/login.php');
    }
    if (!isAdmin()) {
        redirect(APP_URL . '/user/dashboard.php');
    }
}
?>
