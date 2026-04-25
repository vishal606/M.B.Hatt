<?php
$pageTitle = 'Dashboard';
include 'includes/header.php';

// Get stats for charts
$monthlySales = $pdo->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total_amount) as total 
    FROM orders WHERE order_status = 'completed' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY month LIMIT 6")->fetchAll();

$topProducts = $pdo->query("SELECT p.name, COUNT(oi.id) as sales_count, SUM(oi.product_price) as revenue 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    JOIN orders o ON oi.order_id = o.id AND o.order_status = 'completed'
    GROUP BY p.id ORDER BY sales_count DESC LIMIT 5")->fetchAll();

// Recent orders
$recentOrders = $pdo->query("SELECT o.*, u.name as user_name FROM orders o 
    JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 10")->fetchAll();
?>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">Total Users</h6>
                        <h3 class="mb-0 fw-bold"><?php echo number_format($totalUsers); ?></h3>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card h-100" style="background: linear-gradient(135deg, #76D2DB 0%, #36064D 100%);">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">Products</h6>
                        <h3 class="mb-0 fw-bold"><?php echo number_format($totalProducts); ?></h3>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-box"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card h-100" style="background: linear-gradient(135deg, #DA4848 0%, #36064D 100%);">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">Total Orders</h6>
                        <h3 class="mb-0 fw-bold"><?php echo number_format($totalOrders); ?></h3>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card h-100" style="background: linear-gradient(135deg, #F7F6E5 0%, #76D2DB 100%); color: #36064D;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1 opacity-75">Today's Sales</h6>
                        <h3 class="mb-0 fw-bold"><?php echo formatPrice($todaySales); ?></h3>
                    </div>
                    <div class="stat-icon" style="background: #36064D;">
                        <i class="fas fa-dollar-sign text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Orders -->
    <div class="col-lg-8">
        <div class="card admin-card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Recent Orders</h5>
                <a href="orders.php" class="btn btn-sm btn-brand-blue">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table admin-table mb-0">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td><a href="order-detail.php?id=<?php echo $order['id']; ?>" class="fw-bold"><?php echo $order['order_number']; ?></a></td>
                                <td><?php echo $order['user_name']; ?></td>
                                <td class="fw-bold text-brand-blue"><?php echo formatPrice($order['total_amount']); ?></td>
                                <td><span class="badge bg-<?php echo $order['order_status'] === 'completed' ? 'success' : 'warning'; ?>"><?php echo ucfirst($order['order_status']); ?></span></td>
                                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Top Products -->
    <div class="col-lg-4">
        <div class="card admin-card h-100">
            <div class="card-header">
                <h5 class="mb-0 fw-bold">Top Selling Products</h5>
            </div>
            <div class="card-body">
                <?php foreach ($topProducts as $i => $product): ?>
                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 <?php echo $i < count($topProducts) - 1 ? 'border-bottom' : ''; ?>">
                    <div>
                        <h6 class="mb-0 fw-bold"><?php echo substr($product['name'], 0, 25); ?><?php echo strlen($product['name']) > 25 ? '...' : ''; ?></h6>
                        <small class="text-muted"><?php echo $product['sales_count']; ?> sales</small>
                    </div>
                    <span class="fw-bold text-brand-blue"><?php echo formatPrice($product['revenue']); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
