<?php
require_once __DIR__ . '/src/init.php';

$pageTitle = getSetting('site_name', 'MBHaat.com') . ' — ' . getSetting('site_tagline', 'Premium Digital Products');
$pageDesc  = getSetting('site_tagline', 'Browse and download premium digital products.');

// Featured products
$featuredProducts = Database::fetchAll(
    "SELECT p.*, c.name as category_name, 
     (SELECT image_path FROM product_screenshots WHERE product_id = p.id ORDER BY sort_order LIMIT 1) as thumbnail
     FROM products p LEFT JOIN categories c ON c.id = p.category_id
     WHERE p.status = 'active' ORDER BY p.created_at DESC LIMIT 8"
);

// Categories (Top-level only)
$categories = Database::fetchAll(
    "SELECT c.*, COUNT(p.id) as product_count FROM categories c
     LEFT JOIN products p ON p.category_id = c.id AND p.status='active'
     WHERE c.parent_id IS NULL
     GROUP BY c.id ORDER BY c.id ASC LIMIT 8"
);

// Testimonials
$testimonials = Database::fetchAll(
    "SELECT * FROM testimonials WHERE is_active = 1 ORDER BY sort_order"
);

include __DIR__ . '/src/views/layouts/header.php';
?>

<!-- Hero -->
<section class="hero">
  <div class="hero-slider">
    <div class="hero-slide active" style="background-image: url('<?= ASSETS_URL ?>/images/Background_1.jpg')"></div>
    <div class="hero-slide" style="background-image: url('<?= ASSETS_URL ?>/images/Background_2.jpg')"></div>
    <div class="hero-slide" style="background-image: url('<?= ASSETS_URL ?>/images/Background_3.jpg')"></div>
  </div>
  <div class="container">
    <div class="hero-content">
      <div class="hero-badge">
        ✨ Premium Digital Marketplace
      </div>
      <h1 class="hero-title">Upgrade Your <br>Tech Lifestyle</h1>
      <p class="hero-subtitle">Shop the latest gadgets, accessories, and smart devices at the best prices.</p>
      <div class="hero-actions">
        <a href="<?= APP_URL ?>/products.php" class="btn btn-accent btn-lg">Explore Products →</a>
        <?php if (!isLoggedIn()): ?>
        <a href="<?= APP_URL ?>/register.php" class="btn btn-secondary btn-lg hero-btn-outline">Create Account</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- Search Bar -->
<div class="container hero-search-wrapper">
  <form action="<?= APP_URL ?>/products.php" method="GET">
    <div class="search-bar hero-search-bar">
      <input type="text" name="q" placeholder="Search products..." value="<?= e($_GET['q'] ?? '') ?>">
      <button type="submit" class="btn btn-primary btn-sm">🔍 Search</button>
    </div>
  </form>
</div>

<!-- Categories -->
<section class="section">
  <div class="container">
    <div class="section-header">
      <h2 class="section-title">Browse by Category</h2>
      <p class="section-subtitle">Find exactly what you're looking for</p>
    </div>
    <div class="grid grid-4 gap-2">
      <?php foreach ($categories as $cat): ?>
      <a href="<?= APP_URL ?>/products.php?category=<?= e($cat['slug']) ?>" class="card" style="text-decoration:none;transition:all var(--transition)">
        <div class="card-body" style="display:flex;align-items:center;gap:1rem">
          <div style="width:48px;height:48px;background:linear-gradient(135deg,var(--purple),var(--blue-dark));border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;font-size:1.4rem;flex-shrink:0">
            <?= e($cat['icon'] ?: '📁') ?>
          </div>
          <div>
            <div style="font-weight:700;font-family:var(--font-display)"><?= e($cat['name']) ?></div>
            <div style="font-size:.8rem;color:var(--text-muted)"><?= $cat['product_count'] ?> products</div>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Featured Products -->
<section class="section" style="background:var(--surface2);padding:3.5rem 0">
  <div class="container">
    <div class="flex-between section-header" style="align-items:flex-end">
      <div>
        <h2 class="section-title">Featured Products</h2>
        <p class="section-subtitle">Handpicked premium digital downloads</p>
      </div>
      <a href="<?= APP_URL ?>/products.php" class="btn btn-secondary btn-sm">View All →</a>
    </div>
    <div class="grid grid-4 gap-2 fade-in">
      <?php foreach ($featuredProducts as $product): ?>
      <div class="product-card">
        <div class="product-card-image">
          <?php if ($product['thumbnail']): ?>
            <img src="<?= UPLOADS_URL ?>/screenshots/<?= e($product['thumbnail']) ?>" alt="<?= e($product['title']) ?>">
          <?php else: ?>
            <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:3rem;background:linear-gradient(135deg,var(--purple),var(--blue-dark))">📦</div>
          <?php endif; ?>
          <span class="product-card-badge"><?= e($product['category_name'] ?? 'Digital') ?></span>
        </div>
        <div class="product-card-body">
          <div class="product-card-title"><?= e($product['title']) ?></div>
          <div class="product-card-price"><?= formatPrice($product['price']) ?></div>
        </div>
        <div class="product-card-footer">
          <a href="<?= APP_URL ?>/product.php?slug=<?= e($product['slug']) ?>" class="btn btn-secondary btn-sm" style="flex:1">View</a>
          <button class="btn btn-primary btn-sm" style="flex:1" onclick="addToCart(<?= $product['id'] ?>, this)">🛒 Add</button>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Why Us -->
<section class="section">
  <div class="container">
    <div class="text-center section-header">
      <h2 class="section-title">Why Choose MBHaat?</h2>
    </div>
    <div class="grid grid-4 gap-2">
      <?php
      $features = [
        ['icon'=>'⚡','title'=>'Instant Delivery','desc'=>'Download immediately after payment. No waiting.'],
        ['icon'=>'🔒','title'=>'Secure Payment','desc'=>'Bkash, Nagad, SSL, Visa, Mastercard & Bank.'],
        ['icon'=>'📁','title'=>'Quality Products','desc'=>'Curated premium digital products only.'],
        ['icon'=>'🎧','title'=>'24/7 Support','desc'=>'Our team is always here to help you.'],
      ];
      foreach ($features as $f):
      ?>
      <div class="card card-body text-center" style="padding:2rem 1.5rem">
        <div style="font-size:2.5rem;margin-bottom:1rem"><?= $f['icon'] ?></div>
        <h4 style="margin-bottom:.5rem"><?= $f['title'] ?></h4>
        <p class="text-muted text-small"><?= $f['desc'] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Testimonials -->
<?php if (!empty($testimonials)): ?>
<section class="section" style="background:var(--purple-dark);padding:4rem 0">
  <div class="container">
    <div class="text-center section-header">
      <h2 class="section-title" style="color:var(--blue-light)">What Customers Say</h2>
    </div>
    <div class="grid grid-3 gap-2">
      <?php foreach ($testimonials as $t): ?>
      <div style="background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:var(--radius);padding:1.5rem">
        <div style="color:var(--gold);font-size:1rem;margin-bottom:.75rem"><?= str_repeat('★', $t['rating']) ?><?= str_repeat('☆', 5 - $t['rating']) ?></div>
        <p style="color:rgba(255,255,255,.85);font-size:.9rem;line-height:1.7;margin-bottom:1rem">"<?= e($t['message']) ?>"</p>
        <div style="display:flex;align-items:center;gap:.75rem">
          <div style="width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,var(--blue),var(--purple));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:.9rem"><?= strtoupper(substr($t['name'], 0, 1)) ?></div>
          <div>
            <div style="color:#fff;font-weight:600;font-size:.9rem"><?= e($t['name']) ?></div>
            <?php if ($t['role']): ?><div style="color:var(--blue-light);font-size:.75rem"><?= e($t['role']) ?></div><?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php include __DIR__ . '/src/views/layouts/footer.php'; ?>
