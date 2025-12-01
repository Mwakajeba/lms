<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ExpectedVsCollectedExport implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $exportData = [];
        
        // Add header information
        $exportData[] = ['Expected vs Collected Report'];
        $exportData[] = ['Generated:', $this->data['generated_date']];
        $exportData[] = ['Period:', \Carbon\Carbon::parse($this->data['start_date'])->format('d-m-Y') . ' to ' . \Carbon\Carbon::parse($this->data['end_date'])->format('d-m-Y')];
        $exportData[] = ['Branch:', $this->data['branch_name']];
        $exportData[] = ['Group:', $this->data['group_name']];
        $exportData[] = ['Loan Officer:', $this->data['loan_officer_name']];
        $exportData[] = []; // Empty row
        
        // Add summary information if there's data
        if (!empty($this->data['report_data'])) {
            $totalExpected = array_sum(array_column($this->data['report_data'], 'expected_total'));
            $totalCollected = array_sum(array_column($this->data['report_data'], 'collected_total'));
            $totalVariance = array_sum(array_column($this->data['report_data'], 'variance'));
            $collectionRate = $totalExpected > 0 ? ($totalCollected / $totalExpected) * 100 : 0;
            
            $exportData[] = ['SUMMARY'];
            $exportData[] = ['Total Expected:', 'TZS ' . number_format($totalExpected, 2)];
            $exportData[] = ['Total Collected:', 'TZS ' . number_format($totalCollected, 2)];
            $exportData[] = ['Total Variance:', 'TZS ' . number_format($totalVariance, 2)];
            $exportData[] = ['Collection Rate:', number_format($collectionRate, 2) . '%'];
            $exportData[] = []; // Empty row
        }
        
        // Add data rows
        foreach ($this->data['report_data'] as $row) {
            $exportData[] = [
                $row['customer'],
                $row['customer_no'],
                $row['phone'],
                $row['loan_no'],
                $row['loan_amount'],
                $row['disbursed_date'],
                $row['branch'],
                $row['group'],
                $row['loan_officer'],
                $row['expected_principal'],
                $row['expected_interest'],
                $row['expected_fees'],
                $row['expected_penalty'],
                $row['expected_total'],
                $row['collected_principal'],
                $row['collected_interest'],
                $row['collected_fees'],
                $row['collected_penalty'],
                $row['collected_total'],
                $row['variance'],
                $row['collection_rate'] . '%',
                $row['collection_status']
            ];
        }

        return $exportData;
    }

    public function headings(): array
    {
        return [
            'Customer',
            'Customer No',
            'Phone',
            'Loan No',
            'Loan Amount',
            'Disbursed Date',
            'Branch',
            'Group',
            'Loan Officer',
            'Expected Principal',
            'Expected Interest',
            'Expected Fees',
            'Expected Penalty',
            'Expected Total',
            'Collected Principal',
            'Collected Interest',
            'Collected Fees',
            'Collected Penalty',
            'Collected Total',
            'Variance',
            'Collection Rate',
            'Status'
        ];
    }

    public function title(): string
    {
        return 'Expected vs Collected Report';
    }

    public function styles(Worksheet $sheet)
    {
        $headerRowNumber = count($this->data['report_data']) > 0 ? 13 : 8; // Adjust based on summary presence
        
        return [
            // Style the title
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 16,
                    'color' => ['rgb' => '000000']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ]
            ],
            
            // Style the summary section
            9 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => '000000']
                ]
            ],
            
            // Style the data headers
            $headerRowNumber => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ]
            ]
        ];
    }
}
