<?php
$conn = new mysqli("localhost", "root", "1234", "vetsmartdb");
$result = $conn->query("SELECT id, farm_name, farm_id, owner_name, farm_type FROM farms ORDER BY created_at DESC");
$farms = [];
while ($row = $result->fetch_assoc()) { $farms[] = $row; }
echo json_encode($farms);
$conn->close();
?>
