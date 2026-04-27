<?php
require_once __DIR__ . '/../src/init.php';
requireAdmin();
$pageTitle = 'Reports — Admin';

$period = sanitize($_GET['period'] ?? 'month');
$dateFrom = match($period) {
    'today'  => date('Y-m-d'),
    'week'   => date('Y-m-d', strtotime('-7 days')),
    'year'   => date('Y-m-d', strtotime('-1 year')),
    default  => date('Y-m-d', strtotime('-30 days')),
};

$revenue    = Database::fetch("SELECT COALESCE(SUM(total),0) as v, COUNT(*) as c FROM orders WHERE payment_status='paid' AND DATE(created_at) >= ?", [$dateFrom]);
$newUsers   = Database::fetch("SELECT COUNT(*) as v FROM users WHERE DATE(created_at) >= ?", [$dateFrom])['v'];
$topProds   = Database::fetchAll("SELECT p.title, p.price, COUNT(oi.id) as sales, SUM(oi.price) as revenue FROM order_items oi JOIN orders o ON o.id=oi.order_id JOIN products p ON p.id=oi.product_id WHERE o.payment_status='paid' AND DATE(o.created_at)>=? GROUP BY oi.product_id ORDER BY sales DESC LIMIT 10", [$dateFrom]);
$byMethod   = Database::fetchAll("SELECT payment_method, COUNT(*) as c, SUM(total) as revenue FROM orders WHERE payment_status='paid' AND DATE(created_at)>=? GROUP BY payment_method ORDER BY revenue DESC", [$dateFrom]);
$dailySales = Database::fetchAll("SELECT DATE(created_at) as day, COUNT(*) as orders, SUM(total) as revenue FROM orders WHERE payment_status='paid' AND DATE(created_at)>=? GROUP BY DATE(created_at) ORDER BY day", [$dateFrom]);

include __DIR__ . '/partials/header.php';
?>

<div class="admin-page-header">
  <h1>Reports & Analytics</h1>
  <div style="display:flex;gap:.4rem">
    <?php foreach (['today'=>'Today','week'=>'7 Days','month'=>'30 Days','year'=>'1 Year'] as $val=>$label): ?>
    <a href="?period=<?= $val ?>" class="btn <?= $period===$val?'btn-primary':'btn-secondary' ?> btn-sm"><?= $label ?></a>
    <?php endforeach; ?>
  </div>
</div>

<!-- Summary -->
<div class="grid grid-4 gap-2 mb-4">
  <?php
  $summaryStats = [
    ['label'=>'Revenue','value'=>formatPrice($revenue['v']),'icon'=>'💰','color'=>'#36064D'],
    ['label'=>'Orders','value'=>$revenue['c'],'icon'=>'🛒','color'=>'#4fb8c4'],
    ['label'=>'New Users','value'=>$newUsers,'icon'=>'👥','color'=>'#c9a84c'],
    ['label'=>'Avg Order','value'=>$revenue['c']>0?formatPrice($revenue['v']/$revenue['c']):'—','icon'=>'📊','color'=>'#DA4848'],
  ];
  foreach ($summaryStats as $s):
  ?>
  <div class="stat-card">
    <div class="stat-card-icon" style="background:<?= $s['color'] ?>22;color:<?= $s['color'] ?>"><?= $s['icon'] ?></div>
    <div class="stat-card-value"><?= $s['value'] ?></div>
    <div class="stat-card-label"><?= $s['label'] ?></div>
  </div>
  <?php endforeach; ?>
</div>

<div class="grid grid-2 gap-3">
  <!-- Top Products -->
  <div class="card">
    <div class="card-header"><h3 style="font-size:1.05rem">Top Selling Products</h3></div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Product</th><th>Sales</th><th>Revenue</th></tr></thead>
        <tbody>
          <?php foreach ($topProds as $p): ?>
          <tr>
            <td>
              <div style="font-weight:600;font-size:.9rem"><?= e($p['title']) ?></div>
              <div style="font-size:.75rem;color:var(--text-muted)"><?= formatPrice($p['price']) ?> each</div>
            </td>
            <td><span class="badge badge-purple"><?= $p['sales'] ?></span></td>
            <td style="font-weight:600"><?= formatPrice($p['revenue']) ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if(empty($topProds)): ?><tr><td colspan="3" style="text-align:center;padding:1.5rem;color:var(--text-muted)">No sales data.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- By Payment Method -->
  <div class="card">
    <div class="card-header"><h3 style="font-size:1.05rem">Revenue by Payment Method</h3></div>
    <div class="card-body" style="padding:0">
      <?php foreach ($byMethod as $bm): ?>
      <div style="display:flex;align-items:center;justify-content:space-between;padding:.75rem 1.25rem;border-bottom:1px solid var(--border)">
        <span style="font-weight:600;text-transform:uppercase"><?= e($bm['payment_method']) ?></span>
        <div style="text-align:right">
          <div style="font-weight:700;color:var(--purple)"><?= formatPrice($bm['revenue']) ?></div>
          <div style="font-size:.75rem;color:var(--text-muted)"><?= $bm['c'] ?> orders</div>
        </div>
      </div>
      <?php endforeach; ?>
      <?php if(empty($byMethod)): ?><div style="padding:1.5rem;text-align:center;color:var(--text-muted)">No data.</div><?php endif; ?>
    </div>
  </div>
</div>

<!-- Daily Sales Table -->
<div class="card mt-3">
  <div class="card-header flex-between">
    <h3 style="font-size:1.05rem">Daily Sales</h3>
    <span class="text-small text-muted"><?= count($dailySales) ?> days with activity</span>
  </div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Date</th><th>Orders</th><th>Revenue</th></tr></thead>
      <tbody>
        <?php foreach (array_reverse($dailySales) as $day): ?>
        <tr>
          <td><?= date('D, M j, Y', strtotime($day['day'])) ?></td>
          <td><?= $day['orders'] ?></td>
          <td style="font-weight:600"><?= formatPrice($day['revenue']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($dailySales)): ?><tr><td colspan="3" style="text-align:center;padding:1.5rem;color:var(--text-muted)">No sales in this period.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
