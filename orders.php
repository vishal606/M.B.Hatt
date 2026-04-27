<?php
require_once __DIR__ . '/src/init.php';
requireLogin();

$userId = $_SESSION['user_id'];
$pageTitle = 'My Orders — ' . getSetting('site_name', 'MBHaat.com');

// Handle download
if (isset($_GET['download']) && isset($_GET['token'])) {
    $token = sanitize($_GET['token']);
    $item = Database::fetch(
        "SELECT oi.*, p.file_path, p.title, o.user_id 
         FROM order_items oi 
         JOIN orders o ON o.id = oi.order_id
         JOIN products p ON p.id = oi.product_id
         WHERE oi.download_token = ? AND o.user_id = ? AND o.payment_status = 'paid'",
        [$token, $userId]
    );

    if (!$item) { flash('danger','Invalid download link.'); redirect(APP_URL . '/orders.php'); }
    if ($item['download_count'] >= $item['download_limit']) { flash('danger','Download limit reached.'); redirect(APP_URL . '/orders.php'); }
    if ($item['download_expiry'] && strtotime($item['download_expiry']) < time()) { flash('danger','Download link expired.'); redirect(APP_URL . '/orders.php'); }

    $filePath = PRODUCT_UPLOAD_PATH . '/' . $item['file_path'];
    if (!file_exists($filePath)) { flash('danger','File not found. Please contact support.'); redirect(APP_URL . '/orders.php'); }

    Database::execute("UPDATE order_items SET download_count = download_count + 1 WHERE id = ?", [$item['id']]);

    $filename = basename($filePath);
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($filePath));
    header('Cache-Control: no-cache');
    readfile($filePath);
    exit;
}

$orders = Database::fetchAll(
    "SELECT o.*, 
     (SELECT GROUP_CONCAT(p.title SEPARATOR ', ') FROM order_items oi JOIN products p ON p.id = oi.product_id WHERE oi.order_id = o.id) as product_titles,
     (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
     FROM orders o WHERE o.user_id = ? ORDER BY o.created_at DESC",
    [$userId]
);

// Single order view
$viewOrder = null;
$viewItems = [];
if (isset($_GET['order'])) {
    $orderId = (int)$_GET['order'];
    $viewOrder = Database::fetch("SELECT * FROM orders WHERE id = ? AND user_id = ?", [$orderId, $userId]);
    if ($viewOrder) {
        $viewItems = Database::fetchAll(
            "SELECT oi.*, p.title, p.slug
             FROM order_items oi JOIN products p ON p.id = oi.product_id
             WHERE oi.order_id = ?",
            [$orderId]
        );
    }
}

include __DIR__ . '/src/views/layouts/header.php';
?>

<div class="page-body">
<div class="container">
  <div style="padding:1.5rem 0 1rem">
    <h1>My Orders</h1>
    <p class="text-muted">Your purchase history and downloads</p>
  </div>

  <?php if ($viewOrder): ?>
  <!-- Single Order Detail -->
  <div style="margin-bottom:1rem">
    <a href="<?= APP_URL ?>/orders.php" class="btn btn-secondary btn-sm">← Back to Orders</a>
  </div>

  <div class="card mb-3">
    <div class="card-header flex-between">
      <div>
        <strong>Order #<?= e($viewOrder['order_number']) ?></strong>
        <span class="text-muted text-small" style="margin-left:.75rem"><?= date('M j, Y g:i A', strtotime($viewOrder['created_at'])) ?></span>
      </div>
      <span class="badge <?= $viewOrder['payment_status'] === 'paid' ? 'badge-success' : 'badge-warning' ?>"><?= strtoupper($viewOrder['payment_status']) ?></span>
    </div>
    <div class="card-body">
      <div class="grid grid-3 gap-2 mb-3">
        <div><div class="text-muted text-small">Payment Method</div><strong><?= strtoupper(e($viewOrder['payment_method'])) ?></strong></div>
        <div><div class="text-muted text-small">Transaction ID</div><strong><?= $viewOrder['transaction_id'] ? e($viewOrder['transaction_id']) : 'N/A' ?></strong></div>
        <div><div class="text-muted text-small">Total Paid</div><strong style="color:var(--purple)"><?= formatPrice($viewOrder['total']) ?></strong></div>
      </div>

      <h4 style="margin-bottom:1rem">Downloads</h4>
      <div style="display:flex;flex-direction:column;gap:.75rem">
        <?php foreach ($viewItems as $item): ?>
        <div class="card" style="border-radius:var(--radius-sm)">
          <div class="card-body" style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap">
            <div style="flex:1;min-width:200px">
              <div style="font-weight:600;margin-bottom:.25rem"><?= e($item['title']) ?></div>
              <div style="font-size:.8rem;color:var(--text-muted)">
                Downloads: <?= $item['download_count'] ?>/<?= $item['download_limit'] ?> · 
                <?php if ($item['download_expiry']): ?>
                  Expires: <?= date('M j, Y', strtotime($item['download_expiry'])) ?>
                <?php else: ?>No expiry<?php endif; ?>
              </div>
            </div>
            <?php if ($viewOrder['payment_status'] === 'paid' && $item['download_count'] < $item['download_limit'] && (!$item['download_expiry'] || strtotime($item['download_expiry']) > time())): ?>
              <a href="<?= APP_URL ?>/orders.php?download=1&token=<?= e($item['download_token']) ?>" class="btn btn-primary btn-sm">⬇️ Download</a>
            <?php elseif ($item['download_count'] >= $item['download_limit']): ?>
              <span class="badge badge-warning">Limit Reached</span>
            <?php elseif ($item['download_expiry'] && strtotime($item['download_expiry']) < time()): ?>
              <span class="badge badge-danger">Expired</span>
            <?php else: ?>
              <span class="badge badge-warning">Pending Payment</span>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <?php elseif (empty($orders)): ?>

  <div class="card card-body text-center" style="padding:4rem 2rem;max-width:500px;margin:0 auto">
    <div style="font-size:4rem;margin-bottom:1rem">📦</div>
    <h2>No orders yet</h2>
    <p class="text-muted mt-1">Your purchases will appear here once you place an order.</p>
    <a href="<?= APP_URL ?>/products.php" class="btn btn-primary mt-3">Browse Products</a>
  </div>

  <?php else: ?>

  <div style="display:flex;flex-direction:column;gap:1rem">
    <?php foreach ($orders as $order): ?>
    <div class="card">
      <div class="card-body" style="display:flex;align-items:center;gap:1.25rem;flex-wrap:wrap">
        <div style="flex:1;min-width:200px">
          <div style="font-weight:700;margin-bottom:.25rem">Order #<?= e($order['order_number']) ?></div>
          <div style="font-size:.85rem;color:var(--text-muted)"><?= e($order['product_titles']) ?></div>
          <div style="font-size:.8rem;color:var(--text-muted);margin-top:.25rem"><?= date('M j, Y', strtotime($order['created_at'])) ?> · <?= $order['item_count'] ?> item<?= $order['item_count'] != 1 ? 's' : '' ?></div>
        </div>
        <div style="text-align:right;flex-shrink:0">
          <div style="font-size:1.2rem;font-weight:700;color:var(--purple)"><?= formatPrice($order['total']) ?></div>
          <span class="badge <?= $order['payment_status'] === 'paid' ? 'badge-success' : 'badge-warning' ?> mt-1"><?= strtoupper($order['payment_status']) ?></span>
        </div>
        <a href="<?= APP_URL ?>/orders.php?order=<?= $order['id'] ?>" class="btn btn-secondary btn-sm">View & Download →</a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <?php endif; ?>
</div>
</div>

<?php include __DIR__ . '/src/views/layouts/footer.php'; ?>
