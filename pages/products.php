<?php
$pageTitle = 'Products';
require_once '../includes/config.php';
include '../includes/header.php';

// Get filters
$categoryId = isset($_GET['category']) ? intval($_GET['category']) : 0;
$searchQuery = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$sortBy = isset($_GET['sort']) ? sanitizeInput($_GET['sort']) : 'newest';
$minPrice = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
$maxPrice = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 0;

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 12;
$offset = ($page - 1) * $perPage;

// Build query
$query = "SELECT p.*, c.name as category_name FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE p.status = 'active'";
$countQuery = "SELECT COUNT(*) FROM products p WHERE p.status = 'active'";
$params = [];

if ($categoryId > 0) {
    $query .= " AND p.category_id = ?";
    $countQuery .= " AND p.category_id = ?";
    $params[] = $categoryId;
}

if (!empty($searchQuery)) {
    $query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $countQuery .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$searchQuery%";
    $params[] = "%$searchQuery%";
}

if ($minPrice > 0) {
    $query .= " AND p.price >= ?";
    $countQuery .= " AND p.price >= ?";
    $params[] = $minPrice;
}

if ($maxPrice > 0) {
    $query .= " AND p.price <= ?";
    $countQuery .= " AND p.price <= ?";
    $params[] = $maxPrice;
}

// Sorting
switch ($sortBy) {
    case 'price_low':
        $query .= " ORDER BY p.price ASC";
        break;
    case 'price_high':
        $query .= " ORDER BY p.price DESC";
        break;
    case 'popular':
        $query .= " ORDER BY p.downloads DESC";
        break;
    case 'newest':
    default:
        $query .= " ORDER BY p.created_at DESC";
}

$query .= " LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = $offset;

// Get products
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get total count
$countStmt = $pdo->prepare($countQuery);
$countStmt->execute(array_slice($params, 0, -2));
$totalProducts = $countStmt->fetchColumn();
$totalPages = ceil($totalProducts / $perPage);

// Get categories for filter
$catStmt = $pdo->query("SELECT * FROM categories WHERE status = 'active' ORDER BY sort_order");
$categories = $catStmt->fetchAll();

// Get current category name
$currentCategory = null;
if ($categoryId > 0) {
    $currentCatStmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
    $currentCatStmt->execute([$categoryId]);
    $currentCategory = $currentCatStmt->fetchColumn();
}
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>/index.php">Home</a></li>
            <li class="breadcrumb-item active">Products</li>
        </ol>
    </nav>
    
    <div class="row">
        <!-- Sidebar Filters -->
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <h5 class="fw-bold text-brand-purple mb-3">
                        <i class="fas fa-filter me-2"></i>Filters
                    </h5>
                    
                    <!-- Search -->
                    <form method="GET" action="">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Search</label>
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" value="<?php echo htmlspecialchars($searchQuery); ?>" placeholder="Search products...">
                                <button type="submit" class="btn btn-brand-blue">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Categories -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Categories</label>
                            <div class="list-group list-group-flush">
                                <a href="?" class="list-group-item list-group-item-action <?php echo $categoryId == 0 ? 'active' : ''; ?>">
                                    All Categories
                                </a>
                                <?php foreach ($categories as $cat): ?>
                                <a href="?category=<?php echo $cat['id']; ?>" class="list-group-item list-group-item-action <?php echo $categoryId == $cat['id'] ? 'active' : ''; ?>">
                                    <?php echo $cat['name']; ?>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <!-- Price Range -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Price Range</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" name="min_price" class="form-control" placeholder="Min" value="<?php echo $minPrice > 0 ? $minPrice : ''; ?>">
                                </div>
                                <div class="col-6">
                                    <input type="number" name="max_price" class="form-control" placeholder="Max" value="<?php echo $maxPrice > 0 ? $maxPrice : ''; ?>">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Sort -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Sort By</label>
                            <select name="sort" class="form-select" onchange="this.form.submit()">
                                <option value="newest" <?php echo $sortBy == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                                <option value="price_low" <?php echo $sortBy == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                                <option value="price_high" <?php echo $sortBy == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                                <option value="popular" <?php echo $sortBy == 'popular' ? 'selected' : ''; ?>>Most Popular</option>
                            </select>
                        </div>
                        
                        <?php if ($categoryId > 0): ?>
                            <input type="hidden" name="category" value="<?php echo $categoryId; ?>">
                        <?php endif; ?>
                        
                        <button type="submit" class="btn btn-brand-purple w-100">
                            <i class="fas fa-filter me-2"></i>Apply Filters
                        </button>
                        
                        <?php if ($categoryId > 0 || !empty($searchQuery) || $minPrice > 0 || $maxPrice > 0): ?>
                        <a href="products.php" class="btn btn-outline-secondary w-100 mt-2">
                            <i class="fas fa-times me-2"></i>Clear Filters
                        </a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Product Grid -->
        <div class="col-lg-9">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold text-brand-purple mb-0">
                        <?php echo $currentCategory ? $currentCategory : 'All Products'; ?>
                    </h4>
                    <p class="text-muted mb-0 small"><?php echo $totalProducts; ?> products found</p>
                </div>
                
                <?php if (!empty($searchQuery)): ?>
                <span class="badge bg-primary">Search: "<?php echo htmlspecialchars($searchQuery); ?>"</span>
                <?php endif; ?>
            </div>
            
            <?php if (empty($products)): ?>
            <div class="text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No products found</h5>
                <p class="text-muted">Try adjusting your filters or search terms</p>
                <a href="products.php" class="btn btn-brand-blue">Clear Filters</a>
            </div>
            <?php else: ?>
            
            <!-- Mobile: List View -->
            <div class="d-lg-none">
                <?php foreach ($products as $product): ?>
                <div class="card mb-3 border-0 shadow-sm">
                    <div class="row g-0">
                        <div class="col-4">
                            <img src="<?php echo $product['thumbnail'] ? APP_URL . '/' . $product['thumbnail'] : 'https://via.placeholder.com/150?text=No+Image'; ?>" 
                                 class="img-fluid rounded-start h-100" alt="<?php echo $product['name']; ?>">
                        </div>
                        <div class="col-8">
                            <div class="card-body p-3">
                                <h6 class="card-title text-brand-purple mb-1" style="font-size: 0.9rem;">
                                    <?php echo substr($product['name'], 0, 40) . (strlen($product['name']) > 40 ? '...' : ''); ?>
                                </h6>
                                <p class="text-muted small mb-2"><?php echo $product['category_name'] ?? 'Uncategorized'; ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold text-brand-blue">
                                        <?php echo $product['sale_price'] ? formatPrice($product['sale_price']) : formatPrice($product['price']); ?>
                                    </span>
                                    <a href="product.php?slug=<?php echo $product['slug']; ?>" class="btn btn-sm btn-brand-blue">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Desktop: Grid View -->
            <div class="row g-4 d-none d-lg-flex">
                <?php foreach ($products as $product): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="product-card h-100 position-relative">
                        <?php if ($product['sale_price'] && $product['sale_price'] < $product['price']): ?>
                            <span class="product-badge badge-sale">SALE</span>
                        <?php endif; ?>
                        
                        <a href="product.php?slug=<?php echo $product['slug']; ?>">
                            <img src="<?php echo $product['thumbnail'] ? APP_URL . '/' . $product['thumbnail'] : 'https://via.placeholder.com/300x200?text=No+Image'; ?>" 
                                 class="card-img-top" alt="<?php echo $product['name']; ?>">
                        </a>
                        
                        <div class="card-body">
                            <div class="mb-2">
                                <small class="text-muted"><?php echo $product['category_name'] ?? 'Uncategorized'; ?></small>
                            </div>
                            <h5 class="product-title">
                                <a href="product.php?slug=<?php echo $product['slug']; ?>" class="text-decoration-none text-brand-purple">
                                    <?php echo substr($product['name'], 0, 50); ?><?php echo strlen($product['name']) > 50 ? '...' : ''; ?>
                                </a>
                            </h5>
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <?php if ($product['sale_price'] && $product['sale_price'] < $product['price']): ?>
                                        <span class="product-price"><?php echo formatPrice($product['sale_price']); ?></span>
                                        <span class="product-original-price"><?php echo formatPrice($product['price']); ?></span>
                                    <?php else: ?>
                                        <span class="product-price"><?php echo formatPrice($product['price']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <button onclick="addToCart(<?php echo $product['id']; ?>)" class="btn btn-brand-blue btn-sm rounded-circle" style="width: 40px; height: 40px;">
                                    <i class="fas fa-cart-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <nav class="mt-5">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&category=<?php echo $categoryId; ?>&search=<?php echo urlencode($searchQuery); ?>&sort=<?php echo $sortBy; ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&category=<?php echo $categoryId; ?>&search=<?php echo urlencode($searchQuery); ?>&sort=<?php echo $sortBy; ?>"><?php echo $i; ?></a>
                    </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&category=<?php echo $categoryId; ?>&search=<?php echo urlencode($searchQuery); ?>&sort=<?php echo $sortBy; ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
            
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
