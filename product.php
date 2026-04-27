<?php
require_once __DIR__ . '/src/init.php';

$slug = sanitize($_GET['slug'] ?? '');
if (!$slug) redirect(APP_URL . '/products.php');

$product = Database::fetch(
    "SELECT p.*, c.name as category_name, c.slug as category_slug
     FROM products p LEFT JOIN categories c ON c.id = p.category_id
     WHERE p.slug = ? AND p.status = 'active'",
    [$slug]
);
if (!$product) { http_response_code(404); die("Product not found."); }

$screenshots = Database::fetchAll(
    "SELECT * FROM product_screenshots WHERE product_id = ? ORDER BY sort_order",
    [$product['id']]
);

$related = Database::fetchAll(
    "SELECT p.*, (SELECT image_path FROM product_screenshots WHERE product_id=p.id ORDER BY sort_order LIMIT 1) as thumbnail
     FROM products p WHERE p.category_id = ? AND p.id != ? AND p.status='active' LIMIT 4",
    [$product['category_id'], $product['id']]
);

$pageTitle = e($product['title']) . ' — ' . getSetting('site_name', 'MBHaat.com');
$pageDesc  = substr(strip_tags($product['description']), 0, 160);

// Check if already purchased
$alreadyPurchased = false;
if (isLoggedIn()) {
    $purch = Database::fetch(
        "SELECT oi.id FROM order_items oi JOIN orders o ON o.id = oi.order_id
         WHERE o.user_id = ? AND oi.product_id = ? AND o.payment_status = 'paid'",
        [$_SESSION['user_id'], $product['id']]
    );
    $alreadyPurchased = (bool)$purch;
}

include __DIR__ . '/src/views/layouts/header.php';
?>

<div class="page-body">
<div class="container">
  <!-- Breadcrumb -->
  <nav style="padding:1rem 0;font-size:.85rem;color:var(--text-muted)">
    <a href="<?= APP_URL ?>">Home</a> › 
    <a href="<?= APP_URL ?>/products.php?category=<?= e($product['category_slug']) ?>"><?= e($product['category_name']) ?></a> › 
    <span><?= e($product['title']) ?></span>
  </nav>

  <div class="product-grid-main">
    <!-- Screenshots Gallery -->
    <div class="product-gallery">
      <?php if (!empty($screenshots)): ?>
      <div style="position:relative;border-radius:var(--radius);overflow:hidden;background:var(--surface2);aspect-ratio:4/3">
        <?php foreach ($screenshots as $i => $ss): ?>
        <div class="product-slide <?= $i === 0 ? 'active' : '' ?>" style="display:<?= $i === 0 ? 'block' : 'none' ?>">
          <img src="<?= UPLOADS_URL ?>/screenshots/<?= e($ss['image_path']) ?>" alt="Screenshot <?= $i+1 ?>" style="width:100%;height:100%;object-fit:cover">
        </div>
        <?php endforeach; ?>
        <?php if (count($screenshots) > 1): ?>
        <button onclick="changeSlide(-1)" style="position:absolute;left:.75rem;top:50%;transform:translateY(-50%);background:rgba(0,0,0,.5);color:#fff;border:none;border-radius:50%;width:36px;height:36px;cursor:pointer;font-size:1.1rem">‹</button>
        <button onclick="changeSlide(1)" style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);background:rgba(0,0,0,.5);color:#fff;border:none;border-radius:50%;width:36px;height:36px;cursor:pointer;font-size:1.1rem">›</button>
        <?php endif; ?>
      </div>
      <!-- Thumbnails -->
      <?php if (count($screenshots) > 1): ?>
      <div style="display:flex;gap:.5rem;margin-top:.75rem;overflow-x:auto">
        <?php foreach ($screenshots as $i => $ss): ?>
        <img src="<?= UPLOADS_URL ?>/screenshots/<?= e($ss['image_path']) ?>" onclick="currentSlide=<?= $i ?>;document.querySelectorAll('.product-slide').forEach((s,j)=>s.style.display=j===<?= $i ?>?'block':'none')" style="width:70px;height:55px;object-fit:cover;border-radius:var(--radius-sm);cursor:pointer;border:2px solid var(--border);flex-shrink:0">
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
      <?php else: ?>
      <div style="aspect-ratio:4/3;background:linear-gradient(135deg,var(--purple),var(--blue-dark));border-radius:var(--radius);display:flex;align-items:center;justify-content:center;font-size:5rem">📦</div>
      <?php endif; ?>
    </div>

    <!-- Product Info -->
    <div>
      <div style="margin-bottom:.75rem">
        <span class="badge badge-purple"><?= e($product['category_name'] ?? 'Digital') ?></span>
      </div>
      <h1 style="font-size:1.8rem;margin-bottom:1rem"><?= e($product['title']) ?></h1>
      <div style="font-size:2rem;font-weight:800;color:var(--purple);margin-bottom:1.5rem">
        <?= formatPrice($product['price']) ?>
      </div>

      <div style="display:flex;gap:.75rem;flex-wrap:wrap;margin-bottom:2rem">
        <?php if ($alreadyPurchased): ?>
          <a href="<?= APP_URL ?>/orders.php" class="btn btn-accent btn-lg">⬇️ Download Again</a>
        <?php else: ?>
          <button class="btn btn-primary btn-lg" onclick="addToCart(<?= $product['id'] ?>, this)">🛒 Add to Cart</button>
          <a href="<?= APP_URL ?>/checkout.php?buy_now=<?= $product['id'] ?>" class="btn btn-accent btn-lg">⚡ Buy Now</a>
        <?php endif; ?>
      </div>

      <hr class="divider">

      <div style="display:flex;flex-direction:column;gap:.6rem;font-size:.9rem">
        <div style="display:flex;gap:.75rem">
          <span style="color:var(--text-muted);min-width:100px">📦 Format</span>
          <span>Digital Download</span>
        </div>
        <div style="display:flex;gap:.75rem">
          <span style="color:var(--text-muted);min-width:100px">⚡ Delivery</span>
          <span>Instant after payment</span>
        </div>
        <div style="display:flex;gap:.75rem">
          <span style="color:var(--text-muted);min-width:100px">🔄 Downloads</span>
          <span>Up to <?= getSetting('download_limit', '5') ?> times</span>
        </div>
        <div style="display:flex;gap:.75rem">
          <span style="color:var(--text-muted);min-width:100px">📅 Validity</span>
          <span><?= getSetting('download_expiry_days', '30') ?> days</span>
        </div>
      </div>

      <hr class="divider">
      <div style="display:flex;gap:.75rem;font-size:.8rem;color:var(--text-muted)">
        <span>🔒 Secure Payment</span>
        <span>⚡ Instant Download</span>
        <span>✅ Verified Product</span>
      </div>
    </div>
  </div>

  <!-- Description -->
  <div style="margin-top:3rem">
    <div class="card">
      <div class="card-header"><h3 style="font-size:1.2rem">Product Description</h3></div>
      <div class="card-body" style="line-height:1.8;color:var(--text)">
        <?= nl2br(e($product['description'])) ?>
      </div>
    </div>
  </div>

  <!-- Related Products -->
  <?php if (!empty($related)): ?>
  <div style="margin-top:3rem">
    <h2 style="margin-bottom:1.5rem">Related Products</h2>
    <div class="grid grid-4 gap-2">
      <?php foreach ($related as $rp): ?>
      <div class="product-card">
        <div class="product-card-image">
          <?php if ($rp['thumbnail']): ?>
            <img src="<?= UPLOADS_URL ?>/screenshots/<?= e($rp['thumbnail']) ?>" alt="<?= e($rp['title']) ?>">
          <?php else: ?>
            <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:2.5rem">📦</div>
          <?php endif; ?>
        </div>
        <div class="product-card-body">
          <div class="product-card-title"><?= e($rp['title']) ?></div>
          <div class="product-card-price"><?= formatPrice($rp['price']) ?></div>
        </div>
        <div class="product-card-footer">
          <a href="<?= APP_URL ?>/product.php?slug=<?= e($rp['slug']) ?>" class="btn btn-secondary btn-sm" style="flex:1">View</a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</div>
</div>

<?php include __DIR__ . '/src/views/layouts/footer.php'; ?>
