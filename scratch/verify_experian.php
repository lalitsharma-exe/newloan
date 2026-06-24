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
    $targetDate = '2025-09-25';
    echo "No disbursed loans found in database, using fallback date: $targetDate\n\n";
}

$service = new CompuscanService();

echo "Running Verification for Experian/Compuscan layout changes...\n\n";

function isValidLuhn(string $num): bool
{
    if (!preg_match('/^[0-9]{13}$/', $num)) {
        return false;
    }
    $sum = 0;
    for ($i = 12; $i >= 0; $i--) {
        $val = (int)$num[$i];
        if ($i % 2 === 1) {
            $val *= 2;
            if ($val > 9) {
                $val -= 9;
            }
        }
        $sum += $val;
    }
    return ($sum % 10) === 0;
}

function verifyRecordFields(string $row, string $typeDesc): bool
{
    // Positions (0-based indices in 718-character data record)
    $recType = substr($row, 0, 1);
    $lsoId = trim(substr($row, 1, 13));
    $loanReason = substr($row, 363, 2);
    $currBalance = trim(substr($row, 402, 9));
    $currBalInd = substr($row, 411, 1);
    $statusCode = substr($row, 432, 2);

    echo "  Parsed fields for first $typeDesc data record:\n";
    echo "    Record Type:             '$recType'\n";
    echo "    LSO ID (trimmed):        '$lsoId'\n";
    echo "    Loan Reason Code:        '$loanReason'\n";
    echo "    Current Balance:         '$currBalance'\n";
    echo "    Current Balance Ind:     '$currBalInd'\n";
    echo "    Status Code:             '$statusCode'\n";

    // Validations
    $errors = [];
    
    // 1. Check ID format: 12-digit Lesotho or 13-digit Luhn SA ID
    $cleanId = preg_replace('/[^0-9]/', '', $lsoId);
    $isValidId = (strlen($cleanId) === 12) || (strlen($cleanId) === 13 && isValidLuhn($cleanId));
    if (!$isValidId) {
        $errors[] = "Invalid LSO ID format or Luhn check failed ('$lsoId').";
    }

    // 2. Loan Reason Code must be '00'
    if ($loanReason !== '00') {
        $errors[] = "Loan Reason Code must be '00' (got '$loanReason').";
    }

    // 3. Current Balance Indicator must be 'D'
    if ($currBalInd !== 'D') {
        $errors[] = "Current Balance Indicator must be 'D' (got '$currBalInd').";
    }

    // 4. If balance is 0, status code must be 'C ' (Closed) or 'W ' (Written off)
    if ((int)$currBalance === 0) {
        if ($statusCode !== 'C ' && $statusCode !== 'W ') {
            $errors[] = "Status Code must be 'C ' or 'W ' where balance is 0 (got '$statusCode').";
        }
    } else {
        if ($statusCode !== '  ') {
            $errors[] = "Status Code must be '  ' (Open) where balance > 0 (got '$statusCode').";
        }
    }

    if (empty($errors)) {
        echo "    Field Validation: PASS\n";
        return true;
    } else {
        echo "    Field Validation: FAIL\n";
        foreach ($errors as $err) {
            echo "      - $err\n";
        }
        return false;
    }
}

// 1. Generate daily file
$dailyContent = $service->buildDailyFile($targetDate);
$dailyLines = explode("\r\n", rtrim($dailyContent, "\r\n"));

echo "=== DAILY FILE VERIFICATION ===\n";
echo "Total lines: " . count($dailyLines) . "\n";
$dailyPass = false;
if (count($dailyLines) > 0) {
    $firstLine = $dailyLines[0];
    $isDataRecord = in_array(substr($firstLine, 0, 1), ['D', 'R', 'C']);
    
    $lastLine = $dailyLines[count($dailyLines) - 1];
    $expectedTrailerValue = str_pad(count($dailyLines), 10, '0', STR_PAD_LEFT);
    $actualTrailerValue = substr($lastLine, 1);
    
    $fieldsPass = $isDataRecord ? verifyRecordFields($firstLine, "Daily") : true;

    if ($actualTrailerValue === $expectedTrailerValue && strlen($firstLine) === 718 && $isDataRecord && $fieldsPass) {
        $dailyPass = true;
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
$monthlyPass = false;
if (count($monthlyLines) > 0) {
    $headerLine = $monthlyLines[0];
    $version = substr($headerLine, 19, 2);
    
    $lastLine = $monthlyLines[count($monthlyLines) - 1];
    $expectedTrailerValue = str_pad(count($monthlyLines), 10, '0', STR_PAD_LEFT);
    $actualTrailerValue = substr($lastLine, 1);
    
    $firstDataLine = $monthlyLines[1] ?? '';
    
    $hasCorrectHeader = (substr($headerLine, 0, 1) === 'H' && strlen($headerLine) === 700 && $version === '06');
    $hasCorrectTrailer = ($actualTrailerValue === $expectedTrailerValue);
    $hasCorrectDataRecord = (strlen($firstDataLine) === 700 && substr($firstDataLine, 0, 1) === 'D');
    
    $fieldsPass = $hasCorrectDataRecord ? verifyRecordFields($firstDataLine, "Monthly") : false;
    
    echo "\n  --- Detailed Monthly Records Breakdown ---\n";
    for ($i = 1; $i < count($monthlyLines) - 1; $i++) {
        $row = $monthlyLines[$i];
        $lsoId = trim(substr($row, 1, 13));
        $loanReason = substr($row, 363, 2);
        $currBalance = trim(substr($row, 402, 9));
        $currBalInd = substr($row, 411, 1);
        $statusCode = substr($row, 432, 2);
        echo sprintf("    Record %d: ID=%s, Reason=%s, Balance=%s, Ind=%s, Status=%s\n",
            $i, $lsoId, $loanReason, $currBalance, $currBalInd, $statusCode
        );
    }
    echo "  ------------------------------------------\n\n";

    if ($hasCorrectHeader && $hasCorrectTrailer && $hasCorrectDataRecord && $fieldsPass) {
        $monthlyPass = true;
        echo "Monthly verification: PASS\n";
    } else {
        echo "Monthly verification: FAIL\n";
    }
}
