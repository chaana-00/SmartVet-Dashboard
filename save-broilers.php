<?php
declare(strict_types=1);

/* ============================================================================
   SAFE ERROR LOGGING (DO NOT REMOVE)
   ============================================================================ */
ini_set('display_errors', '0');     // NEVER show errors (breaks DOCX)
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/php-error.log');
error_reporting(E_ALL);

// Clear all output buffers (prevents corrupted DOCX)
while (ob_get_level()) { ob_end_clean(); }

/* ============================================================================
   AUTOLOAD
   ============================================================================ */
require __DIR__ . '/vendor/autoload.php';
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

/* ============================================================================
   DB CONNECTION
   ============================================================================ */
$conn = new mysqli("localhost", "root", "1234", "vetsmartdb");
if ($conn->connect_error) {
    error_log("DB ERROR: " . $conn->connect_error);
    exit("Internal Server Error"); // NO HTML
}

function clean($v) { return trim((string)$v); }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: add-farm-records.php");
    exit;
}

/* ============================================================================
   INPUTS
   ============================================================================ */
$farm_id            = (int)($_POST['farm_name'] ?? 0);
$reg_number         = clean($_POST['reg_number'] ?? '');
$visit_datetime     = clean($_POST['visit_datetime'] ?? '');
$flock_size         = (int)($_POST['flock_size'] ?? 0);
$age                = (int)($_POST['age'] ?? 0);
$avg_weight         = (float)($_POST['avg_weight'] ?? 0);
$weight_gain        = (float)($_POST['weight_gain'] ?? 0);
$feed_intake        = clean($_POST['feed_intake'] ?? '');
$mortality          = (int)($_POST['mortality'] ?? 0);
$mortality_percent  = (float)($_POST['mortality_percent'] ?? 0);

// Text sections
$complain           = clean($_POST['complain'] ?? '');
$clinical_signs     = clean($_POST['clinical_signs'] ?? '');
$dd                 = clean($_POST['dd'] ?? '');
$test_request       = clean($_POST['test_request'] ?? '');
$test_report        = clean($_POST['test_report'] ?? '');
$recommendation     = clean($_POST['recommendation'] ?? '');
$treatment          = clean($_POST['treatment'] ?? '');
$follow_ups         = clean($_POST['follow_ups'] ?? '');

/* ============================================================================
   FILE UPLOAD
   ============================================================================ */
$uploadedFiles = [];
$folderName = $farm_id . "-" . preg_replace('/[^a-zA-Z0-9_-]/', '', $reg_number);
$uploadDir = __DIR__ . "/uploads/postmortem-broilers/" . $folderName . "/";

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if (!empty($_FILES['post_mortem_images']['name'][0])) {
    foreach ($_FILES['post_mortem_images']['name'] as $i => $name) {
        if ($_FILES['post_mortem_images']['error'][$i] !== UPLOAD_ERR_OK) continue;

        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) continue;

        $new = $farm_id . "_" . $reg_number . "_" . time() . "_" . rand(1000, 9999) . "." . $ext;
        $path = $uploadDir . $new;

        if (move_uploaded_file($_FILES['post_mortem_images']['tmp_name'][$i], $path)) {
            $uploadedFiles[] = "uploads/postmortem-broilers/$folderName/$new";
        }
    }
}

$post_images_json = $uploadedFiles ? json_encode($uploadedFiles) : null;

/* ============================================================================
   INSERT INTO DATABASE
   ============================================================================ */
$sql = "INSERT INTO broilers_records (
    farm_id, reg_number, visit_datetime, flock_size, age, avg_weight, weight_gain,
    feed_intake, mortality, mortality_percent, complain, clinical_signs,
    post_mortem_images, dd, test_request, test_report, recommendation, treatment, follow_ups
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log("SQL PREPARE ERROR: " . $conn->error);
    exit("Internal Server Error");
}

$stmt->bind_param(
    "issiiddsidsssssssss",
    $farm_id, $reg_number, $visit_datetime, $flock_size, $age, $avg_weight, $weight_gain,
    $feed_intake, $mortality, $mortality_percent, $complain, $clinical_signs,
    $post_images_json, $dd, $test_request, $test_report, $recommendation, $treatment, $follow_ups
);

if (!$stmt->execute()) {
    error_log("SQL EXECUTE ERROR: " . $stmt->error);
    exit("Internal Server Error");
}
$stmt->close();

/* ============================================================================
   FETCH FARM DETAILS
   ============================================================================ */
$farm_name = "";
$farm_location = "";

$r = $conn->query("SELECT farm_name, farm_location FROM farms WHERE id=$farm_id LIMIT 1");
if ($r && $r->num_rows) {
    $row = $r->fetch_assoc();
    $farm_name = $row['farm_name'];
    $farm_location = $row['farm_location'];
}

/* ============================================================================
   GENERATE WORD DOCUMENT
   ============================================================================ */
$phpWord = new PhpWord();
$section = $phpWord->addSection();

/* ---- TITLE ---- */
$section->addText("Broiler Farm Visit Report", ['bold'=>true, 'size'=>18], ['alignment'=>'center']);
$section->addText("Farm: $farm_name ($farm_location)", ['size'=>12], ['alignment'=>'center']);
$section->addTextBreak(1);

/* ---- INFO TABLE ---- */
$phpWord->addTableStyle('infoTable', ['borderSize'=>0, 'cellMargin'=>80]);
$table = $section->addTable('infoTable');

function rowAdd($t, $l, $v) {
    $t->addRow();
    $t->addCell(3000)->addText($l, ['bold'=>true]);
    $t->addCell(7000)->addText($v ?: '—');
}

rowAdd($table, "Registration No:", $reg_number);
rowAdd($table, "Visit:", $visit_datetime);
rowAdd($table, "Flock Size:", "$flock_size birds");
rowAdd($table, "Age:", "$age days");
rowAdd($table, "Avg Weight:", "$avg_weight kg");
rowAdd($table, "Weight Gain:", "$weight_gain kg");
rowAdd($table, "Feed Intake:", $feed_intake);
rowAdd($table, "Mortality:", "$mortality ({$mortality_percent}%)");

$section->addTextBreak(1);

/* ---- TEXT BLOCKS ---- */
function block($sec, $title, $text) {
    $sec->addText($title, ['bold'=>true]);
    $sec->addText($text ?: '—');
    $sec->addTextBreak(1);
}

block($section, "Complain / Visit Purpose", $complain);
block($section, "Clinical Signs", $clinical_signs);
block($section, "Post Mortem Changes", $dd);

/* ---- IMAGES ---- */
if ($uploadedFiles) {
    $section->addText("Post Mortem Images", ['bold'=>true]);
    $phpWord->addTableStyle('imgTable', ['borderSize'=>0]);
    $imgTable = $section->addTable('imgTable');

    $col = 3;
    $i = 0;
    foreach ($uploadedFiles as $img) {
        if ($i % $col == 0) $imgTable->addRow();
        $cell = $imgTable->addCell(3000);

        $full = __DIR__ . "/" . $img;
        if (file_exists($full)) {
            $cell->addImage($full, ['width'=>150]);
            $cell->addText(basename($img), ['size'=>8]);
        } else {
            $cell->addText("Missing");
        }
        $i++;
    }
}

block($section, "Test Request", $test_request);
block($section, "Test Report", $test_report);
block($section, "Recommendation", $recommendation);
block($section, "Treatment", $treatment);
block($section, "Follow Ups", $follow_ups);

/* ============================================================================
   OUTPUT DOCX FILE
   ============================================================================ */
$temp = tempnam(sys_get_temp_dir(), "docx_") . ".docx";
$writer = IOFactory::createWriter($phpWord, "Word2007");
$writer->save($temp);

if (ob_get_length()) ob_end_clean();

$filename = "Broiler-Report-" . $farm_id . "-" . $reg_number . ".docx";

header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Content-Length: " . filesize($temp));

readfile($temp);
unlink($temp);

$conn->close();

/* ============================================================================
   AUTO REDIRECT AFTER DOWNLOAD
   ============================================================================ */
echo "<html>
<head>
<meta http-equiv='refresh' content='1;url=add-farm-records.php' />
</head>
<body style='font-family:Arial; text-align:center; margin-top:40px;'>
Record saved successfully.<br>Redirecting...
</body>
</html>";

exit;
