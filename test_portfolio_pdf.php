<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Http\Controllers\LoanReportController;
use Illuminate\Http\Request;
use App\Models\Loan;
use App\Models\Branch;
use App\Models\Company;

echo "=== Testing PDF Generation and Save ===\n";

// Create a test request
$request = new Request([
    'as_of_date' => now()->format('Y-m-d'),
    'export_type' => 'pdf'
]);

// Create controller instance
$controller = new LoanReportController();

try {
    // Test the portfolio report method
    $response = $controller->portfolioReport($request);
    
    echo "PDF generation successful!\n";
    
    // Get the actual content
    $content = $response->getContent();
    echo "Content length: " . strlen($content) . " bytes\n";
    
    // Save PDF to file for testing
    $filename = 'test_portfolio_' . date('Y_m_d_His') . '.pdf';
    file_put_contents($filename, $content);
    echo "PDF saved as: " . $filename . "\n";
    echo "File size: " . filesize($filename) . " bytes\n";
    
    // Test if PDF is valid by checking header
    $fileHandle = fopen($filename, 'rb');
    $header = fread($fileHandle, 20);
    fclose($fileHandle);
    
    if (strpos($header, '%PDF') === 0) {
        echo "PDF header is valid\n";
    } else {
        echo "PDF header is invalid\n";
        echo "Header: " . bin2hex($header) . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
