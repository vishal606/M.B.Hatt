<?php
require_once __DIR__ . '/../src/init.php';
requireAdmin();

$pageTitle = 'Orders — Admin';

// View single order
if (isset($_GET['view'])) {
    $orderId = (int)$_GET['view'];
    $order   = Database::fetch("SELECT o.*, u.name as user_name, u.email as user_email FROM orders o JOIN users u ON u.id=o.user_id WHERE o.id=?", [$orderId]);
    if (!$order) { flash('danger','Order not found.'); redirect(APP_URL.'/admin/orders.php'); }
    $items = Database::fetchAll("SELECT oi.*, p.title FROM order_items oi JOIN products p ON p.id=oi.product_id WHERE oi.order_id=?", [$orderId]);

    // Update status
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
        verifyCsrf();
        $payStatus = sanitize($_POST['payment_status'] ?? '');
        $ordStatus = sanitize($_POST['status'] ?? '');
        Database::execute("UPDATE orders SET payment_status=?, status=? WHERE id=?", [$payStatus, $ordStatus, $orderId]);
        flash('success', 'Order updated.');
        redirect(APP_URL.'/admin/orders.php?view='.$orderId);
    }

    include __DIR__ . '/partials/header.php';
    ?>
    <div class="admin-page-header">
      <h1>Order #<?= e($order['order_number']) ?></h1>
      <a href="<?= APP_URL ?>/admin/orders.php" class="btn btn-secondary">← Orders</a>
    </div>

    <div class="grid grid-2 gap-3">
      <div class="card card-body">
        <h4 style="margin-bottom:1rem">Order Details</h4>
        <?php
        $details = [
          'Customer'    => e($order['user_name']) . ' (' . e($order['user_email']) . ')',
          'Date'        => date('M j, Y g:i A', strtotime($order['created_at'])),
          'Payment'     => strtoupper($order['payment_method']),
          'Transaction' => $order['transaction_id'] ?: 'N/A',
          'Subtotal'    => formatPrice($order['subtotal']),
          'Discount'    => formatPrice($order['discount']),
          'Tax'         => formatPrice($order['tax']),
          'Total'       => formatPrice($order['total']),
        ];
        foreach ($details as $k => $v):
        ?>
        <div style="display:flex;justify-content:space-between;padding:.4rem 0;border-bottom:1px solid var(--border);font-size:.9rem">
          <span class="text-muted"><?= $k ?></span>
          <span style="font-weight:600"><?= $v ?></span>
        </div>
        <?php endforeach; ?>
      </div>

      <div class="card card-body">
        <h4 style="margin-bottom:1rem">Update Status</h4>
        <form method="POST">
          <?= csrfField() ?>
          <div class="form-group">
            <label class="form-label">Payment Status</label>
            <select name="payment_status" class="form-control">
              <?php foreach (['pending','paid','failed','refunded'] as $s): ?>
              <option value="<?= $s ?>" <?= $order['payment_status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Order Status</label>
            <select name="status" class="form-control">
              <?php foreach (['pending','processing','completed','cancelled','refunded'] as $s): ?>
              <option value="<?= $s ?>" <?= $order['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="submit" name="update_status" class="btn btn-primary">Update Order</button>
        </form>
      </div>
    </div>

    <div class="card mt-3">
      <div class="card-header"><h3 style="font-size:1.05rem">Order Items</h3></div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Product</th><th>Price</th><th>Downloads</th><th>Limit</th><th>Expiry</th></tr></thead>
          <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
              <td><strong><?= e($item['title']) ?></strong></td>
              <td><?= formatPrice($item['price']) ?></td>
              <td><?= $item['download_count'] ?></td>
              <td><?= $item['download_limit'] ?></td>
              <td><?= $item['download_expiry'] ? date('M j, Y', strtotime($item['download_expiry'])) : 'No expiry' ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php
    include __DIR__ . '/partials/footer.php';
    exit;
}

// Orders list
$search = sanitize($_GET['q'] ?? '');
$status = sanitize($_GET['status'] ?? '');
$method = sanitize($_GET['method'] ?? '');
$where  = ['1=1']; $params = [];
if ($search) { $where[]="(o.order_number LIKE ? OR u.name LIKE ?)"; $params[]="%$search%"; $params[]="%$search%"; }
if ($status) { $where[]="o.payment_status=?"; $params[]=$status; }
if ($method) { $where[]="o.payment_method=?"; $params[]=$method; }

$orders = Database::fetchAll(
    "SELECT o.*, u.name as user_name FROM orders o JOIN users u ON u.id=o.user_id WHERE " . implode(' AND ',$where) . " ORDER BY o.created_at DESC",
    $params
);

include __DIR__ . '/partials/header.php';
?>

<div class="admin-page-header">
  <h1>Orders <span class="badge badge-purple"><?= count($orders) ?></span></h1>
</div>

<div class="admin-filters">
  <form method="GET" style="display:flex;gap:.5rem;flex-wrap:wrap">
    <input type="text" name="q" class="form-control" placeholder="Order# or user..." value="<?= e($search) ?>">
    <select name="status" class="form-control">
      <option value="">All Payment Status</option>
      <?php foreach (['pending','paid','failed','refunded'] as $s): ?>
      <option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="method" class="form-control">
      <option value="">All Methods</option>
      <?php foreach (['bkash','nagad','ssl','bank','visa','mastercard'] as $m): ?>
      <option value="<?= $m ?>" <?= $method===$m?'selected':'' ?>><?= strtoupper($m) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-secondary">Filter</button>
    <a href="<?= APP_URL ?>/admin/orders.php" class="btn btn-secondary">Reset</a>
  </form>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>Order #</th><th>Customer</th><th>Total</th><th>Method</th><th>Payment</th><th>Date</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($orders as $o): ?>
        <tr>
          <td style="font-weight:700"><?= e($o['order_number']) ?></td>
          <td><?= e($o['user_name']) ?></td>
          <td style="font-weight:600"><?= formatPrice($o['total']) ?></td>
          <td><span style="text-transform:uppercase;font-size:.8rem;font-weight:600"><?= e($o['payment_method']) ?></span></td>
          <td><span class="badge <?= $o['payment_status']==='paid'?'badge-success':($o['payment_status']==='failed'?'badge-danger':'badge-warning') ?>"><?= $o['payment_status'] ?></span></td>
          <td style="font-size:.85rem"><?= date('M j, Y', strtotime($o['created_at'])) ?></td>
          <td><a href="?view=<?= $o['id'] ?>" class="btn btn-secondary btn-sm">View</a></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($orders)): ?>
        <tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--text-muted)">No orders found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
