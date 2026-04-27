<?php
require_once __DIR__ . '/src/init.php';

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    verifyCsrf();

    $action    = sanitize($_POST['action']);
    $productId = (int)($_POST['product_id'] ?? 0);

    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Please login first.', 'redirect' => APP_URL . '/login.php']);
        exit;
    }

    $userId = $_SESSION['user_id'];

    if ($action === 'add') {
        $product = Database::fetch("SELECT id, title, status FROM products WHERE id = ? AND status = 'active'", [$productId]);
        if (!$product) { echo json_encode(['success' => false, 'message' => 'Product not available.']); exit; }

        // Check already in cart
        $exists = Database::fetch("SELECT id FROM cart_items WHERE user_id = ? AND product_id = ?", [$userId, $productId]);
        if ($exists) { echo json_encode(['success' => true, 'message' => 'Already in cart.', 'cart_count' => cartCount()]); exit; }

        Database::insert("INSERT IGNORE INTO cart_items (user_id, product_id) VALUES (?,?)", [$userId, $productId]);
        echo json_encode(['success' => true, 'message' => 'Added to cart!', 'cart_count' => cartCount()]);
        exit;
    }

    if ($action === 'remove') {
        Database::execute("DELETE FROM cart_items WHERE user_id = ? AND product_id = ?", [$userId, $productId]);
        echo json_encode(['success' => true, 'cart_count' => cartCount()]);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    exit;
}

requireLogin();

$items = cartItems();
$total = cartTotal();
$pageTitle = 'My Cart — ' . getSetting('site_name', 'MBHaat.com');

include __DIR__ . '/src/views/layouts/header.php';
?>

<div class="page-body">
<div class="container">
  <div style="padding:1.5rem 0 1rem">
    <h1>Shopping Cart</h1>
    <p class="text-muted"><?= count($items) ?> item<?= count($items) != 1 ? 's' : '' ?></p>
  </div>

  <?php if (empty($items)): ?>
  <div class="card card-body text-center" style="padding:4rem 2rem;max-width:500px;margin:0 auto">
    <div style="font-size:4rem;margin-bottom:1rem">🛒</div>
    <h2>Your cart is empty</h2>
    <p class="text-muted mt-1">Browse our products and add something you like!</p>
    <a href="<?= APP_URL ?>/products.php" class="btn btn-primary mt-3">Explore Products</a>
  </div>
  <?php else: ?>

  <div style="display:grid;grid-template-columns:1fr 340px;gap:2rem;align-items:start">
    <!-- Cart Items -->
    <div style="display:flex;flex-direction:column;gap:1rem">
      <?php foreach ($items as $item): ?>
      <div class="card">
        <div class="card-body" style="display:flex;align-items:center;gap:1.25rem">
          <div style="width:80px;height:64px;border-radius:var(--radius-sm);overflow:hidden;flex-shrink:0;background:linear-gradient(135deg,var(--purple),var(--blue-dark))">
            <?php if ($item['thumbnail']): ?>
              <img src="<?= UPLOADS_URL ?>/screenshots/<?= e($item['thumbnail']) ?>" style="width:100%;height:100%;object-fit:cover">
            <?php else: ?>
              <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:1.75rem">📦</div>
            <?php endif; ?>
          </div>
          <div style="flex:1;min-width:0">
            <div style="font-family:var(--font-display);font-weight:600;margin-bottom:.25rem"><?= e($item['title']) ?></div>
            <div style="font-size:.85rem;color:var(--text-muted)">Digital Download · Instant Access</div>
          </div>
          <div style="font-size:1.25rem;font-weight:700;color:var(--purple);flex-shrink:0"><?= formatPrice($item['price']) ?></div>
          <button onclick="removeFromCart(<?= $item['product_id'] ?>)" class="btn btn-danger btn-sm" style="flex-shrink:0">🗑</button>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Order Summary -->
    <div class="card card-body" style="position:sticky;top:80px">
      <h3 style="margin-bottom:1.25rem">Order Summary</h3>

      <div style="display:flex;flex-direction:column;gap:.6rem;font-size:.9rem">
        <?php foreach ($items as $item): ?>
        <div style="display:flex;justify-content:space-between">
          <span style="color:var(--text-muted);flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;margin-right:.5rem"><?= e($item['title']) ?></span>
          <span style="font-weight:600;flex-shrink:0"><?= formatPrice($item['price']) ?></span>
        </div>
        <?php endforeach; ?>
      </div>

      <hr class="divider">

      <div style="display:flex;justify-content:space-between;font-weight:700;font-size:1.1rem">
        <span>Total</span>
        <span style="color:var(--purple)"><?= formatPrice($total) ?></span>
      </div>

      <a href="<?= APP_URL ?>/checkout.php" class="btn btn-primary btn-block mt-3 btn-lg">
        Proceed to Checkout →
      </a>
      <a href="<?= APP_URL ?>/products.php" class="btn btn-secondary btn-block mt-2">
        Continue Shopping
      </a>
    </div>
  </div>

  <?php endif; ?>
</div>
</div>

<?php include __DIR__ . '/src/views/layouts/footer.php'; ?>
