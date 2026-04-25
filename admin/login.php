<?php
/**
 * Admin Login - Direct access for admin panel
 */

require_once '../includes/config.php';

// If already logged in as admin, redirect to dashboard
if (isLoggedIn() && isAdmin()) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = $_POST['password'] ?? '';
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        if ($user['role'] !== 'admin') {
            $error = "Access denied. Admin only.";
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            // Update last login
            $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);
            
            header("Location: index.php");
            exit;
        }
    } else {
        $error = "Invalid email or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - MBHaat.com</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.2.0/mdb.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #36064D 0%, #76D2DB 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .login-logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-logo i {
            font-size: 4rem;
            color: #36064D;
        }
        .btn-admin {
            background-color: #36064D;
            color: white;
        }
        .btn-admin:hover {
            background-color: #5a1a7a;
            color: white;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-logo">
            <i class="fas fa-shield-alt"></i>
            <h3 class="mt-3 fw-bold" style="color: #36064D;">Admin Login</h3>
            <p class="text-muted">MBHaat.com</p>
        </div>
        
        <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-outline mb-4" data-mdb-input-init>
                <input type="email" name="email" id="email" class="form-control" required value="admin@mbhaat.com">
                <label class="form-label" for="email">Email Address</label>
            </div>
            
            <div class="form-outline mb-4" data-mdb-input-init>
                <input type="password" name="password" id="password" class="form-control" required>
                <label class="form-label" for="password">Password</label>
            </div>
            
            <button type="submit" class="btn btn-admin btn-block btn-lg" data-mdb-ripple-init>
                <i class="fas fa-sign-in-alt me-2"></i>Login to Admin
            </button>
        </form>
        
        <div class="alert alert-light mt-4 mb-0">
            <small class="text-muted">
                <strong>Default Credentials:</strong><br>
                Email: admin@mbhaat.com<br>
                Password: admin123
            </small>
        </div>
        
        <div class="text-center mt-4">
            <a href="../index.php" class="text-muted">
                <i class="fas fa-arrow-left me-1"></i>Back to Website
            </a>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.2.0/mdb.umd.min.js"></script>
</body>
</html>
