<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DisbursementsExport implements FromCollection, WithHeadings
{
    protected $disbursements;

    public function __construct($disbursements)
    {
        $this->disbursements = $disbursements;
    }

    public function collection()
    {
        return $this->disbursements->map(function ($loan) {
            return [
                'A/C NO.' => ($loan->customer->customerNo ?? '-') . ' - ' . ($loan->loanNo ?? '-'),
                'Disbursement Date' => \Carbon\Carbon::parse($loan->disbursed_on)->format('Y M d'),
                'Period' => $loan->period . ' Months',
                'Customer Name' => $loan->customer->name ?? 'N/A',
                'Regstra Name' => $loan->loanOfficer->name ?? 'N/A',
                'REF NO' => $loan->loanNo ?? '-',
                'Customer NO' => $loan->customer->customerNo ?? '-',
                'Application Date' => \Carbon\Carbon::parse($loan->date_applied)->format('Y M d'),
                'Loan Product' => $loan->product->name ?? 'N/A',
                'Disbursed Amount' => number_format($loan->amount, 2),
                'Amount To Pay' => number_format($loan->amount_total, 2),
                'Branch' => $loan->branch->name ?? 'N/A',
                'Interest Amount' => number_format($loan->interest_amount, 2),
                'END DATE' => \Carbon\Carbon::parse($loan->last_repayment_date)->format('Y M d'),
            ];
        });
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'A/C NO.',
            'Disbursement Date',
            'Period', 
            'Regstra Name',
            'Customer Name',
            'REF NO',
            'Customer NO',
            'Application Date',
            'Loan Product',
            'Disbursed Amount',
            'Amount To Pay',
            'Branch',
             'Interest Amount',
             'END DATE',
        ];
    }
}
