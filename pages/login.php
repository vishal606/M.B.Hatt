<?php
$pageTitle = 'Login';
require_once '../includes/config.php';

if (isLoggedIn()) {
    redirect(APP_URL . '/user/dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(sanitizeInput($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        
        // Update last login
        $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);
        
        // Log activity
        $pdo->prepare("INSERT INTO activity_logs (user_id, action, ip_address, user_agent) VALUES (?, 'login', ?, ?)")
            ->execute([$user['id'], $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']]);
        
        $redirect = $_SESSION['redirect_after_login'] ?? APP_URL . '/user/dashboard.php';
        unset($_SESSION['redirect_after_login']);
        redirect($redirect);
    } else {
        $error = "Invalid email or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-mdb-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.2.0/mdb.min.css" rel="stylesheet">
    <link href="<?php echo APP_URL; ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>

<div class="auth-container">
    <div class="auth-card fade-in">
        <div class="auth-logo">
            <i class="fas fa-shopping-bag"></i>
            <h3 class="mt-2 text-brand-purple">Welcome Back</h3>
            <p class="text-muted small">Login to your MBHaat.com account</p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-1"></i><?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-outline mb-4" data-mdb-input-init>
                <input type="email" id="email" name="email" class="form-control" required>
                <label class="form-label" for="email">Email Address</label>
            </div>
            
            <div class="form-outline mb-4" data-mdb-input-init>
                <input type="password" id="password" name="password" class="form-control" required>
                <label class="form-label" for="password">Password</label>
            </div>
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="remember" name="remember">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>
                <a href="forgot-password.php" style="color: #76D2DB;">Forgot password?</a>
            </div>
            
            <button type="submit" class="btn btn-brand-purple btn-block mb-4" data-mdb-ripple-init>
                <i class="fas fa-sign-in-alt me-2"></i>Login
            </button>
            
            <div class="text-center">
                <p class="text-muted">Don't have an account? <a href="register.php" style="color: #76D2DB;">Register here</a></p>
            </div>
            
            <div class="text-center mt-3">
                <a href="<?php echo APP_URL; ?>/index.php" class="text-muted small">
                    <i class="fas fa-arrow-left me-1"></i>Back to Home
                </a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.2.0/mdb.umd.min.js"></script>
</body>
</html>
