<?php
require_once '../includes/config.php';
requireAuth();

$token = $_GET['token'] ?? '';

if (empty($token)) {
    $_SESSION['flash_message'] = "Invalid download link";
    $_SESSION['flash_type'] = "error";
    redirect(APP_URL . '/user/downloads.php');
}

// Get order item
$stmt = $pdo->prepare("SELECT oi.*, p.file_path, o.user_id, o.order_status 
                      FROM order_items oi 
                      JOIN products p ON oi.product_id = p.id 
                      JOIN orders o ON oi.order_id = o.id 
                      WHERE oi.download_token = ?");
$stmt->execute([$token]);
$item = $stmt->fetch();

if (!$item) {
    $_SESSION['flash_message'] = "Download not found";
    $_SESSION['flash_type'] = "error";
    redirect(APP_URL . '/user/downloads.php');
}

// Verify ownership
if ($item['user_id'] != getUserId()) {
    $_SESSION['flash_message'] = "Unauthorized download";
    $_SESSION['flash_type'] = "error";
    redirect(APP_URL . '/user/downloads.php');
}

// Verify order status
if ($item['order_status'] !== 'completed') {
    $_SESSION['flash_message'] = "Order not yet confirmed";
    $_SESSION['flash_type'] = "warning";
    redirect(APP_URL . '/user/orders.php');
}

// Check expiry
if ($item['download_expires'] && strtotime($item['download_expires']) < time()) {
    $_SESSION['flash_message'] = "Download link has expired";
    $_SESSION['flash_type'] = "error";
    redirect(APP_URL . '/user/downloads.php');
}

// Check download limit
if ($item['download_limit'] > 0 && $item['download_count'] >= $item['download_limit']) {
    $_SESSION['flash_message'] = "Download limit reached";
    $_SESSION['flash_type'] = "error";
    redirect(APP_URL . '/user/downloads.php');
}

// Get file path
$filePath = '../' . $item['file_path'];

if (!file_exists($filePath)) {
    $_SESSION['flash_message'] = "File not found on server";
    $_SESSION['flash_type'] = "error";
    redirect(APP_URL . '/user/downloads.php');
}

// Update download count
$pdo->prepare("UPDATE order_items SET download_count = download_count + 1 WHERE id = ?")
    ->execute([$item['id']]);

// Log download
$pdo->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address, user_agent) VALUES (?, 'product_download', ?, ?, ?)")
    ->execute([getUserId(), 'Downloaded: ' . $item['product_name'], $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']]);

// Send file
$filename = basename($filePath);
$fileSize = filesize($filePath);
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $filePath);
finfo_close($finfo);

header('Content-Type: ' . $mimeType);
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . $fileSize);
header('Cache-Control: no-cache, must-revalidate');

readfile($filePath);
exit;
