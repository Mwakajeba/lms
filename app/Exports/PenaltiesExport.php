<?php

namespace App\Exports;

use App\Models\Company;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PenaltiesExport implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize, WithEvents
{
    protected $penaltiesData;
    protected $startDate;
    protected $endDate;
    protected $penaltyName;
    protected $penaltyTypeName;
    protected $branchName;
    protected $company;

    public function __construct($penaltiesData, $startDate, $endDate, $penaltyName, $penaltyTypeName, $branchName)
    {
        $this->penaltiesData = $penaltiesData;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->penaltyName = $penaltyName;
        $this->penaltyTypeName = $penaltyTypeName;
        $this->branchName = $branchName;
        $this->company = Company::first();
    }

    public function array(): array
    {
        return $this->penaltiesData['data']->map(function ($item, $index) {
            return [
                $index + 1,
                \Carbon\Carbon::parse($item->date)->format('d/m/Y'),
                $item->customer_name ?? 'N/A',
                $item->amount,
                $item->description,
            ];
        })->toArray();
    }

    public function headings(): array
    {
        return [
            '#',
            'Date',
            'Customer',
            'Amount',
            'Description'
        ];
    }

    public function title(): string
    {
        return 'Penalties Report';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Add company header
                $sheet->insertNewRowBefore(1, 6);
                
                // Company name
                $sheet->setCellValue('A1', $this->company->name ?? 'SmartFinance');
                $sheet->mergeCells('A1:E1');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Report title
                $sheet->setCellValue('A2', 'PENALTIES REPORT');
                $sheet->mergeCells('A2:E2');
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A2')->getFont()->getColor()->setRGB('DC3545');
                
                // Report period
                $sheet->setCellValue('A3', 'Period: ' . \Carbon\Carbon::parse($this->startDate)->format('d-m-Y') . ' to ' . \Carbon\Carbon::parse($this->endDate)->format('d-m-Y'));
                $sheet->mergeCells('A3:E3');
                $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Generated date
                $sheet->setCellValue('A4', 'Generated on: ' . date('d-m-Y H:i:s'));
                $sheet->mergeCells('A4:E4');
                $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Company details
                if ($this->company) {
                    $companyDetails = [];
                    if ($this->company->address) $companyDetails[] = $this->company->address;
                    if ($this->company->phone) $companyDetails[] = 'Tel: ' . $this->company->phone;
                    if ($this->company->email) $companyDetails[] = 'Email: ' . $this->company->email;
                    
                    if (!empty($companyDetails)) {
                        $sheet->setCellValue('A5', implode(' | ', $companyDetails));
                        $sheet->mergeCells('A5:E5');
                        $sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle('A5')->getFont()->setSize(10);
                    }
                }
                
                // Empty row
                $sheet->setCellValue('A6', '');
                
                // Style the headers (now row 7)
                $headerRange = 'A7:E7';
                $sheet->getStyle($headerRange)->getFont()->setBold(true);
                $sheet->getStyle($headerRange)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F8F9FA');
                $sheet->getStyle($headerRange)->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);
                
                // Add borders to all data cells
                $lastRow = $sheet->getHighestRow();
                $dataRange = 'A7:E' . $lastRow;
                $sheet->getStyle($dataRange)->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);
                
                // Format currency column (column D - Amount)
                $sheet->getStyle('D8:D' . $lastRow)->getNumberFormat()->setFormatCode('#,##0.00');
                
                // Auto-size columns
                foreach (range('A', 'E') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
            },
        ];
    }
}
