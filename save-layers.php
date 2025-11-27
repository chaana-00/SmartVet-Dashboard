<?php
declare(strict_types=1);

/* ========================================================================
   SAFE ERROR LOGGING – Required for DOCX to work
   ======================================================================== */
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/php-error.log');
error_reporting(E_ALL);

// Clear all buffers to prevent DOCX corruption
while (ob_get_level()) ob_end_clean();

/* ========================================================================
   AUTOLOAD
   ======================================================================== */
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config/db-layers.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

function clean($v) { return trim((string)$v); }

/* ========================================================================
   ONLY ALLOW POST REQUESTS
   ======================================================================== */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: add-farm-records.php");
    exit;
}

/* ========================================================================
   INPUTS
   ======================================================================== */
$farm_id           = (int)($_POST['farm_name'] ?? 0);
$reg_number        = clean($_POST['reg_number'] ?? '');
$visit_datetime    = clean($_POST['visit_datetime'] ?? '');
$flock_size        = (int)($_POST['flock_size'] ?? 0);
$age               = (int)($_POST['age'] ?? 0);
$avg_age           = (int)($_POST['avg_age'] ?? 0);
$egg_production    = (int)($_POST['egg_production'] ?? 0);
$egg_percent       = (float)($_POST['egg_percent'] ?? 0);
$feed_intake       = clean($_POST['feed_intake'] ?? '');
$mortality         = (int)($_POST['mortality'] ?? 0);
$mortality_percent = (float)($_POST['mortality_percent'] ?? 0);

$complain       = clean($_POST['complain'] ?? '');
$clinical_signs = clean($_POST['clinical_signs'] ?? '');
$post_changes   = clean($_POST['post_mortem_changes'] ?? '');
$dd             = clean($_POST['dd'] ?? '');
$test_request   = clean($_POST['test_request'] ?? '');
$test_report    = clean($_POST['test_report'] ?? '');
$recommendation = clean($_POST['recommendation'] ?? '');
$treatment      = clean($_POST['treatment'] ?? '');
$follow_ups     = clean($_POST['follow_ups'] ?? '');


/* ========================================================================
   IMAGE UPLOAD
   ======================================================================== */
$uploadedFiles = [];
$folderName = $farm_id . "-" . preg_replace('/[^A-Za-z0-9_-]/', '', $reg_number);
$uploadDir = __DIR__ . "/uploads/postmortem-layers/" . $folderName . "/";

if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

if (!empty($_FILES['post_mortem_images']['name'][0])) {
    foreach ($_FILES['post_mortem_images']['name'] as $i => $name) {

        if ($_FILES['post_mortem_images']['error'][$i] !== UPLOAD_ERR_OK) continue;

        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','gif','heic'])) continue;

        $newName = $farm_id . "_" . $reg_number . "_" . time() . "_" . rand(1000, 9999) . ".$ext";
        $target = $uploadDir . $newName;

        if (move_uploaded_file($_FILES['post_mortem_images']['tmp_name'][$i], $target)) {
            $uploadedFiles[] = "uploads/postmortem-layers/$folderName/$newName";
        }
    }
}

$post_images_json = $uploadedFiles ? json_encode($uploadedFiles) : null;

/* ========================================================================
   INSERT INTO DATABASE
   ======================================================================== */
$sql = "INSERT INTO layers_records (
    farm_id, reg_number, visit_datetime, flock_size, age, avg_age,
    egg_production, egg_percent, feed_intake, mortality, mortality_percent,
    complain, clinical_signs, post_mortem_changes, post_mortem_images,
    dd, test_request, test_report, recommendation, treatment, follow_ups
) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "issiiiidsidssssssssss",
    $farm_id, $reg_number, $visit_datetime, $flock_size, $age, $avg_age,
    $egg_production, $egg_percent, $feed_intake, $mortality, $mortality_percent,
    $complain, $clinical_signs, $post_changes, $post_images_json,
    $dd, $test_request, $test_report, $recommendation, $treatment, $follow_ups
);

$stmt->execute();
$stmt->close();

/* ========================================================================
   FARM DETAILS
   ======================================================================== */
$farm_name = "";
$farm_location = "";

$q = $conn->query("SELECT farm_name, farm_location FROM farms WHERE id=$farm_id LIMIT 1");
if ($q && $q->num_rows) {
    $row = $q->fetch_assoc();
    $farm_name = $row['farm_name'];
    $farm_location = $row['farm_location'];
}

/* ========================================================================
   GENERATE WORD DOCUMENT
   ======================================================================== */
$phpWord = new PhpWord();
$section = $phpWord->addSection();

/* ========================================================================
   HEADER LOGO
   ======================================================================== */
$section->addImage(
    './asset/fcl-logo.png',
    [
        'width' => 300,
        'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER
    ]
);

// ------------------------------------------------------
// Company Address
// ------------------------------------------------------
$section->addText(
    "No. 78, Industrial Zone, Katuwana, Homagama, Sri Lanka.",
    ['name' => 'Arial', 'size' => 10],
    ['alignment' => 'center']
);

$section->addText(
    "Tel: +94 (0) 11 2893922 | Fax: +94 (0) 11 2893680 | Email: info@farmchemie.com",
    ['name' => 'Arial', 'size' => 10],
    ['alignment' => 'center']
);

$section->addText(
    "Web: www.farmchemie.com",
    ['name' => 'Arial', 'size' => 10],
    ['alignment' => 'center']
);

$section->addTextBreak(1);


/* TITLE */
$section->addText("Layer Farm Visit Report", ['bold'=>true, 'size'=>18], ['alignment'=>'center']);
$section->addText("Farm: $farm_name ($farm_location)", [], ['alignment'=>'center']);
$section->addTextBreak(1);

/* INFO TABLE */
$phpWord->addTableStyle('info', ['borderSize'=>0, 'cellMargin'=>80]);
$table = $section->addTable('info');

function addRow($table, $label, $value) {
    $table->addRow();
    $table->addCell(3000)->addText($label, ['bold'=>true]);
    $table->addCell(7000)->addText($value ?: '—');
}

addRow($table, "Registration No:", $reg_number);
addRow($table, "Visit:", $visit_datetime);
addRow($table, "Flock Size:", $flock_size);
addRow($table, "Age:", $age);
addRow($table, "Average Age:", $avg_age);
addRow($table, "Egg Production:", $egg_production);
addRow($table, "Egg %:", $egg_percent);
addRow($table, "Feed Intake:", $feed_intake);
addRow($table, "Mortality:", "$mortality ({$mortality_percent}%)");

$section->addTextBreak(1);

/* TEXT BLOCKS */
function block($sec, $title, $text) {
    $sec->addText($title, ['bold'=>true]);
    $sec->addText($text ?: '—');
    $sec->addTextBreak(1);
}

block($section, "Complain / Visit Purpose", $complain);
block($section, "Clinical Signs", $clinical_signs);
block($section, "Post Mortem Changes", $post_changes);
block($section, "Differential Diagnosis", $dd);

/* IMAGES */
if ($uploadedFiles) {
    $section->addText("Post Mortem Images", ['bold'=>true]);
    $imgTable = $section->addTable();

    $i = 0;
    foreach ($uploadedFiles as $img) {
        if ($i % 3 == 0) $imgTable->addRow();
        $cell = $imgTable->addCell(3000);

        $full = __DIR__ . "/" . $img;
        if (file_exists($full)) {
            $cell->addImage($full, ['width'=>140]);
            $cell->addText(basename($img), ['size'=>8]);
        }
        $i++;
    }
}

block($section, "Test Request", $test_request);
block($section, "Test Report", $test_report);
block($section, "Recommendation", $recommendation);
block($section, "Treatment", $treatment);
block($section, "Follow Ups", $follow_ups);

/* ========================================================================
   SIGNATURE SECTION
   ======================================================================== */
$section->addTextBreak(3);

$signature = __DIR__ . "/assets/signature.png";
if (file_exists($signature)) {
    $section->addImage($signature, [
        'width' => 120,
        'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT
    ]);
}

$section->addText("______________________________", ['size'=>10]);
$section->addText("Dr. XXXXX XXXXX", ['bold'=>true]);
$section->addText("Veterinary Surgeon", ['size'=>10]);

/* ========================================================================
   OUTPUT DOCX DIRECTLY (NO DOWNLOAD PAGE)
   ======================================================================== */
$temp = tempnam(sys_get_temp_dir(), "docx_") . ".docx";
$writer = IOFactory::createWriter($phpWord, "Word2007");
$writer->save($temp);

// Clear output buffer again
if (ob_get_length()) ob_end_clean();

$filename = "Layer-Report-" . $farm_id . "-" . $reg_number . ".docx";

header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Content-Length: " . filesize($temp));

readfile($temp);
unlink($temp);

$conn->close();

exit;
