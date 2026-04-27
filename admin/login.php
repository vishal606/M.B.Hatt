<?php
require_once __DIR__ . '/../src/init.php';

if (isAdminLoggedIn()) redirect(APP_URL . '/admin/dashboard.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $email    = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $admin    = Database::fetch("SELECT * FROM admins WHERE email = ?", [$email]);
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id']   = $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];
        $_SESSION['admin_role'] = $admin['role'];
        redirect(APP_URL . '/admin/dashboard.php');
    } else {
        $error = 'Invalid admin credentials.';
    }
}

$settings = getAllSettings();
$logoPath = APP_URL . '/' . ($settings['logo'] ?? 'assets/images/logo.png');
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Login — <?= e($settings['site_name'] ?? 'MBHaat') ?></title>
<link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
<script>const APP_URL='<?= APP_URL ?>';const CSRF_TOKEN='<?= e(csrfToken()) ?>';</script>
</head><body class="<?= isDarkMode() ? 'dark' : '' ?>">
<div class="auth-wrapper" style="background:linear-gradient(135deg,#1e0030,#36064D)">
  <div class="auth-card">
    <div class="auth-logo">
      <a href="<?= APP_URL ?>"><img src="<?= e($logoPath) ?>" alt="MBHaat" style="height:50px"></a>
      <h1 class="auth-title">Admin Login</h1>
      <p class="auth-subtitle">Restricted area — authorized personnel only</p>
    </div>
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <form method="POST">
      <?= csrfField() ?>
      <div class="form-group">
        <label class="form-label">Admin Email</label>
        <input type="email" name="email" class="form-control" required autofocus>
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-primary btn-block btn-lg">Sign In to Admin →</button>
    </form>
    <p style="text-align:center;margin-top:1.25rem;font-size:.85rem"><a href="<?= APP_URL ?>">← Back to site</a></p>
  </div>
</div>
<script src="<?= ASSETS_URL ?>/js/app.js"></script>
</body></html>
