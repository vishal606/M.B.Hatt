<?php
$pageTitle = 'Order Details';
include 'includes/header.php';

$orderId = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $pdo->prepare("SELECT o.*, u.name as user_name, u.email as user_email FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order) {
    $_SESSION['flash_message'] = "Order not found";
    $_SESSION['flash_type'] = "error";
    redirect(APP_URL . '/admin/orders.php');
}

$itemStmt = $pdo->prepare("SELECT oi.*, p.slug FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$itemStmt->execute([$orderId]);
$items = $itemStmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $newStatus = sanitizeInput($_POST['order_status']);
    $pdo->prepare("UPDATE orders SET order_status = ? WHERE id = ?")->execute([$newStatus, $orderId]);
    $_SESSION['flash_message'] = "Order updated";
    $_SESSION['flash_type'] = "success";
    redirect(APP_URL . '/admin/order-detail.php?id=' . $orderId);
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold text-brand-purple">Order #<?php echo $order['order_number']; ?></h4>
    <a href="orders.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card admin-card mb-4">
            <div class="card-header"><h5 class="mb-0 fw-bold">Order Items</h5></div>
            <div class="card-body p-0">
                <table class="table admin-table mb-0">
                    <thead><tr><th>Product</th><th>Price</th><th>Qty</th><th>Total</th></tr></thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?php echo $item['product_name']; ?></td>
                            <td><?php echo formatPrice($item['product_price']); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td class="fw-bold"><?php echo formatPrice($item['product_price'] * $item['quantity']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card admin-card">
            <div class="card-header"><h5 class="mb-0 fw-bold">Order Summary</h5></div>
            <div class="card-body">
                <p><strong>Subtotal:</strong> <?php echo formatPrice($order['subtotal']); ?></p>
                <?php if ($order['coupon_discount'] > 0): ?><p><strong>Discount:</strong> -<?php echo formatPrice($order['coupon_discount']); ?></p><?php endif; ?>
                <p><strong>Total:</strong> <?php echo formatPrice($order['total_amount']); ?></p>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card admin-card mb-4">
            <div class="card-header"><h5 class="mb-0 fw-bold">Customer</h5></div>
            <div class="card-body">
                <p><strong>Name:</strong> <?php echo $order['billing_name']; ?></p>
                <p><strong>Email:</strong> <?php echo $order['billing_email']; ?></p>
                <p><strong>Phone:</strong> <?php echo $order['billing_phone'] ?: 'N/A'; ?></p>
            </div>
        </div>
        
        <div class="card admin-card">
            <div class="card-header"><h5 class="mb-0 fw-bold">Update Status</h5></div>
            <div class="card-body">
                <form method="POST">
                    <select name="order_status" class="form-select mb-3">
                        <option value="pending" <?php echo $order['order_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="processing" <?php echo $order['order_status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                        <option value="completed" <?php echo $order['order_status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $order['order_status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                    <button type="submit" name="update_status" class="btn btn-brand-purple w-100"><i class="fas fa-save me-2"></i>Update</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
