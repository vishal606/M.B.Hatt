<?php
$pageTitle = 'My Profile';
require_once '../includes/config.php';
requireAuth();
include '../includes/header.php';

$userId = getUserId();
$success = false;
$errors = [];

// Get user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($errors)) {
        $updateStmt = $pdo->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?");
        if ($updateStmt->execute([$name, $phone, $userId])) {
            $_SESSION['user_name'] = $name;
            $success = true;
            
            // Refresh user data
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
        } else {
            $errors[] = "Failed to update profile";
        }
    }
}
?>

<div class="container py-4">
    <h3 class="fw-bold text-brand-purple mb-4">
        <i class="fas fa-user me-2"></i>My Profile
    </h3>
    
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 p-md-5">
                    <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>Profile updated successfully!
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?>
                        <div><i class="fas fa-exclamation-circle me-1"></i><?php echo $error; ?></div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="text-center mb-4">
                            <div class="rounded-circle bg-brand-purple text-white d-inline-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px; font-size: 2.5rem;">
                                <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                            </div>
                            <h4 class="fw-bold text-brand-purple"><?php echo $user['name']; ?></h4>
                            <p class="text-muted"><?php echo $user['email']; ?></p>
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Full Name *</label>
                                <input type="text" name="name" class="form-control" value="<?php echo $user['name']; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email Address</label>
                                <input type="email" class="form-control" value="<?php echo $user['email']; ?>" disabled>
                                <small class="text-muted">Email cannot be changed</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Phone Number</label>
                                <input type="tel" name="phone" class="form-control" value="<?php echo $user['phone'] ?? ''; ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Member Since</label>
                                <input type="text" class="form-control" value="<?php echo date('M d, Y', strtotime($user['created_at'])); ?>" disabled>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-3 mt-4">
                            <button type="submit" class="btn btn-brand-purple flex-fill">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                            <a href="change-password.php" class="btn btn-outline-secondary">
                                <i class="fas fa-lock me-2"></i>Change Password
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
