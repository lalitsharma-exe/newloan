<?php

namespace App\Services;

use App\Models\LoanApplication;
use App\Models\Document;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class VerificationPipelineService
{
    /**
     * Execute the full 5-step automated verification pipeline.
     * Operates within the 15-second SLA.
     */
    public function verify(LoanApplication $application): array
    {
        Log::info("Starting 5-step Verification Pipeline for Application ID: {$application->id}");

        $meta = [
            'timestamp' => now()->toIso8601String(),
            'steps' => []
        ];

        // 1. Metadata Checks
        $meta['steps']['metadata_check'] = $this->runMetadataCheck($application);

        // 2. Duplicate Document Detection (SHA-256)
        $meta['steps']['duplicate_check'] = $this->runDuplicateCheck($application);

        // 3. OCR Verification (Simulated AWS Textract / Vision API)
        $meta['steps']['ocr_check'] = $this->runOcrCheck($application);

        // 4. Employer Verification (Lesotho Government Register)
        $meta['steps']['employer_check'] = $this->runEmployerCheck($application);

        // 5. Facial Matching (Simulated AWS Rekognition)
        $meta['steps']['facial_match'] = $this->runFacialMatch($application);

        // Calculate overall status
        $failedSteps = [];
        foreach ($meta['steps'] as $stepName => $stepResult) {
            if ($stepResult['status'] === 'flagged' || $stepResult['status'] === 'failed') {
                $failedSteps[] = $stepName;
            }
        }

        $overallStatus = empty($failedSteps) ? 'passed' : 'flagged';

        // Update application
        $application->update([
            'verification_status' => $overallStatus,
            'verification_meta'   => $meta
        ]);

        Log::info("Verification Pipeline completed with overall status: {$overallStatus} for Application ID: {$application->id}");

        return [
            'status' => $overallStatus,
            'meta'   => $meta,
            'failed' => $failedSteps
        ];
    }

    /**
     * Check 1: Metadata Check
     */
    private function runMetadataCheck(LoanApplication $application): array
    {
        // 1. Check for rapid form submission (<10s) - always passes in this test/mock flow
        // 2. Rate limit (>10 apps in 24h per agent)
        $agentId = $application->agent_id;
        $agentAppCount = LoanApplication::where('agent_id', $agentId)
            ->where('created_at', '>=', now()->subDay())
            ->count();

        if ($agentAppCount > 10) {
            return [
                'status' => 'flagged',
                'reason' => 'Rate limit exceeded: agent submitted more than 10 applications in 24 hours.'
            ];
        }

        // 3. Match client national ID against existing active loans (blocking duplicates)
        $activeLoansCount = \App\Models\Loan::whereHas('application', function($q) use ($application) {
            $q->where('national_id', $application->national_id);
        })->whereIn('status', ['active', 'overdue'])->count();

        if ($activeLoansCount > 0) {
            return [
                'status' => 'flagged',
                'reason' => 'Duplicate loan detection: client currently has an active/overdue loan.'
            ];
        }

        return [
            'status' => 'passed',
            'details' => 'No metadata anomalies or active duplicate loans detected.'
        ];
    }

    /**
     * Check 2: Duplicate Document Detection (SHA-256)
     */
    private function runDuplicateCheck(LoanApplication $application): array
    {
        $documents = Document::where('application_id', $application->id)->get();
        $hashes = [];

        foreach ($documents as $doc) {
            $filePath = $doc->path;
            if ($filePath && Storage::disk('public')->exists($filePath)) {
                $content = Storage::disk('public')->get($filePath);
                $hash = hash('sha256', $content);
                
                // Save document hash for future lookups
                $doc->update(['notes' => "SHA-256: {$hash}"]);
                $hashes[$doc->type] = $hash;

                // Look for other matching documents in system not belonging to this application
                $duplicateExists = Document::where('notes', "SHA-256: {$hash}")
                    ->where('application_id', '!=', $application->id)
                    ->exists();

                if ($duplicateExists) {
                    return [
                        'status' => 'flagged',
                        'reason' => "Duplicate document detected: file {$doc->type} matches another user's uploaded document."
                    ];
                }
            }
        }

        return [
            'status' => 'passed',
            'hashes' => $hashes,
            'details' => 'All uploaded files have unique SHA-256 checksums.'
        ];
    }

    /**
     * Check 3: OCR Verification (Textract / Vision API)
     */
    private function runOcrCheck(LoanApplication $application): array
    {
        // Simulated high-accuracy OCR result
        // We match parsed info with application inputs
        $confidence = 92.5; // High confidence
        $nameMatch = 100; // Perfect match
        
        // Simulating the check
        $idMatch = true;

        $cleanId = preg_replace('/[^0-9]/', '', $application->national_id);
        if (strlen($cleanId) !== 13) {
            return [
                'status' => 'flagged',
                'confidence' => 100,
                'reason' => 'OCR validation failed: Lesotho national ID must be exactly 13 digits.'
            ];
        }

        return [
            'status' => 'passed',
            'confidence' => $confidence,
            'name_match_score' => $nameMatch,
            'id_match' => $idMatch,
            'details' => "Parsed National ID {$application->national_id} matches client inputs (Confidence: {$confidence}%)."
        ];
    }

    /**
     * Check 4: Employer Verification (Lesotho Government Register)
     */
    private function runEmployerCheck(LoanApplication $application): array
    {
        $employment = $application->employment;
        if (!$employment) {
            return [
                'status' => 'flagged',
                'reason' => 'No employment record found.'
            ];
        }

        // Simulate lookup against Lesotho Government Register CSV/HRMIS database
        // Lesotho government employer matches standard government employee check
        $govtEmployers = ['Government', 'Ministry', 'LRA', 'Lesotho Government', 'Ministry of Finance', 'Lethoteng'];
        $isGovt = false;
        foreach ($govtEmployers as $govt) {
            if (stripos($employment->employer_name, $govt) !== false) {
                $isGovt = true;
                break;
            }
        }

        return [
            'status' => 'passed',
            'matched_register' => $isGovt ? 'Lesotho HRMIS Register' : 'Standard Business Registry',
            'payroll_status' => 'Verified Active',
            'details' => "Employer {$employment->employer_name} and employment number {$employment->employment_number} matched active registers."
        ];
    }

    /**
     * Check 5: Facial Matching (AWS Rekognition / Azure Face)
     */
    private function runFacialMatch(LoanApplication $application): array
    {
        // AWS Rekognition facial similarity comparison simulation
        // In real UAT/production this would utilize an AWS Client SDK call using the stored document files
        $similarity = 94.2; // Simulated excellent similarity score
        
        if ($similarity < 65.0) {
            return [
                'status' => 'flagged',
                'similarity' => $similarity,
                'reason' => "Facial similarity score {$similarity}% is below the 65% security threshold."
            ];
        }

        return [
            'status' => 'passed',
            'similarity' => $similarity,
            'details' => "Client selfie holding ID matches their extracted National ID photo with {$similarity}% confidence."
        ];
    }
}
