<?php
/**
 * Admin Panel Debug
 * DELETE THIS FILE AFTER USE
 */

require_once 'includes/config.php';

echo "<style>
body { font-family: Arial; padding: 20px; max-width: 900px; margin: 0 auto; }
h2 { color: #36064D; }
.success { color: green; }
.error { color: red; }
table { width: 100%; border-collapse: collapse; margin: 20px 0; }
th, td { padding: 8px; border: 1px solid #ddd; }
th { background: #36064D; color: white; }
</style>";

echo "<h2>🔍 Admin Panel Debug</h2>";

// 1. Check login session
echo "<h3>Session Status</h3>";
if (isLoggedIn()) {
    echo "<p class='success'>✓ Logged in as: " . getUserName() . " (" . $_SESSION['user_email'] . ")</p>";
    echo "<p>Role: " . ($_SESSION['role'] ?? 'N/A') . "</p>";
    if (isAdmin()) {
        echo "<p class='success'>✓ Has admin role</p>";
    } else {
        echo "<p class='error'>✗ NOT an admin</p>";
    }
} else {
    echo "<p class='error'>✗ Not logged in</p>";
}

// 2. Check required tables
echo "<h3>Database Tables</h3>";
$requiredTables = ['users', 'products', 'orders', 'order_items', 'categories', 'coupons', 'settings'];
foreach ($requiredTables as $table) {
    try {
        $result = $pdo->query("SHOW TABLES LIKE '$table'")->fetch();
        echo $result ? "<p class='success'>✓ $table</p>" : "<p class='error'>✗ $table missing</p>";
    } catch (Exception $e) {
        echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
    }
}

// 3. Check admin user in database
echo "<h3>Admin User Check</h3>";
$stmt = $pdo->query("SELECT id, name, email, role, status FROM users WHERE role = 'admin'");
$admins = $stmt->fetchAll();
if (count($admins) > 0) {
    echo "<table><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th></tr>";
    foreach ($admins as $admin) {
        echo "<tr><td>{$admin['id']}</td><td>{$admin['name']}</td><td>{$admin['email']}</td><td>{$admin['role']}</td><td>{$admin['status']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p class='error'>✗ No admin users found!</p>";
}

// 4. Test queries from admin/index.php
echo "<h3>Testing Admin Dashboard Queries</h3>";

try {
    $users = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
    echo "<p class='success'>✓ Total Users: $users</p>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Users query failed: " . $e->getMessage() . "</p>";
}

try {
    $products = $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'active'")->fetchColumn();
    echo "<p class='success'>✓ Active Products: $products</p>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Products query failed: " . $e->getMessage() . "</p>";
}

try {
    $orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'completed'")->fetchColumn();
    echo "<p class='success'>✓ Completed Orders: $orders</p>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Orders query failed: " . $e->getMessage() . "</p>";
}

try {
    $today = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE DATE(created_at) = CURDATE() AND order_status = 'completed'")->fetchColumn();
    echo "<p class='success'>✓ Today's Sales: $today</p>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Sales query failed: " . $e->getMessage() . "</p>";
}

try {
    $pending = $pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'pending'")->fetchColumn();
    echo "<p class='success'>✓ Pending Orders: $pending</p>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Pending orders query failed: " . $e->getMessage() . "</p>";
}

// 5. Check file paths
echo "<h3>File Checks</h3>";
$files = [
    'admin/assets/css/admin.css',
    'admin/assets/js/admin.js',
    'admin/includes/header.php',
    'admin/includes/footer.php',
];
foreach ($files as $file) {
    if (file_exists($file)) {
        echo "<p class='success'>✓ $file</p>";
    } else {
        echo "<p class='error'>✗ $file missing</p>";
    }
}

// 6. Create admin user if missing
if (count($admins) == 0) {
    echo "<h3>Fix: Create Admin User</h3>";
    try {
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO users (name, email, password, role, email_verified, status) VALUES (?, ?, ?, ?, 1, 'active')")
            ->execute(['Super Admin', 'admin@mbhaat.com', $hash, 'admin']);
        echo "<p class='success'>✓ Admin user created!</p>";
        echo "<p>Email: admin@mbhaat.com</p>";
        echo "<p>Password: admin123</p>";
    } catch (Exception $e) {
        echo "<p class='error'>✗ Failed to create admin: " . $e->getMessage() . "</p>";
    }
}

echo "<hr><p style='color:red'><strong>⚠ DELETE THIS FILE AFTER USE</strong></p>";
echo "<p><a href='admin/login.php' style='padding:10px 20px;background:#36064D;color:white;text-decoration:none;border-radius:5px;'>Go to Admin Login</a></p>";
?>
