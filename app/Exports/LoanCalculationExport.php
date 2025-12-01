<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LoanCalculationExport implements FromArray, WithHeadings, WithStyles, WithTitle, WithColumnWidths
{
    protected $calculation;
    
    public function __construct($calculation)
    {
        $this->calculation = $calculation;
    }
    
    public function array(): array
    {
        if (!$this->calculation['success']) {
            return [
                ['Error', $this->calculation['error'] ?? 'Unknown error']
            ];
        }
        
        $data = [];
        
        // Summary section
        $data[] = ['LOAN CALCULATION SUMMARY'];
        $data[] = [];
        $data[] = ['Product', $this->calculation['product']['name']];
        $data[] = ['Product Type', ucfirst($this->calculation['product']['product_type'])];
        $data[] = ['Interest Method', ucfirst(str_replace('_', ' ', $this->calculation['product']['interest_method']))];
        $data[] = ['Interest Cycle', ucfirst($this->calculation['product']['interest_cycle'])];
        $data[] = ['Grace Period', $this->calculation['product']['grace_period'] . ' days'];
        $data[] = [];
        
        // Totals
        $totals = $this->calculation['totals'];
        $data[] = ['FINANCIAL SUMMARY'];
        $data[] = [];
        $data[] = ['Loan Amount', number_format($totals['principal'], 2)];
        $data[] = ['Total Interest', number_format($totals['total_interest'], 2)];
        $data[] = ['Total Fees', number_format($totals['total_fees'], 2)];
        $data[] = ['Total Amount', number_format($totals['total_amount'], 2)];
        $data[] = ['Monthly Payment', number_format($totals['monthly_payment'], 2)];
        $data[] = ['Interest Percentage', $this->calculation['summary']['interest_percentage'] . '%'];
        $data[] = [];
        
        // Fees breakdown
        if (!empty($this->calculation['fees'])) {
            $data[] = ['FEES BREAKDOWN'];
            $data[] = [];
            $data[] = ['Fee Name', 'Type', 'Amount', 'Application'];
            foreach ($this->calculation['fees'] as $fee) {
                $data[] = [
                    $fee['name'],
                    ucfirst($fee['type']),
                    number_format($fee['amount'], 2),
                    ucfirst(str_replace('_', ' ', $fee['criteria']))
                ];
            }
            $data[] = [];
        }
        
        // Repayment schedule
        $data[] = ['REPAYMENT SCHEDULE'];
        $data[] = [];
        $data[] = ['#', 'Due Date', 'Principal', 'Interest', 'Fees', 'Total', 'Balance'];
        
        foreach ($this->calculation['schedule'] as $installment) {
            $data[] = [
                $installment['installment_number'],
                \Carbon\Carbon::parse($installment['due_date'])->format('d/m/Y'),
                number_format($installment['principal'], 2),
                number_format($installment['interest'], 2),
                number_format($installment['fee_amount'], 2),
                number_format($installment['total_amount'], 2),
                number_format($installment['remaining_balance'] ?? 0, 2)
            ];
        }
        
        // Schedule totals
        $schedule = $this->calculation['schedule'];
        $data[] = [
            'TOTAL',
            '',
            number_format(array_sum(array_column($schedule, 'principal')), 2),
            number_format(array_sum(array_column($schedule, 'interest')), 2),
            number_format(array_sum(array_column($schedule, 'fee_amount')), 2),
            number_format(array_sum(array_column($schedule, 'total_amount')), 2),
            number_format(end($schedule)['remaining_balance'] ?? 0, 2)
        ];
        
        return $data;
    }
    
    public function headings(): array
    {
        return [];
    }
    
    public function title(): string
    {
        return 'Loan Calculation';
    }
    
    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 20,
            'C' => 15,
            'D' => 15,
            'E' => 15,
            'F' => 15,
            'G' => 15,
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold
            1 => ['font' => ['bold' => true, 'size' => 14]],
            // Style section headers
            'A1' => ['font' => ['bold' => true, 'size' => 16]],
            'A8' => ['font' => ['bold' => true, 'size' => 14]],
            'A15' => ['font' => ['bold' => true, 'size' => 14]],
            'A22' => ['font' => ['bold' => true, 'size' => 14]],
        ];
    }
}
