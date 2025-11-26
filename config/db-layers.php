<?php
$conn = new mysqli("localhost", "root", "1234", "vetsmartdb");

if ($conn->connect_error) {
    die("DB Connection Failed: " . $conn->connect_error);
}
?>
