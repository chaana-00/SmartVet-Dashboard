<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config/db-prescription.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

// ------------------------------------
// CLEAN OUTPUT
// ------------------------------------
ob_clean();
ob_start();

// ------------------------------------
// GET PRESCRIPTION ID
// ------------------------------------
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    die("Invalid Prescription ID");
}

// ------------------------------------
// GET MAIN PRESCRIPTION DATA
// ------------------------------------
$q = $conn->prepare("
    SELECT p.*, f.farm_name 
    FROM prescriptions p
    LEFT JOIN farms f ON p.farm_id = f.id
    WHERE p.id = ?
    LIMIT 1
");
$q->bind_param("i", $id);
$q->execute();
$pres = $q->get_result()->fetch_assoc();

if (!$pres) {
    die("Prescription Not Found");
}

// ------------------------------------
// GET PRESCRIPTION ITEMS
// ------------------------------------
$items_q = $conn->prepare("
    SELECT * FROM prescription_items
    WHERE prescription_id = ?
");
$items_q->bind_param("i", $id);
$items_q->execute();
$items = $items_q->get_result();

// ------------------------------------
// CREATE WORD DOCUMENT
// ------------------------------------
$phpWord = new PhpWord();

// DOC STYLES
$section = $phpWord->addSection();
$header = $section->addHeader();

// ------------------------------------
// ADD HEADER LOGO + DOCTOR DETAILS
// ------------------------------------
$header->addImage("images/FCL.gif", [
    'width' => 50,
    'height' => 80,
    'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT
]);

$header->addText("VetSmart Prescription Report", [
    'bold' => true,
    'size' => 14
]);

// $header->addText("Dr. John Doe - Veterinary Surgeon", ['size' => 12]);
$header->addTextBreak(1);

// ------------------------------------
// MAIN TITLE
// ------------------------------------
$section->addText("Prescription Details", [
    'size' => 20,
    'bold' => true
]);
$section->addTextBreak(1);

// ------------------------------------
// PRESCRIPTION MAIN INFO
// ------------------------------------
$section->addText("+ Farm Name:  " . $pres['farm_name'], ['size' => 12]);
$section->addText("+ Bird Count: " . $pres['no_of_birds'], ['size' => 12]);
$section->addText("+ Date/Time: " . $pres['date_time'], ['size' => 12]);
$section->addTextBreak(1);

// ------------------------------------
// TABLE OF PRESCRIPTION ITEMS
// ------------------------------------
$tableStyle = [
    'borderSize' => 6,
    'borderColor' => '000000',
    'cellMargin' => 80
];
$phpWord->addTableStyle('PrescriptionTable', $tableStyle);

$table = $section->addTable('PrescriptionTable');

// Headings
$table->addRow();
$table->addCell(2500)->addText("Drug", ['bold' => true]);
$table->addCell(3000)->addText("Indication", ['bold' => true]);
$table->addCell(2000)->addText("Duration", ['bold' => true]);
$table->addCell(2500)->addText("Total Volume", ['bold' => true]);

// Rows
while ($row = $items->fetch_assoc()) {
    $table->addRow();
    $table->addCell(2500)->addText($row['drug']);
    $table->addCell(3000)->addText($row['indication']);
    $table->addCell(2000)->addText($row['duration']);
    $table->addCell(2500)->addText($row['total_volume']);
}

$section->addTextBreak(2);

// ------------------------------------
// FOOTER WITH SIGNATURE
// ------------------------------------

$section->addTextBreak(1);
$section->addText("-----------------------", ['size' => 12]);
$section->addText("Dr. xxxxx", ['size' => 12]);
$section->addText("Veterinary Surgeon", ['size' => 12]);
// $section->addImage("images/Dr Rasanka - Vet.png", [
//     'width' => 300,
//     'height' => 60
// ]);

$section->addTextBreak(3);
$section->addText("*** Thank you for choosing VetSmart Service! ***", ['size' => 10]);
$footer = $section->addFooter();
$footer->addText("© 2025 All Rights Reserved! | Developed by IT Department | www.farmchemie.com | Contact: +94 70 228 5959", ['size' => 8]);

// ------------------------------------
// OUTPUT FILE
// ------------------------------------
$filename = "Prescription_" . $id . ".docx";
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
