<?php
$pageTitle = 'Orders';
include 'includes/header.php';

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Filter
$statusFilter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';
$searchQuery = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

// Build query
$query = "SELECT o.*, u.name as user_name FROM orders o JOIN users u ON o.user_id = u.id WHERE 1=1";
$countQuery = "SELECT COUNT(*) FROM orders o JOIN users u ON o.user_id = u.id WHERE 1=1";
$params = [];

if (!empty($statusFilter)) {
    $query .= " AND o.order_status = ?";
    $countQuery .= " AND o.order_status = ?";
    $params[] = $statusFilter;
}

if (!empty($searchQuery)) {
    $query .= " AND (o.order_number LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
    $countQuery .= " AND (o.order_number LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
    $params[] = "%$searchQuery%";
    $params[] = "%$searchQuery%";
    $params[] = "%$searchQuery%";
}

$query .= " ORDER BY o.created_at DESC LIMIT $perPage OFFSET $offset";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$orders = $stmt->fetchAll();

$countStmt = $pdo->prepare($countQuery);
$countStmt->execute($params);
$total = $countStmt->fetchColumn();
$totalPages = ceil($total / $perPage);

// Handle status update
if (isset($_POST['update_status'])) {
    $orderId = intval($_POST['order_id']);
    $newStatus = sanitizeInput($_POST['new_status']);
    
    $pdo->prepare("UPDATE orders SET order_status = ? WHERE id = ?")
        ->execute([$newStatus, $orderId]);
    
    $_SESSION['flash_message'] = "Order status updated";
    $_SESSION['flash_type'] = "success";
    redirect(APP_URL . '/admin/orders.php');
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold text-brand-purple mb-0">Orders Management</h4>
</div>

<!-- Filters -->
<div class="card admin-card mb-4">
    <div class="card-body">
        <form method="GET" action="">
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" value="<?php echo $searchQuery; ?>" placeholder="Search orders...">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="processing" <?php echo $statusFilter === 'processing' ? 'selected' : ''; ?>>Processing</option>
                        <option value="completed" <?php echo $statusFilter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $statusFilter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-brand-blue w-100">
                        <i class="fas fa-filter me-2"></i>Filter
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="orders.php" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-times me-2"></i>Clear
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card admin-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>
                            <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="fw-bold text-brand-purple">
                                <?php echo $order['order_number']; ?>
                            </a>
                        </td>
                        <td>
                            <span class="fw-bold"><?php echo $order['user_name']; ?></span>
                        </td>
                        <td class="fw-bold text-brand-blue"><?php echo formatPrice($order['total_amount']); ?></td>
                        <td>
                            <span class="text-capitalize"><?php echo $order['payment_method']; ?></span>
                            <br>
                            <small class="badge bg-<?php echo $order['payment_status'] === 'completed' ? 'success' : 'warning'; ?>">
                                <?php echo ucfirst($order['payment_status']); ?>
                            </small>
                        </td>
                        <td>
                            <form method="POST" action="" class="d-inline">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <select name="new_status" class="form-select form-select-sm" onchange="this.form.submit()" style="width: 120px;">
                                    <option value="pending" <?php echo $order['order_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="processing" <?php echo $order['order_status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                    <option value="completed" <?php echo $order['order_status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="cancelled" <?php echo $order['order_status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                                <input type="hidden" name="update_status" value="1">
                            </form>
                        </td>
                        <td><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></td>
                        <td>
                            <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="action-btn view" title="View">
                                <i class="fas fa-eye"></i>
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
        <li class="page-item"><a class="page-link" href="?page=<?php echo $page - 1; ?>&status=<?php echo $statusFilter; ?>&search=<?php echo urlencode($searchQuery); ?>"><i class="fas fa-chevron-left"></i></a></li>
        <?php endif; ?>
        
        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>"><a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo $statusFilter; ?>&search=<?php echo urlencode($searchQuery); ?>"><?php echo $i; ?></a></li>
        <?php endfor; ?>
        
        <?php if ($page < $totalPages): ?>
        <li class="page-item"><a class="page-link" href="?page=<?php echo $page + 1; ?>&status=<?php echo $statusFilter; ?>&search=<?php echo urlencode($searchQuery); ?>"><i class="fas fa-chevron-right"></i></a></li>
        <?php endif; ?>
    </ul>
</nav>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
