<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class BorrowersExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    public function __construct(private array $filters = []) {}

    public function title(): string
    {
        return 'Borrowers';
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Email',
            'Phone',
            'National ID',
            'Maiden Name',
            'Assigned Officer ID',
            'Assigned Officer',
            'Status',
            'Joined',
            'Last Login',
        ];
    }

    public function collection(): Collection
    {
        $query = User::where('role', 'borrower')
            ->orderBy('created_at', 'desc');

        if (!empty($this->filters['search'])) {
            $q = $this->filters['search'];
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%")
                  ->orWhere('phone', 'like', "%{$q}%")
                  ->orWhere('national_id', 'like', "%{$q}%");
            });
        }

        if (!empty($this->filters['status'])) {
            $query->where('is_active', $this->filters['status'] === 'active');
        }

        return $query->get()->map(fn(User $u) => [
            $u->id,
            $u->name,
            $u->email ?? '',
            $u->phone ?? '',
            $u->national_id ?? '',
            $u->maiden_name ?? '',
            $u->assigned_officer_id ?? '',
            $u->assigned_officer_id
                ? (User::find($u->assigned_officer_id)?->name ?? '')
                : '',
            $u->is_active ? 'Active' : 'Inactive',
            $u->created_at?->format('Y-m-d') ?? '',
            $u->last_login_at?->format('Y-m-d H:i') ?? 'Never',
        ]);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E3A5F']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }
}
