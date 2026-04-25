<?php
$pageTitle = 'Order Details';
require_once '../includes/config.php';
requireAuth();
include '../includes/header.php';

$userId = getUserId();
$orderId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get order
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$orderId, $userId]);
$order = $stmt->fetch();

if (!$order) {
    $_SESSION['flash_message'] = "Order not found";
    $_SESSION['flash_type'] = "error";
    redirect(APP_URL . '/user/orders.php');
}

// Get order items
$itemStmt = $pdo->prepare("SELECT oi.*, p.slug, p.thumbnail FROM order_items oi 
                          JOIN products p ON oi.product_id = p.id 
                          WHERE oi.order_id = ?");
$itemStmt->execute([$orderId]);
$items = $itemStmt->fetchAll();
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="orders.php">My Orders</a></li>
            <li class="breadcrumb-item active">Order #<?php echo $order['order_number']; ?></li>
        </ol>
    </nav>
    
    <h3 class="fw-bold text-brand-purple mb-4">
        <i class="fas fa-file-invoice me-2"></i>Order #<?php echo $order['order_number']; ?>
    </h3>
    
    <div class="row">
        <div class="col-lg-8">
            <!-- Order Status -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Order Status</h6>
                            <span class="badge bg-<?php 
                                echo $order['order_status'] === 'completed' ? 'success' : 
                                     ($order['order_status'] === 'processing' ? 'warning' : 
                                     ($order['order_status'] === 'cancelled' ? 'danger' : 'secondary')); ?> fs-6">
                                <?php echo ucfirst($order['order_status']); ?>
                            </span>
                        </div>
                        <div class="text-end">
                            <h6 class="text-muted mb-1">Order Date</h6>
                            <span class="fw-bold"><?php echo date('F d, Y', strtotime($order['created_at'])); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Order Items -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="fw-bold text-brand-purple mb-0">Order Items</h5>
                </div>
                <div class="card-body px-4 pb-4">
                    <?php foreach ($items as $item): ?>
                    <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                        <img src="<?php echo $item['thumbnail'] ? APP_URL . '/' . $item['thumbnail'] : 'https://via.placeholder.com/80?text=No+Image'; ?>" 
                             alt="<?php echo $item['product_name']; ?>" class="rounded-3 me-3" style="width: 80px; height: 60px; object-fit: cover;">
                        <div class="flex-fill">
                            <h6 class="fw-bold text-brand-purple mb-1">
                                <a href="../pages/product.php?slug=<?php echo $item['slug']; ?>" class="text-decoration-none">
                                    <?php echo $item['product_name']; ?>
                                </a>
                            </h6>
                            <small class="text-muted">Qty: <?php echo $item['quantity']; ?> × <?php echo formatPrice($item['product_price']); ?></small>
                        </div>
                        <div class="text-end">
                            <span class="fw-bold"><?php echo formatPrice($item['product_price'] * $item['quantity']); ?></span>
                            <?php if ($order['order_status'] === 'completed'): ?>
                            <br>
                            <a href="download.php?token=<?php echo $item['download_token']; ?>" class="btn btn-sm btn-brand-blue mt-1">
                                <i class="fas fa-download me-1"></i>Download
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Order Summary -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 sticky-top" style="top: 20px;">
                <div class="card-body">
                    <h5 class="fw-bold text-brand-purple mb-4">Order Summary</h5>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal</span>
                        <span><?php echo formatPrice($order['subtotal']); ?></span>
                    </div>
                    
                    <?php if ($order['coupon_discount'] > 0): ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Discount</span>
                        <span class="text-success">-<?php echo formatPrice($order['coupon_discount']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($order['tax_amount'] > 0): ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Tax</span>
                        <span><?php echo formatPrice($order['tax_amount']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-4">
                        <span class="h5 fw-bold text-brand-purple">Total</span>
                        <span class="h5 fw-bold text-brand-blue"><?php echo formatPrice($order['total_amount']); ?></span>
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1">Payment Method</small>
                        <span class="text-capitalize"><?php echo $order['payment_method']; ?></span>
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1">Payment Status</small>
                        <span class="badge bg-<?php echo $order['payment_status'] === 'completed' ? 'success' : 'warning'; ?>">
                            <?php echo ucfirst($order['payment_status']); ?>
                        </span>
                    </div>
                    
                    <?php if ($order['transaction_id']): ?>
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1">Transaction ID</small>
                        <code><?php echo $order['transaction_id']; ?></code>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="mt-4">
        <a href="orders.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Orders
        </a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
