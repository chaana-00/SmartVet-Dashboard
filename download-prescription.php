<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config/db-prescription.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

/* STOP ALL OUTPUT */
ob_clean();
ob_start();

/* GET DATA */
$id = intval($_GET['id']);

$q = $conn->prepare("SELECT * FROM prescriptions WHERE id=? LIMIT 1");
// $q = $conn->prepare("SELECT * FROM prescription_items WHERE id=? LIMIT 1");
$q->bind_param("i", $id);
$q->execute();
$res = $q->get_result();

if ($res->num_rows === 0) {
    die("Invalid ID");
}

$data = $res->fetch_assoc();

/* CREATE DOCX */
$phpWord = new PhpWord();
$section = $phpWord->addSection();

$section->addText("Prescription", ['size' => 20, 'bold' => true]);
$section->addTextBreak(1);

$section->addText("Farm Name: " . $data['farm_id']);
$section->addText("Bird Count: " . $data['no_of_birds']);
$section->addText("Date: " . $data['date']);
$section->addTextBreak(1);

$rows = json_decode($data['rows'], true);


// ------------------------------------
// TABLE
// ------------------------------------
$table = $section->addTable([
    'borderSize'  => 6,
    'borderColor' => '000000'
]);

$table->addRow();
$table->addCell(1500)->addText("Drug", ['bold' => true]);
$table->addCell(2500)->addText("Indication", ['bold' => true]);
$table->addCell(2500)->addText("Duration", ['bold' => true]);
$table->addCell(2500)->addText("Total Volume", ['bold' => true]);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {

        $table->addRow();
        $table->addCell(1500)->addText($row['drug']);
        $table->addCell(2500)->addText($row['indication']);
        $table->addCell(2500)->addText($row['duration']);
        $table->addCell(2500)->addText($row['total_volume']);
    }
} else {
    $section->addText("No farm records found.");
}

$conn->close();

// Save file
$filename = "Prescription.docx";
$tempFile = tempnam(sys_get_temp_dir(), 'docx');

$writer = IOFactory::createWriter($phpWord, 'Word2007');
$writer->save($tempFile);

// Send to browser
header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Content-Length: " . filesize($tempFile));

readfile($tempFile);
unlink($tempFile);
exit;
exit;
