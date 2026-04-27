<?php
/**
 * Secure Download Handler
 * Serves product files only to authenticated buyers with valid tokens.
 * Place product files OUTSIDE webroot for maximum security.
 * This script validates token, logs download, then streams file.
 */
require_once __DIR__ . '/src/init.php';

$token = sanitize($_GET['token'] ?? '');

if (!$token) {
    http_response_code(400);
    die("Invalid request.");
}

if (!isLoggedIn()) {
    redirect(APP_URL . '/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
}

$userId = $_SESSION['user_id'];

$item = Database::fetch(
    "SELECT oi.*, p.file_path, p.title, p.slug, o.user_id, o.payment_status
     FROM order_items oi
     JOIN orders o ON o.id = oi.order_id
     JOIN products p ON p.id = oi.product_id
     WHERE oi.download_token = ?",
    [$token]
);

if (!$item) {
    http_response_code(404);
    die("Download link not found.");
}

if ($item['user_id'] != $userId) {
    http_response_code(403);
    die("Access denied.");
}

if ($item['payment_status'] !== 'paid') {
    http_response_code(403);
    die("Payment not confirmed for this order.");
}

if ($item['download_count'] >= $item['download_limit']) {
    http_response_code(403);
    die("Download limit of {$item['download_limit']} reached for this file.");
}

if ($item['download_expiry'] && strtotime($item['download_expiry']) < time()) {
    http_response_code(403);
    $expDate = date('M j, Y', strtotime($item['download_expiry']));
    die("This download link expired on $expDate. Contact support if you need access.");
}

$filePath = PRODUCT_UPLOAD_PATH . '/' . $item['file_path'];

if (!$item['file_path'] || !file_exists($filePath)) {
    http_response_code(404);
    die("File not found. Please contact support.");
}

// Increment download count
Database::execute(
    "UPDATE order_items SET download_count = download_count + 1 WHERE id = ?",
    [$item['id']]
);

// Stream the file
$filename = basename($filePath);
$mimeType = mime_content_type($filePath) ?: 'application/octet-stream';
$fileSize = filesize($filePath);

// Clear any output buffers
while (ob_get_level()) ob_end_clean();

header('Content-Type: ' . $mimeType);
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . $fileSize);
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Stream in chunks to handle large files
$handle = fopen($filePath, 'rb');
if ($handle) {
    while (!feof($handle)) {
        echo fread($handle, 8192);
        flush();
    }
    fclose($handle);
}
exit;
