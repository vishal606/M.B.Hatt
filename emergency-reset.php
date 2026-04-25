<?php
/**
 * EMERGENCY PASSWORD RESET
 * DELETE AFTER USE!
 */

require_once 'includes/config.php';

echo "<style>
body { font-family: Arial; padding: 20px; max-width: 600px; margin: 50px auto; background: #f0f0f0; }
.box { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
h2 { color: #36064D; }
.success { color: green; background: #d4edda; padding: 10px; border-radius: 5px; }
.error { color: red; background: #f8d7da; padding: 10px; border-radius: 5px; }
button { padding: 12px 24px; background: #dc3545; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
button:hover { background: #c82333; }
pre { background: #f4f4f4; padding: 10px; font-size: 12px; overflow-x: auto; }
</style>";

echo "<div class='box'>";
echo "<h2>🚨 Emergency Password Reset</h2>";

// Get current admin data
$stmt = $pdo->query("SELECT id, name, email, password, role, status FROM users WHERE email = 'admin@mbhaat.com'");
$admin = $stmt->fetch();

if ($admin) {
    echo "<p>Found admin user:</p>";
    echo "<pre>";
    echo "ID: " . $admin['id'] . "\n";
    echo "Name: " . $admin['name'] . "\n";
    echo "Email: " . $admin['email'] . "\n";
    echo "Role: " . $admin['role'] . "\n";
    echo "Status: " . $admin['status'] . "\n";
    echo "Password Hash: " . substr($admin['password'], 0, 30) . "...\n";
    echo "</pre>";
    
    // Test if current hash works with admin123
    $test = password_verify('admin123', $admin['password']);
    echo "<p>Current hash works with 'admin123': <strong>" . ($test ? 'YES' : 'NO') . "</strong></p>";
    
    if (isset($_POST['reset'])) {
        // Generate new hash
        $newPassword = 'admin123';
        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // Update user
        $update = $pdo->prepare("UPDATE users SET password = ?, role = 'admin', status = 'active', email_verified = 1 WHERE id = ?");
        $update->execute([$newHash, $admin['id']);
        
        // Verify update
        $verify = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $verify->execute([$admin['id']]);
        $updated = $verify->fetch();
        
        // Test new hash
        $works = password_verify($newPassword, $updated['password']);
        
        if ($works) {
            echo "<div class='success'>";
            echo "<h3>✓ Password Reset Successful!</h3>";
            echo "<p><strong>Email:</strong> admin@mbhaat.com</p>";
            echo "<p><strong>Password:</strong> admin123</p>";
            echo "<p>New hash verified and working!</p>";
            echo "<a href='admin/login.php' style='display:inline-block;margin-top:10px;padding:10px 20px;background:#36064D;color:white;text-decoration:none;border-radius:5px;'>Go to Admin Login</a>";
            echo "</div>";
        } else {
            echo "<div class='error'>";
            echo "<h3>✗ Reset Failed</h3>";
            echo "<p>New hash doesn't work. This is a system issue.</p>";
            echo "</div>";
        }
    } else {
        echo "<form method='post'>";
        echo "<p>Click below to reset admin password to <strong>admin123</strong>:</p>";
        echo "<button type='submit' name='reset'>🔄 RESET PASSWORD NOW</button>";
        echo "</form>";
    }
} else {
    // Create admin user
    echo "<p class='error'>No admin user found! Creating one...</p>";
    
    if (isset($_POST['create'])) {
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $insert = $pdo->prepare("INSERT INTO users (name, email, password, role, email_verified, status) VALUES (?, ?, ?, ?, 1, 'active')");
        $insert->execute(['Super Admin', 'admin@mbhaat.com', $hash, 'admin']);
        
        echo "<div class='success'>";
        echo "<h3>✓ Admin Created!</h3>";
        echo "<p><strong>Email:</strong> admin@mbhaat.com</p>";
        echo "<p><strong>Password:</strong> admin123</p>";
        echo "<a href='admin/login.php' style='display:inline-block;margin-top:10px;padding:10px 20px;background:#36064D;color:white;text-decoration:none;border-radius:5px;'>Go to Admin Login</a>";
        echo "</div>";
    } else {
        echo "<form method='post'>";
        echo "<button type='submit' name='create'>➕ CREATE ADMIN USER</button>";
        echo "</form>";
    }
}

echo "<hr style='margin-top:30px;'>";
echo "<p style='color:red'><strong>DELETE THIS FILE AFTER USE!</strong></p>";

if (isset($_POST['delete'])) {
    unlink(__FILE__);
    echo "<script>alert('File deleted!'); window.location.href='admin/login.php';</script>";
}

echo "<form method='post' style='margin-top:10px;'><button type='submit' name='delete' style='background:#6c757d;'>🗑️ Delete This File</button></form>";

echo "</div>";
?>
