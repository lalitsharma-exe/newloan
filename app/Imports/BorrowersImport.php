<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class BorrowersImport implements ToCollection, WithHeadingRow
{
    public int $imported = 0;
    public int $skipped  = 0;
    public array $errors  = [];

    /**
     * Normalise heading row keys coming from Excel/CSV.
     * Maatwebsite lowercases and snake_cases them.
     */
    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNum = $index + 2; // +2 because row 1 is header

            // Map row to array, trim everything
            $data = array_map('trim', $row->toArray());

            // Resolve common key aliases
            $name       = $data['name']        ?? $data['full_name']   ?? '';
            $phone      = $data['phone']        ?? $data['phone_number'] ?? $data['cell'] ?? '';
            $email      = $data['email']        ?? '';
            $nationalId = $data['national_id']  ?? $data['id_number']   ?? $data['national_number'] ?? '';
            $maidenName = $data['maiden_name']  ?? $data['maiden']      ?? '';
            $officerId  = $data['officer_id']   ?? $data['assigned_officer_id'] ?? null;

            // Skip completely empty rows
            if (!$name && !$phone) {
                $this->skipped++;
                continue;
            }

            // Basic validation
            if (!$name) {
                $this->errors[] = "Row {$rowNum}: Name is required.";
                $this->skipped++;
                continue;
            }

            if (!$phone) {
                $this->errors[] = "Row {$rowNum}: Phone is required.";
                $this->skipped++;
                continue;
            }

            // Normalise phone to +266XXXXXXXX
            $phone = $this->formatPhone($phone);
            if (!$phone) {
                $this->errors[] = "Row {$rowNum}: Invalid phone number '{$data['phone']}'.";
                $this->skipped++;
                continue;
            }

            // Duplicate phone check
            if (User::where('phone', $phone)->exists()) {
                $this->errors[] = "Row {$rowNum}: Phone {$phone} already exists — skipped.";
                $this->skipped++;
                continue;
            }

            // Duplicate national ID check
            if ($nationalId && User::where('national_id', $nationalId)->exists()) {
                $this->errors[] = "Row {$rowNum}: National ID '{$nationalId}' already exists — skipped.";
                $this->skipped++;
                continue;
            }

            // Duplicate email check
            if ($email && User::where('email', $email)->exists()) {
                $this->errors[] = "Row {$rowNum}: Email '{$email}' already exists — skipped.";
                $this->skipped++;
                continue;
            }

            // Resolve officer — numeric ID or null
            $assignedOfficerId = null;
            if ($officerId && is_numeric($officerId)) {
                $assignedOfficerId = (int) $officerId;
            }

            // Password = phone number (8-digit local, no +266)
            $password = preg_replace('/[^0-9]/', '', $phone);
            $password = strlen($password) > 8 ? substr($password, -8) : $password;

            try {
                User::create([
                    'name'                => $name,
                    'email'               => $email ?: null,
                    'phone'               => $phone,
                    'national_id'         => $nationalId ?: null,
                    'maiden_name'         => $maidenName ?: null,
                    'role'                => 'borrower',
                    'password'            => Hash::make($password),
                    'is_active'           => true,
                    'email_verified_at'   => now(),
                    'assigned_officer_id' => $assignedOfficerId,
                ]);
                $this->imported++;
            } catch (\Throwable $e) {
                $this->errors[] = "Row {$rowNum}: Failed to create — " . $e->getMessage();
                $this->skipped++;
            }
        }
    }

    private function formatPhone(?string $phone): string
    {
        if (!$phone) return '';
        $digits = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($digits) === 12 && str_starts_with($digits, '266')) return '+' . $digits;
        if (strlen($digits) === 11 && str_starts_with($digits, '266'))  return '+' . $digits;
        if (strlen($digits) === 8)                                        return '+266' . $digits;
        if (strlen($digits) === 9 && str_starts_with($digits, '0'))      return '+266' . substr($digits, 1);
        if (str_starts_with($phone, '+266'))                              return $phone;
        // Fallback — take last 8 digits
        if (strlen($digits) >= 8) return '+266' . substr($digits, -8);
        return '';
    }
}
