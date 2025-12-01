<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;


class RepaymentExport implements FromCollection, WithHeadings
{
    protected $repayments;

    public function __construct($repayments)
    {
        $this->repayments = $repayments;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->repayments->map(function ($repayment) {
            return [
                'Repayment Date' => Carbon::parse($repayment->payment_date)->format('Y-M-d'),
                'Amount Paid' => number_format($repayment->principal + $repayment->interest + $repayment->fees_amount+ $repayment->penalt_amount, 2),
                'Payment Method' => $repayment->payment_method ?? 'N/A',
                'Customer Name' => $repayment->loan->customer->name ?? 'N/A',
                'Loan No' => $repayment->loan->loanNo ?? '-',
                'Loan Product' => $repayment->loan->product->name ?? 'N/A',
                'Principal Paid' => number_format($repayment->principal, 2),
                'Interest Paid' => number_format($repayment->interest, 2),
                'Fees Paid' => number_format($repayment->fees_amount, 2),
                'Penalties Paid' => number_format($repayment->penalt_amount, 2),
                'Loan Balance' => number_format($repayment->loan->balance, 2),
                'Branch' => $repayment->loan->branch->name ?? 'N/A',
            ];
        });
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Repayment Date',
            'Amount Paid',
            'Payment Method',
            'Customer Name',
            'Loan No',
            'Loan Product',
            'Principal Paid',
            'Interest Paid',
            'Fees Paid',
            'Penalties Paid',
            'Loan Balance',
            'Branch',
        ];
    }
}
