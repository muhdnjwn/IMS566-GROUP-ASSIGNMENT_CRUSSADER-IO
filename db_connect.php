<?php

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "crussader_io4";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
