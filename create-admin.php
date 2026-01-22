<?php
include 'db_connect.php';

$username = "admin1";
$password = "11223344";
$name = "Muhammad Najwan";
$email = "najwancrussader@gmail.com";

// Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert into Admin table
$sql = "INSERT INTO Admin (Username, Password, Name, Email) 
        VALUES ('$username', '$hashed_password', '$name', '$email')";

if ($conn->query($sql) === TRUE) {
    echo "Admin created successfully!";
} else {
    echo "Error: " . $conn->error;
}
?>