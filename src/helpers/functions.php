<?php
// ── Session ──────────────────────────────────────────────────────────────────
function startSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_set_cookie_params(['lifetime' => SESSION_LIFETIME, 'httponly' => true, 'samesite' => 'Lax']);
        session_start();
    }
}

// ── Auth checks ───────────────────────────────────────────────────────────────
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function isAdminLoggedIn(): bool {
    return isset($_SESSION['admin_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        redirect(APP_URL . '/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    }
}

function requireAdmin(): void {
    if (!isAdminLoggedIn()) {
        redirect(APP_URL . '/admin/login.php');
    }
}

function currentUser(): ?array {
    if (!isLoggedIn()) return null;
    return Database::fetch("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
}

function currentAdmin(): ?array {
    if (!isAdminLoggedIn()) return null;
    return Database::fetch("SELECT * FROM admins WHERE id = ?", [$_SESSION['admin_id']]);
}

// ── Redirect ──────────────────────────────────────────────────────────────────
function redirect(string $url): never {
    header("Location: $url");
    exit;
}

// ── Sanitize / Escape ─────────────────────────────────────────────────────────
function e(mixed $val): string {
    return htmlspecialchars((string)$val, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function sanitize(string $val): string {
    return trim(strip_tags($val));
}

// ── CSRF ──────────────────────────────────────────────────────────────────────
function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">';
}

function verifyCsrf(): void {
    if (!isset($_POST['csrf_token']) || !hash_equals(csrfToken(), $_POST['csrf_token'])) {
        http_response_code(403);
        die("Invalid CSRF token.");
    }
}

// ── Flash messages ────────────────────────────────────────────────────────────
function flash(string $type, string $message): void {
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function getFlash(): array {
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

// ── Settings ──────────────────────────────────────────────────────────────────
function getSetting(string $key, string $default = ''): string {
    static $cache = [];
    if (!isset($cache[$key])) {
        $row = Database::fetch("SELECT setting_value FROM settings WHERE setting_key = ?", [$key]);
        $cache[$key] = $row ? $row['setting_value'] : $default;
    }
    return $cache[$key] ?? $default;
}

function getAllSettings(): array {
    $rows = Database::fetchAll("SELECT setting_key, setting_value FROM settings");
    $settings = [];
    foreach ($rows as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    return $settings;
}

// ── Slug ──────────────────────────────────────────────────────────────────────
function makeSlug(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

function uniqueSlug(string $table, string $baseSlug, int $excludeId = 0): string {
    $slug = $baseSlug;
    $i = 1;
    while (true) {
        $sql = "SELECT id FROM $table WHERE slug = ? AND id != ?";
        $row = Database::fetch($sql, [$slug, $excludeId]);
        if (!$row) break;
        $slug = $baseSlug . '-' . $i++;
    }
    return $slug;
}

// ── Currency ──────────────────────────────────────────────────────────────────
function formatPrice(float $amount): string {
    $symbol = getSetting('currency_symbol', '৳');
    return $symbol . number_format($amount, 2);
}

// ── Order number ──────────────────────────────────────────────────────────────
function generateOrderNumber(): string {
    return 'MBH-' . strtoupper(substr(uniqid(), -6)) . '-' . date('Ymd');
}

// ── Download token ────────────────────────────────────────────────────────────
function generateToken(int $length = 64): string {
    return bin2hex(random_bytes($length / 2));
}

// ── File upload ───────────────────────────────────────────────────────────────
function uploadFile(array $file, string $destination, array $allowedMimes = [], int $maxSize = 0): array {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Upload error: ' . $file['error']];
    }
    if ($maxSize && $file['size'] > $maxSize) {
        return ['success' => false, 'error' => 'File too large.'];
    }
    if ($allowedMimes && !in_array($file['type'], $allowedMimes)) {
        return ['success' => false, 'error' => 'File type not allowed.'];
    }
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = generateToken(32) . '.' . strtolower($ext);
    $path = rtrim($destination, '/') . '/' . $filename;
    if (!is_dir($destination)) mkdir($destination, 0755, true);
    if (!move_uploaded_file($file['tmp_name'], $path)) {
        return ['success' => false, 'error' => 'Failed to move file.'];
    }
    return ['success' => true, 'filename' => $filename, 'path' => $path];
}

// ── Pagination ────────────────────────────────────────────────────────────────
function paginate(string $sql, array $params, int $page, int $perPage = 12): array {
    $countSql = "SELECT COUNT(*) as total FROM (" . $sql . ") as sub";
    $total = (int) Database::fetch($countSql, $params)['total'];
    $totalPages = max(1, (int) ceil($total / $perPage));
    $page = max(1, min($page, $totalPages));
    $offset = ($page - 1) * $perPage;
    $items = Database::fetchAll($sql . " LIMIT $perPage OFFSET $offset", $params);
    return compact('items', 'total', 'totalPages', 'page', 'perPage');
}

// ── Cart helpers ──────────────────────────────────────────────────────────────
function cartCount(): int {
    if (!isLoggedIn()) return 0;
    $row = Database::fetch("SELECT COUNT(*) as c FROM cart_items WHERE user_id = ?", [$_SESSION['user_id']]);
    return (int)($row['c'] ?? 0);
}

function cartItems(): array {
    if (!isLoggedIn()) return [];
    return Database::fetchAll(
        "SELECT ci.*, p.title, p.price, p.slug, ps.image_path as thumbnail
         FROM cart_items ci
         JOIN products p ON p.id = ci.product_id
         LEFT JOIN product_screenshots ps ON ps.product_id = p.id AND ps.sort_order = 0
         WHERE ci.user_id = ?
         GROUP BY ci.id",
        [$_SESSION['user_id']]
    );
}

function cartTotal(): float {
    if (!isLoggedIn()) return 0;
    $row = Database::fetch(
        "SELECT SUM(p.price) as total FROM cart_items ci JOIN products p ON p.id = ci.product_id WHERE ci.user_id = ?",
        [$_SESSION['user_id']]
    );
    return (float)($row['total'] ?? 0);
}

// ── Time ago ──────────────────────────────────────────────────────────────────
function timeAgo(string $datetime): string {
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 2592000) return floor($diff / 86400) . 'd ago';
    return date('M j, Y', strtotime($datetime));
}

// ── Mode ──────────────────────────────────────────────────────────────────────
function isDarkMode(): bool {
    return isset($_COOKIE['dark_mode']) && $_COOKIE['dark_mode'] === '1';
}
