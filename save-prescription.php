<?php
// =========================
// DATABASE CONNECTION
// =========================
$conn = new mysqli("localhost", "root", "1234", "vetsmartdb");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// =========================
// CHECK POST REQUEST
// =========================
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: create-prescription.php");
    exit;
}

// =========================
// CLEAN INPUT
// =========================
function clean($value) {
    return htmlspecialchars(trim($value));
}

// =========================
// MAIN PRESCRIPTION DATA
// =========================
$farm_id       = clean($_POST["farm_id"]);
$no_of_birds   = clean($_POST["no_of_birds"]);
$date_time     = clean($_POST["date_time"]);

// =========================
// PRESCRIPTION TABLE INPUTS
// =========================
$drugs         = $_POST["drug"] ?? [];
$indications   = $_POST["indication"] ?? [];
$durations     = $_POST["duration"] ?? [];
$total_volume  = $_POST["total_volume"] ?? [];

// =========================
// INSERT MAIN PRESCRIPTION
// =========================
$stmt = $conn->prepare("INSERT INTO prescriptions (farm_id, no_of_birds, date_time) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $farm_id, $no_of_birds, $date_time);

if (!$stmt->execute()) {
    die("Error saving prescription: " . $stmt->error);
}

$prescription_id = $stmt->insert_id;
$stmt->close();

// =========================
// INSERT MULTIPLE ITEMS
// =========================
$item_sql = $conn->prepare(
    "INSERT INTO prescription_items (prescription_id, drug, indication, duration, total_volume)
     VALUES (?, ?, ?, ?, ?)"
);

for ($i = 0; $i < count($drugs); $i++) {

    $drug        = clean($drugs[$i]);
    $indication  = clean($indications[$i]);
    $duration    = clean($durations[$i]);
    $volume      = clean($total_volume[$i]);

    if ($drug == "" && $indication == "" && $duration == "" && $volume == "") {
        continue; // skip empty rows
    }

    $item_sql->bind_param("issss", $prescription_id, $drug, $indication, $duration, $volume);
    $item_sql->execute();
}

$item_sql->close();
$conn->close();

// =========================
// AUTO-REDIRECT TO DOWNLOAD
// =========================
header("Location: create-prescription.php?id=" . $prescription_id);
exit;
?>
