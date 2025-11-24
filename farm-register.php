<?php
// farm-register.php
session_start();

// DB connection
$servername = "localhost";
$username = "root";
$password = "1234";
$dbname = "vetsmartdb";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { 
    die("Connection failed: " . $conn->connect_error); 
}

// Get POST data (matching your HTML form)
$farm_name       = $_POST['farm_name'];
$farm_id         = $_POST['farm_id'];
$farm_location   = $_POST['farm_location'];
$farm_contact1   = $_POST['farm_contact1'];
$farm_contact2   = $_POST['farm_contact2'];
$farm_contact3   = $_POST['farm_contact3'];
$owner_name      = $_POST['ownerName'];        // corrected
$owner_contact1  = $_POST['ownerContact1'];    // corrected
$owner_contact2  = $_POST['ownerContact2'];    // corrected
$farm_type       = $_POST['farmType'];         // corrected
$farm_capacity   = $_POST['farmCapacity'];     // corrected
$note            = $_POST['farmNote'];         // corrected

// Insert Query
$sql = "INSERT INTO farms 
        (farm_name, farm_id, farm_location, farm_contact1, farm_contact2, farm_contact3,
         owner_name, owner_contact1, owner_contact2, farm_type, farm_capacity, note)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "ssssssssssis",
    $farm_name,
    $farm_id,
    $farm_location,
    $farm_contact1,
    $farm_contact2,
    $farm_contact3,
    $owner_name,
    $owner_contact1,
    $owner_contact2,
    $farm_type,
    $farm_capacity,
    $note
);

if ($stmt->execute()) {

    // 🔥 Redirect to View History page
    header("Location: view-history.html");
    exit();

} else {
    echo json_encode([
        "status" => "error", 
        "message" => $stmt->error
    ]);
}

$conn->close();
?>
