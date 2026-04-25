<?php
/**
 * MBHaat.com - Complete Fix Script
 * Run this then DELETE it immediately!
 */

set_time_limit(120);
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<style>
body { font-family: Arial; padding: 20px; max-width: 900px; margin: 0 auto; background: #f5f5f5; }
.box { background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
h2 { color: #36064D; margin-top: 0; }
.success { color: green; }
.error { color: red; }
.warning { color: orange; }
pre { background: #f4f4f4; padding: 10px; overflow-x: auto; font-size: 12px; }
button { padding: 12px 24px; background: #36064D; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
button:hover { background: #5a1a7a; }
table { width: 100%; border-collapse: collapse; margin: 10px 0; }
th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
th { background: #36064D; color: white; }
</style>";

echo "<div class='box'><h2>🔧 MBHaat.com - System Fix Tool</h2><p>This will check and fix all common issues.</p></div>";

$errors = [];
$fixes = [];

// 1. Check config file exists
if (!file_exists('includes/config.php')) {
    $errors[] = "includes/config.php is missing!";
} else {
    $fixes[] = "✓ includes/config.php exists";
}

// 2. Check database connection
try {
    require_once 'includes/config.php';
    $fixes[] = "✓ Database connection successful";
} catch (Exception $e) {
    $errors[] = "Database connection failed: " . $e->getMessage();
    echo "<div class='box'><h3 class='error'>Critical Error</h3><p>Cannot connect to database. Please check your config.php settings.</p></div>";
    exit;
}

// 3. Check and create tables
$tables = [
    'users' => "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        phone VARCHAR(20) DEFAULT NULL,
        role ENUM('user', 'admin', 'editor') DEFAULT 'user',
        avatar VARCHAR(255) DEFAULT NULL,
        email_verified TINYINT(1) DEFAULT 0,
        verification_token VARCHAR(255) DEFAULT NULL,
        reset_token VARCHAR(255) DEFAULT NULL,
        reset_token_expires DATETIME DEFAULT NULL,
        status ENUM('active', 'inactive', 'blocked') DEFAULT 'active',
        last_login DATETIME DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    'categories' => "CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        slug VARCHAR(100) NOT NULL UNIQUE,
        description TEXT,
        image VARCHAR(255) DEFAULT NULL,
        sort_order INT DEFAULT 0,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    'products' => "CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        description LONGTEXT,
        short_description TEXT,
        price DECIMAL(10,2) NOT NULL,
        sale_price DECIMAL(10,2) DEFAULT NULL,
        thumbnail VARCHAR(255) DEFAULT NULL,
        screenshots JSON DEFAULT NULL,
        file_path VARCHAR(255) DEFAULT NULL,
        file_size BIGINT DEFAULT 0,
        download_limit INT DEFAULT 5,
        download_expiry_days INT DEFAULT 30,
        demo_url VARCHAR(255) DEFAULT NULL,
        tags VARCHAR(255) DEFAULT NULL,
        views INT DEFAULT 0,
        downloads_count INT DEFAULT 0,
        is_featured TINYINT(1) DEFAULT 0,
        status ENUM('active', 'inactive', 'draft') DEFAULT 'draft',
        meta_title VARCHAR(255) DEFAULT NULL,
        meta_description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    'orders' => "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_number VARCHAR(50) NOT NULL UNIQUE,
        user_id INT NOT NULL,
        subtotal DECIMAL(10,2) NOT NULL,
        coupon_code VARCHAR(50) DEFAULT NULL,
        coupon_discount DECIMAL(10,2) DEFAULT 0.00,
        tax_amount DECIMAL(10,2) DEFAULT 0.00,
        total_amount DECIMAL(10,2) NOT NULL,
        payment_method VARCHAR(50) NOT NULL,
        payment_status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
        transaction_id VARCHAR(255) DEFAULT NULL,
        order_status ENUM('pending', 'processing', 'completed', 'cancelled', 'refunded') DEFAULT 'pending',
        billing_name VARCHAR(100) NOT NULL,
        billing_email VARCHAR(100) NOT NULL,
        billing_phone VARCHAR(20) DEFAULT NULL,
        billing_address TEXT,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    'order_items' => "CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        product_name VARCHAR(255) NOT NULL,
        product_price DECIMAL(10,2) NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        download_token VARCHAR(255) DEFAULT NULL,
        download_count INT DEFAULT 0,
        download_expires DATETIME DEFAULT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    'coupons' => "CREATE TABLE IF NOT EXISTS coupons (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) NOT NULL UNIQUE,
        type ENUM('percentage', 'flat') DEFAULT 'percentage',
        value DECIMAL(10,2) NOT NULL,
        min_order_amount DECIMAL(10,2) DEFAULT 0.00,
        max_discount DECIMAL(10,2) DEFAULT NULL,
        usage_limit INT DEFAULT NULL,
        usage_count INT DEFAULT 0,
        start_date DATE DEFAULT NULL,
        end_date DATE DEFAULT NULL,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    'settings' => "CREATE TABLE IF NOT EXISTS settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) NOT NULL UNIQUE,
        setting_value TEXT,
        setting_type VARCHAR(50) DEFAULT 'text',
        description VARCHAR(255) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    'payment_gateways' => "CREATE TABLE IF NOT EXISTS payment_gateways (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        code VARCHAR(50) NOT NULL UNIQUE,
        is_active TINYINT(1) DEFAULT 1,
        config JSON DEFAULT NULL,
        sort_order INT DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
];

echo "<div class='box'><h3>📊 Database Tables Check</h3>";
foreach ($tables as $table => $sql) {
    try {
        $check = $pdo->query("SHOW TABLES LIKE '$table'")->fetch();
        if ($check) {
            echo "<p class='success'>✓ Table: $table</p>";
        } else {
            $pdo->exec($sql);
            echo "<p class='warning'>⚠ Created missing table: $table</p>";
        }
    } catch (Exception $e) {
        $errors[] = "Failed to create $table: " . $e->getMessage();
    }
}
echo "</div>";

// 4. Check/Create Admin User
echo "<div class='box'><h3>👤 Admin User Check</h3>";
$stmt = $pdo->query("SELECT id, name, email, role, status FROM users WHERE email = 'admin@mbhaat.com'");
$admin = $stmt->fetch();

if ($admin) {
    echo "<p class='success'>✓ Admin user exists: {$admin['name']} ({$admin['email']})</p>";
    echo "<p>Role: {$admin['role']}, Status: {$admin['status']}</p>";
    
    // Fix password
    $newHash = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->prepare("UPDATE users SET password = ?, role = 'admin', status = 'active' WHERE email = ?")
        ->execute([$newHash, 'admin@mbhaat.com']);
    echo "<p class='success'>✓ Admin password reset to: admin123</p>";
} else {
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->prepare("INSERT INTO users (name, email, password, role, email_verified, status) VALUES (?, ?, ?, ?, 1, 'active')")
        ->execute(['Super Admin', 'admin@mbhaat.com', $hash, 'admin']);
    echo "<p class='success'>✓ Created admin user: admin@mbhaat.com / admin123</p>";
}
echo "</div>";

// 5. Insert Default Settings
echo "<div class='box'><h3>⚙️ Default Settings</h3>";
$defaultSettings = [
    ['site_name', 'MBHaat.com', 'text', 'Website name'],
    ['site_description', 'Digital Product Selling Platform', 'textarea', 'Meta description'],
    ['contact_email', 'support@mbhaat.com', 'email', 'Contact email'],
    ['currency', 'BDT', 'text', 'Currency code'],
    ['currency_symbol', '৳', 'text', 'Currency symbol']
];

foreach ($defaultSettings as $setting) {
    try {
        $check = $pdo->prepare("SELECT id FROM settings WHERE setting_key = ?");
        $check->execute([$setting[0]]);
        if (!$check->fetch()) {
            $pdo->prepare("INSERT INTO settings (setting_key, setting_value, setting_type, description) VALUES (?, ?, ?, ?)")
                ->execute($setting);
            echo "<p class='warning'>⚠ Created setting: {$setting[0]}</p>";
        } else {
            echo "<p class='success'>✓ Setting exists: {$setting[0]}</p>";
        }
    } catch (Exception $e) {
        $errors[] = "Setting error: " . $e->getMessage();
    }
}
echo "</div>";

// 6. Insert Default Payment Gateways
echo "<div class='box'><h3>💳 Payment Gateways</h3>";
$gateways = [
    ['bKash', 'bkash', 1, 1],
    ['Nagad', 'nagad', 1, 2],
    ['SSLCommerz', 'sslcommerz', 1, 3],
    ['Bank Transfer', 'bank', 1, 4],
    ['Visa Card', 'visa', 1, 5],
    ['Master Card', 'mastercard', 1, 6]
];

foreach ($gateways as $gw) {
    try {
        $check = $pdo->prepare("SELECT id FROM payment_gateways WHERE code = ?");
        $check->execute([$gw[1]]);
        if (!$check->fetch()) {
            $pdo->prepare("INSERT INTO payment_gateways (name, code, is_active, sort_order) VALUES (?, ?, ?, ?)")
                ->execute($gw);
            echo "<p class='warning'>⚠ Created gateway: {$gw[0]}</p>";
        } else {
            echo "<p class='success'>✓ Gateway exists: {$gw[0]}</p>";
        }
    } catch (Exception $e) {
        $errors[] = "Gateway error: " . $e->getMessage();
    }
}
echo "</div>";

// 7. Check critical files
echo "<div class='box'><h3>📁 Critical Files Check</h3>";
$criticalFiles = [
    'admin/login.php',
    'admin/index.php',
    'admin/includes/header.php',
    'admin/includes/footer.php',
    'admin/assets/css/admin.css',
    'pages/login.php',
    'pages/register.php',
    'user/dashboard.php'
];

foreach ($criticalFiles as $file) {
    if (file_exists($file)) {
        echo "<p class='success'>✓ $file</p>";
    } else {
        $errors[] = "Missing file: $file";
        echo "<p class='error'>✗ $file MISSING</p>";
    }
}
echo "</div>";

// 8. Create upload directories
echo "<div class='box'><h3>📂 Upload Directories</h3>";
$dirs = [
    'assets/uploads/products',
    'assets/uploads/thumbnails',
    'assets/uploads/screenshots'
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "<p class='warning'>⚠ Created directory: $dir</p>";
        } else {
            $errors[] = "Cannot create directory: $dir";
        }
    } else {
        echo "<p class='success'>✓ Directory exists: $dir</p>";
    }
}
echo "</div>";

// Summary
echo "<div class='box'><h3>📋 Summary</h3>";
if (empty($errors)) {
    echo "<p class='success' style='font-size: 18px;'><strong>✓ All checks passed! System is ready.</strong></p>";
    echo "<table>";
    echo "<tr><th>Item</th><th>Value</th></tr>";
    echo "<tr><td>Admin URL</td><td>http://localhost/M.B>Hatt/admin/login.php</td></tr>";
    echo "<tr><td>Email</td><td>admin@mbhaat.com</td></tr>";
    echo "<tr><td>Password</td><td>admin123</td></tr>";
    echo "</table>";
} else {
    echo "<p class='error' style='font-size: 18px;'><strong>✗ Some errors found:</strong></p>";
    foreach ($errors as $error) {
        echo "<p class='error'>• $error</p>";
    }
}
echo "</div>";

echo "<div class='box' style='background: #fff3cd; border: 2px solid #ffc107;'>";
echo "<h3 style='color: #856404;'>⚠ SECURITY WARNING</h3>";
echo "<p style='color: #856404;'><strong>DELETE THIS FILE IMMEDIATELY AFTER USE!</strong></p>";
echo "<form method='post'><button type='submit' name='delete_self'>🗑️ DELETE fix-all.php NOW</button></form>";
echo "</div>";

// Self-delete
if (isset($_POST['delete_self'])) {
    unlink(__FILE__);
    echo "<script>alert('fix-all.php has been deleted!'); window.location.href='admin/login.php';</script>";
}
?>
