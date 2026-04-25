<?php
$pageTitle = 'Change Password';
require_once '../includes/config.php';
requireAuth();
include '../includes/header.php';

$userId = getUserId();
$success = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validate
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!password_verify($currentPassword, $user['password'])) {
        $errors[] = "Current password is incorrect";
    }
    
    if (strlen($newPassword) < 6) {
        $errors[] = "New password must be at least 6 characters";
    }
    
    if ($newPassword !== $confirmPassword) {
        $errors[] = "New passwords do not match";
    }
    
    if (empty($errors)) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        
        if ($updateStmt->execute([$hashedPassword, $userId])) {
            $success = true;
            
            // Log activity
            $pdo->prepare("INSERT INTO activity_logs (user_id, action, ip_address, user_agent) VALUES (?, 'password_changed', ?, ?)")
                ->execute([$userId, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']]);
        } else {
            $errors[] = "Failed to update password";
        }
    }
}
?>

<div class="container py-4">
    <h3 class="fw-bold text-brand-purple mb-4">
        <i class="fas fa-lock me-2"></i>Change Password
    </h3>
    
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 p-md-5">
                    <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>Password changed successfully!
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
                        <div class="mb-4">
                            <label class="form-label fw-bold">Current Password</label>
                            <div class="input-group">
                                <input type="password" name="current_password" class="form-control" id="currentPassword" required>
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('currentPassword')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">New Password</label>
                            <div class="input-group">
                                <input type="password" name="new_password" class="form-control" id="newPassword" required minlength="6">
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('newPassword')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="progress mt-2" style="height: 4px;">
                                <div id="passwordStrength" class="progress-bar" style="width: 0%;"></div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">Confirm New Password</label>
                            <div class="input-group">
                                <input type="password" name="confirm_password" class="form-control" id="confirmPassword" required>
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('confirmPassword')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-3">
                            <button type="submit" class="btn btn-brand-purple flex-fill">
                                <i class="fas fa-save me-2"></i>Update Password
                            </button>
                            <a href="profile.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(id) {
    const input = document.getElementById(id);
    const type = input.type === 'password' ? 'text' : 'password';
    input.type = type;
}

document.getElementById('newPassword').addEventListener('input', function() {
    const strength = checkPasswordStrength(this.value);
    const meter = document.getElementById('passwordStrength');
    const colors = ['#DA4848', '#DA4848', '#F7F6E5', '#76D2DB', '#36064D'];
    meter.style.width = ((strength + 1) * 20) + '%';
    meter.style.backgroundColor = colors[strength];
});

function checkPasswordStrength(password) {
    let strength = 0;
    if (password.length >= 8) strength++;
    if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
    if (password.match(/\d/)) strength++;
    if (password.match(/[^a-zA-Z\d]/)) strength++;
    return strength;
}
</script>

<?php include '../includes/footer.php'; ?>
