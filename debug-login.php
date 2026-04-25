<?php
/**
 * Debug Login - Check database status
 * DELETE THIS FILE AFTER USE
 */

require_once 'includes/config.php';

echo "<style>
body { font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; }
h2 { color: #36064D; border-bottom: 2px solid #76D2DB; padding-bottom: 10px; }
table { width: 100%; border-collapse: collapse; margin: 20px 0; }
th, td { padding: 10px; text-align: left; border: 1px solid #ddd; }
th { background: #36064D; color: white; }
.success { color: green; }
.error { color: red; }
pre { background: #f4f4f4; padding: 10px; overflow-x: auto; }
</style>";

echo "<h2>🔍 Login Debug Information</h2>";

// Check database connection
try {
    $test = $pdo->query("SELECT 1");
    echo "<p class='success'>✓ Database connection: OK</p>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Database connection failed: " . $e->getMessage() . "</p>";
    exit;
}

// Check if users table exists
try {
    $tables = $pdo->query("SHOW TABLES LIKE 'users'")->fetchAll();
    if (count($tables) > 0) {
        echo "<p class='success'>✓ Users table exists</p>";
    } else {
        echo "<p class='error'>✗ Users table does not exist!</p>";
        echo "<p>Please import database/schema.sql</p>";
        exit;
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Error checking tables: " . $e->getMessage() . "</p>";
    exit;
}

// Check admin user
echo "<h3>Admin User Check</h3>";
$stmt = $pdo->prepare("SELECT id, name, email, role, status, password, last_login FROM users WHERE email = ?");
$stmt->execute(['admin@mbhaat.com']);
$admin = $stmt->fetch();

if ($admin) {
    echo "<table>";
    echo "<tr><th>Field</th><th>Value</th></tr>";
    echo "<tr><td>ID</td><td>" . $admin['id'] . "</td></tr>";
    echo "<tr><td>Name</td><td>" . htmlspecialchars($admin['name']) . "</td></tr>";
    echo "<tr><td>Email</td><td>" . htmlspecialchars($admin['email']) . "</td></tr>";
    echo "<tr><td>Role</td><td>" . $admin['role'] . "</td></tr>";
    echo "<tr><td>Status</td><td>" . $admin['status'] . "</td></tr>";
    echo "<tr><td>Last Login</td><td>" . ($admin['last_login'] ?? 'Never') . "</td></tr>";
    echo "<tr><td>Password Hash</td><td><pre style='font-size: 10px;'>" . $admin['password'] . "</pre></td></tr>";
    echo "</table>";
    
    // Test password verification
    $testPassword = 'admin123';
    if (password_verify($testPassword, $admin['password'])) {
        echo "<p class='success'>✓ Password 'admin123' matches the hash!</p>";
    } else {
        echo "<p class='error'>✗ Password 'admin123' does NOT match the hash!</p>";
        echo "<p>Generating correct hash for 'admin123':</p>";
        $correctHash = password_hash('admin123', PASSWORD_DEFAULT);
        echo "<pre>$correctHash</pre>";
        echo "<p><strong>To fix, run this SQL:</strong></p>";
        echo "<pre>UPDATE users SET password = '$correctHash' WHERE email = 'admin@mbhaat.com';</pre>";
    }
} else {
    echo "<p class='error'>✗ Admin user not found!</p>";
    echo "<p>Creating admin user...</p>";
    
    try {
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $insert = $pdo->prepare("INSERT INTO users (name, email, password, role, email_verified, status) VALUES (?, ?, ?, ?, 1, 'active')");
        $insert->execute(['Super Admin', 'admin@mbhaat.com', $hashedPassword, 'admin']);
        echo "<p class='success'>✓ Admin user created! Try logging in now.</p>";
    } catch (Exception $e) {
        echo "<p class='error'>✗ Failed to create admin: " . $e->getMessage() . "</p>";
    }
}

// Show all users
echo "<h3>All Users</h3>";
$allUsers = $pdo->query("SELECT id, name, email, role, status FROM users LIMIT 10")->fetchAll();
if (count($allUsers) > 0) {
    echo "<table>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th></tr>";
    foreach ($allUsers as $user) {
        echo "<tr>";
        echo "<td>" . $user['id'] . "</td>";
        echo "<td>" . htmlspecialchars($user['name']) . "</td>";
        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
        echo "<td>" . $user['role'] . "</td>";
        echo "<td>" . $user['status'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No users found in database.</p>";
}

echo "<hr>";
echo "<p style='color: red;'><strong>⚠ DELETE THIS FILE AFTER USE FOR SECURITY</strong></p>";
echo "<p><a href='admin/login.php' style='display: inline-block; padding: 10px 20px; background: #36064D; color: white; text-decoration: none; border-radius: 5px;'>Go to Admin Login</a></p>";
?>
