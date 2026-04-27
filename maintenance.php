<?php
require_once __DIR__ . '/src/init.php';

// Only show maintenance if enabled AND not admin
if (getSetting('maintenance_mode') === '1' && !isAdminLoggedIn()) {
    http_response_code(503);
    $siteName = getSetting('site_name', 'MBHaat.com');
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
      <title>Under Maintenance — <?= e($siteName) ?></title>
      <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
    </head>
    <body>
    <div style="min-height:100vh;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,var(--purple-dark),var(--purple));padding:2rem">
      <div style="text-align:center;color:#fff;max-width:500px">
        <img src="<?= APP_URL ?>/assets/images/logo.png" style="height:70px;margin:0 auto 2rem">
        <h1 style="font-size:2.5rem;margin-bottom:1rem">🔧 Under Maintenance</h1>
        <p style="opacity:.8;font-size:1.1rem;line-height:1.7">We're working hard to improve your experience. We'll be back online shortly.</p>
        <div style="margin-top:2rem;font-size:.9rem;opacity:.6">If you're the admin, <a href="<?= APP_URL ?>/admin/login.php" style="color:var(--blue-light)">login here</a>.</div>
      </div>
    </div>
    </body></html>
    <?php
    exit;
}
redirect(APP_URL . '/index.php');
