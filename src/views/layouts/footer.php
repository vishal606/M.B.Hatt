<?php
$siteName = getSetting('site_name', 'MBHaat.com');
$footerText = getSetting('footer_text', '© 2026 MBHaat.com — All rights reserved');
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$navItems = [
  ['icon' => '🏠', 'label' => 'Home', 'url' => APP_URL . '/index.php', 'page' => 'index'],
  ['icon' => '📦', 'label' => 'Products', 'url' => APP_URL . '/products.php', 'page' => 'products'],
  ['icon' => '🛒', 'label' => 'Cart', 'url' => APP_URL . '/cart.php', 'page' => 'cart'],
  ['icon' => '👤', 'label' => 'Profile', 'url' => APP_URL . '/dashboard.php', 'page' => 'dashboard'],
];
?>

<!-- Desktop Footer -->
<footer class="footer">
  <div class="container">
    <div class="grid grid-4 gap-3">
      <div style="grid-column: span 1.5">
        <img src="<?= APP_URL ?>/assets/images/logo.png" alt="<?= e($siteName) ?>" style="height:50px;margin-bottom:1rem">
        <p style="font-size:0.9rem;opacity:.75;line-height:1.7;max-width:300px"><?= e(getSetting('site_tagline', 'Your premium destination for digital products.')) ?></p>
      </div>
      <div>
        <h4>Quick Links</h4>
        <div style="display:flex;flex-direction:column;gap:0.6rem">
          <a href="<?= APP_URL ?>">Home</a>
          <a href="<?= APP_URL ?>/products.php">Products</a>
          <a href="<?= APP_URL ?>/faq.php">FAQ</a>
          <a href="<?= APP_URL ?>/contact.php">Contact</a>
        </div>
      </div>
      <div>
        <h4>Account</h4>
        <div style="display:flex;flex-direction:column;gap:0.6rem">
          <?php if (isLoggedIn()): ?>
            <a href="<?= APP_URL ?>/dashboard.php">My Dashboard</a>
            <a href="<?= APP_URL ?>/orders.php">My Orders</a>
            <a href="<?= APP_URL ?>/profile.php">Profile Settings</a>
            <a href="<?= APP_URL ?>/logout.php">Logout</a>
          <?php else: ?>
            <a href="<?= APP_URL ?>/login.php">Login</a>
            <a href="<?= APP_URL ?>/register.php">Sign Up</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <span><?= e($footerText) ?></span>
      <span style="opacity:.5">Secure payments · Instant delivery</span>
    </div>
  </div>
</footer>

<!-- Mobile Bottom Navigation -->
<nav class="bottom-nav">
  <ul>
    <?php foreach ($navItems as $item): ?>
    <li>
      <a href="<?= e($item['url']) ?>" class="<?= $currentPage === $item['page'] ? 'active' : '' ?>">
        <span class="nav-icon"><?= $item['icon'] ?></span>
        <?= e($item['label']) ?>
      </a>
    </li>
    <?php endforeach; ?>
  </ul>
</nav>

<div id="toast-container"></div>
<script src="<?= ASSETS_URL ?>/js/app.js"></script>
</body>
</html>
