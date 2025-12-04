<?php
declare(strict_types=1);

/* ========================================================================
   SAFE ERROR LOGGING
   ======================================================================== */
ini_set('display_errors', '0');     
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/php-error.log');
error_reporting(E_ALL);

/* Clear output buffers → prevents DOCX corruption */
while (ob_get_level()) { ob_end_clean(); }

/* ========================================================================
   AUTOLOAD
   ======================================================================== */
require __DIR__ . '/vendor/autoload.php';
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

/* ========================================================================
   DB CONNECTION
   ======================================================================== */
$conn = new mysqli("localhost", "root", "1234", "vetsmartdb");
if ($conn->connect_error) {
    error_log("DB ERROR: " . $conn->connect_error);
    exit("Internal Server Error");
}

function clean($v){ return trim((string)$v); }

/* ========================================================================
   ONLY POST
   ======================================================================== */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: add-farm-records.php");
    exit;
}

/* ========================================================================
   INPUTS
   ======================================================================== */
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

$complain       = clean($_POST['complain'] ?? '');
$clinical_signs = clean($_POST['clinical_signs'] ?? '');
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
$folderName = $farm_id . "-" . preg_replace('/[^a-zA-Z0-9_-]/', '', $reg_number);
$uploadDir = __DIR__ . "/uploads/postmortem-broilers/" . $folderName . "/";

if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

if (!empty($_FILES['post_mortem_images']['name'][0])) {
    foreach ($_FILES['post_mortem_images']['name'] as $i => $name) {
        if ($_FILES['post_mortem_images']['error'][$i] !== UPLOAD_ERR_OK) continue;

        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'heic'])) continue;

        $newFile = $farm_id . "_" . $reg_number . "_" . time() . "_" . rand(1000,9999) . ".$ext";
        $full = $uploadDir . $newFile;

        if (move_uploaded_file($_FILES['post_mortem_images']['tmp_name'][$i], $full)) {
            $uploadedFiles[] = "uploads/postmortem-broilers/$folderName/$newFile";
        }
    }
}

$post_images_json = $uploadedFiles ? json_encode($uploadedFiles) : null;

/* ========================================================================
   INSERT DATABASE
   ======================================================================== */
$sql = "INSERT INTO broilers_records (
    farm_id, reg_number, visit_datetime, flock_size, age, avg_weight, weight_gain,
    feed_intake, mortality, mortality_percent, complain, clinical_signs,
    post_mortem_images, dd, test_request, test_report, recommendation, treatment, follow_ups
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "issiiddsidsssssssss",
    $farm_id, $reg_number, $visit_datetime, $flock_size, $age, $avg_weight, $weight_gain,
    $feed_intake, $mortality, $mortality_percent, $complain, $clinical_signs,
    $post_images_json, $dd, $test_request, $test_report, $recommendation, $treatment, $follow_ups
);
$stmt->execute();
$stmt->close();

/* ========================================================================
   FARM DETAILS
   ======================================================================== */
$farm_name = ""; $farm_location = "";

$q = $conn->query("SELECT farm_name, farm_location FROM farms WHERE id=$farm_id");
if ($q && $q->num_rows) {
    $row = $q->fetch_assoc();
    $farm_name = $row['farm_name'];
    $farm_location = $row['farm_location'];
}

/* ========================================================================
   WORD DOCUMENT
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
    ['name' => 'Arial', 'size' => 11],
    ['alignment' => 'center']
);

$section->addText(
    "Tel: +94 (0) 11 2893922 | Fax: +94 (0) 11 2893680 | Email: info@farmchemie.com",
    ['name' => 'Arial', 'size' => 11],
    ['alignment' => 'center']
);

$section->addText(
    "Web: www.farmchemie.com",
    ['name' => 'Arial', 'size' => 11],
    ['alignment' => 'center']
);

$section->addTextBreak(1);

/* ========================================================================
   TITLE
   ======================================================================== */
$section->addText("Broiler Farm Visit Report", ['bold'=>true, 'size'=>18], ['alignment'=>'center']);
$section->addText("Farm: $farm_name ($farm_location)", ['size'=>12], ['alignment'=>'center']);
$section->addTextBreak(1);

/* ========================================================================
   TABLE
   ======================================================================== */
$phpWord->addTableStyle('infoTable', ['borderSize'=>0, 'cellMargin'=>80]);
$table = $section->addTable('infoTable');

function rowAdd($tbl, $label, $value){
    $tbl->addRow();
    $tbl->addCell(3000)->addText($label, ['bold'=>true]);
    $tbl->addCell(7000)->addText($value ?: '—');
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

/* ========================================================================
   TEXT BLOCKS
   ======================================================================== */
function block($sec, $t, $v){
    $sec->addText($t, ['bold'=>true]);
    $sec->addText($v ?: '—');
    $sec->addTextBreak(1);
}

block($section, "Complain / Visit Purpose", $complain);
block($section, "Clinical Signs", $clinical_signs);
block($section, "Post Mortem Changes", $dd);

/* ========================================================================
   POST MORTEM IMAGES
   ======================================================================== */
if ($uploadedFiles) {
    $section->addText("Post Mortem Images", ['bold'=>true]);
    $imgTable = $section->addTable();

    $col = 3;
    $i = 0;

    foreach ($uploadedFiles as $img) {
        if ($i % $col === 0) $imgTable->addRow();
        $cell = $imgTable->addCell(3000);

        $fullPath = __DIR__ . "/" . $img;
        if (file_exists($fullPath)) {
            $cell->addImage($fullPath, ['width'=>150]);
        }
        $cell->addText(basename($img), ['size'=>8]);
        $i++;
    }
}

/* Remaining text blocks */
block($section, "Test Request", $test_request);
block($section, "Test Report", $test_report);
block($section, "Recommendation", $recommendation);
block($section, "Treatment", $treatment);
block($section, "Follow Ups", $follow_ups);

/* ========================================================================
   SIGNATURE AREA
   ======================================================================== */
$section->addTextBreak(1);

$signaturePath = __DIR__ . "/assets/signature.png";

if (file_exists($signaturePath)) {
    $section->addImage($signaturePath, [
        'width' => 120,
        'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT
    ]);
}

$section->addText("______________________________", ['size'=>10]);
$section->addText("Dr. XXXXX XXXXX", ['bold'=>true]);
$section->addText("Veterinary Surgeon", ['size'=>8]);


$section->addTextBreak(1);
$section->addText("*** Thank you for choosing VetSmart Service! ***", ['size' => 10]);
$footer = $section->addFooter();
$footer->addText("© 2025 All Rights Reserved! | Developed by IT Department | www.farmchemie.com | Contact: +94 70 228 5959", ['size' => 8]);

/* ========================================================================
   OUTPUT WORD FILE
   ======================================================================== */
$temp = tempnam(sys_get_temp_dir(), "docx_") . ".docx";
$writer = IOFactory::createWriter($phpWord, "Word2007");
$writer->save($temp);

if (ob_get_length()) ob_end_clean();

$filename = "Broiler-Report-$farm_id-$reg_number.docx";

header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Content-Length: " . filesize($temp));

readfile($temp);
unlink($temp);
$conn->close();

exit;
