<?php
$host = "localhost";   // your database host
$user = "root";        // your database username
$pass = "";            // your database password
$dbname = "login_system"; // database name

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
