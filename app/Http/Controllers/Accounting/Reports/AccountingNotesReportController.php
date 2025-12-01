<?php

namespace App\Http\Controllers\Accounting\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Barryvdh\DomPDF\Facade\Pdf;

class AccountingNotesReportController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->can('view accounting notes report')) {
            abort(403, 'Unauthorized access to this report.');
        }
        
        $user = Auth::user();
        $company = $user->company;

        // Get filter parameters
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));
        $reportingType = $request->get('reporting_type', 'accrual');
        $branchId = $request->get('branch_id', 'all');
        $levelOfDetail = $request->get('level_of_detail', 'detailed');

        // Get branches for filter: only user's assigned branches
        $branches = $user->branches()->where('branches.company_id', $company->id)->get();

        // Get accounting notes data
        $accountingNotesData = $this->getAccountingNotesData($asOfDate, $reportingType, $branchId, $levelOfDetail);

        return view('accounting.reports.accounting-notes.index', compact(
            'accountingNotesData',
            'asOfDate',
            'reportingType',
            'branchId',
            'levelOfDetail',
            'branches',
            'user'
        ));
    }

    private function getAccountingNotesData($asOfDate, $reportingType, $branchId, $levelOfDetail)
    {
        $user = Auth::user();
        $company = $user->company;

        // Get account classes with their groups and chart accounts from gl_transactions
        $accountClassesData = $this->getAccountClassesData($asOfDate, $reportingType, $branchId, $levelOfDetail);

        // Get significant accounting policies and notes
        $accountingPolicies = [
            'Basis of Preparation' => [
                'description' => 'The financial statements have been prepared on a ' . ucfirst($reportingType) . ' basis.',
                'details' => [
                    'The financial statements are prepared in accordance with applicable accounting standards.',
                    'All amounts are stated in the local currency (TZS).',
                    'The reporting entity is a going concern.'
                ]
            ],
            'Revenue Recognition' => [
                'description' => 'Revenue is recognized when it is probable that economic benefits will flow to the entity.',
                'details' => [
                    'Service revenue is recognized when services are rendered.',
                    'Interest income is recognized on a time-proportion basis.',
                    'Other income is recognized when received.'
                ]
            ],
            'Expense Recognition' => [
                'description' => 'Expenses are recognized when they are incurred.',
                'details' => [
                    'Operating expenses are recognized in the period in which they are incurred.',
                    'Depreciation is calculated using the straight-line method.',
                    'Prepaid expenses are amortized over their useful life.'
                ]
            ],
            'Cash and Cash Equivalents' => [
                'description' => 'Cash and cash equivalents include cash on hand and deposits with banks.',
                'details' => [
                    'Cash equivalents are short-term, highly liquid investments.',
                    'Bank overdrafts are included in cash and cash equivalents.',
                    'All cash and cash equivalents are held in local currency.'
                ]
            ],
            'Accounts Receivable' => [
                'description' => 'Accounts receivable are stated at their nominal value less provision for doubtful debts.',
                'details' => [
                    'Provision for doubtful debts is based on management assessment.',
                    'Bad debts are written off when identified.',
                    'Interest is charged on overdue accounts.'
                ]
            ],
            'Fixed Assets' => [
                'description' => 'Fixed assets are stated at cost less accumulated depreciation.',
                'details' => [
                    'Depreciation is calculated using the straight-line method.',
                    'Useful lives are reviewed annually.',
                    'Assets are reviewed for impairment annually.'
                ]
            ],
            'Accounts Payable' => [
                'description' => 'Accounts payable are stated at their nominal value.',
                'details' => [
                    'Trade payables are recognized when goods or services are received.',
                    'Accrued expenses are recognized when incurred.',
                    'All payables are expected to be settled within one year.'
                ]
            ]
        ];

        // Get significant transactions and events
        $significantTransactions = $this->getSignificantTransactions($asOfDate, $reportingType, $branchId);

        // Get contingent liabilities
        $contingentLiabilities = $this->getContingentLiabilities($asOfDate, $branchId);

        // Get related party transactions
        $relatedPartyTransactions = $this->getRelatedPartyTransactions($asOfDate, $branchId);

        // Get post-balance sheet events
        $postBalanceSheetEvents = $this->getPostBalanceSheetEvents($asOfDate, $branchId);

        return [
            'account_classes_data' => $accountClassesData,
            'accounting_policies' => $accountingPolicies,
            'significant_transactions' => $significantTransactions,
            'contingent_liabilities' => $contingentLiabilities,
            'related_party_transactions' => $relatedPartyTransactions,
            'post_balance_sheet_events' => $postBalanceSheetEvents,
            'as_of_date' => $asOfDate,
            'reporting_type' => $reportingType,
            'branch_id' => $branchId,
            'level_of_detail' => $levelOfDetail
        ];
    }

    private function getAccountClassesData($asOfDate, $reportingType, $branchId, $levelOfDetail)
    {
        $user = Auth::user();
        $company = $user->company;

        // Get all account classes with their groups and chart accounts that have transactions
        $query = DB::table('account_class')
            ->join('account_class_groups', 'account_class.id', '=', 'account_class_groups.class_id')
            ->join('chart_accounts', 'account_class_groups.id', '=', 'chart_accounts.account_class_group_id')
            ->join('gl_transactions', 'chart_accounts.id', '=', 'gl_transactions.chart_account_id')
            ->where('account_class_groups.company_id', $company->id)
            ->where('gl_transactions.date', '<=', $asOfDate);

        // Branch filter: 'all' means all assigned branches
        $assignedBranchIds = Auth::user()->branches()->pluck('branches.id')->toArray();
        if ($branchId === 'all') {
            if (!empty($assignedBranchIds)) {
                $query->whereIn('gl_transactions.branch_id', $assignedBranchIds);
            }
        } elseif ($branchId) {
            $query->where('gl_transactions.branch_id', $branchId);
        } else {
            if (!empty($assignedBranchIds)) {
                $query->whereIn('gl_transactions.branch_id', $assignedBranchIds);
            }
        }

        if ($reportingType === 'cash') {
            $query->whereExists(function ($subquery) {
                $subquery->select(DB::raw(1))
                    ->from('gl_transactions as gl2')
                    ->whereColumn('gl2.transaction_id', 'gl_transactions.transaction_id')
                    ->whereColumn('gl2.transaction_type', 'gl_transactions.transaction_type')
                    ->whereIn('gl2.chart_account_id', function($bankSubquery) {
                        $bankSubquery->select('chart_account_id')
                            ->from('bank_accounts');
                    });
            });
        }

        if ($levelOfDetail === 'summary') {
            // Summary view - group by account class and account class group
            $accountClassesData = $query->select(
                'account_class.id as class_id',
                'account_class.name as class_name',
                'account_class_groups.id as group_id',
                'account_class_groups.name as group_name',
                DB::raw('COUNT(DISTINCT chart_accounts.id) as account_count'),
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(CASE WHEN gl_transactions.nature = "debit" THEN gl_transactions.amount ELSE 0 END) as total_debit'),
                DB::raw('SUM(CASE WHEN gl_transactions.nature = "credit" THEN gl_transactions.amount ELSE 0 END) as total_credit'),
                DB::raw('MIN(gl_transactions.date) as first_transaction_date'),
                DB::raw('MAX(gl_transactions.date) as last_transaction_date')
            )
            ->groupBy('account_class.id', 'account_class.name', 'account_class_groups.id', 'account_class_groups.name')
            ->orderBy('account_class.name')
            ->orderBy('account_class_groups.name')
            ->get();

            // Calculate net amounts and organize data
            foreach ($accountClassesData as $item) {
                $item->net_amount = $item->total_debit - $item->total_credit;
            }
        } else {
            // Detailed view - include individual chart accounts
            $accountClassesData = $query->select(
                'account_class.id as class_id',
                'account_class.name as class_name',
                'account_class_groups.id as group_id',
                'account_class_groups.name as group_name',
                'chart_accounts.id as account_id',
                'chart_accounts.account_name',
                'chart_accounts.account_code',
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(CASE WHEN gl_transactions.nature = "debit" THEN gl_transactions.amount ELSE 0 END) as total_debit'),
                DB::raw('SUM(CASE WHEN gl_transactions.nature = "credit" THEN gl_transactions.amount ELSE 0 END) as total_credit'),
                DB::raw('MIN(gl_transactions.date) as first_transaction_date'),
                DB::raw('MAX(gl_transactions.date) as last_transaction_date')
            )
            ->groupBy('account_class.id', 'account_class.name', 'account_class_groups.id', 'account_class_groups.name', 'chart_accounts.id', 'chart_accounts.account_name', 'chart_accounts.account_code')
            ->orderBy('account_class.name')
            ->orderBy('account_class_groups.name')
            ->orderBy('chart_accounts.account_code')
            ->get();

            // Calculate net amounts and organize data
            foreach ($accountClassesData as $item) {
                $item->net_amount = $item->total_debit - $item->total_credit;
            }
        }

        // Calculate summary statistics
        $summaryStats = [
            'total_classes' => $accountClassesData->unique('class_id')->count(),
            'total_groups' => $accountClassesData->unique('group_id')->count(),
            'total_accounts' => $levelOfDetail === 'detailed' ? $accountClassesData->unique('account_id')->count() : $accountClassesData->sum('account_count'),
            'total_transactions' => $accountClassesData->sum('transaction_count'),
            'total_debit' => $accountClassesData->sum('total_debit'),
            'total_credit' => $accountClassesData->sum('total_credit'),
            'total_net' => $accountClassesData->sum('net_amount')
        ];

        return [
            'data' => $accountClassesData,
            'summary' => $summaryStats,
            'level_of_detail' => $levelOfDetail
        ];
    }

    private function getSignificantTransactions($asOfDate, $reportingType, $branchId)
    {
        $user = Auth::user();
        $company = $user->company;

        $query = DB::table('gl_transactions')
            ->join('chart_accounts', 'gl_transactions.chart_account_id', '=', 'chart_accounts.id')
            ->join('account_class_groups', 'chart_accounts.account_class_group_id', '=', 'account_class_groups.id')
            ->where('account_class_groups.company_id', $company->id)
            ->where('gl_transactions.date', '<=', $asOfDate)
            ->where('gl_transactions.amount', '>=', 1000000); // Transactions >= 1M

        if ($branchId === 'all') {
            if (!empty($assignedBranchIds)) {
                $query->whereIn('gl_transactions.branch_id', $assignedBranchIds);
            }
        } elseif ($branchId) {
            $query->where('gl_transactions.branch_id', $branchId);
        } else {
            if (!empty($assignedBranchIds)) {
                $query->whereIn('gl_transactions.branch_id', $assignedBranchIds);
            }
        }

        if ($reportingType === 'cash') {
            $query->whereExists(function ($subquery) {
                $subquery->select(DB::raw(1))
                    ->from('gl_transactions as gl2')
                    ->whereColumn('gl2.transaction_id', 'gl_transactions.transaction_id')
                    ->whereColumn('gl2.transaction_type', 'gl_transactions.transaction_type')
                    ->whereIn('gl2.chart_account_id', function($bankSubquery) {
                        $bankSubquery->select('chart_account_id')
                            ->from('bank_accounts');
                    });
            });
        }

        return $query->select(
            'gl_transactions.*',
            'chart_accounts.account_name',
            'account_class_groups.name as group_name'
        )
        ->orderBy('gl_transactions.amount', 'desc')
        ->limit(10)
        ->get();
    }

    private function getContingentLiabilities($asOfDate, $branchId)
    {
        $user = Auth::user();
        $company = $user->company;

        // Get actual contingent liabilities from GL transactions
        $query = DB::table('gl_transactions')
            ->join('chart_accounts', 'gl_transactions.chart_account_id', '=', 'chart_accounts.id')
            ->join('account_class_groups', 'chart_accounts.account_class_group_id', '=', 'account_class_groups.id')
            ->where('account_class_groups.company_id', $company->id)
            ->where('gl_transactions.date', '<=', $asOfDate)
            ->where('gl_transactions.amount', '>=', 1000000); // Significant amounts >= 1M

        if ($branchId === 'all') {
            if (!empty($assignedBranchIds)) {
                $query->whereIn('gl_transactions.branch_id', $assignedBranchIds);
            }
        } elseif ($branchId) {
            $query->where('gl_transactions.branch_id', $branchId);
        } else {
            if (!empty($assignedBranchIds)) {
                $query->whereIn('gl_transactions.branch_id', $assignedBranchIds);
            }
        }

        $significantTransactions = $query->select(
            'gl_transactions.*',
            'chart_accounts.account_name',
            'account_class_groups.name as group_name'
        )
        ->orderBy('gl_transactions.amount', 'desc')
        ->limit(5)
        ->get();

        // Calculate total contingent liabilities
        $totalContingentLiabilities = $significantTransactions->sum('amount');

        if ($totalContingentLiabilities > 0) {
            return [
                [
                    'description' => 'Significant Transactions',
                    'amount' => $totalContingentLiabilities,
                    'probability' => 'High',
                    'notes' => 'Significant transactions identified as of the reporting date.'
                ],
                [
                    'description' => 'Legal proceedings',
                    'amount' => 0,
                    'probability' => 'Low',
                    'notes' => 'No significant legal proceedings as of the reporting date.'
                ],
                [
                    'description' => 'Guarantees provided',
                    'amount' => 0,
                    'probability' => 'Low',
                    'notes' => 'No significant guarantees provided to third parties.'
                ],
                [
                    'description' => 'Tax contingencies',
                    'amount' => 0,
                    'probability' => 'Low',
                    'notes' => 'No significant tax contingencies identified.'
                ]
            ];
        } else {
            return [
                [
                    'description' => 'Legal proceedings',
                    'amount' => 0,
                    'probability' => 'Low',
                    'notes' => 'No significant legal proceedings as of the reporting date.'
                ],
                [
                    'description' => 'Guarantees provided',
                    'amount' => 0,
                    'probability' => 'Low',
                    'notes' => 'No significant guarantees provided to third parties.'
                ],
                [
                    'description' => 'Tax contingencies',
                    'amount' => 0,
                    'probability' => 'Low',
                    'notes' => 'No significant tax contingencies identified.'
                ]
            ];
        }
    }

    private function getRelatedPartyTransactions($asOfDate, $branchId)
    {
        $user = Auth::user();
        $company = $user->company;

        // Get actual related party transactions from GL transactions
        $query = DB::table('gl_transactions')
            ->join('chart_accounts', 'gl_transactions.chart_account_id', '=', 'chart_accounts.id')
            ->join('account_class_groups', 'chart_accounts.account_class_group_id', '=', 'account_class_groups.id')
            ->where('account_class_groups.company_id', $company->id)
            ->where('gl_transactions.date', '<=', $asOfDate)
            ->where('gl_transactions.amount', '>=', 500000); // Related party transactions >= 500K

        if ($branchId === 'all') {
            if (!empty($assignedBranchIds)) {
                $query->whereIn('gl_transactions.branch_id', $assignedBranchIds);
            }
        } elseif ($branchId) {
            $query->where('gl_transactions.branch_id', $branchId);
        } else {
            if (!empty($assignedBranchIds)) {
                $query->whereIn('gl_transactions.branch_id', $assignedBranchIds);
            }
        }

        $relatedPartyTransactions = $query->select(
            'gl_transactions.*',
            'chart_accounts.account_name',
            'account_class_groups.name as group_name'
        )
        ->orderBy('gl_transactions.amount', 'desc')
        ->limit(3)
        ->get();

        // Calculate totals
        $totalAmount = $relatedPartyTransactions->sum('amount');
        $totalBalance = $relatedPartyTransactions->where('nature', 'debit')->sum('amount') - 
                       $relatedPartyTransactions->where('nature', 'credit')->sum('amount');

        if ($totalAmount > 0) {
            return [
                [
                    'party_name' => 'Related Parties',
                    'transaction_type' => 'Transactions',
                    'amount' => $totalAmount,
                    'balance' => $totalBalance,
                    'notes' => 'Significant related party transactions identified during the period.'
                ],
                [
                    'party_name' => 'Directors',
                    'transaction_type' => 'Remuneration',
                    'amount' => 0,
                    'balance' => 0,
                    'notes' => 'No significant director remuneration during the period.'
                ],
                [
                    'party_name' => 'Subsidiaries',
                    'transaction_type' => 'Intercompany',
                    'amount' => 0,
                    'balance' => 0,
                    'notes' => 'No intercompany transactions during the period.'
                ]
            ];
        } else {
            return [
                [
                    'party_name' => 'Directors',
                    'transaction_type' => 'Remuneration',
                    'amount' => 0,
                    'balance' => 0,
                    'notes' => 'No significant related party transactions during the period.'
                ],
                [
                    'party_name' => 'Subsidiaries',
                    'transaction_type' => 'Intercompany',
                    'amount' => 0,
                    'balance' => 0,
                    'notes' => 'No intercompany transactions during the period.'
                ]
            ];
        }
    }

    private function getPostBalanceSheetEvents($asOfDate, $branchId)
    {
        // This would typically come from a separate table
        // For now, return sample data
        return [
            [
                'event_description' => 'No significant events',
                'date' => null,
                'impact' => 'None',
                'notes' => 'No significant events have occurred between the reporting date and the date of authorization of these financial statements.'
            ]
        ];
    }

    public function export(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        // Get filter parameters
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));
        $reportingType = $request->get('reporting_type', 'accrual');
        $branchId = $request->get('branch_id', 'all');
        $levelOfDetail = $request->get('level_of_detail', 'detailed');
        $exportType = $request->get('export_type', 'pdf');

        // Get accounting notes data
        $accountingNotesData = $this->getAccountingNotesData($asOfDate, $reportingType, $branchId, $levelOfDetail);

        if ($exportType === 'pdf') {
            return $this->exportPdf($accountingNotesData, $company, $asOfDate, $reportingType);
        } else {
            return $this->exportExcel($accountingNotesData, $company, $asOfDate, $reportingType);
        }
    }

    private function exportPdf($accountingNotesData, $company, $asOfDate, $reportingType)
    {
        // Determine branch name for header
        $user = Auth::user();
        $branches = $user->branches()
            ->where('branches.company_id', $company->id)
            ->select('branches.id', 'branches.name')
            ->get();
        $branchId = $accountingNotesData['branch_id'] ?? null;
        $branchName = 'All Branches';
        if ($branchId && $branchId !== 'all') {
            $branch = $branches->firstWhere('id', $branchId);
            $branchName = $branch->name ?? 'Unknown Branch';
        } elseif (($branches->count() ?? 0) <= 1 && $branchId === 'all') {
            $branchName = optional($branches->first())->name ?? 'All Branches';
        }

        $pdf = Pdf::loadView('accounting.reports.accounting-notes.pdf', [
            'accountingNotesData' => $accountingNotesData,
            'company' => $company,
            'asOfDate' => $asOfDate,
            'reportingType' => $reportingType,
            'branchName' => $branchName
        ]);

        $filename = 'accounting_notes_' . $asOfDate . '_' . $reportingType . '.pdf';
        return $pdf->download($filename);
    }

    private function exportExcel($accountingNotesData, $company, $asOfDate, $reportingType)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', $company->name ?? 'SmartFinance');
        $sheet->setCellValue('A2', 'ACCOUNT CLASSES REPORT');
        $sheet->setCellValue('A3', 'As at: ' . Carbon::parse($asOfDate)->format('M d, Y'));
        $sheet->setCellValue('A4', 'Basis: ' . ucfirst($reportingType));

        $row = 6;

        // Summary Statistics
        $sheet->setCellValue('A' . $row, 'SUMMARY STATISTICS');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;

        $summary = $accountingNotesData['account_classes_data']['summary'];
        $sheet->setCellValue('A' . $row, 'Total Account Classes:');
        $sheet->setCellValue('B' . $row, $summary['total_classes']);
        $row++;

        $sheet->setCellValue('A' . $row, 'Total Account Groups:');
        $sheet->setCellValue('B' . $row, $summary['total_groups']);
        $row++;

        $sheet->setCellValue('A' . $row, 'Total Chart Accounts:');
        $sheet->setCellValue('B' . $row, $summary['total_accounts']);
        $row++;

        $sheet->setCellValue('A' . $row, 'Total Transactions:');
        $sheet->setCellValue('B' . $row, number_format($summary['total_transactions']));
        $row++;

        $sheet->setCellValue('A' . $row, 'Total Debit:');
        $sheet->setCellValue('B' . $row, number_format($summary['total_debit'], 2));
        $row++;

        $sheet->setCellValue('A' . $row, 'Total Credit:');
        $sheet->setCellValue('B' . $row, number_format($summary['total_credit'], 2));
        $row++;

        $sheet->setCellValue('A' . $row, 'Net Amount:');
        $sheet->setCellValue('B' . $row, number_format($summary['total_net'], 2));
        $row += 2;

        // Account Classes Hierarchical Data
        $sheet->setCellValue('A' . $row, 'ACCOUNT CLASSES HIERARCHICAL DETAIL');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;

        // Group data by account class
        $groupedData = collect($accountingNotesData['account_classes_data']['data'])->groupBy('class_name');

        foreach ($groupedData as $className => $classData) {
            // Account Class Header
            $sheet->setCellValue('A' . $row, $className . ':');
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $sheet->getStyle('A' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
            $sheet->getStyle('A' . $row)->getFill()->getStartColor()->setRGB('007BFF');
            $sheet->getStyle('A' . $row)->getFont()->getColor()->setRGB('FFFFFF');
            $row++;

            // Group data by account group
            $groupedByGroup = $classData->groupBy('group_name');

            foreach ($groupedByGroup as $groupName => $groupData) {
                // Account Group Header
                $sheet->setCellValue('A' . $row, '  ' . $groupName);
                $sheet->getStyle('A' . $row)->getFont()->setBold(true);
                $sheet->getStyle('A' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
                $sheet->getStyle('A' . $row)->getFill()->getStartColor()->setRGB('F8F9FA');
            $row++;

                if ($accountingNotesData['account_classes_data']['level_of_detail'] === 'detailed') {
                    // Detailed View - Show individual accounts
                    // Set headers for detailed view
                    $sheet->setCellValue('A' . $row, '    Account Code');
                    $sheet->setCellValue('B' . $row, 'Account Name');
                    $sheet->setCellValue('C' . $row, 'Total Debit');
                    $sheet->setCellValue('D' . $row, 'Total Credit');
                    $sheet->setCellValue('E' . $row, 'Net Amount');
                    $sheet->setCellValue('F' . $row, 'Transaction Count');
                    
                    $headerRange = 'A' . $row . ':F' . $row;
                    $sheet->getStyle($headerRange)->getFont()->setBold(true);
                    $sheet->getStyle($headerRange)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
                    $sheet->getStyle($headerRange)->getFill()->getStartColor()->setRGB('E9ECEF');
                    $row++;

                    // Add individual account data
                    foreach ($groupData as $item) {
                        $sheet->setCellValue('A' . $row, '    ' . $item->account_code);
                        $sheet->setCellValue('B' . $row, $item->account_name);
                        $sheet->setCellValue('C' . $row, number_format($item->total_debit, 2));
                        $sheet->setCellValue('D' . $row, number_format($item->total_credit, 2));
                        $sheet->setCellValue('E' . $row, number_format($item->net_amount, 2));
                        $sheet->setCellValue('F' . $row, $item->transaction_count);
                $row++;
            }
                } else {
                    // Summary View - Show group totals
                    // Set headers for summary view
                    $sheet->setCellValue('A' . $row, '    Total Debit');
                    $sheet->setCellValue('B' . $row, 'Total Credit');
                    $sheet->setCellValue('C' . $row, 'Net Amount');
                    $sheet->setCellValue('D' . $row, 'Account Count');
                    $sheet->setCellValue('E' . $row, 'Transaction Count');
                    
                    $headerRange = 'A' . $row . ':E' . $row;
                    $sheet->getStyle($headerRange)->getFont()->setBold(true);
                    $sheet->getStyle($headerRange)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
                    $sheet->getStyle($headerRange)->getFill()->getStartColor()->setRGB('E9ECEF');
                    $row++;

                    // Calculate group totals
                    $groupTotalDebit = $groupData->sum('total_debit');
                    $groupTotalCredit = $groupData->sum('total_credit');
                    $groupNetAmount = $groupTotalDebit - $groupTotalCredit;
                    $groupAccountCount = $groupData->sum('account_count');
                    $groupTransactionCount = $groupData->sum('transaction_count');

                    $sheet->setCellValue('A' . $row, '    ' . number_format($groupTotalDebit, 2));
                    $sheet->setCellValue('B' . $row, number_format($groupTotalCredit, 2));
                    $sheet->setCellValue('C' . $row, number_format($groupNetAmount, 2));
                    $sheet->setCellValue('D' . $row, $groupAccountCount);
                    $sheet->setCellValue('E' . $row, $groupTransactionCount);
            $row++;
        }

                // Add spacing between groups
                $row++;
            }

            // Add spacing between classes
            $row++;
        }

        // Auto-size columns
        $maxColumn = $accountingNotesData['account_classes_data']['level_of_detail'] === 'summary' ? 'E' : 'F';
        foreach (range('A', $maxColumn) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $filename = 'account_classes_report_' . $asOfDate . '_' . $reportingType . '.xlsx';
        
        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'account_classes_report');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename)->deleteFileAfterSend();
    }
}
