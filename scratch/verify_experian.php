<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Loan;
use App\Services\CompuscanService;

// Find a date with disbursements
$loan = Loan::whereNotNull('disbursement_date')->first();
if ($loan) {
    $targetDate = $loan->disbursement_date->format('Y-m-d');
    echo "Using target date with disbursements: $targetDate\n\n";
} else {
    $targetDate = '2026-04-27';
    echo "No disbursed loans found in database, using fallback date: $targetDate\n\n";
}

$service = new CompuscanService();

echo "Running Verification for Experian/Compuscan layout changes...\n\n";

// 1. Generate daily file
$dailyContent = $service->buildDailyFile($targetDate);
$dailyLines = explode("\r\n", rtrim($dailyContent, "\r\n"));

echo "=== DAILY FILE VERIFICATION ===\n";
echo "Total lines: " . count($dailyLines) . "\n";
if (count($dailyLines) > 0) {
    $firstLine = $dailyLines[0];
    echo "First line starts with: '" . substr($firstLine, 0, 1) . "' (Expected 'D' or 'R' or 'C')\n";
    echo "First line length: " . strlen($firstLine) . " chars (Expected 718)\n";
    echo "First line ends with: '" . substr($firstLine, -20) . "'\n";
    
    $lastLine = $dailyLines[count($dailyLines) - 1];
    echo "Last line starts with: '" . substr($lastLine, 0, 1) . "' (Expected 'T')\n";
    echo "Last line content: '$lastLine'\n";
    
    $expectedTrailerValue = str_pad(count($dailyLines), 10, '0', STR_PAD_LEFT);
    $actualTrailerValue = substr($lastLine, 1);
    echo "Trailer count: '$actualTrailerValue' (Expected '$expectedTrailerValue')\n";
    
    $isDataRecord = in_array(substr($firstLine, 0, 1), ['D', 'R', 'C']);
    if ($actualTrailerValue === $expectedTrailerValue && strlen($firstLine) === 718 && $isDataRecord) {
        echo "Daily verification: PASS\n";
    } else {
        echo "Daily verification: FAIL\n";
    }
}
echo "\n";

// 2. Generate monthly file
$monthlyContent = $service->buildMonthlyFile($targetDate);
$monthlyLines = explode("\r\n", rtrim($monthlyContent, "\r\n"));

echo "=== MONTHLY FILE VERIFICATION ===\n";
echo "Total lines: " . count($monthlyLines) . "\n";
if (count($monthlyLines) > 0) {
    $headerLine = $monthlyLines[0];
    echo "Header line starts with: '" . substr($headerLine, 0, 1) . "' (Expected 'H')\n";
    echo "Header line length: " . strlen($headerLine) . " chars (Expected 700)\n";
    
    // Check version number '06' between month end date and creation date
    $srn = substr($headerLine, 1, 10);
    $monthEndDate = substr($headerLine, 11, 8);
    $version = substr($headerLine, 19, 2);
    $creationDate = substr($headerLine, 21, 8);
    
    echo "Parsed SRN: '$srn' (Expected right-aligned, e.g. '    LSO250')\n";
    echo "Parsed Month End Date: '$monthEndDate'\n";
    echo "Parsed Version: '$version' (Expected '06')\n";
    echo "Parsed Creation Date: '$creationDate'\n";
    
    $lastLine = $monthlyLines[count($monthlyLines) - 1];
    echo "Last line starts with: '" . substr($lastLine, 0, 1) . "' (Expected 'T')\n";
    echo "Last line content: '$lastLine'\n";
    
    $expectedTrailerValue = str_pad(count($monthlyLines), 10, '0', STR_PAD_LEFT);
    $actualTrailerValue = substr($lastLine, 1);
    echo "Trailer count: '$actualTrailerValue' (Expected '$expectedTrailerValue')\n";
    
    $firstDataLine = $monthlyLines[1] ?? '';
    echo "First data line length: " . strlen($firstDataLine) . " chars (Expected 718)\n";
    echo "First data line ends with: '" . substr($firstDataLine, -20) . "'\n";

    $hasCorrectHeader = (substr($headerLine, 0, 1) === 'H' && strlen($headerLine) === 700 && $version === '06');
    $hasCorrectTrailer = ($actualTrailerValue === $expectedTrailerValue);
    $hasCorrectDataRecord = (strlen($firstDataLine) === 718 && substr($firstDataLine, 0, 1) === 'D');
    
    if ($hasCorrectHeader && $hasCorrectTrailer && $hasCorrectDataRecord) {
        echo "Monthly verification: PASS\n";
    } else {
        echo "Monthly verification: FAIL\n";
    }
}
