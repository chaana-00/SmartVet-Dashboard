<?php
// DB connection
$servername = "localhost";
$username = "root";
$password = "1234";
$dbname = "vetsmartdb";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { 
    die(json_encode(["status" => "error", "message" => "DB connection failed"]));
}

// ------------------------------------------------------
// 1. Load farm IDs for dropdown
// ------------------------------------------------------
if (isset($_GET['load_ids'])) {
    $sql = "SELECT farm_id FROM farms ORDER BY farm_id DESC";
    $result = $conn->query($sql);

    $ids = [];
    while ($row = $result->fetch_assoc()) {
        $ids[] = $row['farm_id'];
    }

    echo json_encode($ids);
    exit;
}

// ------------------------------------------------------
// 2. Fetch specific farm details
// ------------------------------------------------------
if (isset($_GET['farm_id'])) {
    $farm_id = $_GET['farm_id'];

    $sql = "SELECT * FROM farms WHERE farm_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $farm_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo json_encode(["status" => "error", "message" => "Farm ID not found"]);
    } else {
        echo json_encode(["status" => "success", "data" => $result->fetch_assoc()]);
    }
    exit;
}

?>
