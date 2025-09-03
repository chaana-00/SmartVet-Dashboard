<?php
$conn = new mysqli("localhost","root","","vetsmartdb");
$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM farms WHERE id=?");
$stmt->bind_param("i",$id);
$stmt->execute();
$result = $stmt->get_result();
$farm = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head><title>Farm Details</title></head>
<body>
<h2><?php echo $farm['farm_name']; ?></h2>
<p><strong>Farm ID:</strong> <?php echo $farm['farm_id']; ?></p>
<p><strong>Location:</strong> <?php echo $farm['farm_location']; ?></p>
<p><strong>Owner:</strong> <?php echo $farm['owner_name']; ?></p>
<p><strong>Contacts:</strong> <?php echo $farm['farm_contact1'] . ', ' . $farm['farm_contact2'] . ', ' . $farm['farm_contact3']; ?></p>
<p><strong>Type:</strong> <?php echo $farm['farm_type']; ?></p>
<p><strong>Capacity:</strong> <?php echo $farm['farm_capacity']; ?></p>
<p><strong>Note:</strong> <?php echo $farm['note']; ?></p>
</body>
</html>
