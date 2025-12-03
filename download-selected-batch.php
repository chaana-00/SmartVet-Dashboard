<?php
require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

// -------------------------------------------
// Fetch filter values (from GET)
// -------------------------------------------
$farm_id       = isset($_GET['farm_id']) ? $_GET['farm_id'] : '';
$farm_location = isset($_GET['farm_location']) ? $_GET['farm_location'] : '';

// -------------------------------------------
// DB connection
// -------------------------------------------
$conn = new mysqli("localhost", "root", "1234", "vetsmartdb");

if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

// -------------------------------------------
// Build SQL query
// -------------------------------------------
$sql = "
    SELECT 
        b.*,
        f.farm_name,
        f.farm_location
    FROM batch_details b
    LEFT JOIN farms f ON b.farm_id = f.id
    WHERE 1=1
";

if ($farm_id !== "") {
    $sql .= " AND b.farm_id = '$farm_id'";
}

if ($farm_location !== "") {
    $sql .= " AND f.farm_location = '$farm_location'";
}

$sql .= " ORDER BY f.farm_name DESC, b.farm_id DESC";

$result = $conn->query($sql);

// -------------------------------------------
// Create Word Document
// -------------------------------------------
$phpWord = new PhpWord();
$section = $phpWord->addSection();

// ------------------------------------------------------
// Company Logo
// ------------------------------------------------------
$section->addImage(
    './asset/fcl-logo.png',
    [
        'width' => 400,
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

// ------------------------------------------------------
// Title
// ------------------------------------------------------
$section->addText(
    "Batch Details Report",
    ['name' => 'Arial', 'size' => 16, 'bold' => true],
    ['alignment' => 'center']
);

$section->addTextBreak(1);

// System note
$section->addText(
    "This record is system generated!\nCopyright © Farmchemie Private Limited.",
    ['italic' => true, 'size' => 10],
    ['alignment' => 'center']
);

$section->addTextBreak(1);

// ------------------------------------------------------
// Table Setup
// ------------------------------------------------------
$table = $section->addTable([
    'borderSize'  => 6,
    'borderColor' => '000000'
]);

// Header Row
$table->addRow();
$table->addCell(2500)->addText("Farm Name", ['bold' => true]);
$table->addCell(2500)->addText("Farm Location", ['bold' => true]);
$table->addCell(1500)->addText("Cage No", ['bold' => true]);
$table->addCell(2000)->addText("Breed", ['bold' => true]);
$table->addCell(2000)->addText("Hatchery", ['bold' => true]);
$table->addCell(1500)->addText("Total Input", ['bold' => true]);
$table->addCell(3000)->addText("Vaccination Details", ['bold' => true]);
$table->addCell(3000)->addText("Treatment History", ['bold' => true]);

// Data Rows
if ($result->num_rows > 0) {

    while ($row = $result->fetch_assoc()) {
        $table->addRow();

        $table->addCell(2500)->addText($row['farm_name']);
        $table->addCell(2500)->addText($row['farm_location']);
        $table->addCell(1500)->addText($row['cage_no']);
        $table->addCell(2000)->addText($row['breed']);
        $table->addCell(2000)->addText($row['hatchery']);
        $table->addCell(1500)->addText($row['total_input']);
        $table->addCell(3000)->addText($row['vaccination_details']);
        $table->addCell(3000)->addText($row['treatment_history']);
    }

} else {
    $section->addText("No batch details found.", ['bold' => true]);
}

$conn->close();

// Fotter
$section->addTextBreak(3);
$section->addText("*** Thank you for choosing VetSmart Service! ***", ['size' => 10]);
$footer = $section->addFooter();
$footer->addText("© 2025 All Rights Reserved! | Developed by IT Department | www.farmchemie.com | Contact: +94 70 228 5959", ['size' => 8]);

// ------------------------------------------------------
// Output the File
// ------------------------------------------------------
$filename = "Selected-Batch-Details.docx";
$tempFile = tempnam(sys_get_temp_dir(), 'docx');

$writer = IOFactory::createWriter($phpWord, 'Word2007');
$writer->save($tempFile);

header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Content-Length: " . filesize($tempFile));

readfile($tempFile);
unlink($tempFile);
exit;
?>
