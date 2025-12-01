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

class NPLExport implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize, WithEvents
{
    protected $nplData;
    protected $asOfDate;
    protected $branchId;
    protected $loanOfficerId;
    protected $company;

    public function __construct($nplData, $asOfDate, $branchId = null, $loanOfficerId = null)
    {
        $this->nplData = $nplData;
        $this->asOfDate = $asOfDate;
        $this->branchId = $branchId;
        $this->loanOfficerId = $loanOfficerId;
        $this->company = Company::first();
    }

    public function array(): array
    {
        return collect($this->nplData)->map(function ($row) {
            return [
                $row['date_of'],
                $row['branch'],
                $row['loan_officer'],
                $row['loan_id'],
                $row['borrower'],
                $row['disbursed_date'] ?? 'N/A',
                $row['last_payment_date'] ?? 'N/A',
                $row['outstanding'],
                $row['npl_outstanding'] ?? $row['outstanding'],
                $row['dpd'],
                $row['classification'],
                $row['provision_percent'],
                $row['provision_amount'],
                $row['collateral'] ?: 'None',
                $row['status'],
            ];
        })->toArray();
    }

    public function headings(): array
    {
        return [
            'Date Of',
            'Branch',
            'Loan Officer',
            'Loan ID',
            'Borrower',
            'Disbursed Date',
            'Last Payment',
            'Total Outstanding (TZS)',
            'NPL Outstanding (TZS)',
            'DPD',
            'Classification',
            'Provision %',
            'Provision (TZS)',
            'Collateral',
            'Status'
        ];
    }

    public function title(): string
    {
        return 'NPL Report';
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
                $sheet->mergeCells('A1:O1');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Report title
                $sheet->setCellValue('A2', 'NON PERFORMING LOAN REPORT');
                $sheet->mergeCells('A2:O2');
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A2')->getFont()->getColor()->setRGB('DC3545');
                
                // Report date
                $sheet->setCellValue('A3', 'As of Date: ' . \Carbon\Carbon::parse($this->asOfDate)->format('d-m-Y'));
                $sheet->mergeCells('A3:O3');
                $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Generated date
                $sheet->setCellValue('A4', 'Generated on: ' . date('d-m-Y H:i:s'));
                $sheet->mergeCells('A4:O4');
                $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Company details
                if ($this->company) {
                    $companyDetails = [];
                    if ($this->company->address) $companyDetails[] = $this->company->address;
                    if ($this->company->phone) $companyDetails[] = 'Tel: ' . $this->company->phone;
                    if ($this->company->email) $companyDetails[] = 'Email: ' . $this->company->email;
                    
                    if (!empty($companyDetails)) {
                        $sheet->setCellValue('A5', implode(' | ', $companyDetails));
                        $sheet->mergeCells('A5:O5');
                        $sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle('A5')->getFont()->setSize(10);
                    }
                }
                
                // Empty row
                $sheet->setCellValue('A6', '');
                
                // Style the headers (now row 7)
                $headerRange = 'A7:O7';
                $sheet->getStyle($headerRange)->getFont()->setBold(true);
                $sheet->getStyle($headerRange)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F8F9FA');
                $sheet->getStyle($headerRange)->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);
                
                // Add borders to all data cells
                $lastRow = $sheet->getHighestRow();
                $dataRange = 'A7:O' . $lastRow;
                $sheet->getStyle($dataRange)->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);
                
                // Format currency columns (H, I, M columns)
                $sheet->getStyle('H8:H' . $lastRow)->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle('I8:I' . $lastRow)->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle('M8:M' . $lastRow)->getNumberFormat()->setFormatCode('#,##0.00');
                
                // Auto-size columns
                foreach (range('A', 'O') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
            },
        ];
    }
}
