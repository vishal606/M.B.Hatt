<?php
$pageTitle = 'Product Details';
require_once '../includes/config.php';
include '../includes/header.php';

$slug = isset($_GET['slug']) ? sanitizeInput($_GET['slug']) : '';

if (empty($slug)) {
    redirect(APP_URL . '/pages/products.php');
}

// Get product
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug FROM products p 
                      LEFT JOIN categories c ON p.category_id = c.id 
                      WHERE p.slug = ? AND p.status = 'active'");
$stmt->execute([$slug]);
$product = $stmt->fetch();

if (!$product) {
    $_SESSION['flash_message'] = "Product not found";
    $_SESSION['flash_type'] = "error";
    redirect(APP_URL . '/pages/products.php');
}

// Update views
$pdo->prepare("UPDATE products SET views = views + 1 WHERE id = ?")->execute([$product['id']]);

// Get related products
$relatedStmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? AND status = 'active' LIMIT 4");
$relatedStmt->execute([$product['category_id'], $product['id']]);
$relatedProducts = $relatedStmt->fetchAll();

// Parse screenshots
$screenshots = [];
if ($product['screenshots']) {
    $screenshots = json_decode($product['screenshots'], true) ?: [];
}

// Check if product is already purchased
$isPurchased = false;
if (isLoggedIn()) {
    $purchaseStmt = $pdo->prepare("SELECT COUNT(*) FROM orders o 
                                   JOIN order_items oi ON o.id = oi.order_id 
                                   WHERE o.user_id = ? AND oi.product_id = ? AND o.order_status = 'completed'");
    $purchaseStmt->execute([getUserId(), $product['id']]);
    $isPurchased = $purchaseStmt->fetchColumn() > 0;
}
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>/index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="products.php">Products</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo $product['name']; ?></li>
        </ol>
    </nav>
    
    <div class="row">
        <!-- Product Images -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <img src="<?php echo $product['thumbnail'] ? APP_URL . '/' . $product['thumbnail'] : 'https://via.placeholder.com/600x400?text=No+Image'; ?>" 
                     class="card-img-top" alt="<?php echo $product['name']; ?>" id="mainImage">
                
                <?php if (!empty($screenshots)): ?>
                <div class="card-body bg-light">
                    <div class="row g-2">
                        <div class="col-3">
                            <img src="<?php echo $product['thumbnail'] ? APP_URL . '/' . $product['thumbnail'] : 'https://via.placeholder.com/150?text=No+Image'; ?>" 
                                 class="img-fluid rounded cursor-pointer border border-2 border-brand-blue" 
                                 onclick="changeImage(this.src)" style="cursor: pointer;">
                        </div>
                        <?php foreach ($screenshots as $screenshot): ?>
                        <div class="col-3">
                            <img src="<?php echo APP_URL . '/' . $screenshot; ?>" 
                                 class="img-fluid rounded cursor-pointer" 
                                 onclick="changeImage(this.src)" style="cursor: pointer;">
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Product Info -->
        <div class="col-lg-6">
            <div class="mb-3">
                <span class="badge rounded-pill bg-light text-dark">
                    <i class="fas fa-folder me-1"></i><?php echo $product['category_name'] ?? 'Uncategorized'; ?>
                </span>
                <span class="badge rounded-pill bg-light text-dark ms-1">
                    <i class="fas fa-eye me-1"></i><?php echo number_format($product['views']); ?> views
                </span>
                <span class="badge rounded-pill bg-light text-dark ms-1">
                    <i class="fas fa-download me-1"></i><?php echo number_format($product['downloads']); ?> downloads
                </span>
            </div>
            
            <h1 class="fw-bold text-brand-purple mb-3"><?php echo $product['name']; ?></h1>
            
            <div class="mb-4">
                <?php if ($product['sale_price'] && $product['sale_price'] < $product['price']): ?>
                    <span class="display-5 fw-bold text-brand-blue"><?php echo formatPrice($product['sale_price']); ?></span>
                    <span class="h4 text-muted text-decoration-line-through ms-2"><?php echo formatPrice($product['price']); ?></span>
                    <span class="badge bg-danger ms-2">
                        <?php echo round((1 - $product['sale_price'] / $product['price']) * 100); ?>% OFF
                    </span>
                <?php else: ?>
                    <span class="display-5 fw-bold text-brand-blue"><?php echo formatPrice($product['price']); ?></span>
                <?php endif; ?>
            </div>
            
            <div class="mb-4">
                <h5 class="fw-bold text-brand-purple">Description</h5>
                <p class="text-muted"><?php echo nl2br($product['description']); ?></p>
                
                <?php if ($product['short_description']): ?>
                <p class="text-muted"><?php echo $product['short_description']; ?></p>
                <?php endif; ?>
            </div>
            
            <div class="row mb-4">
                <div class="col-6">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-file-archive fa-2x text-brand-blue me-3"></i>
                        <div>
                            <small class="text-muted d-block">File Size</small>
                            <span class="fw-bold"><?php echo $product['file_size'] ? number_format($product['file_size'] / 1024 / 1024, 2) . ' MB' : 'N/A'; ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-file fa-2x text-brand-blue me-3"></i>
                        <div>
                            <small class="text-muted d-block">File Type</small>
                            <span class="fw-bold"><?php echo $product['file_type'] ?? 'N/A'; ?></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if ($product['demo_url']): ?>
            <div class="mb-4">
                <a href="<?php echo $product['demo_url']; ?>" target="_blank" class="btn btn-outline-primary">
                    <i class="fas fa-external-link-alt me-2"></i>Live Demo
                </a>
            </div>
            <?php endif; ?>
            
            <div class="d-flex gap-3 mb-4">
                <?php if ($isPurchased): ?>
                    <a href="../user/orders.php" class="btn btn-success btn-lg flex-fill">
                        <i class="fas fa-check me-2"></i>Already Purchased
                    </a>
                <?php else: ?>
                    <button onclick="addToCart(<?php echo $product['id']; ?>)" class="btn btn-brand-blue btn-lg flex-fill">
                        <i class="fas fa-cart-plus me-2"></i>Add to Cart
                    </button>
                    <a href="checkout.php?buy_now=<?php echo $product['id']; ?>" class="btn btn-brand-purple btn-lg">
                        <i class="fas fa-bolt me-2"></i>Buy Now
                    </a>
                <?php endif; ?>
            </div>
            
            <div class="alert" style="background-color: #F7F6E5; border-color: #F7F6E5;">
                <h6 class="fw-bold"><i class="fas fa-shield-alt me-2 text-brand-purple"></i>Secure Purchase</h6>
                <ul class="list-unstyled mb-0 small text-muted">
                    <li><i class="fas fa-check text-brand-blue me-2"></i>Instant download after payment</li>
                    <li><i class="fas fa-check text-brand-blue me-2"></i>30-day download access</li>
                    <li><i class="fas fa-check text-brand-blue me-2"></i>Secure SSL encrypted checkout</li>
                </ul>
            </div>
        </div>
    </div>
    
    <!-- Related Products -->
    <?php if (!empty($relatedProducts)): ?>
    <section class="mt-5 pt-5 border-top">
        <h3 class="fw-bold text-brand-purple mb-4">Related Products</h3>
        <div class="row g-4">
            <?php foreach ($relatedProducts as $related): ?>
            <div class="col-6 col-md-3">
                <div class="product-card h-100">
                    <a href="product.php?slug=<?php echo $related['slug']; ?>">
                        <img src="<?php echo $related['thumbnail'] ? APP_URL . '/' . $related['thumbnail'] : 'https://via.placeholder.com/300x200?text=No+Image'; ?>" 
                             class="card-img-top" alt="<?php echo $related['name']; ?>">
                    </a>
                    <div class="card-body p-3">
                        <h6 class="product-title" style="font-size: 0.9rem;">
                            <a href="product.php?slug=<?php echo $related['slug']; ?>" class="text-decoration-none text-brand-purple">
                                <?php echo substr($related['name'], 0, 35); ?><?php echo strlen($related['name']) > 35 ? '...' : ''; ?>
                            </a>
                        </h6>
                        <span class="fw-bold text-brand-blue"><?php echo formatPrice($related['sale_price'] ?? $related['price']); ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</div>

<script>
function changeImage(src) {
    document.getElementById('mainImage').src = src;
}
</script>

<?php include '../includes/footer.php'; ?>
