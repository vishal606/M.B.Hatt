    <!-- Desktop Footer -->
    <footer class="text-center text-lg-start bg-white text-muted mt-auto d-none d-lg-block" style="border-top: 1px solid #e0e0e0;">
        <div class="container p-4">
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4 mb-md-0">
                    <h5 class="text-uppercase mb-3" style="color: #36064D;">
                        <i class="fas fa-shopping-bag me-2"></i>MBHaat.com
                    </h5>
                    <p><?php echo getSettings('site_description') ?? 'Your trusted platform for premium digital products. Download instantly after purchase.'; ?></p>
                    <div class="mt-3">
                        <a href="#" class="me-3 text-reset"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="me-3 text-reset"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="me-3 text-reset"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="me-3 text-reset"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4 mb-md-0">
                    <h6 class="text-uppercase fw-bold mb-3" style="color: #36064D;">Quick Links</h6>
                    <ul class="list-unstyled mb-0">
                        <li><a href="<?php echo APP_URL; ?>/pages/products.php" class="text-muted">Products</a></li>
                        <li><a href="<?php echo APP_URL; ?>/pages/faq.php" class="text-muted">FAQ</a></li>
                        <li><a href="#" class="text-muted">About Us</a></li>
                        <li><a href="#" class="text-muted">Contact</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4 mb-md-0">
                    <h6 class="text-uppercase fw-bold mb-3" style="color: #36064D;">Support</h6>
                    <ul class="list-unstyled mb-0">
                        <li><a href="#" class="text-muted">Help Center</a></li>
                        <li><a href="#" class="text-muted">Terms of Service</a></li>
                        <li><a href="#" class="text-muted">Privacy Policy</a></li>
                        <li><a href="#" class="text-muted">Refund Policy</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4 mb-md-0">
                    <h6 class="text-uppercase fw-bold mb-3" style="color: #36064D;">Contact</h6>
                    <p><i class="fas fa-home me-2"></i><?php echo getSettings('contact_address') ?? 'Dhaka, Bangladesh'; ?></p>
                    <p><i class="fas fa-envelope me-2"></i><?php echo getSettings('contact_email') ?? 'support@mbhaat.com'; ?></p>
                    <p><i class="fas fa-phone me-2"></i><?php echo getSettings('contact_phone') ?? '+8801XXXXXXXXX'; ?></p>
                </div>
            </div>
        </div>
        
        <div class="text-center p-3" style="background-color: #F7F6E5;">
            © <?php echo date('Y'); ?> Copyright: <a href="<?php echo APP_URL; ?>" style="color: #36064D;">MBHaat.com</a> - All Rights Reserved.
        </div>
    </footer>

    <!-- Mobile Bottom Navigation -->
    <nav class="mobile-bottom-nav d-lg-none">
        <a href="<?php echo APP_URL; ?>/index.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="<?php echo APP_URL; ?>/pages/products.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>">
            <i class="fas fa-th-large"></i>
            <span>Products</span>
        </a>
        <a href="<?php echo APP_URL; ?>/pages/cart.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'cart.php' ? 'active' : ''; ?>">
            <i class="fas fa-shopping-cart"></i>
            <span>Cart</span>
            <?php if (getCartCount() > 0): ?>
                <span class="badge"><?php echo getCartCount(); ?></span>
            <?php endif; ?>
        </a>
        <a href="<?php echo isLoggedIn() ? APP_URL . '/user/dashboard.php' : APP_URL . '/pages/login.php'; ?>" class="nav-item">
            <i class="fas fa-user"></i>
            <span>Profile</span>
        </a>
    </nav>

    <!-- MDB Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.2.0/mdb.umd.min.js"></script>
    
    <!-- Custom JS -->
    <script src="<?php echo APP_URL; ?>/assets/js/main.js"></script>
    
    <script>
        // Dark Mode Toggle
        function toggleDarkMode() {
            const html = document.documentElement;
            const icon = document.getElementById('darkModeIcon');
            const mobileIcon = document.getElementById('mobileDarkModeIcon');
            
            if (html.getAttribute('data-mdb-theme') === 'light') {
                html.setAttribute('data-mdb-theme', 'dark');
                if (icon) icon.classList.replace('fa-moon', 'fa-sun');
                if (mobileIcon) mobileIcon.classList.replace('fa-moon', 'fa-sun');
                localStorage.setItem('theme', 'dark');
            } else {
                html.setAttribute('data-mdb-theme', 'light');
                if (icon) icon.classList.replace('fa-sun', 'fa-moon');
                if (mobileIcon) mobileIcon.classList.replace('fa-sun', 'fa-moon');
                localStorage.setItem('theme', 'light');
            }
        }
        
        // Load saved theme
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-mdb-theme', savedTheme);
            
            if (savedTheme === 'dark') {
                const icon = document.getElementById('darkModeIcon');
                const mobileIcon = document.getElementById('mobileDarkModeIcon');
                if (icon) icon.classList.replace('fa-moon', 'fa-sun');
                if (mobileIcon) mobileIcon.classList.replace('fa-moon', 'fa-sun');
            }
        });
        
        // Auto-hide flash messages
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new mdb.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>
