<?php
$conn = new mysqli("localhost", "root", "1234", "vetsmartdb");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$farm_id              = $_POST['farm_id'];
$farm_location        = $_POST['farm_location'];
$cage_no              = $_POST['cage_no'];
$breed                = $_POST['breed'];
$hatchery             = $_POST['hatchery'];
$total_input          = $_POST['total_input'];
$vaccination_details  = $_POST['vaccination_details'];
$treatment_history    = $_POST['treatment_history'];

$sql = "INSERT INTO batch_details 
(farm_id, farm_location, cage_no, breed, hatchery, total_input, vaccination_details, treatment_history) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "issssiss",
    $farm_id,
    $farm_location,
    $cage_no,
    $breed,
    $hatchery,
    $total_input,
    $vaccination_details,
    $treatment_history
);

if ($stmt->execute()) {
    echo "<script>alert('Batch Details Saved Successfully'); window.location='view-batch-details.php';</script>";
} else {
    echo "Error: " . $stmt->error;
}
?>
