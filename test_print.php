<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include FPDF library
require('fpdf.php');

// Create a simple PDF
try {
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(40, 10, 'Test PDF');
    $pdf->Output('I', 'test.pdf');
} catch (Exception $e) {
    // Display any errors that occur during PDF generation
    echo '<h1>Error creating PDF</h1>';
    echo '<p>' . $e->getMessage() . '</p>';
    echo '<p>Error on line ' . $e->getLine() . ' in file ' . $e->getFile() . '</p>';
    echo '<pre>' . $e->getTraceAsString() . '</pre>';
}
?> 