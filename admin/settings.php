<?php
$pageTitle = 'Settings';
include 'includes/header.php';

$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'setting_') === 0) {
            $settingKey = substr($key, 8); // Remove 'setting_' prefix
            
            // Update or insert
            $checkStmt = $pdo->prepare("SELECT id FROM settings WHERE setting_key = ?");
            $checkStmt->execute([$settingKey]);
            
            if ($checkStmt->fetch()) {
                $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?")
                    ->execute([$value, $settingKey]);
            } else {
                $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)")
                    ->execute([$settingKey, $value]);
            }
        }
    }
    
    $success = true;
    $_SESSION['flash_message'] = "Settings saved successfully";
    $_SESSION['flash_type'] = "success";
}

// Get all settings
$settings = $pdo->query("SELECT * FROM settings ORDER BY setting_key")->fetchAll(PDO::FETCH_KEY_PAIR);

// Payment gateways
$gateways = $pdo->query("SELECT * FROM payment_gateways ORDER BY sort_order")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold text-brand-purple mb-0">Settings</h4>
</div>

<?php if ($success): ?>
<div class="alert alert-success">
    <i class="fas fa-check-circle me-2"></i>Settings saved successfully!
</div>
<?php endif; ?>

<div class="row g-4">
    <!-- General Settings -->
    <div class="col-lg-6">
        <div class="card admin-card h-100">
            <div class="card-header">
                <h5 class="mb-0 fw-bold"><i class="fas fa-cog me-2"></i>General Settings</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="" class="admin-form">
                    <div class="mb-3">
                        <label>Site Name</label>
                        <input type="text" name="setting_site_name" class="form-control" value="<?php echo $settings['site_name'] ?? 'MBHaat.com'; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label>Site Description</label>
                        <textarea name="setting_site_description" class="form-control" rows="2"><?php echo $settings['site_description'] ?? ''; ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label>Contact Email</label>
                        <input type="email" name="setting_contact_email" class="form-control" value="<?php echo $settings['contact_email'] ?? ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label>Contact Phone</label>
                        <input type="text" name="setting_contact_phone" class="form-control" value="<?php echo $settings['contact_phone'] ?? ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label>Currency</label>
                        <input type="text" name="setting_currency" class="form-control" value="<?php echo $settings['currency'] ?? 'BDT'; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label>Currency Symbol</label>
                        <input type="text" name="setting_currency_symbol" class="form-control" value="<?php echo $settings['currency_symbol'] ?? '৳'; ?>">
                    </div>
                    
                    <button type="submit" class="btn btn-brand-purple">
                        <i class="fas fa-save me-2"></i>Save Settings
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Tax Settings -->
    <div class="col-lg-6">
        <div class="card admin-card h-100">
            <div class="card-header">
                <h5 class="mb-0 fw-bold"><i class="fas fa-calculator me-2"></i>Tax Settings</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="" class="admin-form">
                    <div class="mb-3">
                        <div class="form-check mb-2">
                            <input type="checkbox" name="setting_tax_enabled" id="tax_enabled" class="form-check-input" value="1" <?php echo ($settings['tax_enabled'] ?? '0') == '1' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="tax_enabled">Enable Tax</label>
                        </div>
                        <input type="number" name="setting_tax_percentage" class="form-control" step="0.01" value="<?php echo $settings['tax_percentage'] ?? '0'; ?>">
                        <small class="text-muted">Tax percentage (%)</small>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check mb-2">
                            <input type="checkbox" name="setting_gst_enabled" id="gst_enabled" class="form-check-input" value="1" <?php echo ($settings['gst_enabled'] ?? '0') == '1' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="gst_enabled">Enable GST</label>
                        </div>
                        <input type="number" name="setting_gst_percentage" class="form-control" step="0.01" value="<?php echo $settings['gst_percentage'] ?? '0'; ?>">
                        <small class="text-muted">GST percentage (%)</small>
                    </div>
                    
                    <button type="submit" class="btn btn-brand-purple">
                        <i class="fas fa-save me-2"></i>Save Tax Settings
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Download Settings -->
    <div class="col-lg-6">
        <div class="card admin-card h-100">
            <div class="card-header">
                <h5 class="mb-0 fw-bold"><i class="fas fa-download me-2"></i>Download Settings</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="" class="admin-form">
                    <div class="mb-3">
                        <label>Default Download Limit</label>
                        <input type="number" name="setting_download_limit" class="form-control" value="<?php echo $settings['download_limit'] ?? '5'; ?>">
                        <small class="text-muted">Number of times a customer can download (-1 for unlimited)</small>
                    </div>
                    
                    <div class="mb-3">
                        <label>Download Expiry (Days)</label>
                        <input type="number" name="setting_download_expiry" class="form-control" value="<?php echo $settings['download_expiry'] ?? '30'; ?>">
                        <small class="text-muted">Days before download link expires (0 for never)</small>
                    </div>
                    
                    <button type="submit" class="btn btn-brand-purple">
                        <i class="fas fa-save me-2"></i>Save Download Settings
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Payment Gateways -->
    <div class="col-lg-6">
        <div class="card admin-card h-100">
            <div class="card-header">
                <h5 class="mb-0 fw-bold"><i class="fas fa-credit-card me-2"></i>Payment Gateways</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Gateway</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($gateways as $gateway): ?>
                            <tr>
                                <td><?php echo $gateway['name']; ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $gateway['is_active'] ? 'success' : 'secondary'; ?>">
                                        <?php echo $gateway['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="payment-gateway.php?id=<?php echo $gateway['id']; ?>" class="btn btn-sm btn-brand-blue">
                                        <i class="fas fa-cog"></i> Configure
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
