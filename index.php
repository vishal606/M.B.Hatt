<?php
$pageTitle = 'Home';
require_once 'includes/config.php';
include 'includes/header.php';

// Get featured products
$featuredStmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.status = 'active' AND p.featured = 1 
    ORDER BY p.created_at DESC LIMIT 8");
$featuredProducts = $featuredStmt->fetchAll();

// Get categories
$categoryStmt = $pdo->query("SELECT * FROM categories WHERE status = 'active' ORDER BY sort_order LIMIT 6");
$categories = $categoryStmt->fetchAll();

// Get testimonials
$testimonialStmt = $pdo->query("SELECT * FROM testimonials WHERE status = 'active' ORDER BY created_at DESC LIMIT 3");
$testimonials = $testimonialStmt->fetchAll();
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 hero-content fade-in">
                <h1 class="hero-title mb-3">Premium Digital Products for Everyone</h1>
                <p class="hero-subtitle">Discover a world of high-quality digital products. Templates, software, graphics, and more at unbeatable prices.</p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="pages/products.php" class="btn btn-lg btn-brand-blue px-4 py-3 rounded-pill">
                        <i class="fas fa-compass me-2"></i>Explore Products
                    </a>
                    <a href="pages/register.php" class="btn btn-lg btn-outline-light px-4 py-3 rounded-pill">
                        <i class="fas fa-user-plus me-2"></i>Join Free
                    </a>
                </div>
                <div class="mt-4 d-flex gap-4">
                    <div class="text-white">
                        <h3 class="mb-0 fw-bold">1000+</h3>
                        <small>Products</small>
                    </div>
                    <div class="text-white">
                        <h3 class="mb-0 fw-bold">5000+</h3>
                        <small>Happy Customers</small>
                    </div>
                    <div class="text-white">
                        <h3 class="mb-0 fw-bold">99%</h3>
                        <small>Satisfaction</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 text-center d-none d-lg-block">
                <img src="https://img.icons8.com/clouds/500/online-shop.png" alt="Shopping" class="img-fluid" style="max-height: 400px;">
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="py-5" style="background-color: #F7F6E5;">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold text-brand-purple">Browse Categories</h2>
            <p class="text-muted">Find exactly what you need</p>
        </div>
        <div class="row g-4">
            <?php foreach ($categories as $category): ?>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="pages/products.php?category=<?php echo $category['id']; ?>" class="text-decoration-none">
                    <div class="category-card h-100">
                        <div class="category-icon">
                            <i class="fas fa-folder-open"></i>
                        </div>
                        <h6 class="fw-bold text-brand-purple mb-0"><?php echo $category['name']; ?></h6>
                        <small class="text-muted">
                            <?php
                            $countStmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ? AND status = 'active'");
                            $countStmt->execute([$category['id']]);
                            echo $countStmt->fetchColumn() . ' items';
                            ?>
                        </small>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-brand-purple mb-0">Featured Products</h2>
                <p class="text-muted mb-0">Handpicked just for you</p>
            </div>
            <a href="pages/products.php" class="btn btn-brand-blue">
                View All <i class="fas fa-arrow-right ms-1"></i>
            </a>
        </div>
        
        <div class="row g-4">
            <?php foreach ($featuredProducts as $product): ?>
            <div class="col-12 col-md-6 col-lg-3">
                <div class="product-card h-100 position-relative">
                    <?php if ($product['sale_price'] && $product['sale_price'] < $product['price']): ?>
                        <span class="product-badge badge-sale">SALE</span>
                    <?php endif; ?>
                    <?php if ($product['featured']): ?>
                        <span class="product-badge badge-featured">FEATURED</span>
                    <?php endif; ?>
                    
                    <a href="pages/product.php?slug=<?php echo $product['slug']; ?>">
                        <img src="<?php echo $product['thumbnail'] ? APP_URL . '/' . $product['thumbnail'] : 'https://via.placeholder.com/300x200?text=No+Image'; ?>" 
                             class="card-img-top" alt="<?php echo $product['name']; ?>">
                    </a>
                    
                    <div class="card-body">
                        <div class="mb-2">
                            <small class="text-muted"><?php echo $product['category_name'] ?? 'Uncategorized'; ?></small>
                        </div>
                        <h5 class="product-title">
                            <a href="pages/product.php?slug=<?php echo $product['slug']; ?>" class="text-decoration-none text-brand-purple">
                                <?php echo $product['name']; ?>
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
    </div>
</section>

<!-- Features Section -->
<section class="py-5" style="background-color: #F7F6E5;">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold text-brand-purple">Why Choose MBHaat.com?</h2>
            <p class="text-muted">We provide the best digital product experience</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-shield-alt fa-3x text-brand-purple"></i>
                    </div>
                    <h4 class="fw-bold text-brand-purple">Secure Payments</h4>
                    <p class="text-muted">Your transactions are protected with industry-standard encryption and security protocols.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-bolt fa-3x text-brand-purple"></i>
                    </div>
                    <h4 class="fw-bold text-brand-purple">Instant Delivery</h4>
                    <p class="text-muted">Get instant access to your purchases right after payment confirmation.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-headset fa-3x text-brand-purple"></i>
                    </div>
                    <h4 class="fw-bold text-brand-purple">24/7 Support</h4>
                    <p class="text-muted">Our dedicated support team is always ready to help you with any issues.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold text-brand-purple">What Our Customers Say</h2>
            <p class="text-muted">Real feedback from real customers</p>
        </div>
        <div class="row g-4">
            <?php foreach ($testimonials as $testimonial): ?>
            <div class="col-md-4">
                <div class="testimonial-card h-100">
                    <p class="mb-4" style="padding-top: 1rem;">"<?php echo $testimonial['content']; ?>"</p>
                    <div class="testimonial-author">
                        <img src="<?php echo $testimonial['avatar'] ? APP_URL . '/' . $testimonial['avatar'] : 'https://via.placeholder.com/50?text=' . substr($testimonial['name'], 0, 1); ?>" 
                             alt="<?php echo $testimonial['name']; ?>" class="testimonial-avatar">
                        <div>
                            <h6 class="mb-0 fw-bold text-brand-purple"><?php echo $testimonial['name']; ?></h6>
                            <small class="text-muted"><?php echo $testimonial['designation']; ?></small>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Newsletter Section -->
<section class="py-5" style="background: linear-gradient(135deg, #36064D 0%, #76D2DB 100%);">
    <div class="container text-center text-white">
        <h2 class="fw-bold mb-3">Subscribe to Our Newsletter</h2>
        <p class="mb-4">Get the latest updates on new products and exclusive offers</p>
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <form class="d-flex gap-2">
                    <input type="email" class="form-control rounded-pill" placeholder="Enter your email" required>
                    <button type="submit" class="btn btn-light rounded-pill px-4" style="color: #36064D;">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
