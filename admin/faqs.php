<?php
require_once __DIR__ . '/../src/init.php';
requireAdmin();
$pageTitle = 'FAQs — Admin';
$action = sanitize($_GET['action'] ?? '');
$editId = (int)($_GET['edit'] ?? 0);
$errors = [];

if (isset($_GET['delete'])) {
    Database::execute("DELETE FROM faqs WHERE id=?", [(int)$_GET['delete']]);
    flash('success','FAQ deleted.'); redirect(APP_URL.'/admin/faqs.php');
}
if (isset($_GET['toggle'])) {
    Database::execute("UPDATE faqs SET is_active=IF(is_active=1,0,1) WHERE id=?", [(int)$_GET['toggle']]);
    redirect(APP_URL.'/admin/faqs.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $question = sanitize($_POST['question'] ?? '');
    $answer   = sanitize($_POST['answer'] ?? '');
    $order    = (int)($_POST['sort_order'] ?? 0);
    $id       = (int)($_POST['id'] ?? 0);

    if (!$question) $errors[] = 'Question is required.';
    if (!$answer) $errors[] = 'Answer is required.';

    if (!$errors) {
        if ($id) {
            Database::execute("UPDATE faqs SET question=?,answer=?,sort_order=? WHERE id=?", [$question,$answer,$order,$id]);
            flash('success','FAQ updated.');
        } else {
            Database::insert("INSERT INTO faqs (question,answer,sort_order) VALUES (?,?,?)", [$question,$answer,$order]);
            flash('success','FAQ added.');
        }
        redirect(APP_URL.'/admin/faqs.php');
    }
}

$editFaq = $editId ? Database::fetch("SELECT * FROM faqs WHERE id=?", [$editId]) : null;
$faqs    = Database::fetchAll("SELECT * FROM faqs ORDER BY sort_order, id");

include __DIR__ . '/partials/header.php';
?>

<?php if ($action==='add' || $editFaq): ?>
<div class="admin-page-header">
  <h1><?= $editFaq ? 'Edit FAQ' : 'Add FAQ' ?></h1>
  <a href="<?= APP_URL ?>/admin/faqs.php" class="btn btn-secondary">← Back</a>
</div>
<?php if ($errors): ?><div class="alert alert-danger"><?php foreach($errors as $e): ?><div><?= e($e) ?></div><?php endforeach; ?></div><?php endif; ?>
<div class="card form-card card-body">
  <form method="POST">
    <?= csrfField() ?>
    <input type="hidden" name="id" value="<?= $editFaq['id'] ?? 0 ?>">
    <div class="form-group">
      <label class="form-label">Question *</label>
      <input type="text" name="question" class="form-control" required value="<?= e($editFaq['question'] ?? '') ?>">
    </div>
    <div class="form-group">
      <label class="form-label">Answer *</label>
      <textarea name="answer" class="form-control" rows="5" required><?= e($editFaq['answer'] ?? '') ?></textarea>
    </div>
    <div class="form-group" style="max-width:150px">
      <label class="form-label">Sort Order</label>
      <input type="number" name="sort_order" class="form-control" min="0" value="<?= e($editFaq['sort_order'] ?? 0) ?>">
    </div>
    <button type="submit" class="btn btn-primary"><?= $editFaq ? 'Update FAQ' : 'Add FAQ' ?></button>
    <a href="<?= APP_URL ?>/admin/faqs.php" class="btn btn-secondary">Cancel</a>
  </form>
</div>

<?php else: ?>
<div class="admin-page-header">
  <h1>FAQs <span class="badge badge-purple"><?= count($faqs) ?></span></h1>
  <a href="?action=add" class="btn btn-primary">+ Add FAQ</a>
</div>
<div class="card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>Question</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($faqs as $f): ?>
        <tr>
          <td><?= $f['sort_order'] ?></td>
          <td>
            <div style="font-weight:600;margin-bottom:.2rem"><?= e($f['question']) ?></div>
            <div style="font-size:.8rem;color:var(--text-muted)"><?= e(substr($f['answer'],0,80)) ?>...</div>
          </td>
          <td><span class="badge <?= $f['is_active']?'badge-success':'badge-danger' ?>"><?= $f['is_active']?'Active':'Hidden' ?></span></td>
          <td>
            <div class="action-buttons">
              <a href="?edit=<?= $f['id'] ?>" class="btn btn-secondary btn-sm">Edit</a>
              <a href="?toggle=<?= $f['id'] ?>" class="btn btn-sm <?= $f['is_active']?'btn-danger':'btn-primary' ?>"><?= $f['is_active']?'Hide':'Show' ?></a>
              <a href="?delete=<?= $f['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete FAQ?')">🗑</a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($faqs)): ?><tr><td colspan="4" style="text-align:center;padding:2rem;color:var(--text-muted)">No FAQs yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/partials/footer.php'; ?>
