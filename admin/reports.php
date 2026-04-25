<?php
$pageTitle = 'Reports';
include 'includes/header.php';

// Get date range
$startDate = isset($_GET['start']) ? $_GET['start'] : date('Y-m-01');
$endDate = isset($_GET['end']) ? $_GET['end'] : date('Y-m-d');

// Sales report
$salesStmt = $pdo->prepare("SELECT DATE(created_at) as date, COUNT(*) as orders, SUM(total_amount) as revenue 
    FROM orders WHERE order_status = 'completed' AND DATE(created_at) BETWEEN ? AND ? 
    GROUP BY DATE(created_at) ORDER BY date");
$salesStmt->execute([$startDate, $endDate]);
$salesData = $salesStmt->fetchAll();

// Summary stats
$summaryStmt = $pdo->prepare("SELECT 
    COUNT(*) as total_orders,
    SUM(total_amount) as total_revenue,
    AVG(total_amount) as avg_order,
    COUNT(DISTINCT user_id) as unique_customers
    FROM orders WHERE order_status = 'completed' AND DATE(created_at) BETWEEN ? AND ?");
$summaryStmt->execute([$startDate, $endDate]);
$summary = $summaryStmt->fetch();

// Top products
$topProducts = $pdo->prepare("SELECT p.name, COUNT(oi.id) as sales, SUM(oi.product_price) as revenue 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    JOIN orders o ON oi.order_id = o.id 
    WHERE o.order_status = 'completed' AND DATE(o.created_at) BETWEEN ? AND ?
    GROUP BY p.id ORDER BY sales DESC LIMIT 10");
$topProducts->execute([$startDate, $endDate]);
$topProductsData = $topProducts->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold text-brand-purple mb-0">Sales Reports</h4>
    
    <form method="GET" action="" class="d-flex gap-2">
        <input type="date" name="start" class="form-control" value="<?php echo $startDate; ?>">
        <input type="date" name="end" class="form-control" value="<?php echo $endDate; ?>">
        <button type="submit" class="btn btn-brand-blue">
            <i class="fas fa-filter me-1"></i>Filter
        </button>
        <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
            <i class="fas fa-print me-1"></i>Print
        </button>
    </form>
</div>

<!-- Summary Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <h6 class="text-white-50">Total Orders</h6>
                <h3 class="fw-bold mb-0"><?php echo number_format($summary['total_orders'] ?? 0); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card h-100" style="background: linear-gradient(135deg, #76D2DB 0%, #36064D 100%);">
            <div class="card-body">
                <h6 class="text-white-50">Total Revenue</h6>
                <h3 class="fw-bold mb-0"><?php echo formatPrice($summary['total_revenue'] ?? 0); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card h-100" style="background: linear-gradient(135deg, #DA4848 0%, #36064D 100%);">
            <div class="card-body">
                <h6 class="text-white-50">Average Order</h6>
                <h3 class="fw-bold mb-0"><?php echo formatPrice($summary['avg_order'] ?? 0); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card h-100" style="background: linear-gradient(135deg, #F7F6E5 0%, #76D2DB 100%); color: #36064D;">
            <div class="card-body">
                <h6 class="opacity-75">Customers</h6>
                <h3 class="fw-bold mb-0"><?php echo number_format($summary['unique_customers'] ?? 0); ?></h3>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Sales Data -->
    <div class="col-lg-8">
        <div class="card admin-card">
            <div class="card-header">
                <h5 class="mb-0 fw-bold">Daily Sales</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table admin-table mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Orders</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($salesData as $day): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($day['date'])); ?></td>
                                <td><?php echo $day['orders']; ?></td>
                                <td class="fw-bold text-brand-blue"><?php echo formatPrice($day['revenue']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($salesData)): ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">No data available for selected period</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Top Products -->
    <div class="col-lg-4">
        <div class="card admin-card">
            <div class="card-header">
                <h5 class="mb-0 fw-bold">Top Products</h5>
            </div>
            <div class="card-body">
                <?php foreach ($topProductsData as $product): ?>
                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                    <div>
                        <span class="fw-bold d-block" style="font-size: 0.9rem;"><?php echo substr($product['name'], 0, 25); ?><?php echo strlen($product['name']) > 25 ? '...' : ''; ?></span>
                        <small class="text-muted"><?php echo $product['sales']; ?> sales</small>
                    </div>
                    <span class="fw-bold text-brand-blue"><?php echo formatPrice($product['revenue']); ?></span>
                </div>
                <?php endforeach; ?>
                <?php if (empty($topProductsData)): ?>
                <p class="text-center text-muted py-4">No data available</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
