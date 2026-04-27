<?php
$settings = getAllSettings();
$siteName = $settings['site_name'] ?? 'MBHaat.com';
$logoPath = APP_URL . '/' . ($settings['logo'] ?? 'assets/images/logo.png');
$cartCount = cartCount();
$darkMode = isDarkMode();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle ?? $siteName) ?></title>
  <meta name="description" content="<?= e($pageDesc ?? ($settings['site_tagline'] ?? 'Premium Digital Products')) ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
  <script>
    const APP_URL = '<?= APP_URL ?>';
    const CSRF_TOKEN = '<?= e(csrfToken()) ?>';
  </script>
</head>
<body class="<?= $darkMode ? 'dark' : '' ?>">

<!-- Desktop Navbar -->
<nav class="navbar d-none d-md-block">
  <div class="container navbar-inner">
    <a href="<?= APP_URL ?>" class="navbar-brand">
      <img src="<?= e($logoPath) ?>" alt="<?= e($siteName) ?>">
    </a>
    <ul class="navbar-links">
      <li><a href="<?= APP_URL ?>" class="<?= $currentPage === 'index' ? 'active' : '' ?>">Home</a></li>
      <li><a href="<?= APP_URL ?>/products.php" class="<?= $currentPage === 'products' ? 'active' : '' ?>">Products</a></li>
      <li><a href="<?= APP_URL ?>/contact.php" class="<?= $currentPage === 'contact' ? 'active' : '' ?>">Support</a></li>
      <li><a href="<?= APP_URL ?>/faq.php" class="<?= $currentPage === 'faq' ? 'active' : '' ?>">FAQ</a></li>
    </ul>
    <div class="navbar-actions">
      <a href="<?= APP_URL ?>/cart.php" class="btn btn-secondary btn-sm" style="position:relative">
        🛒 Cart
        <?php if ($cartCount > 0): ?>
          <span class="cart-badge" style="position:absolute;top:-6px;right:-6px;background:var(--red);color:#fff;border-radius:50%;width:18px;height:18px;display:flex;align-items:center;justify-content:center;font-size:0.65rem;font-weight:700"><?= $cartCount ?></span>
        <?php endif; ?>
      </a>
      <?php if (isLoggedIn()): ?>
        <a href="<?= APP_URL ?>/dashboard.php" class="btn btn-primary btn-sm">My Account</a>
      <?php else: ?>
        <a href="<?= APP_URL ?>/login.php" class="btn btn-secondary btn-sm">Login</a>
        <a href="<?= APP_URL ?>/register.php" class="btn btn-primary btn-sm">Sign Up</a>
      <?php endif; ?>
      <button class="dark-toggle" onclick="toggleDarkMode()" title="Toggle dark mode"><?= $darkMode ? '☀️' : '🌙' ?></button>
    </div>
  </div>
</nav>

<!-- Mobile AppBar -->
<div class="mobile-appbar">
  <a href="<?= APP_URL ?>">
    <img src="<?= e($logoPath) ?>" alt="<?= e($siteName) ?>">
  </a>
  <div class="mobile-appbar-actions">
    <a href="<?= APP_URL ?>/search.php" title="Search">🔍</a>
    <a href="<?= APP_URL ?>/cart.php" title="Cart" style="position:relative">
      🛒
      <?php if ($cartCount > 0): ?>
        <span class="cart-badge" style="position:absolute;top:-4px;right:-4px;background:var(--red);color:#fff;border-radius:50%;width:16px;height:16px;display:flex;align-items:center;justify-content:center;font-size:0.6rem;font-weight:700"><?= $cartCount ?></span>
      <?php endif; ?>
    </a>
    <button class="dark-toggle" onclick="toggleDarkMode()" style="background:transparent;border:none;color:#fff;font-size:1.2rem;cursor:pointer"><?= $darkMode ? '☀️' : '🌙' ?></button>
  </div>
</div>

<!-- Flash Messages -->
<?php foreach (getFlash() as $flash): ?>
<div class="container mt-2">
  <div class="alert alert-<?= e($flash['type']) ?> flash-message" style="animation:fadeIn 0.3s ease">
    <?= e($flash['message']) ?>
  </div>
</div>
<?php endforeach; ?>
