<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class LoanArrearsExport implements FromArray, WithHeadings, WithStyles, WithTitle, WithColumnWidths, WithEvents
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $exportData = [];
        
        if (count($this->data['arrears_data']) > 0) {
            foreach ($this->data['arrears_data'] as $index => $row) {
                $exportData[] = [
                    $index + 1,
                    $row['customer'],
                    $row['customer_no'],
                    $row['phone'],
                    $row['loan_no'],
                    $row['loan_amount'],
                    $row['disbursed_date'],
                    $row['branch'],
                    $row['group'],
                    $row['loan_officer'],
                    $row['arrears_amount'],
                    $row['days_in_arrears'],
                    $row['first_overdue_date'],
                    $row['overdue_schedules_count'],
                    $row['arrears_severity'],
                ];
            }
            
            // Add summary row
            $exportData[] = [];
            $exportData[] = [
                '', '', '', '', '', '', '', '', '', 'TOTAL:',
                array_sum(array_column($this->data['arrears_data'], 'arrears_amount')),
                round(array_sum(array_column($this->data['arrears_data'], 'days_in_arrears')) / count($this->data['arrears_data'])),
                '', count($this->data['arrears_data']) . ' Loans', ''
            ];
        }

        return $exportData;
    }

    public function headings(): array
    {
        return [
            ['LOAN ARREARS REPORT'],
            ['Generated: ' . $this->data['generated_date']],
            ['Branch: ' . $this->data['branch_name']],
            ['Group: ' . $this->data['group_name']],
            ['Loan Officer: ' . $this->data['loan_officer_name']],
            [],
            [
                '#',
                'Customer',
                'Customer No',
                'Phone',
                'Loan No',
                'Loan Amount',
                'Disbursed Date',
                'Branch',
                'Group',
                'Loan Officer',
                'Arrears Amount',
                'Days in Arrears',
                'First Overdue Date',
                'Overdue Items',
                'Severity'
            ]
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Title row
            1 => [
                'font' => ['bold' => true, 'size' => 16],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            
            // Info rows
            2 => ['font' => ['bold' => true]],
            3 => ['font' => ['bold' => true]],
            4 => ['font' => ['bold' => true]],
            5 => ['font' => ['bold' => true]],
            
            // Header row
            7 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF4472C4']
                ],
                'font' => ['color' => ['argb' => 'FFFFFFFF'], 'bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // #
            'B' => 20,  // Customer
            'C' => 12,  // Customer No
            'D' => 15,  // Phone
            'E' => 12,  // Loan No
            'F' => 15,  // Loan Amount
            'G' => 12,  // Disbursed Date
            'H' => 15,  // Branch
            'I' => 15,  // Group
            'J' => 15,  // Loan Officer
            'K' => 15,  // Arrears Amount
            'L' => 12,  // Days in Arrears
            'M' => 12,  // First Overdue Date
            'N' => 10,  // Overdue Items
            'O' => 10,  // Severity
        ];
    }

    public function title(): string
    {
        return 'Loan Arrears Report';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Merge title cells
                $sheet->mergeCells('A1:O1');
                
                // Add borders to data
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                
                $sheet->getStyle('A7:' . $highestColumn . $highestRow)
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);
                
                // Format currency columns
                $sheet->getStyle('F8:F' . $highestRow)->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle('K8:K' . $highestRow)->getNumberFormat()->setFormatCode('#,##0.00');
                
                // Center align specific columns
                $sheet->getStyle('A8:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('C8:C' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('D8:D' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('E8:E' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('G8:G' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('L8:L' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('M8:M' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('N8:N' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('O8:O' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Right align amount columns
                $sheet->getStyle('F8:F' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('K8:K' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            },
        ];
    }
}
