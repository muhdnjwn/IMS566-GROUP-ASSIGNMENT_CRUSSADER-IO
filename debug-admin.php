<?php
// debug-admin.php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug Admin Login</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .debug-box { background: #f0f0f0; padding: 15px; margin: 10px 0; border-left: 5px solid #333; }
        .success { background: #d4edda; border-left: 5px solid #28a745; }
        .error { background: #f8d7da; border-left: 5px solid #dc3545; }
        .warning { background: #fff3cd; border-left: 5px solid #ffc107; }
        .info { background: #d1ecf1; border-left: 5px solid #17a2b8; }
    </style>
</head>
<body>
    <h1>🔧 Debug Admin Login - Laragon</h1>
    
    <?php
    // Database connection
    $servername = "localhost";
    $username = "root";
    $password = "";  // Laragon default
    $dbname = "crussader_io4";
    
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        echo "<div class='error'>❌ Database Connection Failed: " . $conn->connect_error . "</div>";
        die();
    }
    echo "<div class='success'>✅ Database Connected Successfully</div>";
    ?>
    
    <div class="debug-box info">
        <h3>📊 Database Check</h3>
        <?php
        // Check Admin table
        $check_table = $conn->query("SHOW TABLES LIKE 'Admin'");
        if ($check_table->num_rows > 0) {
            echo "✅ Admin table exists<br>";
            
            // Check if admin1 exists
            $result = $conn->query("SELECT * FROM Admin WHERE Username = 'admin1'");
            if ($result->num_rows > 0) {
                $admin = $result->fetch_assoc();
                echo "✅ Admin 'admin1' found<br>";
                echo "Admin ID: " . $admin['Admin_ID'] . "<br>";
                echo "Name: " . $admin['Name'] . "<br>";
                echo "Email: " . $admin['Email'] . "<br>";
                echo "Username: " . $admin['Username'] . "<br>";
                echo "Password in DB: <code>" . $admin['Password'] . "</code><br>";
                echo "Password length: " . strlen($admin['Password']) . " characters<br>";
                
                // Check if password is hashed
                if (strlen($admin['Password']) > 20 && strpos($admin['Password'], '$') === 0) {
                    echo "🔐 Password appears to be HASHED<br>";
                } else {
                    echo "⚠️ Password appears to be PLAIN TEXT<br>";
                }
            } else {
                echo "❌ Admin 'admin1' NOT found in database<br>";
            }
        } else {
            echo "❌ Admin table does NOT exist<br>";
        }
        ?>
    </div>
    
    <div class="debug-box warning">
        <h3>🔑 Password Verification Test</h3>
        <?php
        if (isset($admin)) {
            $test_password = "11223344";
            
            echo "Testing password: <strong>$test_password</strong><br>";
            echo "Against DB password: <code>" . $admin['Password'] . "</code><br><br>";
            
            // Test 1: password_verify (for hashed passwords)
            $verify_result = password_verify($test_password, $admin['Password']);
            echo "1. password_verify(): " . ($verify_result ? "✅ SUCCESS" : "❌ FAILED") . "<br>";
            
            // Test 2: Direct comparison (for plain text)
            $direct_result = ($test_password == $admin['Password']);
            echo "2. Direct comparison (==): " . ($direct_result ? "✅ SUCCESS" : "❌ FAILED") . "<br>";
            
            // Test 3: Exact comparison
            $exact_result = ($test_password === $admin['Password']);
            echo "3. Exact comparison (===): " . ($exact_result ? "✅ SUCCESS" : "❌ FAILED") . "<br>";
            
            echo "<br><strong>Conclusion:</strong> ";
            if ($verify_result) {
                echo "Password is HASHED correctly";
            } elseif ($direct_result) {
                echo "Password is PLAIN TEXT (needs to be hashed)";
            } else {
                echo "Password is WRONG in database";
            }
        } else {
            echo "Cannot test - admin not found";
        }
        ?>
    </div>
    
    <div class="debug-box">
        <h3>🛠️ Fix Password</h3>
        <?php
        if (isset($admin) && !password_verify("11223344", $admin['Password'])) {
            echo "Password needs fixing!<br>";
            echo "<form method='POST'>";
            echo "<input type='hidden' name='fix_password' value='1'>";
            echo "<button type='submit' style='padding:10px; background:#28a745; color:white; border:none; cursor:pointer;'>";
            echo "🔧 FIX PASSWORD NOW (Hash it)";
            echo "</button>";
            echo "</form>";
            
            if (isset($_POST['fix_password'])) {
                $hashed = password_hash("11223344", PASSWORD_DEFAULT);
                $update = $conn->query("UPDATE Admin SET Password = '$hashed' WHERE Username = 'admin1'");
                
                if ($update) {
                    echo "<div class='success'>✅ Password fixed! New hash created.<br>";
                    echo "New hash: <code>" . $hashed . "</code></div>";
                    
                    // Verify it works
                    $new_check = $conn->query("SELECT * FROM Admin WHERE Username = 'admin1'");
                    $new_admin = $new_check->fetch_assoc();
                    echo "Verification: " . (password_verify("11223344", $new_admin['Password']) ? "✅ WORKS" : "❌ FAILS");
                } else {
                    echo "<div class='error'>❌ Failed to update: " . $conn->error . "</div>";
                }
            }
        } elseif (isset($admin)) {
            echo "✅ Password is already hashed correctly!";
        }
        ?>
    </div>
    
    <div class="debug-box info">
        <h3>🔍 Session Check</h3>
        <?php
        echo "Session ID: " . session_id() . "<br>";
        echo "Session data:<br>";
        echo "<pre>";
        print_r($_SESSION);
        echo "</pre>";
        
        echo "<br>Your login.php sets these sessions:<br>";
        echo "1. \$_SESSION['admin_id'] = \$admin['Admin_ID']<br>";
        echo "2. \$_SESSION['admin_name'] = \$admin['Name']<br>";
        echo "3. \$_SESSION['role'] = 'admin'<br>";
        ?>
    </div>
    
    <div class="debug-box">
        <h3>🧪 Test Login Function</h3>
        <?php
        echo "<form method='POST'>";
        echo "Test login directly:<br>";
        echo "Username: <input type='text' name='test_user' value='admin1'><br>";
        echo "Password: <input type='text' name='test_pass' value='11223344'><br>";
        echo "<button type='submit' name='test_login'>Test Login</button>";
        echo "</form>";
        
        if (isset($_POST['test_login'])) {
            $test_user = $_POST['test_user'];
            $test_pass = $_POST['test_pass'];
            
            $stmt = $conn->prepare("SELECT * FROM Admin WHERE Username = ?");
            $stmt->bind_param("s", $test_user);
            $stmt->execute();
            $result = $stmt->get_result();
            $test_admin = $result->fetch_assoc();
            
            echo "<div class='" . ($test_admin ? 'info' : 'error') . "'>";
            if ($test_admin) {
                echo "✅ Admin found<br>";
                
                if (password_verify($test_pass, $test_admin['Password'])) {
                    echo "✅ password_verify SUCCESS<br>";
                    echo "Would set session: admin_id = " . $test_admin['Admin_ID'] . "<br>";
                    echo "<a href='login.php' style='color:green;'>Go to actual login page</a>";
                } elseif ($test_pass == $test_admin['Password']) {
                    echo "⚠️ Direct comparison works (password is plain text)<br>";
                    echo "You need to hash the password!<br>";
                } else {
                    echo "❌ Password verification failed<br>";
                }
            } else {
                echo "❌ Admin not found";
            }
            echo "</div>";
        }
        ?>
    </div>
    
    <div class="debug-box success">
        <h3>✅ Quick Links</h3>
        <a href="login.php">Go to Login Page</a> | 
        <a href="admin-dashboard.php">Go to Admin Dashboard</a> |
        <a href="index.php">Go to Home</a>
    </div>
    
    <?php $conn->close(); ?>
</body>
</html>