<?php
require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

// DB connection
$servername = "localhost";
$username   = "root";
$password   = "1234";
$dbname     = "vetsmartdb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

// Fetch farms
$sql = "SELECT * FROM farms ORDER BY id DESC";
$result = $conn->query($sql);

// Create Word document
$phpWord = new PhpWord();
$section = $phpWord->addSection();

// ------------------------------------
// COMPANY LOGO
// ------------------------------------
$section->addImage(
    './asset/fcl-logo.png',                  // <-- Set your correct path
    [
        'width' => 400,
        'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER
    ]
);

// ------------------------------------
// COMPANY ADDRESS
// ------------------------------------
$section->addText(
    "No. 78, Industrial Zone, Katuwana, Homagama, Sri Lanka.",
    ['name' => 'Arial', 'size' => 11],
    ['alignment' => 'center']
);

$section->addText(
    "Tel: +94 (0) 11 2893922 | Fax: +94 (0) 11 2893680\n | Email: info@farmchemie.com",
    ['name' => 'Arial', 'size' => 11],
    ['alignment' => 'center']
);

$section->addText(
    "Web: www.farmchemie.com",
    ['name' => 'Arial', 'size' => 11],
    ['alignment' => 'center']
);

$section->addTextBreak(1);

// ------------------------------------
// HEADING (BOLD)
// ------------------------------------
$section->addText(
    "All Farm Registration Records",
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

// ------------------------------------
// TABLE
// ------------------------------------
$table = $section->addTable([
    'borderSize'  => 6,
    'borderColor' => '000000'
]);

$table->addRow();
$table->addCell(1500)->addText("Farm ID", ['bold' => true]);
$table->addCell(2500)->addText("Owner Name", ['bold' => true]);
$table->addCell(2500)->addText("Farm Name", ['bold' => true]);
$table->addCell(2500)->addText("Address", ['bold' => true]);
$table->addCell(2000)->addText("Mobile", ['bold' => true]);
$table->addCell(2500)->addText("Registered Date", ['bold' => true]);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {

        $table->addRow();
        $table->addCell(1500)->addText($row['farm_id']);
        $table->addCell(2500)->addText($row['owner_name']);
        $table->addCell(2500)->addText($row['farm_name']);
        $table->addCell(2500)->addText($row['farm_location']);
        $table->addCell(2000)->addText($row['owner_contact1']);
        $table->addCell(2500)->addText($row['created_at']);
    }
} else {
    $section->addText("No farm records found.");
}

$conn->close();

// Fotter
$section->addTextBreak(3);
$section->addText("*** Thank you for choosing VetSmart Service! ***", ['size' => 10]);
$footer = $section->addFooter();
$footer->addText("© 2025 All Rights Reserved! | Developed by IT Department | www.farmchemie.com | Contact: +94 70 228 5959", ['size' => 8]);


// Save file
$filename = "All-Farm-Records.docx";
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
?>
