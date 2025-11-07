<?php
// database credentials
$db_host = "localhost";
$user = "root";
$password = "";
$dbname = "blog";

$conn = new mysqli($db_host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// echo "Database connected successfully!";
?>
