<?php

namespace App\Exports;

use App\Models\Customer;
use Illuminate\Contracts\Support\Renderable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LoanImportTemplateExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    public function headings(): array
    {
        return [
            'customer_name',
            'customer_no',
            'amount',
            'period',
            'interest',
            'date_applied',
            'interest_cycle',
            'loan_officer_id',
            'group_id',
            'sector',
        ];
    }

    public function collection()
    {
        $branchId = auth()->user()->branch_id ?? null;
        $query = Customer::with(['groups:id'])->where('category', 'Borrower');
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }
        $customers = $query->get(['id', 'name', 'customerNo', 'branch_id']);

        $rows = collect();
        // Insert the note row as the first data row
        $rows->push([
            'N.B: delete first customer name before upload', '', '', '', '', '', '', '', '', ''
        ]);

        foreach ($customers as $customer) {
            $groupId = optional($customer->groups->first())->id ?? '';
            $rows->push([
                $customer->name,
                $customer->customerNo,
                '',
                '',
                '',
                '',
                'monthly',
                '',
                $groupId,
                '',
            ]);
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        // Make the first data row (row 2) bold and red only in column A
        $sheet->getStyle('A2')->getFont()->setBold(true)->getColor()->setRGB('FF0000');
        return [];
    }
}



