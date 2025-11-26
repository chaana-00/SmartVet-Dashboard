<?php
require __DIR__ . '/config/db-layers.php';

function clean($v) { return trim($v); }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: add-farm-records.php");
    exit;
}

// -------- INPUTS --------
$farm_id           = (int)($_POST['farm_name'] ?? 0);
$reg_number        = clean($_POST['reg_number'] ?? '');
$visit_datetime    = $_POST['visit_datetime'] ?? null;
$flock_size        = (int)($_POST['flock_size'] ?? 0);
$age               = (int)($_POST['age'] ?? 0);
$avg_age           = (int)($_POST['avg_age'] ?? 0);
$egg_production    = (int)($_POST['egg_production'] ?? 0);
$egg_percent       = (float)($_POST['egg_percent'] ?? 0);
$feed_intake       = clean($_POST['feed_intake'] ?? '');
$mortality         = (int)($_POST['mortality'] ?? 0);
$mortality_percent = (float)($_POST['mortality_percent'] ?? 0);
$complain          = clean($_POST['complain'] ?? '');
$clinical_signs    = clean($_POST['clinical_signs'] ?? '');
$post_changes      = clean($_POST['post_mortem_changes'] ?? '');
$dd                = clean($_POST['dd'] ?? '');
$test_request      = clean($_POST['test_request'] ?? '');
$test_report       = clean($_POST['test_report'] ?? '');
$recommendation    = clean($_POST['recommendation'] ?? '');
$treatment         = clean($_POST['treatment'] ?? '');
$follow_ups        = clean($_POST['follow_ups'] ?? '');

$errors = [];
if ($farm_id <= 0) $errors[] = "Select a farm.";
if ($reg_number == "") $errors[] = "Registration number required.";

if ($errors) {
    echo "<h3>Errors:</h3><ul>";
    foreach ($errors as $e) echo "<li>$e</li>";
    echo "</ul><a href='add-farm-records.php'>Go Back</a>";
    exit;
}

// -------- FILE UPLOAD --------
$uploaded = [];
$dir = __DIR__ . "/uploads/postmortem-layers/";

if (!is_dir($dir)) mkdir($dir, 0777, true);

if (!empty($_FILES['post_mortem_images']['name'][0])) {
    foreach ($_FILES['post_mortem_images']['name'] as $i => $name) {
        if ($_FILES['post_mortem_images']['error'][$i] !== UPLOAD_ERR_OK) continue;

        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','gif','heic'])) continue;

        $newName = "layer_" . time() . "_" . rand(1000, 9999) . "." . $ext;
        $path = $dir . $newName;

        if (move_uploaded_file($_FILES['post_mortem_images']['tmp_name'][$i], $path)) {
            $uploaded[] = "uploads/postmortem-layers/" . $newName;
        }
    }
}

$images_json = $uploaded ? json_encode($uploaded) : null;

// -------- SQL INSERT (21 fields) --------
$sql = "INSERT INTO layers_records (
    farm_id, reg_number, visit_datetime, flock_size, age, avg_age, egg_production, egg_percent,
    feed_intake, mortality, mortality_percent, complain, clinical_signs, post_mortem_changes,
    post_mortem_images, dd, test_request, test_report, recommendation, treatment, follow_ups
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

// 21 types, 21 variables
$stmt->bind_param(
    "issiiiddidsssssssssss",
    $farm_id,
    $reg_number,
    $visit_datetime,
    $flock_size,
    $age,
    $avg_age,
    $egg_production,
    $egg_percent,
    $feed_intake,
    $mortality,
    $mortality_percent,
    $complain,
    $clinical_signs,
    $post_changes,
    $images_json,
    $dd,
    $test_request,
    $test_report,
    $recommendation,
    $treatment,
    $follow_ups
);

if ($stmt->execute()) {
    header("Location: add-farm-records.php?status=success");
    exit;
} else {
    echo "Insert Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
