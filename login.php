<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root"; // default WAMP root user
$password = "";     // leave empty unless you set one
$dbname = "vetsmartdb";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data
$user = $_POST['username'];
$pass = md5($_POST['password']); // same hashing as DB
$role = $_POST['role'];

// Check user
$sql = "SELECT * FROM users WHERE username=? AND password=? AND role=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $user, $pass, $role);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $_SESSION['username'] = $user;
    $_SESSION['role'] = $role;

    if ($role == "admin") {
        header("Location: index.html");  // ✅ Redirect admin to index.html
        exit();
    } else {
        header("Location: users-dashboard.html"); // ✅ Redirect user
        exit();
    }
} else {
    echo "<script>alert('Invalid username, password, or role!'); window.location='login.html';</script>";
}

echo "<pre>";
print_r($_POST);
echo "</pre>";


$conn->close();
?>
