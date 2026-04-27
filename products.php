<?php
require_once __DIR__ . '/src/init.php';

$pageTitle = 'Products — ' . getSetting('site_name', 'MBHaat.com');

$q        = sanitize($_GET['q'] ?? '');
$catSlug  = sanitize($_GET['category'] ?? '');
$sort     = sanitize($_GET['sort'] ?? 'newest');
$page     = max(1, (int)($_GET['page'] ?? 1));
$perPage  = 12;

// Build query
$where   = ["p.status = 'active'"];
$params  = [];

if ($q) {
    $where[] = "(p.title LIKE ? OR p.description LIKE ?)";
    $params[] = "%$q%"; $params[] = "%$q%";
}

$category = null;
if ($catSlug) {
    $category = Database::fetch("SELECT * FROM categories WHERE slug = ?", [$catSlug]);
    if ($category) { $where[] = "p.category_id = ?"; $params[] = $category['id']; }
}

$orderBy = match($sort) {
    'price_asc'  => "p.price ASC",
    'price_desc' => "p.price DESC",
    'popular'    => "p.downloads DESC",
    default      => "p.created_at DESC"
};

$sql = "SELECT p.*, c.name as category_name,
        (SELECT image_path FROM product_screenshots WHERE product_id = p.id ORDER BY sort_order LIMIT 1) as thumbnail
        FROM products p LEFT JOIN categories c ON c.id = p.category_id
        WHERE " . implode(' AND ', $where) . " ORDER BY $orderBy";

$result = paginate($sql, $params, $page, $perPage);
$categories = Database::fetchAll("SELECT * FROM categories ORDER BY name");

include __DIR__ . '/src/views/layouts/header.php';
?>

<div class="page-body">
<div class="container">
  <!-- Page Header -->
  <div style="padding:2rem 0 1rem">
    <h1 style="font-size:2rem;margin-bottom:.25rem"><?= $category ? e($category['name']) : ($q ? "Search: \"" . e($q) . "\"" : 'All Products') ?></h1>
    <p class="text-muted"><?= $result['total'] ?> product<?= $result['total'] != 1 ? 's' : '' ?> found</p>
  </div>

  <div style="display:flex;gap:2rem;align-items:flex-start">
    <!-- Sidebar Filters (Desktop) -->
    <aside style="width:220px;flex-shrink:0" class="d-none d-md-block">
      <div class="card card-body mb-2">
        <h4 style="font-size:1rem;margin-bottom:1rem">Categories</h4>
        <div style="display:flex;flex-direction:column;gap:.35rem">
          <a href="<?= APP_URL ?>/products.php" class="<?= !$catSlug ? 'fw-bold' : '' ?>" style="font-size:.9rem;color:var(--text)">All Categories</a>
          <?php foreach ($categories as $cat): ?>
          <a href="<?= APP_URL ?>/products.php?category=<?= e($cat['slug']) ?>" class="<?= $catSlug === $cat['slug'] ? 'fw-bold' : '' ?>" style="font-size:.9rem;color:var(--text)">
            <?= e($cat['name']) ?>
          </a>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="card card-body">
        <h4 style="font-size:1rem;margin-bottom:1rem">Sort By</h4>
        <div style="display:flex;flex-direction:column;gap:.35rem">
          <?php
          $sorts = ['newest'=>'Newest First','price_asc'=>'Price: Low→High','price_desc'=>'Price: High→Low','popular'=>'Most Popular'];
          foreach ($sorts as $val => $label):
          ?>
          <a href="?<?= http_build_query(array_merge($_GET, ['sort'=>$val,'page'=>1])) ?>" style="font-size:.9rem;color:var(--text)" class="<?= $sort === $val ? 'fw-bold' : '' ?>"><?= $label ?></a>
          <?php endforeach; ?>
        </div>
      </div>
    </aside>

    <!-- Products Grid -->
    <div style="flex:1;min-width:0">
      <!-- Mobile filters row -->
      <div style="display:flex;gap:.5rem;margin-bottom:1.25rem;overflow-x:auto;padding-bottom:.5rem" class="d-md-none">
        <form action="" method="GET" style="display:flex;gap:.5rem">
          <select name="category" class="form-control" style="font-size:.8rem;padding:.4rem .6rem" onchange="this.form.submit()">
            <option value="">All Categories</option>
            <?php foreach ($categories as $cat): ?>
            <option value="<?= e($cat['slug']) ?>" <?= $catSlug === $cat['slug'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <select name="sort" class="form-control" style="font-size:.8rem;padding:.4rem .6rem" onchange="this.form.submit()">
            <?php foreach ($sorts as $val => $label): ?>
            <option value="<?= $val ?>" <?= $sort === $val ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
          </select>
          <?php if ($q): ?><input type="hidden" name="q" value="<?= e($q) ?>"><?php endif; ?>
        </form>
      </div>

      <?php if (empty($result['items'])): ?>
      <div class="card card-body text-center" style="padding:4rem 2rem">
        <div style="font-size:3rem;margin-bottom:1rem">🔍</div>
        <h3>No products found</h3>
        <p class="text-muted">Try adjusting your search or filters.</p>
        <a href="<?= APP_URL ?>/products.php" class="btn btn-primary mt-2">View All Products</a>
      </div>
      <?php else: ?>

      <div class="grid grid-3 gap-2 fade-in">
        <?php foreach ($result['items'] as $product): ?>
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
            <a href="<?= APP_URL ?>/product.php?slug=<?= e($product['slug']) ?>" class="btn btn-secondary btn-sm" style="flex:1">Details</a>
            <button class="btn btn-primary btn-sm" style="flex:1" onclick="addToCart(<?= $product['id'] ?>, this)">🛒 Cart</button>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Pagination -->
      <?php if ($result['totalPages'] > 1): ?>
      <div class="pagination">
        <?php if ($page > 1): ?>
          <a href="?<?= http_build_query(array_merge($_GET, ['page'=>$page-1])) ?>">‹</a>
        <?php endif; ?>
        <?php for ($i = max(1,$page-2); $i <= min($result['totalPages'],$page+2); $i++): ?>
          <?php if ($i == $page): ?>
            <span class="active"><?= $i ?></span>
          <?php else: ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page'=>$i])) ?>"><?= $i ?></a>
          <?php endif; ?>
        <?php endfor; ?>
        <?php if ($page < $result['totalPages']): ?>
          <a href="?<?= http_build_query(array_merge($_GET, ['page'=>$page+1])) ?>">›</a>
        <?php endif; ?>
      </div>
      <?php endif; ?>

      <?php endif; ?>
    </div>
  </div>
</div>
</div>

<?php include __DIR__ . '/src/views/layouts/footer.php'; ?>
