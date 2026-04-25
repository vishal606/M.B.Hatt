<?php
$pageTitle = 'Order Successful';
require_once '../includes/config.php';
requireAuth();
include '../includes/header.php';

$orderNumber = isset($_GET['order_id']) ? sanitizeInput($_GET['order_id']) : '';

if (empty($orderNumber)) {
    redirect(APP_URL . '/user/orders.php');
}

// Get order details
$stmt = $pdo->prepare("SELECT o.* FROM orders o WHERE o.order_number = ? AND o.user_id = ?");
$stmt->execute([$orderNumber, getUserId()]);
$order = $stmt->fetch();

if (!$order) {
    redirect(APP_URL . '/user/orders.php');
}

// Get order items
$itemStmt = $pdo->prepare("SELECT oi.*, p.slug FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$itemStmt->execute([$order['id']]);
$items = $itemStmt->fetchAll();
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="text-center mb-5">
                <div class="order-success-icon mx-auto">
                    <i class="fas fa-check"></i>
                </div>
                <h2 class="fw-bold text-brand-purple">Order Placed Successfully!</h2>
                <p class="text-muted">Thank you for your purchase. Your order has been received and is being processed.</p>
                <div class="mt-4">
                    <span class="badge bg-light text-dark border fs-5 px-4 py-2">
                        Order #<?php echo $orderNumber; ?>
                    </span>
                </div>
            </div>
            
            <!-- Order Status -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold text-brand-purple mb-1">Order Status</h5>
                            <span class="badge bg-warning"><?php echo ucfirst($order['order_status']); ?></span>
                        </div>
                        <div class="text-end">
                            <h5 class="fw-bold text-brand-purple mb-1">Payment Status</h5>
                            <span class="badge bg-<?php echo $order['payment_status'] === 'completed' ? 'success' : ($order['payment_status'] === 'pending' ? 'warning' : 'danger'); ?>">
                                <?php echo ucfirst($order['payment_status']); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Order Details -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="fw-bold text-brand-purple">Order Details</h5>
                </div>
                <div class="card-body px-4 pb-4">
                    <table class="table table-borderless">
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <span class="fw-bold"><?php echo $item['product_name']; ?></span>
                                <small class="text-muted d-block">Qty: <?php echo $item['quantity']; ?></small>
                            </td>
                            <td class="text-end"><?php echo formatPrice($item['product_price'] * $item['quantity']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="border-top">
                            <td class="text-muted">Subtotal</td>
                            <td class="text-end"><?php echo formatPrice($order['subtotal']); ?></td>
                        </tr>
                        <?php if ($order['coupon_discount'] > 0): ?>
                        <tr>
                            <td class="text-muted">Discount</td>
                            <td class="text-end text-success">-<?php echo formatPrice($order['coupon_discount']); ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($order['tax_amount'] > 0): ?>
                        <tr>
                            <td class="text-muted">Tax</td>
                            <td class="text-end"><?php echo formatPrice($order['tax_amount']); ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr class="border-top">
                            <td class="fw-bold">Total</td>
                            <td class="text-end fw-bold text-brand-blue fs-5"><?php echo formatPrice($order['total_amount']); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <!-- Payment Instructions -->
            <?php if ($order['payment_method'] === 'bkash' || $order['payment_method'] === 'nagad' || $order['payment_method'] === 'bank'): ?>
            <div class="card border-0 shadow-sm rounded-4 mb-4" style="background-color: #F7F6E5;">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-brand-purple mb-3">
                        <i class="fas fa-info-circle me-2"></i>Payment Instructions
                    </h5>
                    <?php if ($order['payment_method'] === 'bkash'): ?>
                    <p>Please send payment to <strong>bKash: 01XXXXXXXXX</strong> (Merchant)</p>
                    <p>Reference: <strong><?php echo $orderNumber; ?></strong></p>
                    <?php elseif ($order['payment_method'] === 'nagad'): ?>
                    <p>Please send payment to <strong>Nagad: 01XXXXXXXXX</strong></p>
                    <p>Reference: <strong><?php echo $orderNumber; ?></strong></p>
                    <?php elseif ($order['payment_method'] === 'bank'): ?>
                    <p>Please transfer to:</p>
                    <p><strong>Bank: Example Bank Ltd.</strong></p>
                    <p><strong>Account: XXXXXXXXXXXX</strong></p>
                    <p><strong>Reference: <?php echo $orderNumber; ?></strong></p>
                    <?php endif; ?>
                    <p class="mb-0">After payment, your order will be processed within 24 hours.</p>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Actions -->
            <div class="d-flex gap-3 justify-content-center">
                <a href="../user/orders.php" class="btn btn-brand-blue">
                    <i class="fas fa-box me-2"></i>View My Orders
                </a>
                <a href="products.php" class="btn btn-brand-purple">
                    <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
                </a>
                <button onclick="window.print()" class="btn btn-outline-secondary">
                    <i class="fas fa-print me-2"></i>Print Receipt
                </button>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
