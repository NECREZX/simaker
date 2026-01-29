<?php
/**
 * Database Test Script
 * Test database connection dan cek data users
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

echo "<h2>🔍 SIMAKER Database Test</h2>";
echo "<hr>";

// Test 1: Database Connection
echo "<h3>1. Database Connection Test</h3>";
try {
    $db = db();
    echo "✅ <strong style='color: green;'>Database connected successfully!</strong><br>";
    echo "Database: " . DB_NAME . "<br>";
    echo "Host: " . DB_HOST . "<br><br>";
} catch (Exception $e) {
    echo "❌ <strong style='color: red;'>Database connection failed!</strong><br>";
    echo "Error: " . $e->getMessage() . "<br><br>";
    die();
}

// Test 2: Check if tables exist
echo "<h3>2. Tables Check</h3>";
$tables = ['users', 'roles', 'units', 'shifts', 'logbooks'];
foreach ($tables as $table) {
    try {
        $count = countRecords($table);
        echo "✅ Table <strong>$table</strong>: $count records<br>";
    } catch (Exception $e) {
        echo "❌ Table <strong>$table</strong>: NOT FOUND or ERROR<br>";
    }
}
echo "<br>";

// Test 3: List all users
echo "<h3>3. Users List</h3>";
try {
    $users = fetchAll("SELECT u.id, u.username, u.full_name, u.email, r.role_name, u.is_active 
                       FROM users u 
                       JOIN roles r ON u.role_id = r.id 
                       ORDER BY u.id");
    
    if (empty($users)) {
        echo "⚠️ <strong style='color: orange;'>No users found! Please import seeds.sql</strong><br><br>";
    } else {
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
        echo "<tr style='background: #f0f0f0;'>
                <th>ID</th>
                <th>Username</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Active</th>
              </tr>";
        
        foreach ($users as $user) {
            $status = $user['is_active'] ? '✅ Active' : '❌ Inactive';
            echo "<tr>
                    <td>{$user['id']}</td>
                    <td><strong>{$user['username']}</strong></td>
                    <td>{$user['full_name']}</td>
                    <td>{$user['email']}</td>
                    <td>{$user['role_name']}</td>
                    <td>$status</td>
                  </tr>";
        }
        echo "</table><br>";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br><br>";
}

// Test 4: Test password verification
echo "<h3>4. Password Test</h3>";
echo "<p>Testing password verification for username: <strong>admin</strong></p>";

try {
    $user = fetchOne("SELECT * FROM users WHERE username = :username", ['username' => 'admin']);
    
    if ($user) {
        echo "✅ User 'admin' found in database<br>";
        echo "Stored password hash: <code>" . substr($user['password'], 0, 50) . "...</code><br><br>";
        
        // Test password
        $testPassword = 'password123';
        echo "Testing password: <strong>$testPassword</strong><br>";
        
        if (password_verify($testPassword, $user['password'])) {
            echo "✅ <strong style='color: green;'>Password verification SUCCESS!</strong><br>";
            echo "👉 Login should work with username: admin / password: password123<br><br>";
        } else {
            echo "❌ <strong style='color: red;'>Password verification FAILED!</strong><br>";
            echo "👉 The password in database does NOT match 'password123'<br>";
            echo "👉 You need to re-import the seeds.sql file<br><br>";
        }
    } else {
        echo "❌ User 'admin' NOT FOUND in database<br>";
        echo "👉 Please import seeds.sql<br><br>";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br><br>";
}

// Test 5: Generate new password hash (backup)
echo "<h3>5. Password Hash Generator</h3>";
echo "<p>If you need to manually update password, use this hash:</p>";
$newHash = password_hash('password123', PASSWORD_BCRYPT);
echo "Password: <strong>password123</strong><br>";
echo "Hash: <code>$newHash</code><br><br>";

echo "<h4>SQL to update admin password manually:</h4>";
echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
echo "UPDATE users SET password = '$newHash' WHERE username = 'admin';";
echo "</pre>";

echo "<hr>";
echo "<h3>✅ Test Complete!</h3>";
echo "<p><a href='landing.php'>← Back to Landing Page</a> | <a href='login-page.php'>Go to Login Page →</a></p>";
?>
