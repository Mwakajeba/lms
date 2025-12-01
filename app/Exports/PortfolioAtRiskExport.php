<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PortfolioAtRiskExport implements FromView, WithTitle, ShouldAutoSize, WithStyles
{
    protected $par_data;
    protected $filters;
    protected $company;

    public function __construct($par_data, $filters, $company = null)
    {
        $this->par_data = $par_data;
        $this->filters = $filters;
        $this->company = $company;
    }

    public function view(): View
    {
        // Calculate totals and ratios
        $total_outstanding = array_sum(array_column($this->par_data, 'outstanding_balance'));
        $total_at_risk = array_sum(array_column($this->par_data, 'at_risk_amount'));
        $par_ratio = $total_outstanding > 0 ? ($total_at_risk / $total_outstanding) * 100 : 0;
        $loans_at_risk = count(array_filter($this->par_data, function($item) { 
            return $item['is_at_risk']; 
        }));
        
        // Risk level breakdown
        $risk_levels = ['Low' => 0, 'Medium' => 0, 'High' => 0, 'Critical' => 0];
        foreach ($this->par_data as $loan) {
            if (isset($risk_levels[$loan['risk_level']])) {
                $risk_levels[$loan['risk_level']]++;
            }
        }

        return view('loans.reports.portfolio_at_risk_excel', [
            'par_data' => $this->par_data,
            'filters' => $this->filters,
            'company' => $this->company,
            'generated_date' => now()->format('d-m-Y H:i:s'),
            'as_of_date' => $this->filters['as_of_date'] ?? now()->format('Y-m-d'),
            'par_days' => $this->filters['par_days'] ?? 30,
            'branch_name' => $this->filters['branch_name'] ?? 'All Branches',
            'group_name' => $this->filters['group_name'] ?? 'All Groups',
            'loan_officer_name' => $this->filters['loan_officer_name'] ?? 'All Officers',
            'total_outstanding' => $total_outstanding,
            'total_at_risk' => $total_at_risk,
            'par_ratio' => $par_ratio,
            'loans_at_risk' => $loans_at_risk,
            'total_loans' => count($this->par_data),
            'risk_levels' => $risk_levels,
        ]);
    }

    public function title(): string
    {
        $par_days = $this->filters['par_days'] ?? 30;
        return "PAR {$par_days} Report";
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        // Header styling (rows 1-8)
        $sheet->getStyle('A1:' . $highestColumn . '8')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Report title styling (row 1)
        $sheet->getStyle('A1:' . $highestColumn . '1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => 'fd7e14'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'f8f9fa'],
            ],
        ]);

        // Summary section styling (rows 10-15)
        $sheet->getStyle('A10:D15')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'e9ecef'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        // Data table header styling (row 17)
        $sheet->getStyle('A17:' . $highestColumn . '17')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'ffffff'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '495057'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        // Data rows styling (from row 18 to end)
        if ($highestRow > 17) {
            $sheet->getStyle('A18:' . $highestColumn . $highestRow)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);

            // Center alignment for specific columns
            $centerColumns = ['A', 'C', 'E', 'L', 'M', 'O']; // #, Customer No, Loan No, Risk %, Days, Status
            foreach ($centerColumns as $column) {
                $sheet->getStyle($column . '18:' . $column . $highestRow)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }

            // Right alignment for amount columns
            $rightColumns = ['F', 'J', 'K']; // Loan Amount, Outstanding, At Risk
            foreach ($rightColumns as $column) {
                $sheet->getStyle($column . '18:' . $column . $highestRow)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }

            // Totals row styling (last row)
            $sheet->getStyle('A' . $highestRow . ':' . $highestColumn . $highestRow)->applyFromArray([
                'font' => [
                    'bold' => true,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'f8f9fa'],
                ],
            ]);
        }

        // Auto-size columns
        foreach (range('A', $highestColumn) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Set row heights
        for ($row = 1; $row <= $highestRow; $row++) {
            $sheet->getRowDimension($row)->setRowHeight(20);
        }

        // Freeze header row
        $sheet->freezePane('A18');

        return [];
    }
}
