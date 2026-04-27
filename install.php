<?php
/**
 * MBHaat.com — Install Wizard
 * Run once to set up the database. Delete this file after installation.
 * Access: http://yourdomain.com/mbhaat/install.php
 */

// Security: block if already installed
if (file_exists(__DIR__ . '/.installed')) {
    die("<h2>Already installed.</h2><p>Delete <code>.installed</code> file to re-run. <a href='index.php'>Go to site</a></p>");
}

$step    = (int)($_GET['step'] ?? 1);
$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 2) {
    $dbHost = $_POST['db_host'] ?? 'localhost';
    $dbName = $_POST['db_name'] ?? 'mbhaat';
    $dbUser = $_POST['db_user'] ?? 'root';
    $dbPass = $_POST['db_pass'] ?? '';
    $appUrl = rtrim($_POST['app_url'] ?? '', '/');
    $adminEmail = $_POST['admin_email'] ?? 'admin@mbhaat.com';
    $adminPass  = $_POST['admin_pass'] ?? '';

    // Test DB connection
    try {
        $pdo = new PDO("mysql:host=$dbHost;charset=utf8mb4", $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        // Create database
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$dbName`");

        // Run schema
        $sql = file_get_contents(__DIR__ . '/database/schema.sql');
        // Remove USE statement from schema (already selected)
        $sql = preg_replace('/^USE\s+\w+;\s*/im', '', $sql);
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        foreach ($statements as $stmt) {
            if ($stmt) $pdo->exec($stmt);
        }

        // Update admin password & email
        $hash = password_hash($adminPass ?: 'admin123', PASSWORD_BCRYPT);
        $pdo->prepare("UPDATE admins SET email=?, password=? WHERE role='super_admin' LIMIT 1")
            ->execute([$adminEmail, $hash]);

        // Write config file with user's values
        $configContent = <<<PHP
<?php
define('ROOT_PATH', dirname(__DIR__));
define('APP_VERSION', '1.0.0');
define('DB_HOST', '$dbHost');
define('DB_NAME', '$dbName');
define('DB_USER', '$dbUser');
define('DB_PASS', '$dbPass');
define('DB_CHARSET', 'utf8mb4');
define('APP_URL', '$appUrl');
define('ASSETS_URL', APP_URL . '/assets');
define('UPLOADS_URL', APP_URL . '/uploads');
define('SECRET_KEY', '<?= bin2hex(random_bytes(32)) ?>');
define('HASH_ALGO', PASSWORD_BCRYPT);
define('SESSION_NAME', 'mbhaat_session');
define('SESSION_LIFETIME', 86400 * 30);
define('MAX_FILE_SIZE', 524288000);
define('MAX_IMAGE_SIZE', 5242880);
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('PRODUCT_UPLOAD_PATH', UPLOAD_PATH . '/products');
define('AVATAR_UPLOAD_PATH', UPLOAD_PATH . '/avatars');
define('SCREENSHOT_UPLOAD_PATH', UPLOAD_PATH . '/screenshots');
define('DEBUG_MODE', false);
PHP;
        // Generate real secret key
        $configContent = str_replace("'<?= bin2hex(random_bytes(32)) ?>'", "'" . bin2hex(random_bytes(32)) . "'", $configContent);
        file_put_contents(__DIR__ . '/src/config/config.php', $configContent);

        // Create upload directories
        $dirs = [
            __DIR__ . '/uploads',
            __DIR__ . '/uploads/products',
            __DIR__ . '/uploads/avatars',
            __DIR__ . '/uploads/screenshots',
        ];
        foreach ($dirs as $dir) {
            if (!is_dir($dir)) mkdir($dir, 0755, true);
        }

        // Write .installed flag
        file_put_contents(__DIR__ . '/.installed', date('Y-m-d H:i:s'));

        $success = true;
        $step = 3;

    } catch (PDOException $e) {
        $errors[] = 'Database error: ' . $e->getMessage();
    } catch (Exception $e) {
        $errors[] = 'Error: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>MBHaat.com — Install Wizard</title>
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'DM Sans',system-ui,sans-serif;background:linear-gradient(135deg,#1e0030,#36064D);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem 1rem}
  .box{background:#fff;border-radius:16px;padding:2.5rem;width:100%;max-width:520px;box-shadow:0 20px 60px rgba(0,0,0,0.4)}
  h1{font-family:Georgia,serif;color:#36064D;margin-bottom:.25rem}
  .sub{color:#7a6a8a;font-size:.9rem;margin-bottom:2rem}
  .form-group{margin-bottom:1.1rem}
  label{display:block;font-weight:600;font-size:.85rem;margin-bottom:.35rem;color:#333}
  input{width:100%;padding:.65rem .9rem;border:1.5px solid #ddd;border-radius:6px;font-size:.9rem;transition:border-color .2s}
  input:focus{outline:none;border-color:#36064D}
  .btn{display:block;width:100%;padding:.8rem;background:#36064D;color:#fff;border:none;border-radius:6px;font-size:1rem;font-weight:600;cursor:pointer;margin-top:1.25rem}
  .btn:hover{background:#1e0030}
  .error{background:#fef2f2;border:1px solid #fca5a5;color:#991b1b;padding:.75rem 1rem;border-radius:6px;margin-bottom:1rem;font-size:.9rem}
  .success{background:#f0fdf4;border:1px solid #86efac;color:#166534;padding:1rem;border-radius:6px;margin-bottom:1rem}
  .steps{display:flex;gap:.5rem;margin-bottom:2rem}
  .step{flex:1;height:4px;border-radius:2px;background:#f0ede5}
  .step.done{background:#36064D}
  .step.active{background:#76D2DB}
  code{background:#f5f5f5;padding:.15rem .4rem;border-radius:4px;font-size:.85rem}
</style>
</head>
<body>
<div class="box">
  <div style="text-align:center;margin-bottom:1.5rem">
    <img src="assets/images/logo.png" style="height:55px" alt="MBHaat">
  </div>

  <div class="steps">
    <div class="step <?= $step>=1?'done':'' ?>"></div>
    <div class="step <?= $step>=2?'done':($step===1?'active':'') ?>"></div>
    <div class="step <?= $step>=3?'done':($step===2?'active':'') ?>"></div>
  </div>

  <?php if ($step === 1): ?>
  <h1>Welcome to MBHaat!</h1>
  <p class="sub">Let's set up your digital product store in a few steps.</p>

  <div class="success">
    <strong>Before you begin, make sure you have:</strong><br>
    ✓ PHP 8.0+ with PDO MySQL extension<br>
    ✓ MySQL 5.7+ database server<br>
    ✓ Web server (Apache/Nginx) with mod_rewrite
  </div>

  <a href="?step=2" class="btn">Start Installation →</a>

  <?php elseif ($step === 2): ?>
  <h1>Database Setup</h1>
  <p class="sub">Enter your database and site details below.</p>

  <?php if ($errors): ?>
    <div class="error"><?php foreach($errors as $e): ?><div><?= htmlspecialchars($e) ?></div><?php endforeach; ?></div>
  <?php endif; ?>

  <form method="POST">
    <div class="form-group"><label>Database Host</label><input name="db_host" value="localhost" required></div>
    <div class="form-group"><label>Database Name</label><input name="db_name" value="mbhaat" required></div>
    <div class="form-group"><label>Database Username</label><input name="db_user" value="root" required></div>
    <div class="form-group"><label>Database Password</label><input name="db_pass" type="password" placeholder="(leave blank if none)"></div>
    <div class="form-group"><label>Site URL</label><input name="app_url" value="http://localhost/mbhaat" required placeholder="http://yourdomain.com"></div>
    <hr style="border:none;border-top:1px solid #eee;margin:1rem 0">
    <div class="form-group"><label>Admin Email</label><input name="admin_email" type="email" value="admin@mbhaat.com" required></div>
    <div class="form-group"><label>Admin Password</label><input name="admin_pass" type="password" placeholder="Set a strong password" required></div>
    <button type="submit" class="btn">Install MBHaat →</button>
  </form>

  <?php elseif ($step === 3): ?>
  <h1>🎉 Installation Complete!</h1>
  <p class="sub">MBHaat.com has been successfully installed.</p>

  <div class="success">
    <strong>Next steps:</strong><br>
    1. <strong>Delete</strong> <code>install.php</code> from your server<br>
    2. Login to your admin panel<br>
    3. Add your payment gateway details in Settings<br>
    4. Add your first products!
  </div>

  <div style="display:flex;flex-direction:column;gap:.75rem;margin-top:1.5rem">
    <a href="admin/login.php" class="btn">Go to Admin Panel →</a>
    <a href="index.php" class="btn" style="background:#76D2DB;color:#1e0030">Visit Your Store →</a>
  </div>
  <?php endif; ?>
</div>
</body>
</html>
