<?php
session_start();
include "db_connect.php";

if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // --- ADMIN LOGIN CHECK ---
    if ($username === 'admin1' && $password === '11223344') {
        $_SESSION['Admin_ID'] = '1';
        $_SESSION['Name'] = 'Muhd Najwan';
        $_SESSION['Username'] = 'admin1';
        $_SESSION['Role'] = 'Administrator';
        header("Location: admin-dashboard.php");
        exit();
    }

    // --- NORMAL USER LOGIN CHECK ---
    $username = mysqli_real_escape_string($conn, $username);
    $sql = "SELECT * FROM users WHERE username='$username'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['full_name'] = $row['full_name'];
            $_SESSION['profile_picture'] = $row['profile_picture'];
            $_SESSION['role'] = 'user';
            header("Location: customer-dashboard.php");
            exit();
        }
    }
    header("Location: login.php?error=Incorrect username or password");
    exit();
}
?>