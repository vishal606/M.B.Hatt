<?php
$pageTitle = 'Categories';
include 'includes/header.php';

$errors = [];

// Get all categories
$categories = $pdo->query("SELECT c.*, COUNT(p.id) as product_count 
    FROM categories c 
    LEFT JOIN products p ON c.id = p.category_id 
    GROUP BY c.id ORDER BY c.sort_order, c.name")->fetchAll();

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $catId = intval($_POST['cat_id'] ?? 0);
    $name = sanitizeInput($_POST['name'] ?? '');
    $slug = sanitizeInput($_POST['slug'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $sortOrder = intval($_POST['sort_order'] ?? 0);
    $status = sanitizeInput($_POST['status'] ?? 'active');
    
    if (empty($name)) {
        $errors[] = "Category name is required";
    }
    
    if (empty($slug)) {
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
    }
    
    if (empty($errors)) {
        if ($catId > 0) {
            // Update
            $pdo->prepare("UPDATE categories SET name = ?, slug = ?, description = ?, sort_order = ?, status = ? WHERE id = ?")
                ->execute([$name, $slug, $description, $sortOrder, $status, $catId]);
            $_SESSION['flash_message'] = "Category updated successfully";
        } else {
            // Insert
            $pdo->prepare("INSERT INTO categories (name, slug, description, sort_order, status) VALUES (?, ?, ?, ?, ?)")
                ->execute([$name, $slug, $description, $sortOrder, $status]);
            $_SESSION['flash_message'] = "Category added successfully";
        }
        
        $_SESSION['flash_type'] = "success";
        redirect(APP_URL . '/admin/categories.php');
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    
    // Check if category has products
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
    $checkStmt->execute([$deleteId]);
    
    if ($checkStmt->fetchColumn() > 0) {
        $_SESSION['flash_message'] = "Cannot delete category with existing products";
        $_SESSION['flash_type'] = "error";
    } else {
        $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$deleteId]);
        $_SESSION['flash_message'] = "Category deleted successfully";
        $_SESSION['flash_type'] = "success";
    }
    
    redirect(APP_URL . '/admin/categories.php');
}

// Get category for edit
$editCategory = null;
if (isset($_GET['edit'])) {
    $editStmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $editStmt->execute([intval($_GET['edit'])]);
    $editCategory = $editStmt->fetch();
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold text-brand-purple mb-0">Categories</h4>
</div>

<div class="row g-4">
    <!-- Category Form -->
    <div class="col-lg-4">
        <div class="card admin-card">
            <div class="card-header">
                <h5 class="mb-0 fw-bold"><?php echo $editCategory ? 'Edit Category' : 'Add Category'; ?></h5>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                    <div><?php echo $error; ?></div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="" class="admin-form">
                    <input type="hidden" name="cat_id" value="<?php echo $editCategory['id'] ?? ''; ?>">
                    
                    <div class="mb-3">
                        <label>Name *</label>
                        <input type="text" name="name" class="form-control" value="<?php echo $editCategory['name'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label>Slug</label>
                        <input type="text" name="slug" class="form-control" value="<?php echo $editCategory['slug'] ?? ''; ?>" placeholder="auto-generated">
                    </div>
                    
                    <div class="mb-3">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3"><?php echo $editCategory['description'] ?? ''; ?></textarea>
                    </div>
                    
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label>Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="<?php echo $editCategory['sort_order'] ?? '0'; ?>">
                        </div>
                        <div class="col-6">
                            <label>Status</label>
                            <select name="status" class="form-select">
                                <option value="active" <?php echo ($editCategory['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo ($editCategory['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-brand-purple w-100">
                        <i class="fas fa-save me-2"></i><?php echo $editCategory ? 'Update' : 'Add'; ?> Category
                    </button>
                    
                    <?php if ($editCategory): ?>
                    <a href="categories.php" class="btn btn-outline-secondary w-100 mt-2">
                        <i class="fas fa-times me-2"></i>Cancel
                    </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Categories List -->
    <div class="col-lg-8">
        <div class="card admin-card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table admin-table mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Products</th>
                                <th>Order</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td class="fw-bold"><?php echo $cat['name']; ?></td>
                                <td><?php echo $cat['slug']; ?></td>
                                <td><?php echo $cat['product_count']; ?></td>
                                <td><?php echo $cat['sort_order']; ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $cat['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($cat['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="categories.php?edit=<?php echo $cat['id']; ?>" class="action-btn edit">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <a href="categories.php?delete=<?php echo $cat['id']; ?>" class="action-btn delete" onclick="return confirmDelete()">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
