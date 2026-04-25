<?php
$pageTitle = 'My Orders';
require_once '../includes/config.php';
requireAuth();
include '../includes/header.php';

$userId = getUserId();

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Get total count
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
$countStmt->execute([$userId]);
$totalOrders = $countStmt->fetchColumn();
$totalPages = ceil($totalOrders / $perPage);

// Get orders
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->execute([$userId, $perPage, $offset]);
$orders = $stmt->fetchAll();
?>

<div class="container py-4">
    <h3 class="fw-bold text-brand-purple mb-4">
        <i class="fas fa-box me-2"></i>My Orders
    </h3>
    
    <?php if (empty($orders)): ?>
    <div class="text-center py-5">
        <i class="fas fa-box fa-4x text-muted mb-4"></i>
        <h4 class="text-muted">No orders yet</h4>
        <p class="text-muted mb-4">You haven't placed any orders yet. Start exploring our products!</p>
        <a href="../pages/products.php" class="btn btn-brand-blue btn-lg">
            <i class="fas fa-shopping-bag me-2"></i>Browse Products
        </a>
    </div>
    <?php else: ?>
    
    <!-- Mobile View -->
    <div class="d-lg-none">
        <?php foreach ($orders as $order): ?>
        <div class="card mb-3 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-bold"><?php echo $order['order_number']; ?></span>
                    <span class="badge bg-<?php 
                        echo $order['order_status'] === 'completed' ? 'success' : 
                             ($order['order_status'] === 'processing' ? 'warning' : 
                             ($order['order_status'] === 'cancelled' ? 'danger' : 'secondary')); ?>">
                        <?php echo ucfirst($order['order_status']); ?>
                    </span>
                </div>
                <p class="text-muted small mb-1"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></p>
                <p class="fw-bold text-brand-blue mb-3"><?php echo formatPrice($order['total_amount']); ?></p>
                <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-brand-blue w-100">
                    View Details
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Desktop View -->
    <div class="card border-0 shadow-sm rounded-4 d-none d-lg-block">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Order #</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th class="pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td class="ps-4">
                                <span class="fw-bold"><?php echo $order['order_number']; ?></span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                            <td class="fw-bold text-brand-blue"><?php echo formatPrice($order['total_amount']); ?></td>
                            <td>
                                <span class="text-capitalize"><?php echo $order['payment_method']; ?></span>
                                <br>
                                <small class="badge bg-<?php echo $order['payment_status'] === 'completed' ? 'success' : 'warning'; ?>">
                                    <?php echo ucfirst($order['payment_status']); ?>
                                </small>
                            </td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo $order['order_status'] === 'completed' ? 'success' : 
                                         ($order['order_status'] === 'processing' ? 'warning' : 
                                         ($order['order_status'] === 'cancelled' ? 'danger' : 'secondary')); ?>">
                                    <?php echo ucfirst($order['order_status']); ?>
                                </span>
                            </td>
                            <td class="pe-4">
                                <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-brand-blue">
                                    <i class="fas fa-eye me-1"></i>View
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <nav class="mt-4">
        <ul class="pagination justify-content-center">
            <?php if ($page > 1): ?>
            <li class="page-item">
                <a class="page-link" href="?page=<?php echo $page - 1; ?>"><i class="fas fa-chevron-left"></i></a>
            </li>
            <?php endif; ?>
            
            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
            </li>
            <?php endfor; ?>
            
            <?php if ($page < $totalPages): ?>
            <li class="page-item">
                <a class="page-link" href="?page=<?php echo $page + 1; ?>"><i class="fas fa-chevron-right"></i></a>
            </li>
            <?php endif; ?>
        </ul>
    </nav>
    <?php endif; ?>
    
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
