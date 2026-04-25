<?php
$pageTitle = 'Coupons';
include 'includes/header.php';

$errors = [];

// Get all coupons
$coupons = $pdo->query("SELECT * FROM coupons ORDER BY created_at DESC")->fetchAll();

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $couponId = intval($_POST['coupon_id'] ?? 0);
    $code = strtoupper(sanitizeInput($_POST['code'] ?? ''));
    $type = sanitizeInput($_POST['type'] ?? 'percentage');
    $value = floatval($_POST['value'] ?? 0);
    $minOrder = floatval($_POST['min_order_amount'] ?? 0);
    $maxDiscount = !empty($_POST['max_discount']) ? floatval($_POST['max_discount']) : null;
    $usageLimit = !empty($_POST['usage_limit']) ? intval($_POST['usage_limit']) : null;
    $startDate = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
    $endDate = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
    $status = sanitizeInput($_POST['status'] ?? 'active');
    
    if (empty($code)) {
        $errors[] = "Coupon code is required";
    }
    
    if ($value <= 0) {
        $errors[] = "Value must be greater than 0";
    }
    
    if (empty($errors)) {
        try {
            if ($couponId > 0) {
                // Update
                $pdo->prepare("UPDATE coupons SET 
                    code = ?, type = ?, value = ?, min_order_amount = ?, max_discount = ?,
                    usage_limit = ?, start_date = ?, end_date = ?, status = ? WHERE id = ?")
                    ->execute([$code, $type, $value, $minOrder, $maxDiscount, $usageLimit, $startDate, $endDate, $status, $couponId]);
                $_SESSION['flash_message'] = "Coupon updated successfully";
            } else {
                // Insert
                $pdo->prepare("INSERT INTO coupons 
                    (code, type, value, min_order_amount, max_discount, usage_limit, start_date, end_date, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)")
                    ->execute([$code, $type, $value, $minOrder, $maxDiscount, $usageLimit, $startDate, $endDate, $status]);
                $_SESSION['flash_message'] = "Coupon created successfully";
            }
            
            $_SESSION['flash_type'] = "success";
            redirect(APP_URL . '/admin/coupons.php');
        } catch (PDOException $e) {
            $errors[] = "Coupon code already exists";
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM coupons WHERE id = ?")->execute([intval($_GET['delete'])]);
    $_SESSION['flash_message'] = "Coupon deleted successfully";
    $_SESSION['flash_type'] = "success";
    redirect(APP_URL . '/admin/coupons.php');
}

// Get coupon for edit
$editCoupon = null;
if (isset($_GET['edit'])) {
    $editStmt = $pdo->prepare("SELECT * FROM coupons WHERE id = ?");
    $editStmt->execute([intval($_GET['edit'])]);
    $editCoupon = $editStmt->fetch();
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold text-brand-purple mb-0">Coupons</h4>
</div>

<div class="row g-4">
    <!-- Coupon Form -->
    <div class="col-lg-4">
        <div class="card admin-card">
            <div class="card-header">
                <h5 class="mb-0 fw-bold"><?php echo $editCoupon ? 'Edit Coupon' : 'Add Coupon'; ?></h5>
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
                    <input type="hidden" name="coupon_id" value="<?php echo $editCoupon['id'] ?? ''; ?>">
                    
                    <div class="mb-3">
                        <label>Coupon Code *</label>
                        <input type="text" name="code" class="form-control" value="<?php echo $editCoupon['code'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label>Type</label>
                            <select name="type" class="form-select">
                                <option value="percentage" <?php echo ($editCoupon['type'] ?? '') === 'percentage' ? 'selected' : ''; ?>>Percentage (%)</option>
                                <option value="flat" <?php echo ($editCoupon['type'] ?? '') === 'flat' ? 'selected' : ''; ?>>Flat Amount</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label>Value *</label>
                            <input type="number" name="value" class="form-control" step="0.01" value="<?php echo $editCoupon['value'] ?? ''; ?>" required>
                        </div>
                    </div>
                    
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label>Min Order Amount</label>
                            <input type="number" name="min_order_amount" class="form-control" step="0.01" value="<?php echo $editCoupon['min_order_amount'] ?? '0'; ?>">
                        </div>
                        <div class="col-6">
                            <label>Max Discount</label>
                            <input type="number" name="max_discount" class="form-control" step="0.01" value="<?php echo $editCoupon['max_discount'] ?? ''; ?>">
                            <small class="text-muted">Leave empty for no limit</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label>Usage Limit</label>
                        <input type="number" name="usage_limit" class="form-control" value="<?php echo $editCoupon['usage_limit'] ?? ''; ?>">
                        <small class="text-muted">Leave empty for unlimited</small>
                    </div>
                    
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label>Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="<?php echo $editCoupon['start_date'] ?? ''; ?>">
                        </div>
                        <div class="col-6">
                            <label>End Date</label>
                            <input type="date" name="end_date" class="form-control" value="<?php echo $editCoupon['end_date'] ?? ''; ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label>Status</label>
                        <select name="status" class="form-select">
                            <option value="active" <?php echo ($editCoupon['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($editCoupon['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-brand-purple w-100">
                        <i class="fas fa-save me-2"></i><?php echo $editCoupon ? 'Update' : 'Add'; ?> Coupon
                    </button>
                    
                    <?php if ($editCoupon): ?>
                    <a href="coupons.php" class="btn btn-outline-secondary w-100 mt-2">
                        <i class="fas fa-times me-2"></i>Cancel
                    </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Coupons List -->
    <div class="col-lg-8">
        <div class="card admin-card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table admin-table mb-0">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Type</th>
                                <th>Value</th>
                                <th>Usage</th>
                                <th>Valid Until</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($coupons as $coupon): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-brand-purple"><?php echo $coupon['code']; ?></span>
                                </td>
                                <td><?php echo ucfirst($coupon['type']); ?></td>
                                <td>
                                    <?php echo $coupon['type'] === 'percentage' ? $coupon['value'] . '%' : formatPrice($coupon['value']); ?>
                                </td>
                                <td>
                                    <?php echo $coupon['usage_count']; ?> / <?php echo $coupon['usage_limit'] ?? '∞'; ?>
                                </td>
                                <td>
                                    <?php echo $coupon['end_date'] ? date('M d, Y', strtotime($coupon['end_date'])) : 'Never'; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $coupon['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($coupon['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="coupons.php?edit=<?php echo $coupon['id']; ?>" class="action-btn edit">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <a href="coupons.php?delete=<?php echo $coupon['id']; ?>" class="action-btn delete" onclick="return confirmDelete()">
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
