<?php
require_once __DIR__ . '/../src/init.php';
requireAdmin();
$pageTitle = 'Coupons — Admin';
$action = sanitize($_GET['action'] ?? '');
$editId = (int)($_GET['edit'] ?? 0);
$errors = [];

// Delete
if (isset($_GET['delete'])) {
    verifyCsrf();
    Database::execute("DELETE FROM coupons WHERE id=?", [(int)$_GET['delete']]);
    flash('success','Coupon deleted.'); redirect(APP_URL.'/admin/coupons.php');
}

// Toggle
if (isset($_GET['toggle'])) {
    Database::execute("UPDATE coupons SET is_active=IF(is_active=1,0,1) WHERE id=?", [(int)$_GET['toggle']]);
    redirect(APP_URL.'/admin/coupons.php');
}

// Save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $code  = strtoupper(sanitize($_POST['code'] ?? ''));
    $type  = in_array($_POST['type']??'',['flat','percentage']) ? $_POST['type'] : 'percentage';
    $value = (float)($_POST['value'] ?? 0);
    $min   = (float)($_POST['min_order'] ?? 0);
    $limit = ($_POST['usage_limit'] ?? '') !== '' ? (int)$_POST['usage_limit'] : null;
    $expiry= sanitize($_POST['expiry_date'] ?? '') ?: null;
    $id    = (int)($_POST['id'] ?? 0);

    if (!$code) $errors[] = 'Code is required.';
    if ($value <= 0) $errors[] = 'Value must be greater than 0.';
    if ($type === 'percentage' && $value > 100) $errors[] = 'Percentage cannot exceed 100%.';

    if (!$errors) {
        if ($id) {
            Database::execute(
                "UPDATE coupons SET code=?,type=?,value=?,min_order=?,usage_limit=?,expiry_date=? WHERE id=?",
                [$code,$type,$value,$min,$limit,$expiry,$id]
            );
            flash('success','Coupon updated.');
        } else {
            Database::insert(
                "INSERT INTO coupons (code,type,value,min_order,usage_limit,expiry_date) VALUES (?,?,?,?,?,?)",
                [$code,$type,$value,$min,$limit,$expiry]
            );
            flash('success','Coupon created.');
        }
        redirect(APP_URL.'/admin/coupons.php');
    }
}

$editCoupon = $editId ? Database::fetch("SELECT * FROM coupons WHERE id=?", [$editId]) : null;
$coupons    = Database::fetchAll("SELECT * FROM coupons ORDER BY created_at DESC");

include __DIR__ . '/partials/header.php';
?>

<?php if ($action === 'add' || $editCoupon): ?>
<div class="admin-page-header">
  <h1><?= $editCoupon ? 'Edit Coupon' : 'Add Coupon' ?></h1>
  <a href="<?= APP_URL ?>/admin/coupons.php" class="btn btn-secondary">← Back</a>
</div>
<?php if ($errors): ?><div class="alert alert-danger"><?php foreach($errors as $e): ?><div><?= e($e) ?></div><?php endforeach; ?></div><?php endif; ?>
<div class="card form-card card-body">
  <form method="POST">
    <?= csrfField() ?>
    <input type="hidden" name="id" value="<?= $editCoupon['id'] ?? 0 ?>">
    <div class="grid grid-2 gap-3">
      <div class="form-group">
        <label class="form-label">Coupon Code *</label>
        <input type="text" name="code" class="form-control" required style="text-transform:uppercase" value="<?= e($editCoupon['code'] ?? '') ?>" placeholder="e.g. SAVE20">
      </div>
      <div class="form-group">
        <label class="form-label">Discount Type</label>
        <select name="type" class="form-control">
          <option value="percentage" <?= ($editCoupon['type']??'percentage')==='percentage'?'selected':'' ?>>Percentage (%)</option>
          <option value="flat" <?= ($editCoupon['type']??'')==='flat'?'selected':'' ?>>Flat Amount (<?= getSetting('currency_symbol','৳') ?>)</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Discount Value *</label>
        <input type="number" name="value" step="0.01" min="0.01" class="form-control" required value="<?= e($editCoupon['value'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Minimum Order Amount</label>
        <input type="number" name="min_order" step="0.01" min="0" class="form-control" value="<?= e($editCoupon['min_order'] ?? '0') ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Usage Limit</label>
        <input type="number" name="usage_limit" min="1" class="form-control" placeholder="Leave blank for unlimited" value="<?= e($editCoupon['usage_limit'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Expiry Date</label>
        <input type="date" name="expiry_date" class="form-control" value="<?= e($editCoupon['expiry_date'] ?? '') ?>">
      </div>
    </div>
    <button type="submit" class="btn btn-primary"><?= $editCoupon ? 'Update Coupon' : 'Create Coupon' ?></button>
    <a href="<?= APP_URL ?>/admin/coupons.php" class="btn btn-secondary">Cancel</a>
  </form>
</div>

<?php else: ?>
<div class="admin-page-header">
  <h1>Coupons <span class="badge badge-purple"><?= count($coupons) ?></span></h1>
  <a href="?action=add" class="btn btn-primary">+ Add Coupon</a>
</div>
<div class="card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>Code</th><th>Type</th><th>Value</th><th>Min Order</th><th>Used/Limit</th><th>Expiry</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($coupons as $c): ?>
        <tr>
          <td style="font-weight:700;font-family:monospace"><?= e($c['code']) ?></td>
          <td><?= ucfirst($c['type']) ?></td>
          <td style="font-weight:600"><?= $c['type']==='percentage' ? $c['value'].'%' : formatPrice($c['value']) ?></td>
          <td><?= $c['min_order'] > 0 ? formatPrice($c['min_order']) : '—' ?></td>
          <td><?= $c['used_count'] ?>/<?= $c['usage_limit'] ?? '∞' ?></td>
          <td style="font-size:.85rem"><?= $c['expiry_date'] ? date('M j, Y', strtotime($c['expiry_date'])) : 'No expiry' ?></td>
          <td><span class="badge <?= $c['is_active']?'badge-success':'badge-danger' ?>"><?= $c['is_active']?'Active':'Disabled' ?></span></td>
          <td>
            <div class="action-buttons">
              <a href="?edit=<?= $c['id'] ?>" class="btn btn-secondary btn-sm">Edit</a>
              <a href="?toggle=<?= $c['id'] ?>" class="btn btn-sm <?= $c['is_active']?'btn-danger':'btn-primary' ?>"><?= $c['is_active']?'Disable':'Enable' ?></a>
              <a href="?delete=<?= $c['id'] ?>&csrf_token=<?= e(csrfToken()) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete coupon?')">🗑</a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($coupons)): ?><tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--text-muted)">No coupons yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/partials/footer.php'; ?>
