<?php
$host = "localhost";
$user = "root";
$pass = "1234";   // your MySQL password
$db   = "vetsmartdb";  // your database name

$conn = new mysqli($host, $user, $pass, $db);

// Check DB Connection
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}
?>
