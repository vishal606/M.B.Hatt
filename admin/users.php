<?php
require_once __DIR__ . '/../src/init.php';
requireAdmin();
$pageTitle = 'Users — Admin';

// Toggle block
if (isset($_GET['toggle'])) {
    $uid = (int)$_GET['toggle'];
    Database::execute("UPDATE users SET is_blocked = IF(is_blocked=1,0,1) WHERE id=?", [$uid]);
    flash('success','User status updated.');
    redirect(APP_URL.'/admin/users.php');
}

// View user
$viewUser = null;
$userOrders = [];
if (isset($_GET['view'])) {
    $uid = (int)$_GET['view'];
    $viewUser = Database::fetch("SELECT * FROM users WHERE id=?", [$uid]);
    if ($viewUser) {
        $userOrders = Database::fetchAll(
            "SELECT o.* FROM orders o WHERE o.user_id=? ORDER BY o.created_at DESC LIMIT 10",
            [$uid]
        );
    }
}

$search = sanitize($_GET['q'] ?? '');
$where  = ['1=1']; $params = [];
if ($search) { $where[]="(name LIKE ? OR email LIKE ?)"; $params[]="%$search%"; $params[]="%$search%"; }
$users = Database::fetchAll("SELECT * FROM users WHERE " . implode(' AND ',$where) . " ORDER BY created_at DESC", $params);

include __DIR__ . '/partials/header.php';
?>

<?php if ($viewUser): ?>
<div class="admin-page-header">
  <h1>User: <?= e($viewUser['name']) ?></h1>
  <a href="<?= APP_URL ?>/admin/users.php" class="btn btn-secondary">← Users</a>
</div>
<div class="grid grid-2 gap-3">
  <div class="card card-body">
    <h4 style="margin-bottom:1rem">User Info</h4>
    <?php
    $spent = Database::fetch("SELECT COALESCE(SUM(total),0) as v FROM orders WHERE user_id=? AND payment_status='paid'", [$viewUser['id']])['v'];
    $info = [
      'Name' => e($viewUser['name']),
      'Email' => e($viewUser['email']),
      'Status' => $viewUser['is_blocked'] ? '<span class="badge badge-danger">Blocked</span>' : '<span class="badge badge-success">Active</span>',
      'Joined' => date('M j, Y', strtotime($viewUser['created_at'])),
      'Total Spent' => formatPrice($spent),
    ];
    foreach ($info as $k => $v):
    ?>
    <div style="display:flex;justify-content:space-between;padding:.4rem 0;border-bottom:1px solid var(--border);font-size:.9rem">
      <span class="text-muted"><?= $k ?></span><span><?= $v ?></span>
    </div>
    <?php endforeach; ?>
    <div style="margin-top:1rem">
      <a href="?toggle=<?= $viewUser['id'] ?>" class="btn <?= $viewUser['is_blocked']?'btn-primary':'btn-danger' ?> btn-sm"><?= $viewUser['is_blocked']?'Unblock User':'Block User' ?></a>
    </div>
  </div>
  <div class="card">
    <div class="card-header"><h3 style="font-size:1.05rem">Recent Orders</h3></div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Order</th><th>Total</th><th>Status</th><th>Date</th></tr></thead>
        <tbody>
          <?php foreach ($userOrders as $o): ?>
          <tr>
            <td><a href="<?= APP_URL ?>/admin/orders.php?view=<?= $o['id'] ?>"><?= e($o['order_number']) ?></a></td>
            <td><?= formatPrice($o['total']) ?></td>
            <td><span class="badge <?= $o['payment_status']==='paid'?'badge-success':'badge-warning' ?>"><?= $o['payment_status'] ?></span></td>
            <td style="font-size:.8rem"><?= date('M j, Y', strtotime($o['created_at'])) ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($userOrders)): ?><tr><td colspan="4" style="text-align:center;color:var(--text-muted);padding:1rem">No orders.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php else: ?>

<div class="admin-page-header">
  <h1>Users <span class="badge badge-purple"><?= count($users) ?></span></h1>
</div>

<div class="admin-filters">
  <form method="GET" style="display:flex;gap:.5rem">
    <input type="text" name="q" class="form-control" placeholder="Search by name or email..." value="<?= e($search) ?>">
    <button type="submit" class="btn btn-secondary">Search</button>
    <a href="<?= APP_URL ?>/admin/users.php" class="btn btn-secondary">Reset</a>
  </form>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>Name</th><th>Email</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
          <td style="font-weight:600"><?= e($u['name']) ?></td>
          <td><?= e($u['email']) ?></td>
          <td><span class="badge <?= $u['is_blocked']?'badge-danger':'badge-success' ?>"><?= $u['is_blocked']?'Blocked':'Active' ?></span></td>
          <td style="font-size:.85rem"><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
          <td>
            <div class="action-buttons">
              <a href="?view=<?= $u['id'] ?>" class="btn btn-secondary btn-sm">View</a>
              <a href="?toggle=<?= $u['id'] ?>" class="btn <?= $u['is_blocked']?'btn-primary':'btn-danger' ?> btn-sm"><?= $u['is_blocked']?'Unblock':'Block' ?></a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($users)): ?><tr><td colspan="5" style="text-align:center;padding:2rem;color:var(--text-muted)">No users found.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/partials/footer.php'; ?>
