<?php
$pageTitle = 'Dashboard';
require_once '../includes/config.php';
requireAuth();
include '../includes/header.php';

$userId = getUserId();

// Get stats
$orderStmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND order_status = 'completed'");
$orderStmt->execute([$userId]);
$totalOrders = $orderStmt->fetchColumn();

$downloadStmt = $pdo->prepare("SELECT COUNT(*) FROM order_items oi JOIN orders o ON oi.order_id = o.id WHERE o.user_id = ? AND o.order_status = 'completed'");
$downloadStmt->execute([$userId]);
$totalDownloads = $downloadStmt->fetchColumn();

$spentStmt = $pdo->prepare("SELECT SUM(total_amount) FROM orders WHERE user_id = ? AND order_status = 'completed'");
$spentStmt->execute([$userId]);
$totalSpent = $spentStmt->fetchColumn() ?: 0;

// Get recent orders
$recentOrderStmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$recentOrderStmt->execute([$userId]);
$recentOrders = $recentOrderStmt->fetchAll();

// Get recent downloads
$downloadItemsStmt = $pdo->prepare("SELECT oi.*, o.order_number, o.created_at as order_date FROM order_items oi 
                                    JOIN orders o ON oi.order_id = o.id 
                                    WHERE o.user_id = ? AND o.order_status = 'completed'
                                    ORDER BY o.created_at DESC LIMIT 5");
$downloadItemsStmt->execute([$userId]);
$recentDownloads = $downloadItemsStmt->fetchAll();
?>

<div class="container py-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 mb-4 d-none d-lg-block">
            <div class="card border-0 shadow-sm rounded-4 dashboard-sidebar">
                <div class="card-body p-0">
                    <div class="text-center p-4 border-bottom">
                        <div class="rounded-circle bg-brand-purple text-white d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px; font-size: 2rem;">
                            <?php echo strtoupper(substr(getUserName(), 0, 1)); ?>
                        </div>
                        <h5 class="fw-bold text-brand-purple mb-1"><?php echo getUserName(); ?></h5>
                        <small class="text-muted"><?php echo $_SESSION['user_email']; ?></small>
                    </div>
                    
                    <nav class="nav flex-column dashboard-nav p-3">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                        <a class="nav-link" href="orders.php">
                            <i class="fas fa-box me-2"></i>My Orders
                        </a>
                        <a class="nav-link" href="downloads.php">
                            <i class="fas fa-download me-2"></i>Downloads
                        </a>
                        <a class="nav-link" href="profile.php">
                            <i class="fas fa-user me-2"></i>Profile
                        </a>
                        <a class="nav-link" href="change-password.php">
                            <i class="fas fa-lock me-2"></i>Change Password
                        </a>
                        <hr>
                        <a class="nav-link text-danger" href="../pages/logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </nav>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-lg-9">
            <h3 class="fw-bold text-brand-purple mb-4">Dashboard</h3>
            
            <!-- Stats Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1 opacity-75">Total Orders</h6>
                                <h3 class="mb-0 fw-bold"><?php echo $totalOrders; ?></h3>
                            </div>
                            <i class="fas fa-box fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card blue">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1 opacity-75">Downloads</h6>
                                <h3 class="mb-0 fw-bold"><?php echo $totalDownloads; ?></h3>
                            </div>
                            <i class="fas fa-download fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card beige">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1 opacity-75">Total Spent</h6>
                                <h3 class="mb-0 fw-bold"><?php echo formatPrice($totalSpent); ?></h3>
                            </div>
                            <i class="fas fa-wallet fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Orders -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-brand-purple mb-0">Recent Orders</h5>
                    <a href="orders.php" class="btn btn-sm btn-brand-blue">View All</a>
                </div>
                <div class="card-body px-4 pb-4">
                    <?php if (empty($recentOrders)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-box fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No orders yet. <a href="../pages/products.php">Start shopping!</a></p>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentOrders as $order): ?>
                                <tr>
                                    <td><span class="fw-bold"><?php echo $order['order_number']; ?></span></td>
                                    <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                    <td><?php echo formatPrice($order['total_amount']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $order['order_status'] === 'completed' ? 'success' : 
                                                 ($order['order_status'] === 'processing' ? 'warning' : 
                                                 ($order['order_status'] === 'cancelled' ? 'danger' : 'secondary')); ?>">
                                            <?php echo ucfirst($order['order_status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Recent Downloads -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-brand-purple mb-0">Recent Downloads</h5>
                    <a href="downloads.php" class="btn btn-sm btn-brand-blue">View All</a>
                </div>
                <div class="card-body px-4 pb-4">
                    <?php if (empty($recentDownloads)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-download fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No downloads yet. Purchased products will appear here.</p>
                    </div>
                    <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recentDownloads as $item): ?>
                        <div class="list-group-item px-0 py-3 d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1"><?php echo $item['product_name']; ?></h6>
                                <small class="text-muted">Order: <?php echo $item['order_number']; ?> | Purchased: <?php echo date('M d, Y', strtotime($item['order_date'])); ?></small>
                            </div>
                            <a href="download.php?token=<?php echo $item['download_token']; ?>" class="btn btn-sm btn-brand-blue">
                                <i class="fas fa-download me-1"></i>Download
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
