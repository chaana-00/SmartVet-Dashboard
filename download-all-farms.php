<?php
// Load PhpWord (manual installation)
require_once __DIR__ . '/vendor/PhpOffice/PhpWord/bootstrap.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;


// Database connection
$servername = "localhost"; 
$username = "root"; 
$password = "1234"; 
$dbname = "vetsmartdb"; 

$conn = new mysqli($servername, $username, $password, $dbname);

// Check DB
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Fetch all farm records
$sql = "SELECT * FROM farms ORDER BY id DESC";
$result = $conn->query($sql);

// Create Word document
$phpword = new PhpWord();
$section = $phpword->addSection();

$section->addTitle("All Farm Registration Records", 1);
$section->addTextBreak(1);

// Create table
$table = $section->addTable([
    'borderSize' => 6,
    'borderColor' => '000000',
]);

// Add table header
$table->addRow();
$table->addCell(1500)->addText("Farm ID");
$table->addCell(2500)->addText("Owner Name");
$table->addCell(2500)->addText("Farm Name");
$table->addCell(2500)->addText("Location");
$table->addCell(2000)->addText("Mobile");
$table->addCell(2500)->addText("Registered Date");

// Add data rows
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $table->addRow();
        $table->addCell(1500)->addText($row['farm_id']);
        $table->addCell(2500)->addText($row['owner_name']);
        $table->addCell(2500)->addText($row['farm_name']);
        $table->addCell(2500)->addText($row['location']);
        $table->addCell(2000)->addText($row['mobile']);
        $table->addCell(2500)->addText($row['reg_date']);
    }
} else {
    $section->addText("No farm records found.");
}

$conn->close();

// Save file to temporary path
$filename = "All-Farm-Records.docx";
$filepath = tempnam(sys_get_temp_dir(), 'docx');

$writer = IOFactory::createWriter($phpWord, 'Word2007');
$writer->save($filepath);

// Output to browser for download
header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Content-Length: " . filesize($filepath));

readfile($filepath);
unlink($filepath);  // delete temp file
exit;
?>
