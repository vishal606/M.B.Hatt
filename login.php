<?php
require_once __DIR__ . '/src/init.php';

if (isLoggedIn()) redirect(APP_URL . '/dashboard.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $email    = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $user = Database::fetch("SELECT * FROM users WHERE email = ?", [$email]);

    if ($user && !$user['is_blocked'] && password_verify($password, $user['password'])) {
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        flash('success', 'Welcome back, ' . $user['name'] . '!');
        $redirect = sanitize($_GET['redirect'] ?? '');
        redirect($redirect ?: APP_URL . '/dashboard.php');
    } else {
        $error = $user && $user['is_blocked'] ? 'Your account has been blocked. Contact support.' : 'Invalid email or password.';
    }
}

$pageTitle = 'Login — ' . getSetting('site_name', 'MBHaat.com');
$settings  = getAllSettings();
$logoPath  = APP_URL . '/' . ($settings['logo'] ?? 'assets/images/logo.png');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= e($pageTitle) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
  <script>const APP_URL='<?= APP_URL ?>';const CSRF_TOKEN='<?= e(csrfToken()) ?>';</script>
</head>
<body class="<?= isDarkMode() ? 'dark' : '' ?>">

<div class="auth-wrapper">
  <div class="auth-card">
    <div class="auth-logo">
      <a href="<?= APP_URL ?>"><img src="<?= e($logoPath) ?>" alt="<?= e($settings['site_name'] ?? 'MBHaat') ?>"></a>
      <h1 class="auth-title">Welcome Back</h1>
      <p class="auth-subtitle">Sign in to your account</p>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>

    <?php foreach (getFlash() as $flash): ?>
    <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
    <?php endforeach; ?>

    <form method="POST" action="">
      <?= csrfField() ?>
      <div class="form-group">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-control" placeholder="you@example.com" required autofocus value="<?= e($_POST['email'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label class="form-label" style="display:flex;justify-content:space-between">
          <span>Password</span>
          <a href="<?= APP_URL ?>/forgot-password.php" style="font-size:.85rem;font-weight:400">Forgot password?</a>
        </label>
        <input type="password" name="password" class="form-control" placeholder="Your password" required>
      </div>
      <button type="submit" class="btn btn-primary btn-block btn-lg">Sign In →</button>
    </form>

    <p style="text-align:center;margin-top:1.5rem;font-size:.9rem;color:var(--text-muted)">
      Don't have an account? <a href="<?= APP_URL ?>/register.php" style="font-weight:600">Create one</a>
    </p>
  </div>
</div>

<script src="<?= ASSETS_URL ?>/js/app.js"></script>
</body>
</html>
