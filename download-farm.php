<?php
require_once 'vendor/autoload.php'; // if using PHPWord
$conn = new mysqli("localhost","root","","vetsmartdb");
$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM farms WHERE id=?");
$stmt->bind_param("i",$id);
$stmt->execute();
$result = $stmt->get_result();
$farm = $result->fetch_assoc();

header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
header("Content-Disposition: attachment; filename=farm-".$farm['id'].".docx");

$content = "<h2>{$farm['farm_name']}</h2>
<p><strong>Farm ID:</strong> {$farm['farm_id']}</p>
<p><strong>Location:</strong> {$farm['farm_location']}</p>
<p><strong>Owner:</strong> {$farm['owner_name']}</p>
<p><strong>Contacts:</strong> {$farm['farm_contact1']}, {$farm['farm_contact2']}, {$farm['farm_contact3']}</p>
<p><strong>Type:</strong> {$farm['farm_type']}</p>
<p><strong>Capacity:</strong> {$farm['farm_capacity']}</p>
<p><strong>Note:</strong> {$farm['note']}</p>";

echo $content;
?>
