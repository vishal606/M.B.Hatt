<?php
require_once __DIR__ . '/src/init.php';
requireLogin();

$userId = $_SESSION['user_id'];
$user   = currentUser();

$totalOrders   = Database::fetch("SELECT COUNT(*) as c FROM orders WHERE user_id=?", [$userId])['c'];
$totalSpent    = Database::fetch("SELECT COALESCE(SUM(total),0) as s FROM orders WHERE user_id=? AND payment_status='paid'", [$userId])['s'];
$totalProducts = Database::fetch(
    "SELECT COUNT(*) as c FROM order_items oi JOIN orders o ON o.id=oi.order_id WHERE o.user_id=? AND o.payment_status='paid'",
    [$userId]
)['c'];

$recentOrders = Database::fetchAll(
    "SELECT o.*,
     (SELECT GROUP_CONCAT(p.title SEPARATOR ', ') FROM order_items oi JOIN products p ON p.id=oi.product_id WHERE oi.order_id=o.id) as product_titles
     FROM orders o WHERE o.user_id=? ORDER BY o.created_at DESC LIMIT 5",
    [$userId]
);

$pageTitle = 'Dashboard — ' . getSetting('site_name', 'MBHaat.com');
include __DIR__ . '/src/views/layouts/header.php';
?>

<div class="page-body">
<div class="container">
  <div style="padding:1.5rem 0 1rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem">
    <div>
      <h1>Welcome, <?= e($user['name'] ?? 'User') ?>! 👋</h1>
      <p class="text-muted">Manage your purchases and account settings</p>
    </div>
    <a href="<?= APP_URL ?>/products.php" class="btn btn-primary">Browse Products</a>
  </div>

  <!-- Stats -->
  <div class="grid grid-3 gap-2 mb-4">
    <div class="stat-card">
      <div class="stat-card-icon" style="background:rgba(54,6,77,0.1)">🛍️</div>
      <div class="stat-card-value"><?= $totalOrders ?></div>
      <div class="stat-card-label">Total Orders</div>
    </div>
    <div class="stat-card">
      <div class="stat-card-icon" style="background:rgba(118,210,219,0.15)">📦</div>
      <div class="stat-card-value"><?= $totalProducts ?></div>
      <div class="stat-card-label">Products Purchased</div>
    </div>
    <div class="stat-card">
      <div class="stat-card-icon" style="background:rgba(218,72,72,0.1)">💰</div>
      <div class="stat-card-value"><?= formatPrice($totalSpent) ?></div>
      <div class="stat-card-label">Total Spent</div>
    </div>
  </div>

  <div class="grid grid-2 gap-3">
    <!-- Recent Orders -->
    <div class="card">
      <div class="card-header flex-between">
        <h3 style="font-size:1.05rem">Recent Orders</h3>
        <a href="<?= APP_URL ?>/orders.php" class="btn btn-secondary btn-sm">View All</a>
      </div>
      <div class="card-body" style="padding:0">
        <?php if (empty($recentOrders)): ?>
          <div style="padding:2rem;text-align:center;color:var(--text-muted)">No orders yet.</div>
        <?php else: ?>
          <?php foreach ($recentOrders as $order): ?>
          <div style="padding:.85rem 1.25rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:1rem">
            <div style="flex:1;min-width:0">
              <div style="font-weight:600;font-size:.9rem;margin-bottom:.15rem"><?= e($order['order_number']) ?></div>
              <div style="font-size:.8rem;color:var(--text-muted);overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($order['product_titles']) ?></div>
            </div>
            <div style="text-align:right;flex-shrink:0">
              <div style="font-weight:700;color:var(--purple)"><?= formatPrice($order['total']) ?></div>
              <span class="badge <?= $order['payment_status']==='paid'?'badge-success':'badge-warning' ?>"><?= $order['payment_status'] ?></span>
            </div>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Quick Links -->
    <div style="display:flex;flex-direction:column;gap:1rem">
      <?php
      $links = [
        ['icon'=>'📦','title'=>'My Orders','desc'=>'View & download purchased products','url'=>APP_URL.'/orders.php','color'=>'#36064D'],
        ['icon'=>'👤','title'=>'Edit Profile','desc'=>'Update your name, email & password','url'=>APP_URL.'/profile.php','color'=>'#4fb8c4'],
        ['icon'=>'🎧','title'=>'Support Tickets','desc'=>'Get help or submit a request','url'=>APP_URL.'/tickets.php','color'=>'#DA4848'],
        ['icon'=>'❓','title'=>'FAQ','desc'=>'Find answers to common questions','url'=>APP_URL.'/faq.php','color'=>'#c9a84c'],
      ];
      foreach ($links as $link):
      ?>
      <a href="<?= e($link['url']) ?>" class="card" style="text-decoration:none">
        <div class="card-body" style="display:flex;align-items:center;gap:1rem">
          <div style="width:46px;height:46px;border-radius:var(--radius-sm);background:<?= $link['color'] ?>;display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0"><?= $link['icon'] ?></div>
          <div>
            <div style="font-weight:700;margin-bottom:.15rem"><?= $link['title'] ?></div>
            <div style="font-size:.8rem;color:var(--text-muted)"><?= $link['desc'] ?></div>
          </div>
          <span style="margin-left:auto;color:var(--text-muted)">›</span>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>
</div>

<?php include __DIR__ . '/src/views/layouts/footer.php'; ?>
