<?php
require_once __DIR__ . '/src/init.php';

if (isLoggedIn()) redirect(APP_URL . '/dashboard.php');

$message = '';
$success = false;

// Handle reset form
if (isset($_GET['token'])) {
    $token = sanitize($_GET['token']);
    $user  = Database::fetch(
        "SELECT * FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()",
        [$token]
    );

    if (!$user) {
        $message = 'This reset link is invalid or has expired.';
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_password'])) {
        verifyCsrf();
        $pw  = $_POST['new_password'] ?? '';
        $pw2 = $_POST['confirm_password'] ?? '';
        if (strlen($pw) < 6) {
            $message = 'Password must be at least 6 characters.';
        } elseif ($pw !== $pw2) {
            $message = 'Passwords do not match.';
        } else {
            $hash = password_hash($pw, HASH_ALGO);
            Database::execute(
                "UPDATE users SET password=?, reset_token=NULL, reset_token_expiry=NULL WHERE id=?",
                [$hash, $user['id']]
            );
            flash('success', 'Password reset successful! Please login.');
            redirect(APP_URL . '/login.php');
        }
    }

    $pageTitle = 'Reset Password — ' . getSetting('site_name', 'MBHaat.com');
    $settings  = getAllSettings();
    $logoPath  = APP_URL . '/' . ($settings['logo'] ?? 'assets/images/logo.png');
    ?>
    <!DOCTYPE html><html lang="en"><head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
    <script>const APP_URL='<?= APP_URL ?>';const CSRF_TOKEN='<?= e(csrfToken()) ?>';</script>
    </head><body class="<?= isDarkMode() ? 'dark' : '' ?>">
    <div class="auth-wrapper"><div class="auth-card">
      <div class="auth-logo"><a href="<?= APP_URL ?>"><img src="<?= e($logoPath) ?>" alt="MBHaat" style="height:50px"></a>
      <h1 class="auth-title">Reset Password</h1></div>
      <?php if ($message): ?><div class="alert alert-danger"><?= e($message) ?></div><?php endif; ?>
      <?php if ($user): ?>
      <form method="POST">
        <?= csrfField() ?>
        <div class="form-group"><label class="form-label">New Password</label>
        <input type="password" name="new_password" class="form-control" placeholder="Min 6 characters" required></div>
        <div class="form-group"><label class="form-label">Confirm Password</label>
        <input type="password" name="confirm_password" class="form-control" placeholder="Repeat password" required></div>
        <button type="submit" class="btn btn-primary btn-block btn-lg">Set New Password</button>
      </form>
      <?php endif; ?>
    </div></div>
    <script src="<?= ASSETS_URL ?>/js/app.js"></script></body></html>
    <?php
    exit;
}

// Send reset email (simulated — log token for demo)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $email = sanitize($_POST['email'] ?? '');
    $user  = Database::fetch("SELECT * FROM users WHERE email = ?", [$email]);
    if ($user) {
        $token  = generateToken(48);
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        Database::execute(
            "UPDATE users SET reset_token=?, reset_token_expiry=? WHERE id=?",
            [$token, $expiry, $user['id']]
        );
        // In production send email; for now just show link
        $resetLink = APP_URL . '/forgot-password.php?token=' . $token;
        $success   = true;
        $message   = $resetLink; // Dev: show link on page
    }
    // Always show same message to prevent enumeration
    $success = true;
}

$pageTitle = 'Forgot Password — ' . getSetting('site_name', 'MBHaat.com');
$settings  = getAllSettings();
$logoPath  = APP_URL . '/' . ($settings['logo'] ?? 'assets/images/logo.png');
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($pageTitle) ?></title>
<link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
<script>const APP_URL='<?= APP_URL ?>';const CSRF_TOKEN='<?= e(csrfToken()) ?>';</script>
</head><body class="<?= isDarkMode() ? 'dark' : '' ?>">
<div class="auth-wrapper"><div class="auth-card">
  <div class="auth-logo">
    <a href="<?= APP_URL ?>"><img src="<?= e($logoPath) ?>" alt="MBHaat" style="height:50px"></a>
    <h1 class="auth-title">Forgot Password</h1>
    <p class="auth-subtitle">Enter your email and we'll send a reset link</p>
  </div>

  <?php if ($success): ?>
    <div class="alert alert-success">
      If that email exists, a reset link has been sent.
      <?php if ($message && DEBUG_MODE): ?>
        <br><strong>Dev link:</strong> <a href="<?= e($message) ?>"><?= e($message) ?></a>
      <?php endif; ?>
    </div>
    <a href="<?= APP_URL ?>/login.php" class="btn btn-primary btn-block">Back to Login</a>
  <?php else: ?>
    <form method="POST">
      <?= csrfField() ?>
      <div class="form-group">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-control" placeholder="you@example.com" required autofocus>
      </div>
      <button type="submit" class="btn btn-primary btn-block btn-lg">Send Reset Link</button>
    </form>
    <p style="text-align:center;margin-top:1.5rem;font-size:.9rem">
      <a href="<?= APP_URL ?>/login.php">← Back to Login</a>
    </p>
  <?php endif; ?>
</div></div>
<script src="<?= ASSETS_URL ?>/js/app.js"></script></body></html>
