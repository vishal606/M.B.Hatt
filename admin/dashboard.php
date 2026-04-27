<?php
require_once __DIR__ . '/../src/init.php';
requireAdmin();

$pageTitle = 'Admin Dashboard — ' . getSetting('site_name', 'MBHaat.com');

// Stats
$totalRevenue  = Database::fetch("SELECT COALESCE(SUM(total),0) as v FROM orders WHERE payment_status='paid'")['v'];
$totalOrders   = Database::fetch("SELECT COUNT(*) as v FROM orders")['v'];
$totalUsers    = Database::fetch("SELECT COUNT(*) as v FROM users")['v'];
$totalProducts = Database::fetch("SELECT COUNT(*) as v FROM products")['v'];
$pendingOrders = Database::fetch("SELECT COUNT(*) as v FROM orders WHERE payment_status='pending'")['v'];
$openTickets   = Database::fetch("SELECT COUNT(*) as v FROM tickets WHERE status='open'")['v'];

// Recent orders
$recentOrders = Database::fetchAll(
    "SELECT o.*, u.name as user_name FROM orders o JOIN users u ON u.id=o.user_id ORDER BY o.created_at DESC LIMIT 8"
);

// Monthly revenue (last 6 months)
$monthlyRevenue = Database::fetchAll(
    "SELECT DATE_FORMAT(created_at,'%b %Y') as month, SUM(total) as revenue, COUNT(*) as orders
     FROM orders WHERE payment_status='paid' AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
     GROUP BY YEAR(created_at), MONTH(created_at) ORDER BY created_at"
);

// Top products
$topProducts = Database::fetchAll(
    "SELECT p.title, p.downloads, p.price FROM products p ORDER BY p.downloads DESC LIMIT 5"
);

include __DIR__ . '/partials/header.php';
?>

<div class="dashboard-header">
  <h1>Dashboard</h1>
  <p class="text-muted">Overview of <?= e(getSetting('site_name','MBHaat.com')) ?></p>
</div>

<!-- Stat Cards -->
<div class="grid grid-4 gap-2 mb-4">
  <?php
  $stats = [
    ['icon'=>'💰','label'=>'Total Revenue','value'=>formatPrice($totalRevenue),'color'=>'#36064D'],
    ['icon'=>'🛒','label'=>'Total Orders','value'=>$totalOrders,'color'=>'#4fb8c4'],
    ['icon'=>'👥','label'=>'Registered Users','value'=>$totalUsers,'color'=>'#c9a84c'],
    ['icon'=>'📦','label'=>'Products','value'=>$totalProducts,'color'=>'#DA4848'],
  ];
  foreach ($stats as $s):
  ?>
  <div class="stat-card">
    <div class="stat-card-icon" style="background:<?= $s['color'] ?>22;color:<?= $s['color'] ?>"><?= $s['icon'] ?></div>
    <div class="stat-card-value"><?= $s['value'] ?></div>
    <div class="stat-card-label"><?= $s['label'] ?></div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Alerts -->
<?php if ($pendingOrders > 0 || $openTickets > 0): ?>
<div style="display:flex;gap:1rem;margin-bottom:1.5rem;flex-wrap:wrap">
  <?php if ($pendingOrders > 0): ?>
  <a href="<?= APP_URL ?>/admin/orders.php?status=pending" class="alert alert-warning" style="text-decoration:none;flex:1;min-width:200px">
    ⏳ <strong><?= $pendingOrders ?> pending orders</strong> awaiting review
  </a>
  <?php endif; ?>
  <?php if ($openTickets > 0): ?>
  <a href="<?= APP_URL ?>/admin/tickets.php?status=open" class="alert alert-info" style="text-decoration:none;flex:1;min-width:200px">
    🎧 <strong><?= $openTickets ?> open support tickets</strong> need attention
  </a>
  <?php endif; ?>
</div>
<?php endif; ?>

<div class="grid grid-2 gap-3">
  <!-- Recent Orders -->
  <div class="card">
    <div class="card-header flex-between">
      <h3 style="font-size:1.05rem">Recent Orders</h3>
      <a href="<?= APP_URL ?>/admin/orders.php" class="btn btn-secondary btn-sm">View All</a>
    </div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Order</th><th>User</th><th>Total</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach ($recentOrders as $o): ?>
          <tr>
            <td><a href="<?= APP_URL ?>/admin/orders.php?view=<?= $o['id'] ?>" style="font-weight:600"><?= e($o['order_number']) ?></a></td>
            <td><?= e($o['user_name']) ?></td>
            <td style="font-weight:600"><?= formatPrice($o['total']) ?></td>
            <td><span class="badge <?= $o['payment_status']==='paid'?'badge-success':'badge-warning' ?>"><?= $o['payment_status'] ?></span></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Top Products + Monthly Summary -->
  <div style="display:flex;flex-direction:column;gap:1.5rem">
    <div class="card">
      <div class="card-header"><h3 style="font-size:1.05rem">Top Products by Downloads</h3></div>
      <div class="card-body" style="padding:0">
        <?php foreach ($topProducts as $p): ?>
        <div style="display:flex;align-items:center;justify-content:space-between;padding:.75rem 1.25rem;border-bottom:1px solid var(--border)">
          <div style="flex:1;min-width:0">
            <div style="font-weight:600;font-size:.9rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($p['title']) ?></div>
            <div style="font-size:.75rem;color:var(--text-muted)"><?= formatPrice($p['price']) ?></div>
          </div>
          <span class="badge badge-purple"><?= $p['downloads'] ?> ↓</span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><h3 style="font-size:1.05rem">Monthly Revenue</h3></div>
      <div class="card-body" style="padding:0">
        <?php foreach (array_reverse($monthlyRevenue) as $m): ?>
        <div style="display:flex;align-items:center;justify-content:space-between;padding:.65rem 1.25rem;border-bottom:1px solid var(--border)">
          <span style="font-size:.9rem"><?= e($m['month']) ?></span>
          <div style="text-align:right">
            <div style="font-weight:700;color:var(--purple)"><?= formatPrice($m['revenue']) ?></div>
            <div style="font-size:.75rem;color:var(--text-muted)"><?= $m['orders'] ?> orders</div>
          </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($monthlyRevenue)): ?><div style="padding:1.25rem;text-align:center;color:var(--text-muted)">No sales data yet.</div><?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Quick Actions -->
<div style="margin-top:1.5rem">
  <h3 style="margin-bottom:1rem">Quick Actions</h3>
  <div style="display:flex;gap:.75rem;flex-wrap:wrap">
    <a href="<?= APP_URL ?>/admin/products.php?action=add" class="btn btn-primary">+ Add Product</a>
    <a href="<?= APP_URL ?>/admin/coupons.php?action=add" class="btn btn-secondary">+ Add Coupon</a>
    <a href="<?= APP_URL ?>/admin/faqs.php?action=add" class="btn btn-secondary">+ Add FAQ</a>
    <a href="<?= APP_URL ?>/admin/settings.php" class="btn btn-secondary">⚙️ Settings</a>
    <a href="<?= APP_URL ?>/admin/reports.php" class="btn btn-secondary">📈 Reports</a>
  </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
