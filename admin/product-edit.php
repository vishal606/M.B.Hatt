<?php
$pageTitle = isset($_GET['id']) ? 'Edit Product' : 'Add Product';
include 'includes/header.php';

$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$product = null;
$errors = [];

// Get categories
$categories = $pdo->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name")->fetchAll();

if ($productId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    
    if (!$product) {
        redirect(APP_URL . '/admin/products.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name'] ?? '');
    $slug = sanitizeInput($_POST['slug'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $shortDesc = sanitizeInput($_POST['short_description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $salePrice = !empty($_POST['sale_price']) ? floatval($_POST['sale_price']) : null;
    $categoryId = intval($_POST['category_id'] ?? 0);
    $status = sanitizeInput($_POST['status'] ?? 'active');
    $featured = isset($_POST['featured']) ? 1 : 0;
    $downloadLimit = intval($_POST['download_limit'] ?? -1);
    $downloadExpiry = intval($_POST['download_expiry'] ?? 0);
    
    if (empty($name)) $errors[] = "Product name is required";
    if ($price <= 0) $errors[] = "Price must be greater than 0";
    
    // Generate slug if empty
    if (empty($slug)) {
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
    }
    
    // Handle file upload
    $filePath = $product['file_path'] ?? '';
    if (isset($_FILES['product_file']) && $_FILES['product_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../assets/uploads/products/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        
        $fileName = uniqid() . '_' . basename($_FILES['product_file']['name']);
        $filePath = 'assets/uploads/products/' . $fileName;
        
        if (!move_uploaded_file($_FILES['product_file']['tmp_name'], '../' . $filePath)) {
            $errors[] = "Failed to upload product file";
        }
    }
    
    // Handle thumbnail upload
    $thumbnail = $product['thumbnail'] ?? '';
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../assets/uploads/thumbnails/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        
        $thumbName = uniqid() . '_' . basename($_FILES['thumbnail']['name']);
        $thumbnail = 'assets/uploads/thumbnails/' . $thumbName;
        
        if (!move_uploaded_file($_FILES['thumbnail']['tmp_name'], '../' . $thumbnail)) {
            $errors[] = "Failed to upload thumbnail";
        }
    }
    
    if (empty($errors)) {
        $screenshots = [];
        if (isset($_FILES['screenshots'])) {
            $uploadDir = '../assets/uploads/screenshots/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            
            for ($i = 0; $i < count($_FILES['screenshots']['tmp_name']); $i++) {
                if ($_FILES['screenshots']['error'][$i] === UPLOAD_ERR_OK) {
                    $shotName = uniqid() . '_' . basename($_FILES['screenshots']['name'][$i]);
                    $shotPath = 'assets/uploads/screenshots/' . $shotName;
                    
                    if (move_uploaded_file($_FILES['screenshots']['tmp_name'][$i], '../' . $shotPath)) {
                        $screenshots[] = $shotPath;
                    }
                }
            }
        }
        
        // Keep existing screenshots
        if (!empty($product['screenshots'])) {
            $existing = json_decode($product['screenshots'], true) ?: [];
            $screenshots = array_merge($existing, $screenshots);
        }
        
        if ($productId > 0) {
            // Update
            $stmt = $pdo->prepare("UPDATE products SET 
                name = ?, slug = ?, description = ?, short_description = ?, price = ?, sale_price = ?,
                category_id = ?, file_path = ?, thumbnail = ?, screenshots = ?, status = ?, featured = ?,
                download_limit = ?, download_expiry_days = ? WHERE id = ?");
            
            $stmt->execute([
                $name, $slug, $description, $shortDesc, $price, $salePrice, $categoryId, $filePath,
                $thumbnail, !empty($screenshots) ? json_encode($screenshots) : null, $status,
                $featured, $downloadLimit, $downloadExpiry, $productId
            ]);
            
            $_SESSION['flash_message'] = "Product updated successfully";
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO products 
                (name, slug, description, short_description, price, sale_price, category_id, file_path, thumbnail, screenshots, status, featured, download_limit, download_expiry_days, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->execute([
                $name, $slug, $description, $shortDesc, $price, $salePrice, $categoryId, $filePath,
                $thumbnail, !empty($screenshots) ? json_encode($screenshots) : null, $status,
                $featured, $downloadLimit, $downloadExpiry, getUserId()
            ]);
            
            $_SESSION['flash_message'] = "Product added successfully";
        }
        
        $_SESSION['flash_type'] = "success";
        redirect(APP_URL . '/admin/products.php');
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold text-brand-purple mb-0"><?php echo $productId > 0 ? 'Edit Product' : 'Add Product'; ?></h4>
    <a href="products.php" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Products
    </a>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
    <?php foreach ($errors as $error): ?>
    <div><i class="fas fa-exclamation-circle me-1"></i><?php echo $error; ?></div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="card admin-card">
    <div class="card-body p-4">
        <form method="POST" action="" enctype="multipart/form-data" class="admin-form">
            <div class="row g-4">
                <div class="col-md-8">
                    <div class="mb-4">
                        <label>Product Name *</label>
                        <input type="text" name="name" class="form-control" value="<?php echo $product['name'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="mb-4">
                        <label>Slug (URL-friendly name)</label>
                        <input type="text" name="slug" class="form-control" value="<?php echo $product['slug'] ?? ''; ?>" placeholder="auto-generated-if-empty">
                    </div>
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label>Price *</label>
                            <input type="number" name="price" class="form-control" step="0.01" min="0" value="<?php echo $product['price'] ?? ''; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label>Sale Price</label>
                            <input type="number" name="sale_price" class="form-control" step="0.01" min="0" value="<?php echo $product['sale_price'] ?? ''; ?>">
                        </div>
                    </div>
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label>Category</label>
                            <select name="category_id" class="form-select">
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo ($product['category_id'] ?? '') == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo $cat['name']; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>Status</label>
                            <select name="status" class="form-select">
                                <option value="active" <?php echo ($product['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo ($product['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                <option value="draft" <?php echo ($product['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label>Short Description</label>
                        <input type="text" name="short_description" class="form-control" maxlength="500" value="<?php echo $product['short_description'] ?? ''; ?>">
                    </div>
                    
                    <div class="mb-4">
                        <label>Full Description</label>
                        <textarea name="description" class="form-control" rows="6"><?php echo $product['description'] ?? ''; ?></textarea>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-4">
                        <label>Product File * <?php echo $productId > 0 ? '(Leave empty to keep current)' : ''; ?></label>
                        <input type="file" name="product_file" class="form-control" accept=".zip,.rar,.tar,.gz,.pdf,.doc,.docx" <?php echo $productId == 0 ? 'required' : ''; ?>>
                        <small class="text-muted">ZIP, RAR, PDF, etc.</small>
                        <?php if ($product['file_path'] ?? false): ?>
                        <div class="mt-2">
                            <small>Current: <?php echo basename($product['file_path']); ?></small>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-4">
                        <label>Thumbnail</label>
                        <input type="file" name="thumbnail" class="form-control" accept="image/*">
                        <?php if ($product['thumbnail'] ?? false): ?>
                        <img src="<?php echo APP_URL . '/' . $product['thumbnail']; ?>" alt="Current thumbnail" class="mt-2 rounded-3" style="max-width: 150px;">
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-4">
                        <label>Screenshots</label>
                        <input type="file" name="screenshots[]" class="form-control" accept="image/*" multiple>
                        <small class="text-muted">You can select multiple files</small>
                    </div>
                    
                    <div class="mb-4">
                        <div class="form-check">
                            <input type="checkbox" name="featured" id="featured" class="form-check-input" <?php echo ($product['featured'] ?? 0) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="featured">Featured Product</label>
                        </div>
                    </div>
                    
                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <label>Download Limit</label>
                            <input type="number" name="download_limit" class="form-control" value="<?php echo $product['download_limit'] ?? -1; ?>">
                            <small class="text-muted">-1 = Unlimited</small>
                        </div>
                        <div class="col-6">
                            <label>Expiry (days)</label>
                            <input type="number" name="download_expiry" class="form-control" value="<?php echo $product['download_expiry_days'] ?? 0; ?>">
                            <small class="text-muted">0 = Never</small>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-brand-purple">
                            <i class="fas fa-save me-2"></i><?php echo $productId > 0 ? 'Update' : 'Create'; ?> Product
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
