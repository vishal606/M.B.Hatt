<?php
require_once __DIR__ . '/../src/init.php';
requireAdmin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid method.']);
    exit;
}

verifyCsrf();

$action = sanitize($_POST['action'] ?? '');
$id     = (int)($_POST['id'] ?? 0);
$type   = sanitize($_POST['type'] ?? '');

if ($action === 'toggle_status') {
    $tables = ['product' => 'products', 'user' => 'users', 'coupon' => 'coupons', 'faq' => 'faqs', 'testimonial' => 'testimonials'];
    $cols   = ['product' => 'status', 'user' => 'is_blocked', 'coupon' => 'is_active', 'faq' => 'is_active', 'testimonial' => 'is_active'];

    if (!isset($tables[$type])) {
        echo json_encode(['success' => false, 'message' => 'Unknown type.']);
        exit;
    }

    $table = $tables[$type];
    $col   = $cols[$type];

    if ($type === 'product') {
        Database::execute("UPDATE $table SET $col = IF($col='active','inactive','active') WHERE id=?", [$id]);
    } else {
        Database::execute("UPDATE $table SET $col = IF($col=1,0,1) WHERE id=?", [$id]);
    }

    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unknown action.']);
