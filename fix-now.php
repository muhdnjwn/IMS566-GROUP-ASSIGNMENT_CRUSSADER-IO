<?php
// fix-now.php
$servername = "localhost";
$username = "root";
$password = "";  // Laragon default is empty password
$dbname = "crussader_io4";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Fixing Admin Password</h2>";

// 1. Check current password
$check = $conn->query("SELECT * FROM Admin WHERE Username='admin1'");
$admin = $check->fetch_assoc();

echo "Current password in DB: " . $admin['Password'] . "<br>";
echo "Length: " . strlen($admin['Password']) . " characters<br>";

// 2. Hash the password
$plain_password = "11223344";
$hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);

echo "New hashed password: " . $hashed_password . "<br>";

// 3. Update database
$sql = "UPDATE Admin SET Password = '$hashed_password' WHERE Username = 'admin1'";

if ($conn->query($sql) === TRUE) {
    echo "<h3 style='color:green;'>✅ SUCCESS! Password updated!</h3>";
    echo "You can now login with:<br>";
    echo "Username: <strong>admin1</strong><br>";
    echo "Password: <strong>11223344</strong><br>";
    echo "<br><a href='login.php' style='padding:10px 20px; background:blue; color:white; text-decoration:none;'>GO TO LOGIN PAGE</a>";
} else {
    echo "<h3 style='color:red;'>❌ ERROR: " . $conn->error . "</h3>";
}

// 4. Verify it works
$check2 = $conn->query("SELECT * FROM Admin WHERE Username='admin1'");
$admin2 = $check2->fetch_assoc();

echo "<hr><h3>Verification:</h3>";
echo "New password in DB: " . $admin2['Password'] . "<br>";
echo "Verify '11223344': " . (password_verify("11223344", $admin2['Password']) ? "✅ WORKS" : "❌ FAILS") . "<br>";

$conn->close();
?>