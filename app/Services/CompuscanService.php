<?php

namespace App\Services;

use App\Models\Loan;
use Carbon\Carbon;

class CompuscanService
{
    private string $srn;
    private string $tradingName;

    public function __construct()
    {
        $this->srn = env('COMPUSCAN_SRN', 'LSO250');
        $this->tradingName = env('COMPUSCAN_TRADING_NAME', 'MY LOAN PLATFORM');
    }

    /**
     * Generate the monthly snapshot file (D Frequency)
     */
    public function buildMonthlyFile(string $monthEndDate): string
    {
        $date = Carbon::parse($monthEndDate);
        
        $content = $this->generateHeader($date, 'L702', 'M');
        
        // Fetch active loans as of this date, plus loans closed in this month
        // For simplicity, fetch loans that are active or paid_off/closed this month
        $loans = Loan::with(['user', 'application'])->get();
        
        $count = 0;
        foreach ($loans as $loan) {
            /** @var Loan $loan */
            // Status logic: if paid off before this month, skip
            if ($loan->status === 'paid_off' && $loan->closed_at && $loan->closed_at->format('Y-m') < $date->format('Y-m')) {
                continue;
            }
            if ($loan->status === 'pending' || $loan->status === 'approved' || $loan->status === 'rejected') {
                continue; // Only disbursed or later
            }
            
            $content .= $this->generateDataRecord($loan, 'D', $date);
            $count++;
        }
        
        $content .= $this->generateTrailer($count);
        
        return $content;
    }

    /**
     * Generate the daily file (D Frequency)
     */
    public function buildDailyFile(string $targetDate): string
    {
        $date = Carbon::parse($targetDate);
        
        $content = $this->generateHeader($date, 'L702', 'D');
        
        // Fetch loans disbursed on this day (Registrations)
        $registrations = Loan::with(['user', 'application'])
            ->whereDate('disbursement_date', $date->toDateString())
            ->get();
            
        // Fetch loans closed on this day (Closures)
        $closures = Loan::with(['user', 'application'])
            ->where(function($q) {
                $q->where('status', 'paid_off')->orWhere('status', 'written_off');
            })
            ->whereDate('updated_at', $date->toDateString()) // Approx closed date
            ->get();
            
        $count = 0;
        foreach ($registrations as $loan) {
            /** @var Loan $loan */
            $content .= $this->generateDataRecord($loan, 'R', $date);
            $count++;
        }
        foreach ($closures as $loan) {
            /** @var Loan $loan */
            $content .= $this->generateDataRecord($loan, 'C', $date);
            $count++;
        }
        
        $content .= $this->generateTrailer($count);
        
        return $content;
    }

    /**
     * Fixed-length alphanumeric left-aligned padded with spaces
     */
    private function padA($str, $length): string
    {
        $str = substr((string)$str, 0, $length);
        return str_pad($str, $length, ' ', STR_PAD_RIGHT);
    }

    /**
     * Fixed-length alphanumeric right-aligned padded with spaces
     */
    private function padALeft($str, $length): string
    {
        $str = substr((string)$str, 0, $length);
        return str_pad($str, $length, ' ', STR_PAD_LEFT);
    }

    /**
     * Fixed-length numeric right-aligned padded with spaces (no zeroes before standard digits, no decimals)
     */
    private function padN($num, $length): string
    {
        $num = (int) round((float) $num); // Compuscan requires whole numbers, ZAR Rands
        $str = substr((string)$num, 0, $length);
        return str_pad($str, $length, ' ', STR_PAD_LEFT);
    }

    /**
     * Fixed-length Zero-padded numeric
     */
    private function padZ($num, $length): string
    {
        $num = (int) round((float) $num);
        $str = substr((string)$num, 0, $length);
        return str_pad($str, $length, '0', STR_PAD_LEFT);
    }

    private function generateHeader(Carbon $date, string $fileType, string $frequency): string
    {
        // 1 RECORD TYPE INDICATOR: "H" (1)
        // 2 SUPPLIER REFERENCE NUMBER (10)
        // 3 MONTH END DATE / TRANSACTION DATE CCYYMMDD (8)
        // 4 VERSION NUMBER "03" (2)
        // 5 DATE FILE WAS CREATED CCYYMMDD (8)
        // 6 TRADING NAME (60)
        // 7 FILLER (611) -> 700 chars total for header
        
        $row = 'H';
        $row .= $this->padA($this->srn, 10);
        $row .= $date->format('Ymd');
        $row .= '03'; // V3.00
        $row .= Carbon::now()->format('Ymd');
        $row .= $this->padA($this->tradingName, 60);
        $row .= $this->padA('', 611); // Filler to 700

        return $row . "\r\n";
    }

    private function generateTrailer(int $recordCount): string
    {
        // 1 RECORD TYPE INDICATOR: "T" (1)
        // 2 RECORD COUNT: (10)
        
        $row = 'T';
        $row .= $this->padZ($recordCount, 10);
        
        return $row . "\r\n";
    }

    private function generateDataRecord(Loan $loan, string $recordType, Carbon $date): string
    {
        $user = $loan->user;
        $app = $loan->application;
        
        // Extract basic names
        $userName = $user ? $user->name : 'Unknown User';
        $names = explode(' ', $userName);
        $surname = array_pop($names) ?: 'Unknown';
        $firstName = array_shift($names) ?: 'Unknown';
        $idNumber = $app->national_id ?? ($user ? $user->national_id : '');

        $row = '';
        // 1 RECORD TYPE INDICATOR (1) [D, R, or C]
        $row .= $recordType;
        
        // 2 LSO ID NUMBER (13) numeric
        $idDigits = preg_replace('/[^0-9]/', '', $idNumber);
        $row .= $this->padN($idDigits, 13);
        
        // 3 OTHER ID NUMBER OR PASSPORT (16)
        $row .= $this->padA('', 16); // Left blank if LSO ID provided
        
        // 4 GENDER (1)
        $row .= 'M'; // Default or retrieve from DB if you have it
        
        // 5 DATE OF BIRTH (8) -> Use regex or DB if available, else derive from ID (YYMMDD format if SA/Lesotho style)
        $dob = '19800101'; // Default placeholder, update to actual DOB logic
        $row .= $dob;
        
        // 6 BRANCH CODE (8)
        $row .= $this->padALeft('HQ', 8);
        
        // 7 ACCOUNT NO. (25)
        $row .= $this->padALeft($loan->loan_number, 25);
        
        // 8 SUB-ACCOUNT NO. (4)
        $row .= $this->padA('', 4);
        
        // 9 SURNAME (25)
        $row .= $this->padA($surname, 25);
        
        // 10 TITLE (5)
        $row .= $this->padA('MR', 5);
        
        // 11 FORENAME OR INITIAL 1 (14)
        $row .= $this->padA($firstName, 14);
        
        // 12 FORENAME 2 (14)
        $row .= $this->padA('', 14);
        
        // 13 FORENAME 3 (14)
        $row .= $this->padA('', 14);
        
        // 14-17 RESIDENTIAL ADDRESS LINES (25 each)
        $row .= $this->padA($app->address ?? 'Maseru', 25);
        $row .= $this->padA('', 25);
        $row .= $this->padA('Maseru', 25);
        $row .= $this->padA('Lesotho', 25);
        
        // 18 POSTAL CODE RES (6)
        $row .= $this->padA('100', 6);
        
        // 19 OWNER/TENANT (1)
        $row .= ' ';
        
        // 20-23 POSTAL ADDRESS LINES (25 each)
        $row .= $this->padA($app->address ?? 'Maseru', 25);
        $row .= $this->padA('', 25);
        $row .= $this->padA('Maseru', 25);
        $row .= $this->padA('Lesotho', 25);
        
        // 24 POSTAL CODE POSTAL (6)
        $row .= $this->padA('100', 6);
        
        // 25 OWNERSHIP TYPE (2) => 01 Sole Prop or 00 Other (usually 00 for personal loans)
        $row .= '00';
        
        // 26 LOAN REASON CODE (2) => O Other or P Personal
        $row .= $this->padA('P', 2);
        
        // 27 PAYMENT TYPE (2) => 00 Other, 01 Payroll, 02 Deferred...
        $row .= '00';
        
        // 28 TYPE OF ACCOUNT (2) => P Personal Loan
        $row .= $this->padA('P', 2);
        
        // 29 DATE ACCOUNT OPENED (8)
        $dateOpened = $loan->disbursement_date ? Carbon::parse($loan->disbursement_date) : Carbon::parse($loan->created_at);
        $row .= $dateOpened->format('Ymd');
        
        // 30 DEFERRED PAYMENT DATE (8)
        $row .= $this->padA('', 8);
        
        // 31 DATE LAST PAYMENT RECEIVED (8)
        $lastPayment = $loan->payments()->latest('created_at')->first();
        $row .= $lastPayment ? Carbon::parse($lastPayment->created_at)->format('Ymd') : $this->padA('', 8);
        
        // 32 OPENING BALANCE/CREDIT LIMIT (9) -> principal amount
        $row .= $this->padN($loan->principal_amount, 9);
        
        // 33 CURRENT BALANCE (9) -> outstanding + arrears
        $currentBalance = $recordType === 'C' ? 0 : $loan->outstanding_balance;
        $row .= $this->padN($currentBalance, 9);
        
        // 34 CURRENT BALANCE INDICATOR (1)
        $row .= $currentBalance == 0 ? 'C' : 'D';
        
        // 35 AMOUNT OVERDUE (9)
        // Simplification: if it's overdue, the days overdue > 0
        $amountOverdue = ($loan->status === 'overdue' && $recordType !== 'C') ? 0 : 0; // Temporarily 0, adjust logic if exact overdue amount is tracked
        $row .= $this->padN($amountOverdue, 9);
        
        // 36 INSTALMENT AMOUNT (9)
        $row .= $this->padN($loan->principal_amount / max(1, $loan->term_months), 9); // Simplification
        
        // 37 MONTHS IN ARREARS (2)
        $monthsInArrears = $amountOverdue > 0 ? 1 : 0; // Simple fallback
        $monthsInArrears = $recordType === 'C' ? 0 : $monthsInArrears;
        $row .= $this->padZ($monthsInArrears, 2);
        
        // 38 STATUS CODE (2)
        $statusCode = '  '; // Open
        if ($recordType === 'C') {
            $statusCode = 'C '; // Closed
        } elseif ($loan->status === 'written_off') {
            $statusCode = 'W '; // Written off
        }
        $row .= $this->padA($statusCode, 2);
        
        // 39 REPAYMENT FREQUENCY (2) => 03 Monthly
        $row .= '03';
        
        // 40 TERMS (4) => term in months
        $row .= $this->padN($loan->term_months, 4);
        
        // 41 STATUS DATE (8)
        $row .= $this->padA('', 8); // Required only if Status Code populated
        if (trim($statusCode) !== '') {
            $row = substr($row, 0, -8) . $date->format('Ymd');
        }
        
        // 42-45 OLD FIELDS for conversions
        $row .= $this->padA('', 8);  // 42 OLD BRANCH
        $row .= $this->padA('', 25); // 43 OLD ACCOUNT
        $row .= $this->padA('', 4);  // 44 OLD SUB ACC
        $row .= $this->padA('', 10); // 45 OLD SUPPLIER REF
        
        // 46 HOME TELEPHONE (16)
        $row .= $this->padN('', 16);
        
        // 47 CELLULAR TELEPHONE (16)
        $row .= $this->padN($user ? $user->phone : '', 16);
        
        // 48 WORK TELEPHONE (16)
        $row .= $this->padN('', 16);
        
        // 49 EMPLOYER DETAIL (60)
        $row .= $this->padA($app->employer_name ?? '', 60);
        
        // 50 INCOME (9)
        $row .= $this->padN($app->monthly_gross_salary ?? 0, 9);
        
        // 51 INCOME FREQUENCY (1) => M Monthly
        $row .= ($app->monthly_gross_salary ?? 0) > 0 ? 'M' : ' ';
        
        // 52 OCCUPATION (20)
        $row .= $this->padA($app->occupation ?? '', 20);
        
        // 53 THIRD PARTY NAME (60)
        $row .= $this->padA('', 60);
        
        // 54 ACCOUNT SOLD TO 3RD PARTY (2)
        $row .= '  ';
        
        // 55 NO OF PARTICIPANTS IN JOINT LOAN (3)
        $row .= $this->padA('', 3);
        
        // 56 FILLER (2) => to 700
        $row .= '  ';
        
        // 57 SUPPLIER REFERENCE NUMBER (10)
        $row .= $this->padA($this->srn, 10);
        
        // 58 TRANSACTION DATE (8)
        $row .= $date->format('Ymd');

        return $row . "\r\n";
    }
}
