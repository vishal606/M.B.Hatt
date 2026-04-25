<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en" data-mdb-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?><?php echo APP_NAME; ?></title>
    <meta name="description" content="<?php echo getSettings('site_description') ?? 'Digital Product Selling Platform'; ?>">
    <meta name="keywords" content="<?php echo getSettings('site_keywords') ?? 'digital products, downloads'; ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo APP_URL; ?>/assets/images/favicon.ico">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- MDB Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.2.0/mdb.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?php echo APP_URL; ?>/assets/css/style.css" rel="stylesheet">
    
    <style>
        :root {
            --mb-beige: #F7F6E5;
            --mb-blue: #76D2DB;
            --mb-red: #DA4848;
            --mb-purple: #36064D;
        }
    </style>
</head>
<body class="bg-light">

<?php if (isset($_SESSION['flash_message'])): ?>
    <div class="position-fixed top-0 start-50 translate-middle-x z-3 mt-4" style="z-index: 9999;">
        <?php 
        echo showAlert($_SESSION['flash_message'], $_SESSION['flash_type'] ?? 'info');
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        ?>
    </div>
<?php endif; ?>

<!-- Desktop Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm d-none d-lg-block">
    <div class="container">
        <a class="navbar-brand" href="<?php echo APP_URL; ?>/index.php" style="color: #36064D;">
            <i class="fas fa-shopping-bag me-2"></i>
            <strong>MBHaat.com</strong>
        </a>
        
        <button class="navbar-toggler" type="button" data-mdb-collapse-init data-mdb-target="#navbarDesktop">
            <i class="fas fa-bars"></i>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarDesktop">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo APP_URL; ?>/index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo APP_URL; ?>/pages/products.php">Products</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-mdb-dropdown-init>Categories</a>
                    <ul class="dropdown-menu">
                        <?php
                        $catStmt = $pdo->query("SELECT * FROM categories WHERE status = 'active' ORDER BY sort_order LIMIT 6");
                        while ($cat = $catStmt->fetch()):
                        ?>
                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/pages/products.php?category=<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></a></li>
                        <?php endwhile; ?>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo APP_URL; ?>/pages/faq.php">FAQ</a>
                </li>
            </ul>
            
            <div class="d-flex align-items-center gap-3">
                <!-- Search -->
                <form class="d-flex input-group w-auto" action="<?php echo APP_URL; ?>/pages/products.php" method="GET">
                    <input type="search" name="search" class="form-control rounded" placeholder="Search products..." />
                    <button class="btn btn-primary rounded ms-1" type="submit" style="background-color: #76D2DB; border-color: #76D2DB;">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
                
                <!-- Cart -->
                <a href="<?php echo APP_URL; ?>/pages/cart.php" class="text-dark position-relative">
                    <i class="fas fa-shopping-cart fa-lg"></i>
                    <span class="badge rounded-pill badge-danger position-absolute top-0 start-100 translate-middle" id="cart-count">
                        <?php echo getCartCount(); ?>
                    </span>
                </a>
                
                <!-- User Menu -->
                <?php if (isLoggedIn()): ?>
                    <div class="dropdown">
                        <a class="dropdown-toggle d-flex align-items-center hidden-arrow" href="#" data-mdb-dropdown-init>
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 35px; height: 35px; background-color: #36064D !important;">
                                <?php echo strtoupper(substr(getUserName(), 0, 1)); ?>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/user/dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                            <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/user/orders.php"><i class="fas fa-box me-2"></i>My Orders</a></li>
                            <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/user/profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                            <?php if (isAdmin()): ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/admin/index.php"><i class="fas fa-cog me-2"></i>Admin Panel</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/pages/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?php echo APP_URL; ?>/pages/login.php" class="btn btn-outline-primary btn-sm" style="border-color: #36064D; color: #36064D;">Login</a>
                    <a href="<?php echo APP_URL; ?>/pages/register.php" class="btn btn-primary btn-sm" style="background-color: #76D2DB; border-color: #76D2DB; color: #36064D;">Sign Up</a>
                <?php endif; ?>
                
                <!-- Dark Mode Toggle -->
                <button class="btn btn-link text-dark p-0" onclick="toggleDarkMode()">
                    <i class="fas fa-moon" id="darkModeIcon"></i>
                </button>
            </div>
        </div>
    </div>
</nav>

<!-- Mobile App Bar -->
<div class="mobile-appbar d-lg-none">
    <div class="appbar-top">
        <div class="d-flex justify-content-between align-items-center p-3" style="background: linear-gradient(135deg, #36064D 0%, #76D2DB 100%);">
            <div class="text-white">
                <i class="fas fa-shopping-bag fa-lg me-2"></i>
                <strong>MBHaat</strong>
            </div>
            <div class="d-flex align-items-center gap-3">
                <a href="<?php echo APP_URL; ?>/pages/cart.php" class="text-white position-relative">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="badge rounded-pill bg-danger position-absolute top-0 start-100 translate-middle" style="font-size: 8px;">
                        <?php echo getCartCount(); ?>
                    </span>
                </a>
                <button class="text-white border-0 bg-transparent" onclick="toggleDarkMode()">
                    <i class="fas fa-moon" id="mobileDarkModeIcon"></i>
                </button>
            </div>
        </div>
    </div>
</div>
