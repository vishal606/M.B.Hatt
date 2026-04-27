<?php
require_once __DIR__ . '/../src/init.php';
requireAdmin();
$pageTitle = 'Categories — Admin';
$action = sanitize($_GET['action'] ?? '');
$editId = (int)($_GET['edit'] ?? 0);
$errors = [];

if (isset($_GET['delete'])) {
    Database::execute("DELETE FROM categories WHERE id=?", [(int)$_GET['delete']]);
    flash('success','Category deleted.'); redirect(APP_URL.'/admin/categories.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $name = sanitize($_POST['name'] ?? '');
    $desc = sanitize($_POST['description'] ?? '');
    $id   = (int)($_POST['id'] ?? 0);

    if (!$name) $errors[] = 'Name is required.';

    if (!$errors) {
        $slug = uniqueSlug('categories', makeSlug($name), $id);
        if ($id) {
            Database::execute("UPDATE categories SET name=?,slug=?,description=? WHERE id=?", [$name,$slug,$desc,$id]);
            flash('success','Category updated.');
        } else {
            Database::insert("INSERT INTO categories (name,slug,description) VALUES (?,?,?)", [$name,$slug,$desc]);
            flash('success','Category added.');
        }
        redirect(APP_URL.'/admin/categories.php');
    }
}

$editCat    = $editId ? Database::fetch("SELECT * FROM categories WHERE id=?", [$editId]) : null;
$categories = Database::fetchAll("SELECT c.*, COUNT(p.id) as product_count FROM categories c LEFT JOIN products p ON p.category_id=c.id GROUP BY c.id ORDER BY c.name");

include __DIR__ . '/partials/header.php';
?>

<?php if ($action==='add' || $editCat): ?>
<div class="admin-page-header">
  <h1><?= $editCat ? 'Edit Category' : 'Add Category' ?></h1>
  <a href="<?= APP_URL ?>/admin/categories.php" class="btn btn-secondary">← Back</a>
</div>
<?php if ($errors): ?><div class="alert alert-danger"><?php foreach($errors as $e): ?><div><?= e($e) ?></div><?php endforeach; ?></div><?php endif; ?>
<div class="card form-card card-body">
  <form method="POST">
    <?= csrfField() ?>
    <input type="hidden" name="id" value="<?= $editCat['id'] ?? 0 ?>">
    <div class="form-group">
      <label class="form-label">Category Name *</label>
      <input type="text" name="name" class="form-control" required value="<?= e($editCat['name'] ?? '') ?>">
    </div>
    <div class="form-group">
      <label class="form-label">Description</label>
      <textarea name="description" class="form-control" rows="3"><?= e($editCat['description'] ?? '') ?></textarea>
    </div>
    <button type="submit" class="btn btn-primary"><?= $editCat ? 'Update' : 'Add Category' ?></button>
    <a href="<?= APP_URL ?>/admin/categories.php" class="btn btn-secondary">Cancel</a>
  </form>
</div>

<?php else: ?>
<div class="admin-page-header">
  <h1>Categories <span class="badge badge-purple"><?= count($categories) ?></span></h1>
  <a href="?action=add" class="btn btn-primary">+ Add Category</a>
</div>
<div class="card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>Name</th><th>Slug</th><th>Products</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($categories as $c): ?>
        <tr>
          <td style="font-weight:600"><?= e($c['name']) ?></td>
          <td><code><?= e($c['slug']) ?></code></td>
          <td><?= $c['product_count'] ?></td>
          <td>
            <div class="action-buttons">
              <a href="?edit=<?= $c['id'] ?>" class="btn btn-secondary btn-sm">Edit</a>
              <a href="?delete=<?= $c['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete category?')">🗑</a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/partials/footer.php'; ?>
