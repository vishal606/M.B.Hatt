<?php
$adminUser  = currentAdmin();
$siteName   = getSetting('site_name', 'MBHaat.com');
$logoPath   = APP_URL . '/' . getSetting('logo', 'assets/images/logo.png');
$currentPage= basename($_SERVER['PHP_SELF'], '.php');
$darkMode   = isDarkMode();

$menuItems = [
    'dashboard'  => ['icon'=>'📊','label'=>'Dashboard'],
    'products'   => ['icon'=>'📦','label'=>'Products'],
    'orders'     => ['icon'=>'🛒','label'=>'Orders'],
    'users'      => ['icon'=>'👥','label'=>'Users'],
    'coupons'    => ['icon'=>'🏷️','label'=>'Coupons'],
    'tickets'    => ['icon'=>'🎧','label'=>'Support'],
    'faqs'       => ['icon'=>'❓','label'=>'FAQs'],
    'categories' => ['icon'=>'📂','label'=>'Categories'],
    'testimonials'=>['icon'=>'⭐','label'=>'Testimonials'],
    'reports'    => ['icon'=>'📈','label'=>'Reports'],
    'settings'   => ['icon'=>'⚙️','label'=>'Settings'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= e($pageTitle ?? 'Admin — ' . $siteName) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
  <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin.css">
  <script>const APP_URL='<?= APP_URL ?>';const CSRF_TOKEN='<?= e(csrfToken()) ?>';</script>
</head>
<body class="<?= $darkMode ? 'dark' : '' ?>">

<div class="dashboard-layout">
  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="sidebar-brand">
      <img src="<?= e($logoPath) ?>" alt="<?= e($siteName) ?>">
      <div><?= e($siteName) ?></div>
      <div style="font-size:.7rem;color:var(--blue-light);font-family:var(--font-body);font-weight:400;margin-top:.1rem">Admin Panel</div>
    </div>
    <ul class="sidebar-menu">
      <?php foreach ($menuItems as $page => $item): ?>
      <li>
        <a href="<?= APP_URL ?>/admin/<?= $page ?>.php" class="<?= $currentPage === $page ? 'active' : '' ?>">
          <span><?= $item['icon'] ?></span> <?= $item['label'] ?>
        </a>
      </li>
      <?php endforeach; ?>
      <li style="margin-top:1rem;border-top:1px solid rgba(255,255,255,.1);padding-top:1rem">
        <a href="<?= APP_URL ?>" target="_blank"><span>🌐</span> View Site</a>
      </li>
      <li>
        <a href="<?= APP_URL ?>/admin/logout.php"><span>🚪</span> Logout</a>
      </li>
    </ul>
  </aside>

  <!-- Main Content -->
  <div class="dashboard-content">
    <!-- Top Bar -->
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:.5rem">
      <div style="font-size:.85rem;color:var(--text-muted)">
        Welcome, <strong><?= e($adminUser['name'] ?? 'Admin') ?></strong>
        <span style="margin:0 .4rem">·</span>
        <span class="badge badge-purple"><?= e($adminUser['role'] ?? 'admin') ?></span>
      </div>
      <button class="dark-toggle" onclick="toggleDarkMode()"><?= $darkMode ? '☀️' : '🌙' ?></button>
    </div>

    <!-- Flash messages -->
    <?php foreach (getFlash() as $flash): ?>
    <div class="alert alert-<?= e($flash['type']) ?> flash-message"><?= e($flash['message']) ?></div>
    <?php endforeach; ?>
