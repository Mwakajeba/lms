<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LoanSizeTypeExport implements FromArray, WithHeadings, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(
        protected array $rows,
        protected array $grand,
        protected ?string $startDate,
        protected ?string $endDate
    ) {}

    public function headings(): array
    {
        return [
            ['Loan Size Type Report', $this->startDate . ' - ' . $this->endDate],
            [],
            [
                'LOAN SIZE TYPE', 'NO. OF LOAN', 'LOAN AMOUNT', 'INTEREST', 'TOTAL LOAN',
                'TOTAL LOAN OUTSTANDING', 'NO. OF LOANS IN ARREARS', 'TOTAL ARREARS AMOUNT',
                'NO. OF LOANS IN DELAYED', 'DELAYED AMOUNT', 'OUTSTANDING IN DELAYED'
            ]
        ];
    }

    public function array(): array
    {
        $data = [];
        foreach ($this->rows as $r) {
            $data[] = [
                $r['label'],
                $r['count'],
                number_format($r['loan_amount'], 2),
                number_format($r['interest'], 2),
                number_format($r['total_loan'], 2),
                number_format($r['total_outstanding'], 2),
                $r['arrears_count'],
                number_format($r['arrears_amount'], 2),
                $r['delayed_count'],
                number_format($r['delayed_amount'], 2),
                number_format($r['outstanding_in_delayed'], 2),
            ];
        }

        // grand total row
        $data[] = [
            'GRAND TOTAL',
            $this->grand['count'],
            number_format($this->grand['loan_amount'], 2),
            number_format($this->grand['interest'], 2),
            number_format($this->grand['total_loan'], 2),
            number_format($this->grand['total_outstanding'], 2),
            $this->grand['arrears_count'],
            number_format($this->grand['arrears_amount'], 2),
            $this->grand['delayed_count'],
            number_format($this->grand['delayed_amount'], 2),
            number_format($this->grand['outstanding_in_delayed'], 2),
        ];

        return $data;
    }

    public function title(): string
    {
        return 'Loan Size Type';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            3 => ['font' => ['bold' => true]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 24,
            'B' => 14,
            'C' => 18,
            'D' => 16,
            'E' => 16,
            'F' => 22,
            'G' => 22,
            'H' => 22,
            'I' => 22,
            'J' => 18,
            'K' => 24,
        ];
    }
}


