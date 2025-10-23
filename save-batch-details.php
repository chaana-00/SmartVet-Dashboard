<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "vetsmartdb");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$farm_name = $_POST['farm_name'];
$farm_location = $_POST['farm_location'];
$cage_no = $_POST['cage_no'];
$breed = $_POST['breed'];
$hatchery = $_POST['hatchery'];
$total_input = $_POST['total_input'];
$vaccination_details = $_POST['vaccination_details'];
$treatment_history = $_POST['treatment_history'];

// Insert into batch_details table
$sql = "INSERT INTO batch_details (farm_id, farm_location, cage_no, breed, hatchery, total_input, vaccination_details, treatment_history)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("issssiss", $farm_name, $farm_location, $cage_no, $breed, $hatchery, $total_input, $vaccination_details, $treatment_history);

if ($stmt->execute()) {
    echo "<script>alert('Batch details saved successfully!'); window.location='add-batch-details.html';</script>";
} else {
    echo "<script>alert('Error saving details: " . $conn->error . "'); window.location='add-batch-details.html';</script>";
}

$stmt->close();
$conn->close();
?>
