<?php
$pageTitle = 'Register';
require_once '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = filter_var(sanitizeInput($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $errors = [];
    
    if (empty($name)) $errors[] = "Name is required";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters";
    if ($password !== $confirm_password) $errors[] = "Passwords do not match";
    
    // Check if email exists
    $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $checkStmt->execute([$email]);
    if ($checkStmt->fetch()) {
        $errors[] = "Email already registered";
    }
    
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $verificationToken = generateToken();
        
        $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password, verification_token) VALUES (?, ?, ?, ?, ?)");
        
        if ($stmt->execute([$name, $email, $phone, $hashedPassword, $verificationToken])) {
            $_SESSION['flash_message'] = "Registration successful! Please login.";
            $_SESSION['flash_type'] = "success";
            redirect(APP_URL . '/pages/login.php');
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-mdb-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo APP_NAME; ?></title>
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
            <h3 class="mt-2 text-brand-purple">Create Account</h3>
            <p class="text-muted small">Join MBHaat.com today</p>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <div><i class="fas fa-exclamation-circle me-1"></i><?php echo $error; ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" data-validate>
            <div class="form-outline mb-4" data-mdb-input-init>
                <input type="text" id="name" name="name" class="form-control" required>
                <label class="form-label" for="name">Full Name</label>
            </div>
            
            <div class="form-outline mb-4" data-mdb-input-init>
                <input type="email" id="email" name="email" class="form-control" required>
                <label class="form-label" for="email">Email Address</label>
            </div>
            
            <div class="form-outline mb-4" data-mdb-input-init>
                <input type="tel" id="phone" name="phone" class="form-control">
                <label class="form-label" for="phone">Phone Number (Optional)</label>
            </div>
            
            <div class="form-outline mb-4" data-mdb-input-init>
                <input type="password" id="password" name="password" class="form-control" required minlength="6">
                <label class="form-label" for="password">Password</label>
            </div>
            
            <div id="password-strength-meter" class="mb-3">
                <small class="text-muted">Password strength:</small>
                <div class="progress" style="height: 6px;">
                    <div id="password-strength" class="progress-bar" style="width: 0%;"></div>
                </div>
            </div>
            
            <div class="form-outline mb-4" data-mdb-input-init>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                <label class="form-label" for="confirm_password">Confirm Password</label>
            </div>
            
            <div class="form-check d-flex justify-content-center mb-4">
                <input class="form-check-input me-2" type="checkbox" id="terms" required>
                <label class="form-check-label" for="terms">
                    I agree to the <a href="#" style="color: #76D2DB;">Terms of Service</a> and 
                    <a href="#" style="color: #76D2DB;">Privacy Policy</a>
                </label>
            </div>
            
            <button type="submit" class="btn btn-brand-purple btn-block mb-4" data-mdb-ripple-init>
                <i class="fas fa-user-plus me-2"></i>Create Account
            </button>
            
            <div class="text-center">
                <p class="text-muted">Already have an account? <a href="login.php" style="color: #76D2DB;">Login here</a></p>
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
<script src="<?php echo APP_URL; ?>/assets/js/main.js"></script>
</body>
</html>
