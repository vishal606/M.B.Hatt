<?php
require_once __DIR__ . '/../src/init.php';
requireAdmin();
$pageTitle = 'Testimonials — Admin';
$action = sanitize($_GET['action'] ?? '');
$editId = (int)($_GET['edit'] ?? 0);
$errors = [];

if (isset($_GET['delete'])) {
    Database::execute("DELETE FROM testimonials WHERE id=?", [(int)$_GET['delete']]);
    flash('success','Deleted.'); redirect(APP_URL.'/admin/testimonials.php');
}
if (isset($_GET['toggle'])) {
    Database::execute("UPDATE testimonials SET is_active=IF(is_active=1,0,1) WHERE id=?", [(int)$_GET['toggle']]);
    redirect(APP_URL.'/admin/testimonials.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $name    = sanitize($_POST['name'] ?? '');
    $role    = sanitize($_POST['role'] ?? '');
    $message = sanitize($_POST['message'] ?? '');
    $rating  = min(5, max(1, (int)($_POST['rating'] ?? 5)));
    $order   = (int)($_POST['sort_order'] ?? 0);
    $id      = (int)($_POST['id'] ?? 0);

    if (!$name) $errors[] = 'Name required.';
    if (!$message) $errors[] = 'Message required.';

    if (!$errors) {
        if ($id) {
            Database::execute("UPDATE testimonials SET name=?,role=?,message=?,rating=?,sort_order=? WHERE id=?", [$name,$role,$message,$rating,$order,$id]);
            flash('success','Updated.');
        } else {
            Database::insert("INSERT INTO testimonials (name,role,message,rating,sort_order) VALUES (?,?,?,?,?)", [$name,$role,$message,$rating,$order]);
            flash('success','Added.');
        }
        redirect(APP_URL.'/admin/testimonials.php');
    }
}

$editT  = $editId ? Database::fetch("SELECT * FROM testimonials WHERE id=?", [$editId]) : null;
$items  = Database::fetchAll("SELECT * FROM testimonials ORDER BY sort_order, id");

include __DIR__ . '/partials/header.php';
?>

<?php if ($action==='add' || $editT): ?>
<div class="admin-page-header">
  <h1><?= $editT ? 'Edit Testimonial' : 'Add Testimonial' ?></h1>
  <a href="<?= APP_URL ?>/admin/testimonials.php" class="btn btn-secondary">← Back</a>
</div>
<?php if ($errors): ?><div class="alert alert-danger"><?php foreach($errors as $e): ?><div><?= e($e) ?></div><?php endforeach; ?></div><?php endif; ?>
<div class="card form-card card-body">
  <form method="POST">
    <?= csrfField() ?>
    <input type="hidden" name="id" value="<?= $editT['id'] ?? 0 ?>">
    <div class="grid grid-2 gap-3">
      <div class="form-group">
        <label class="form-label">Name *</label>
        <input type="text" name="name" class="form-control" required value="<?= e($editT['name'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Role / Title</label>
        <input type="text" name="role" class="form-control" value="<?= e($editT['role'] ?? '') ?>" placeholder="e.g. Graphic Designer">
      </div>
    </div>
    <div class="form-group">
      <label class="form-label">Message *</label>
      <textarea name="message" class="form-control" rows="4" required><?= e($editT['message'] ?? '') ?></textarea>
    </div>
    <div class="grid grid-2 gap-3">
      <div class="form-group">
        <label class="form-label">Rating (1–5)</label>
        <select name="rating" class="form-control">
          <?php for ($i=5;$i>=1;$i--): ?>
          <option value="<?= $i ?>" <?= ($editT['rating']??5)==$i?'selected':'' ?>><?= $i ?> Stars</option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Sort Order</label>
        <input type="number" name="sort_order" class="form-control" value="<?= e($editT['sort_order'] ?? 0) ?>">
      </div>
    </div>
    <button type="submit" class="btn btn-primary"><?= $editT ? 'Update' : 'Add' ?></button>
    <a href="<?= APP_URL ?>/admin/testimonials.php" class="btn btn-secondary">Cancel</a>
  </form>
</div>

<?php else: ?>
<div class="admin-page-header">
  <h1>Testimonials <span class="badge badge-purple"><?= count($items) ?></span></h1>
  <a href="?action=add" class="btn btn-primary">+ Add Testimonial</a>
</div>
<div class="card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>Name</th><th>Rating</th><th>Message</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($items as $t): ?>
        <tr>
          <td>
            <div style="font-weight:600"><?= e($t['name']) ?></div>
            <div style="font-size:.75rem;color:var(--text-muted)"><?= e($t['role']) ?></div>
          </td>
          <td style="color:var(--gold)"><?= str_repeat('★',$t['rating']) ?></td>
          <td style="font-size:.85rem;color:var(--text-muted);max-width:250px"><?= e(substr($t['message'],0,80)) ?>...</td>
          <td><span class="badge <?= $t['is_active']?'badge-success':'badge-danger' ?>"><?= $t['is_active']?'Active':'Hidden' ?></span></td>
          <td>
            <div class="action-buttons">
              <a href="?edit=<?= $t['id'] ?>" class="btn btn-secondary btn-sm">Edit</a>
              <a href="?toggle=<?= $t['id'] ?>" class="btn btn-sm <?= $t['is_active']?'btn-danger':'btn-primary' ?>"><?= $t['is_active']?'Hide':'Show' ?></a>
              <a href="?delete=<?= $t['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete?')">🗑</a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($items)): ?><tr><td colspan="5" style="text-align:center;padding:2rem;color:var(--text-muted)">No testimonials yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/partials/footer.php'; ?>
