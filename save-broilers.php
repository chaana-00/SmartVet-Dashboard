<?php
// -------------------- DB CONNECTION --------------------
require __DIR__ . '/config/db-broilers.php';

function clean($v) {
    return trim($v);
}

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: add-farm-records.php");
    exit;
}

// -------------------- INPUTS --------------------
$farm_id            = (int)($_POST['farm_name'] ?? 0);
$reg_number         = clean($_POST['reg_number'] ?? '');
$visit_datetime     = $_POST['visit_datetime'] ?? null;
$flock_size         = (int)($_POST['flock_size'] ?? 0);
$age                = (int)($_POST['age'] ?? 0);
$avg_weight         = (float)($_POST['avg_weight'] ?? 0);
$weight_gain        = (float)($_POST['weight_gain'] ?? 0);
$feed_intake        = clean($_POST['feed_intake'] ?? '');
$mortality          = (int)($_POST['mortality'] ?? 0);
$mortality_percent  = (float)($_POST['mortality_percent'] ?? 0);
$complain           = clean($_POST['complain'] ?? '');
$clinical_signs     = clean($_POST['clinical_signs'] ?? '');
$dd                 = clean($_POST['dd'] ?? '');
$test_request       = clean($_POST['test_request'] ?? '');
$test_report        = clean($_POST['test_report'] ?? '');
$recommendation     = clean($_POST['recommendation'] ?? '');
$treatment          = clean($_POST['treatment'] ?? '');
$follow_ups         = clean($_POST['follow_ups'] ?? '');

// Validate
$errors = [];
if ($farm_id <= 0) $errors[] = "Please select a farm.";
if ($reg_number == "") $errors[] = "Registration number is required.";

if ($errors) {
    echo "<h3>Errors:</h3><ul>";
    foreach ($errors as $e) echo "<li>$e</li>";
    echo "</ul><a href='add-farm-records.php'>Go Back</a>";
    exit;
}

// -------------------- FILE UPLOAD --------------------
$uploadedFiles = [];

$folderName = $farm_id . "-" . $reg_number;
$uploadDir = __DIR__ . "/uploads/postmortem-broilers/" . $folderName . "/";

if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

if (!empty($_FILES['post_mortem_images']['name'][0])) {
    foreach ($_FILES['post_mortem_images']['name'] as $i => $name) {

        if ($_FILES['post_mortem_images']['error'][$i] !== UPLOAD_ERR_OK)
            continue;

        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','heic','gif'])) continue;

        // Custom filename: farmID-reg + timestamp + random
        $newName = $farm_id . "-" . $reg_number . "_" . time() . "_" . rand(1000,9999) . "." . $ext;

        $target = $uploadDir . $newName;

        if (move_uploaded_file($_FILES['post_mortem_images']['tmp_name'][$i], $target)) {
            // Save relative path for database
            $uploadedFiles[] = "uploads/postmortem-broilers/" . $folderName . "/" . $newName;
        }
    }
}

$post_images_json = $uploadedFiles ? json_encode($uploadedFiles) : null;

// -------------------- SQL INSERT (19 fields) --------------------

$sql = "INSERT INTO broilers_records (
    farm_id, reg_number, visit_datetime, flock_size, age, avg_weight, weight_gain,
    feed_intake, mortality, mortality_percent, complain, clinical_signs, post_mortem_images,
    dd, test_request, test_report, recommendation, treatment, follow_ups
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

// 19 placeholders ✔️

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "issiiiddidsssssssss",
    $farm_id,
    $reg_number,
    $visit_datetime,
    $flock_size,
    $age,
    $avg_weight,
    $weight_gain,
    $feed_intake,
    $mortality,
    $mortality_percent,
    $complain,
    $clinical_signs,
    $post_images_json,
    $dd,
    $test_request,
    $test_report,
    $recommendation,
    $treatment,
    $follow_ups
);

// types = 19 letters ✔️  
// values = 19 variables ✔️  

if ($stmt->execute()) {
    header("Location: add-farm-records.php?status=success");
    exit;
} else {
    echo "Insert Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
