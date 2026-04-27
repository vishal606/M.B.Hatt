<?php
require_once __DIR__ . '/../src/init.php';
requireAdmin();

$pageTitle = 'Products — Admin';
$action    = sanitize($_GET['action'] ?? '');
$editId    = (int)($_GET['edit'] ?? 0);
$errors    = [];

// Delete
if (isset($_GET['delete'])) {
    verifyCsrf();
    $pid = (int)$_GET['delete'];
    $p   = Database::fetch("SELECT * FROM products WHERE id=?", [$pid]);
    if ($p) {
        if ($p['file_path'] && file_exists(PRODUCT_UPLOAD_PATH . '/' . $p['file_path'])) unlink(PRODUCT_UPLOAD_PATH . '/' . $p['file_path']);
        Database::execute("DELETE FROM products WHERE id=?", [$pid]);
        flash('success', 'Product deleted.');
    }
    redirect(APP_URL . '/admin/products.php');
}

// Toggle status
if (isset($_GET['toggle'])) {
    $pid = (int)$_GET['toggle'];
    Database::execute("UPDATE products SET status = IF(status='active','inactive','active') WHERE id=?", [$pid]);
    redirect(APP_URL . '/admin/products.php');
}

// Save (add/edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $title      = sanitize($_POST['title'] ?? '');
    $description= sanitize($_POST['description'] ?? '');
    $price      = (float)($_POST['price'] ?? 0);
    $categoryId = (int)($_POST['category_id'] ?? 0) ?: null;
    $status     = in_array($_POST['status'] ?? '', ['active','inactive']) ? $_POST['status'] : 'active';
    $id         = (int)($_POST['id'] ?? 0);

    if (!$title) $errors[] = 'Title is required.';
    if ($price < 0) $errors[] = 'Price must be 0 or more.';

    if (!$errors) {
        $slug = uniqueSlug('products', makeSlug($title), $id);

        // File upload
        $filePath = $_POST['existing_file'] ?? null;
        if (!empty($_FILES['product_file']['name'])) {
            $res = uploadFile($_FILES['product_file'], PRODUCT_UPLOAD_PATH, [], MAX_FILE_SIZE);
            if ($res['success']) $filePath = $res['filename'];
            else $errors[] = $res['error'];
        }

        if (!$errors) {
            if ($id) {
                Database::execute(
                    "UPDATE products SET title=?,slug=?,description=?,price=?,category_id=?,status=?,file_path=? WHERE id=?",
                    [$title,$slug,$description,$price,$categoryId,$status,$filePath,$id]
                );
                flash('success','Product updated.');
            } else {
                $id = Database::insert(
                    "INSERT INTO products (title,slug,description,price,category_id,status,file_path) VALUES (?,?,?,?,?,?,?)",
                    [$title,$slug,$description,$price,$categoryId,$status,$filePath]
                );
                flash('success','Product added.');
            }

            // Screenshots
            if (!empty($_FILES['screenshots']['name'][0])) {
                foreach ($_FILES['screenshots']['tmp_name'] as $i => $tmp) {
                    if (!$tmp) continue;
                    $file = ['name'=>$_FILES['screenshots']['name'][$i],'type'=>$_FILES['screenshots']['type'][$i],'tmp_name'=>$tmp,'error'=>$_FILES['screenshots']['error'][$i],'size'=>$_FILES['screenshots']['size'][$i]];
                    $res = uploadFile($file, SCREENSHOT_UPLOAD_PATH, ALLOWED_IMAGE_TYPES, MAX_IMAGE_SIZE);
                    if ($res['success']) {
                        Database::insert("INSERT INTO product_screenshots (product_id,image_path,sort_order) VALUES (?,?,?)", [$id,$res['filename'],$i]);
                    }
                }
            }
            redirect(APP_URL . '/admin/products.php');
        }
    }
}

$categories  = Database::fetchAll("SELECT * FROM categories ORDER BY name");
$editProduct = $editId ? Database::fetch("SELECT * FROM products WHERE id=?", [$editId]) : null;
$editScreenshots = $editId ? Database::fetchAll("SELECT * FROM product_screenshots WHERE product_id=? ORDER BY sort_order", [$editId]) : [];

// List
$search = sanitize($_GET['q'] ?? '');
$status = sanitize($_GET['status'] ?? '');
$where  = ['1=1']; $params = [];
if ($search) { $where[] = "p.title LIKE ?"; $params[] = "%$search%"; }
if ($status) { $where[] = "p.status=?"; $params[] = $status; }
$products = Database::fetchAll(
    "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON c.id=p.category_id WHERE " . implode(' AND ', $where) . " ORDER BY p.created_at DESC",
    $params
);

include __DIR__ . '/partials/header.php';
?>

<?php if ($action === 'add' || $editProduct): ?>
<!-- Add / Edit Form -->
<div class="admin-page-header">
  <h1><?= $editProduct ? 'Edit Product' : 'Add New Product' ?></h1>
  <a href="<?= APP_URL ?>/admin/products.php" class="btn btn-secondary">← Back</a>
</div>

<?php if ($errors): ?><div class="alert alert-danger"><?php foreach($errors as $e): ?><div><?= e($e) ?></div><?php endforeach; ?></div><?php endif; ?>

<div class="card form-card">
  <div class="card-body">
    <form method="POST" enctype="multipart/form-data">
      <?= csrfField() ?>
      <input type="hidden" name="id" value="<?= $editProduct['id'] ?? 0 ?>">
      <input type="hidden" name="existing_file" value="<?= e($editProduct['file_path'] ?? '') ?>">

      <div class="grid grid-2 gap-3">
        <div class="form-group">
          <label class="form-label">Product Title *</label>
          <input type="text" name="title" class="form-control" required value="<?= e($editProduct['title'] ?? $_POST['title'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Price (<?= getSetting('currency_symbol','৳') ?>) *</label>
          <input type="number" name="price" step="0.01" min="0" class="form-control" required value="<?= e($editProduct['price'] ?? $_POST['price'] ?? '0') ?>">
        </div>
      </div>

      <div class="grid grid-2 gap-3">
        <div class="form-group">
          <label class="form-label">Category</label>
          <select name="category_id" class="form-control">
            <option value="">— Select Category —</option>
            <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>" <?= ($editProduct['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Status</label>
          <select name="status" class="form-control">
            <option value="active" <?= ($editProduct['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
            <option value="inactive" <?= ($editProduct['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="6"><?= e($editProduct['description'] ?? '') ?></textarea>
      </div>

      <div class="form-group">
        <label class="form-label">Product File (Digital Download)</label>
        <?php if (!empty($editProduct['file_path'])): ?>
          <div style="margin-bottom:.5rem;font-size:.85rem;color:var(--text-muted)">Current: <?= e($editProduct['file_path']) ?></div>
        <?php endif; ?>
        <input type="file" name="product_file" class="form-control">
        <div class="form-text">Upload the downloadable file (ZIP, PDF, etc.). Max 500MB.</div>
      </div>

      <div class="form-group">
        <label class="form-label">Screenshots / Preview Images</label>
        <?php if ($editScreenshots): ?>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:.5rem">
          <?php foreach ($editScreenshots as $ss): ?>
          <div style="position:relative">
            <img src="<?= UPLOADS_URL ?>/screenshots/<?= e($ss['image_path']) ?>" style="width:80px;height:60px;object-fit:cover;border-radius:var(--radius-sm)">
            <a href="<?= APP_URL ?>/admin/products.php?del_screenshot=<?= $ss['id'] ?>&edit=<?= $editId ?>" style="position:absolute;top:-5px;right:-5px;background:var(--red);color:#fff;border-radius:50%;width:18px;height:18px;display:flex;align-items:center;justify-content:center;font-size:.7rem;text-decoration:none" onclick="return confirm('Delete this screenshot?')">×</a>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <input type="file" name="screenshots[]" class="form-control" multiple accept="image/*">
        <div class="form-text">You can select multiple images. First image will be the thumbnail.</div>
      </div>

      <div style="display:flex;gap:.75rem">
        <button type="submit" class="btn btn-primary"><?= $editProduct ? 'Update Product' : 'Add Product' ?></button>
        <a href="<?= APP_URL ?>/admin/products.php" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>

<?php else: ?>
<!-- Product List -->
<div class="admin-page-header">
  <h1>Products <span class="badge badge-purple"><?= count($products) ?></span></h1>
  <a href="?action=add" class="btn btn-primary">+ Add Product</a>
</div>

<div class="admin-filters">
  <form method="GET" style="display:flex;gap:.5rem;flex-wrap:wrap">
    <input type="text" name="q" class="form-control" placeholder="Search products..." value="<?= e($search) ?>">
    <select name="status" class="form-control">
      <option value="">All Status</option>
      <option value="active" <?= $status==='active'?'selected':'' ?>>Active</option>
      <option value="inactive" <?= $status==='inactive'?'selected':'' ?>>Inactive</option>
    </select>
    <button type="submit" class="btn btn-secondary">Filter</button>
    <a href="<?= APP_URL ?>/admin/products.php" class="btn btn-secondary">Reset</a>
  </form>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>Image</th><th>Title</th><th>Category</th><th>Price</th><th>Downloads</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($products as $p): ?>
        <?php $thumb = Database::fetch("SELECT image_path FROM product_screenshots WHERE product_id=? ORDER BY sort_order LIMIT 1", [$p['id']]); ?>
        <tr>
          <td>
            <?php if ($thumb): ?>
              <img src="<?= UPLOADS_URL ?>/screenshots/<?= e($thumb['image_path']) ?>" class="table-thumb">
            <?php else: ?>
              <div class="table-thumb" style="display:flex;align-items:center;justify-content:center;font-size:1.2rem">📦</div>
            <?php endif; ?>
          </td>
          <td>
            <div style="font-weight:600"><?= e($p['title']) ?></div>
            <div style="font-size:.75rem;color:var(--text-muted)"><?= e($p['slug']) ?></div>
          </td>
          <td><?= e($p['category_name'] ?? '—') ?></td>
          <td style="font-weight:600"><?= formatPrice($p['price']) ?></td>
          <td><?= $p['downloads'] ?></td>
          <td>
            <span class="status-dot <?= $p['status'] ?>"></span>
            <span style="font-size:.85rem"><?= ucfirst($p['status']) ?></span>
          </td>
          <td>
            <div class="action-buttons">
              <a href="?edit=<?= $p['id'] ?>" class="btn btn-secondary btn-sm">Edit</a>
              <a href="<?= APP_URL ?>/product.php?slug=<?= e($p['slug']) ?>" target="_blank" class="btn btn-accent btn-sm">View</a>
              <a href="?toggle=<?= $p['id'] ?>" class="btn btn-sm <?= $p['status']==='active'?'btn-danger':'btn-primary' ?>"><?= $p['status']==='active'?'Disable':'Enable' ?></a>
              <a href="?delete=<?= $p['id'] ?>&csrf_token=<?= e(csrfToken()) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this product?')">🗑</a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($products)): ?>
        <tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--text-muted)">No products found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php endif; ?>

<?php include __DIR__ . '/partials/footer.php'; ?>
