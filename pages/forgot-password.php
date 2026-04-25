<?php
$pageTitle = 'Forgot Password';
require_once '../includes/config.php';

$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(sanitizeInput($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    
    $stmt = $pdo->prepare("SELECT id, name FROM users WHERE email = ? AND status = 'active'");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user) {
        $resetToken = generateToken(32);
        $resetExpires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?")
            ->execute([$resetToken, $resetExpires, $user['id']]);
        
        // In production, send email with reset link
        // For demo, we'll just show the token
        $resetLink = APP_URL . '/pages/reset-password.php?token=' . $resetToken;
        
        $_SESSION['flash_message'] = "Password reset link has been sent to your email.";
        $_SESSION['flash_type'] = "success";
        $success = true;
    } else {
        $error = "Email not found";
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-mdb-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - <?php echo APP_NAME; ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.2.0/mdb.min.css" rel="stylesheet">
    <link href="<?php echo APP_URL; ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>

<div class="auth-container">
    <div class="auth-card fade-in">
        <div class="auth-logo">
            <i class="fas fa-lock"></i>
            <h3 class="mt-2 text-brand-purple">Forgot Password?</h3>
            <p class="text-muted small">Enter your email to reset password</p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-1"></i><?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!$success): ?>
        <form method="POST" action="">
            <div class="form-outline mb-4" data-mdb-input-init>
                <input type="email" id="email" name="email" class="form-control" required>
                <label class="form-label" for="email">Email Address</label>
            </div>
            
            <button type="submit" class="btn btn-brand-purple btn-block mb-4" data-mdb-ripple-init>
                <i class="fas fa-paper-plane me-2"></i>Send Reset Link
            </button>
            
            <div class="text-center">
                <p class="text-muted">Remember your password? <a href="login.php" style="color: #76D2DB;">Login here</a></p>
            </div>
        </form>
        <?php else: ?>
        <div class="text-center">
            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
            <p>Check your email for password reset instructions.</p>
            <a href="login.php" class="btn btn-brand-blue mt-3">Back to Login</a>
        </div>
        <?php endif; ?>
        
        <div class="text-center mt-3">
            <a href="<?php echo APP_URL; ?>/index.php" class="text-muted small">
                <i class="fas fa-arrow-left me-1"></i>Back to Home
            </a>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.2.0/mdb.umd.min.js"></script>
</body>
</html>
