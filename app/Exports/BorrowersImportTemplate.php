<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class BorrowersImportTemplate implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    public function title(): string
    {
        return 'Borrowers Import Template';
    }

    public function headings(): array
    {
        return [
            'name',          // Required — Full name
            'phone',         // Required — 8-digit local (53797734) or +266 format (+26653797734)
            'email',         // Optional
            'national_id',   // Optional — Unique national ID
            'maiden_name',   // Optional
            'officer_id',    // Optional — Numeric ID of assigned loan officer
        ];
    }

    public function array(): array
    {
        // Sample rows to guide the user
        return [
            ['John Doe',    '53797734', 'john@example.com', 'LS123456789', '',       ''],
            ['Jane Smith',  '58001234', '',                 'LS987654321', 'Mokoena', '2'],
            ['Bob Mokoena', '+26658009999', '',             '',            '',        ''],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Header row
            1 => [
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E3A5F']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            // Sample rows — light highlight
            2 => ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF0F4FF']]],
            3 => ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF0F4FF']]],
            4 => ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF0F4FF']]],
        ];
    }
}
