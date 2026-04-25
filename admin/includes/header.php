<?php
require_once '../includes/config.php';
requireAdmin();

// Get admin stats
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'active'")->fetchColumn();
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'completed'")->fetchColumn();
$todaySales = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE DATE(created_at) = CURDATE() AND order_status = 'completed'")->fetchColumn();

// Get pending orders
$pendingOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'pending'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en" data-mdb-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Admin - <?php echo APP_NAME; ?></title>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.2.0/mdb.min.css" rel="stylesheet">
    <link href="<?php echo APP_URL; ?>/admin/assets/css/admin.css" rel="stylesheet">
</head>
<body>

<!-- Admin Sidebar -->
<div class="admin-wrapper">
    <aside class="admin-sidebar">
        <div class="sidebar-header p-4">
            <a href="index.php" class="text-decoration-none text-white">
                <i class="fas fa-shopping-bag fa-lg me-2"></i>
                <span class="fw-bold">MBHaat Admin</span>
            </a>
        </div>
        
        <nav class="sidebar-nav">
            <a href="index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
            </a>
            <a href="products.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' || basename($_SERVER['PHP_SELF']) == 'product-edit.php' ? 'active' : ''; ?>">
                <i class="fas fa-box me-2"></i>Products
            </a>
            <a href="orders.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>">
                <i class="fas fa-shopping-cart me-2"></i>Orders
                <?php if ($pendingOrders > 0): ?>
                <span class="badge bg-danger ms-2"><?php echo $pendingOrders; ?></span>
                <?php endif; ?>
            </a>
            <a href="users.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
                <i class="fas fa-users me-2"></i>Users
            </a>
            <a href="categories.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>">
                <i class="fas fa-folder me-2"></i>Categories
            </a>
            <a href="coupons.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'coupons.php' ? 'active' : ''; ?>">
                <i class="fas fa-tag me-2"></i>Coupons
            </a>
            <a href="reports.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-bar me-2"></i>Reports
            </a>
            <a href="settings.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                <i class="fas fa-cog me-2"></i>Settings
            </a>
        </nav>
        
        <div class="sidebar-footer p-4 mt-auto">
            <a href="../index.php" class="btn btn-outline-light btn-sm w-100 mb-2">
                <i class="fas fa-globe me-1"></i>View Site
            </a>
            <a href="../pages/logout.php" class="btn btn-danger btn-sm w-100">
                <i class="fas fa-sign-out-alt me-1"></i>Logout
            </a>
        </div>
    </aside>
    
    <!-- Main Content -->
    <main class="admin-main">
        <!-- Top Bar -->
        <header class="admin-header">
            <div class="d-flex justify-content-between align-items-center h-100 px-4">
                <h5 class="mb-0"><?php echo $pageTitle ?? 'Dashboard'; ?></h5>
                
                <div class="d-flex align-items-center gap-3">
                    <div class="dropdown">
                        <a class="dropdown-toggle d-flex align-items-center text-dark text-decoration-none" href="#" data-mdb-dropdown-init>
                            <div class="rounded-circle bg-brand-purple text-white d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px;">
                                <?php echo strtoupper(substr(getUserName(), 0, 1)); ?>
                            </div>
                            <span><?php echo getUserName(); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="../user/profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../pages/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>
        
        <div class="admin-content">
            <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['flash_type'] ?? 'info'; ?> alert-dismissible fade show">
                <?php echo $_SESSION['flash_message']; ?>
                <button type="button" class="btn-close" data-mdb-dismiss="alert"></button>
            </div>
            <?php 
            unset($_SESSION['flash_message'], $_SESSION['flash_type']);
            endif; ?>
