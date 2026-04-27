<?php
require_once __DIR__ . '/src/init.php';

if (isLoggedIn()) redirect(APP_URL . '/dashboard.php');

$errors = [];
$values = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $values['name']  = sanitize($_POST['name'] ?? '');
    $values['email'] = sanitize($_POST['email'] ?? '');
    $password        = $_POST['password'] ?? '';
    $confirm         = $_POST['confirm_password'] ?? '';

    if (strlen($values['name']) < 2)    $errors[] = 'Name must be at least 2 characters.';
    if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if (strlen($password) < 6)          $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm)         $errors[] = 'Passwords do not match.';

    if (!$errors) {
        $exists = Database::fetch("SELECT id FROM users WHERE email = ?", [$values['email']]);
        if ($exists) {
            $errors[] = 'Email already registered. <a href="' . APP_URL . '/login.php">Login instead?</a>';
        } else {
            $hash = password_hash($password, HASH_ALGO);
            $id   = Database::insert(
                "INSERT INTO users (name, email, password) VALUES (?,?,?)",
                [$values['name'], $values['email'], $hash]
            );
            $_SESSION['user_id']   = $id;
            $_SESSION['user_name'] = $values['name'];
            flash('success', 'Account created! Welcome, ' . $values['name'] . '!');
            redirect(APP_URL . '/dashboard.php');
        }
    }
}

$pageTitle = 'Create Account — ' . getSetting('site_name', 'MBHaat.com');
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
      <h1 class="auth-title">Create Account</h1>
      <p class="auth-subtitle">Join <?= e($settings['site_name'] ?? 'MBHaat.com') ?> today</p>
    </div>

    <?php if ($errors): ?>
    <div class="alert alert-danger">
      <?php foreach ($errors as $err): ?><div><?= $err ?></div><?php endforeach; ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="">
      <?= csrfField() ?>
      <div class="form-group">
        <label class="form-label">Full Name</label>
        <input type="text" name="name" class="form-control" placeholder="Your full name" required value="<?= e($values['name'] ?? '') ?>" autofocus>
      </div>
      <div class="form-group">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-control" placeholder="you@example.com" required value="<?= e($values['email'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" placeholder="Minimum 6 characters" required>
      </div>
      <div class="form-group">
        <label class="form-label">Confirm Password</label>
        <input type="password" name="confirm_password" class="form-control" placeholder="Repeat your password" required>
      </div>
      <button type="submit" class="btn btn-primary btn-block btn-lg">Create Account →</button>
    </form>

    <p style="text-align:center;margin-top:1.5rem;font-size:.9rem;color:var(--text-muted)">
      Already have an account? <a href="<?= APP_URL ?>/login.php" style="font-weight:600">Sign in</a>
    </p>
  </div>
</div>

<script src="<?= ASSETS_URL ?>/js/app.js"></script>
</body>
</html>
