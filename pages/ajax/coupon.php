<?php
require_once '../../includes/config.php';

header('Content-Type: application/json');

$code = sanitizeInput($_POST['code'] ?? '');
$total = floatval($_POST['total'] ?? 0);

$response = ['valid' => false, 'message' => ''];

if (empty($code)) {
    $response['message'] = 'Please enter a coupon code';
    echo json_encode($response);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM coupons WHERE code = ? AND status = 'active'");
$stmt->execute([$code]);
$coupon = $stmt->fetch();

if (!$coupon) {
    $response['message'] = 'Invalid coupon code';
    echo json_encode($response);
    exit;
}

// Check expiry
if ($coupon['end_date'] && strtotime($coupon['end_date']) < strtotime('today')) {
    $response['message'] = 'Coupon has expired';
    echo json_encode($response);
    exit;
}

// Check start date
if ($coupon['start_date'] && strtotime($coupon['start_date']) > strtotime('today')) {
    $response['message'] = 'Coupon is not yet active';
    echo json_encode($response);
    exit;
}

// Check usage limit
if ($coupon['usage_limit'] !== null && $coupon['usage_count'] >= $coupon['usage_limit']) {
    $response['message'] = 'Coupon usage limit reached';
    echo json_encode($response);
    exit;
}

// Check minimum order amount
if ($coupon['min_order_amount'] > 0 && $total < $coupon['min_order_amount']) {
    $response['message'] = 'Minimum order amount of ' . formatPrice($coupon['min_order_amount']) . ' required';
    echo json_encode($response);
    exit;
}

// Calculate discount
$discount = 0;
if ($coupon['type'] === 'percentage') {
    $discount = $total * ($coupon['value'] / 100);
    if ($coupon['max_discount'] && $discount > $coupon['max_discount']) {
        $discount = $coupon['max_discount'];
    }
} else {
    $discount = min($coupon['value'], $total);
}

$newTotal = max(0, $total - $discount);

$response['valid'] = true;
$response['discount'] = $discount;
$response['discount_formatted'] = formatPrice($discount);
$response['new_total'] = $newTotal;
$response['new_total_formatted'] = formatPrice($newTotal);
$response['message'] = 'Coupon applied successfully';

echo json_encode($response);
