<?php
require_once __DIR__ . '/src/init.php';

$q       = sanitize($_GET['q'] ?? '');
$results = [];

if (strlen($q) >= 2) {
    $results = Database::fetchAll(
        "SELECT p.*, c.name as category_name,
         (SELECT image_path FROM product_screenshots WHERE product_id=p.id ORDER BY sort_order LIMIT 1) as thumbnail
         FROM products p LEFT JOIN categories c ON c.id=p.category_id
         WHERE p.status='active' AND (p.title LIKE ? OR p.description LIKE ?)
         ORDER BY p.downloads DESC LIMIT 20",
        ["%$q%", "%$q%"]
    );
}

$pageTitle = ($q ? "Search: \"$q\"" : 'Search') . ' — ' . getSetting('site_name', 'MBHaat.com');
include __DIR__ . '/src/views/layouts/header.php';
?>

<div class="page-body">
<div class="container">
  <div style="padding:2rem 0 1.5rem">
    <form action="<?= APP_URL ?>/search.php" method="GET">
      <div class="search-bar" style="max-width:600px">
        <input type="text" name="q" placeholder="Search products..." value="<?= e($q) ?>" autofocus>
        <button type="submit" class="btn btn-primary btn-sm">🔍 Search</button>
      </div>
    </form>
  </div>

  <?php if ($q): ?>
    <p class="text-muted mb-3"><?= count($results) ?> result<?= count($results) != 1 ? 's' : '' ?> for "<strong><?= e($q) ?></strong>"</p>
    <?php if (empty($results)): ?>
      <div class="card card-body text-center" style="padding:3rem">
        <div style="font-size:3rem;margin-bottom:1rem">🔍</div>
        <h3>No results found</h3>
        <p class="text-muted mt-1">Try different keywords or browse all products.</p>
        <a href="<?= APP_URL ?>/products.php" class="btn btn-primary mt-3">Browse All Products</a>
      </div>
    <?php else: ?>
      <div class="grid grid-4 gap-2">
        <?php foreach ($results as $product): ?>
        <div class="product-card">
          <div class="product-card-image">
            <?php if ($product['thumbnail']): ?>
              <img src="<?= UPLOADS_URL ?>/screenshots/<?= e($product['thumbnail']) ?>" alt="<?= e($product['title']) ?>">
            <?php else: ?>
              <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:3rem">📦</div>
            <?php endif; ?>
            <span class="product-card-badge"><?= e($product['category_name'] ?? 'Digital') ?></span>
          </div>
          <div class="product-card-body">
            <div class="product-card-title"><?= e($product['title']) ?></div>
            <div class="product-card-price"><?= formatPrice($product['price']) ?></div>
          </div>
          <div class="product-card-footer">
            <a href="<?= APP_URL ?>/product.php?slug=<?= e($product['slug']) ?>" class="btn btn-secondary btn-sm" style="flex:1">View</a>
            <button class="btn btn-primary btn-sm" style="flex:1" onclick="addToCart(<?= $product['id'] ?>, this)">🛒 Cart</button>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>
</div>

<?php include __DIR__ . '/src/views/layouts/footer.php'; ?>
