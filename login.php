<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root"; // default WAMP root user
$password = "1234";     // leave empty unless you set one
$dbname = "vetsmartdb";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data
$user = $_POST['username'];
$pass = $_POST['password']; // plain text for hardcoded admin
$role = $_POST['role'];

// ✅ Hardcoded admin credentials
$admin_username = "admin";
$admin_password = "admin123"; // you can change this

if ($role === "admin" && $user === $admin_username && $pass === $admin_password) {
    $_SESSION['username'] = $user;
    $_SESSION['role'] = "admin";
    header("Location: index.html");
    exit();
}

// ✅ Check DB for user login
$pass_hashed = md5($pass); // hash password for DB users
$sql = "SELECT * FROM users WHERE username=? AND password=? AND role=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $user, $pass_hashed, $role);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $_SESSION['username'] = $user;
    $_SESSION['role'] = $role;
    header("Location: user-dashboard.html");
    exit();
} else {
    echo "<script>alert('Invalid login! Please try again.'); window.location='login.html';</script>";
}

$conn->close();
?>
