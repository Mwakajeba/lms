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

class InternalPortfolioAnalysisExport implements FromView, WithTitle, ShouldAutoSize, WithStyles
{
    protected $analysis_data;
    protected $filters;
    protected $company;

    public function __construct($analysis_data, $filters, $company = null)
    {
        $this->analysis_data = $analysis_data;
        $this->filters = $filters;
        $this->company = $company;
    }

    public function view(): View
    {
        // Calculate totals and ratios
        $total_outstanding = array_sum(array_column($this->analysis_data, 'outstanding_balance'));
        $total_overdue = array_sum(array_column($this->analysis_data, 'overdue_amount'));
        $total_at_risk = array_sum(array_column($this->analysis_data, 'at_risk_amount'));
        $overdue_ratio = $total_outstanding > 0 ? ($total_overdue / $total_outstanding) * 100 : 0;
        $conservative_par_ratio = $total_outstanding > 0 ? ($total_at_risk / $total_outstanding) * 100 : 0;
        $loans_at_risk = count(array_filter($this->analysis_data, function($item) { 
            return $item['is_at_risk']; 
        }));
        
        // Exposure category breakdown
        $exposure_categories = ['Current' => 0, 'Low Exposure' => 0, 'Medium Exposure' => 0, 'High Exposure' => 0, 'Critical Exposure' => 0];
        foreach ($this->analysis_data as $loan) {
            if (isset($exposure_categories[$loan['exposure_category']])) {
                $exposure_categories[$loan['exposure_category']]++;
            }
        }

        return view('loans.reports.internal_portfolio_analysis_excel', [
            'analysis_data' => $this->analysis_data,
            'filters' => $this->filters,
            'company' => $this->company,
            'generated_date' => now()->format('d-m-Y H:i:s'),
            'as_of_date' => $this->filters['as_of_date'] ?? now()->format('Y-m-d'),
            'par_days' => $this->filters['par_days'] ?? 30,
            'branch_name' => $this->filters['branch_name'] ?? 'All Branches',
            'group_name' => $this->filters['group_name'] ?? 'All Groups',
            'loan_officer_name' => $this->filters['loan_officer_name'] ?? 'All Officers',
            'total_outstanding' => $total_outstanding,
            'total_overdue' => $total_overdue,
            'total_at_risk' => $total_at_risk,
            'overdue_ratio' => $overdue_ratio,
            'conservative_par_ratio' => $conservative_par_ratio,
            'loans_at_risk' => $loans_at_risk,
            'total_loans' => count($this->analysis_data),
            'exposure_categories' => $exposure_categories,
        ]);
    }

    public function title(): string
    {
        $par_days = $this->filters['par_days'] ?? 30;
        return "Internal Analysis PAR {$par_days}";
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
                'color' => ['rgb' => '007bff'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'f8f9fa'],
            ],
        ]);

        // Summary section styling (rows 10-18)
        $sheet->getStyle('A10:F18')->applyFromArray([
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

        // Data table header styling (row 20)
        $sheet->getStyle('A20:' . $highestColumn . '20')->applyFromArray([
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

        // Data rows styling (from row 21 to end)
        if ($highestRow > 20) {
            $sheet->getStyle('A21:' . $highestColumn . $highestRow)->applyFromArray([
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
            $centerColumns = ['A', 'C', 'D', 'J', 'K']; // #, Customer No, Loan No, Overdue %, Days
            foreach ($centerColumns as $column) {
                $sheet->getStyle($column . '21:' . $column . $highestRow)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }

            // Right alignment for amount columns
            $rightColumns = ['G', 'H', 'I']; // Outstanding, Overdue, At Risk
            foreach ($rightColumns as $column) {
                $sheet->getStyle($column . '21:' . $column . $highestRow)->getAlignment()
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
        $sheet->freezePane('A21');

        return [];
    }
}
