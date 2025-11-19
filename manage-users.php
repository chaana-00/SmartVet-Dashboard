<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root"; 
$password = "1234";     
$dbname = "vetsmartdb";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname     = $_POST['fullname'];
    $designation  = $_POST['designation'];
    $company      = $_POST['company'];
    $telephone    = $_POST['telephone'];
    $username     = $_POST['username'];
    $password     = md5($_POST['password']); // hashed password
    $confirmPass  = md5($_POST['confirmPassword']);

    if ($password !== $confirmPass) {
        echo "<script>alert('Passwords do not match!'); window.location='manage-users.html';</script>";
        exit();
    }

    // Insert user (role is always 'user')
    $sql = "INSERT INTO users (fullname, designation, company, telephone, username, password, role)
            VALUES (?, ?, ?, ?, ?, ?, 'user')";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $fullname, $designation, $company, $telephone, $username, $password);

    if ($stmt->execute()) {
        echo "<script>alert('User registered successfully!'); window.location='users-list.php';</script>";
    } else {
        echo "<script>alert('Error: " . $conn->error . "'); window.location='manage-users.html';</script>";
    }

    $stmt->close();
}
$conn->close();
?>
