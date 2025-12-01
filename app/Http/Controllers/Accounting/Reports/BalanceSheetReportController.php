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

class BalanceSheetReportController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->can('view balance sheet report')) {
            abort(403, 'Unauthorized access to this report.');
        }
        
        $user = auth()->user();
        $company = $user->company;

        $asOf = $request->input('as_of', Carbon::today()->toDateString());
        $comparativeAsOf = $request->input('comparative_as_of', Carbon::parse($asOf)->copy()->subYear()->toDateString());
        $comparatives = (array) $request->input('comparatives', []);
        $branchId = $request->input('branch_id');
        $reportingType = $request->input('reporting_type', 'accrual'); // accrual|cash
        $viewType = strtolower($request->input('view_type', 'detailed')); // summary|detailed

        // Branch scope
        $assignedBranchIds = $user->branches()
            ->where('branches.company_id', $company->id)
            ->pluck('branches.id')
            ->toArray();

        // Base transactions until as_of date (inclusive)
        $base = DB::table('gl_transactions')
            ->join('chart_accounts', 'gl_transactions.chart_account_id', '=', 'chart_accounts.id')
            ->join('account_class_groups', 'chart_accounts.account_class_group_id', '=', 'account_class_groups.id')
            ->join('account_class', 'account_class_groups.class_id', '=', 'account_class.id')
            ->where('account_class_groups.company_id', $company->id)
            ->whereDate('gl_transactions.date', '<=', $asOf);

        // Branch filter
        if ($branchId && $branchId !== 'all') {
            $base->where('gl_transactions.branch_id', $branchId);
        } else {
            $base->whereIn('gl_transactions.branch_id', $assignedBranchIds);
        }

        // Reporting type (cash basis hook)
        if ($reportingType === 'cash') {
            // Example: $base->where('gl_transactions.is_cash', 1);
        }

        // Summary by account class
        $summary = (clone $base)
            ->select(
                'account_class.id as class_id',
                'account_class.name as class_name',
                DB::raw('SUM(CASE WHEN gl_transactions.nature = "debit" THEN gl_transactions.amount ELSE 0 END) as total_debit'),
                DB::raw('SUM(CASE WHEN gl_transactions.nature = "credit" THEN gl_transactions.amount ELSE 0 END) as total_credit')
            )
            ->groupBy('account_class.id', 'account_class.name')
            ->get()
            ->map(function ($row) {
                $class = strtolower($row->class_name);
                switch ($class) {
                    case 'assets':
                        $balance = $row->total_debit - $row->total_credit;
                        break;
                    case 'liabilities':
                    case 'equity':
                    case 'income':
                    case 'revenue':
                        $balance = $row->total_credit - $row->total_debit;
                        break;
                    case 'expenses':
                    case 'expense':
                        $balance = $row->total_debit - $row->total_credit;
                        break;
                    default:
                        $balance = $row->total_debit - $row->total_credit;
                        break;
                }
                return [
                    'class_id' => $row->class_id,
                    'class_name' => $row->class_name,
                    'balance' => $balance,
                ];
            });

        // Compute high-level totals and P&L
        $assetsTotal = (float) (collect($summary)->firstWhere('class_name', 'Assets')['balance'] ?? 0);
        $liabilitiesTotal = (float) (collect($summary)->firstWhere('class_name', 'Liabilities')['balance'] ?? 0);
        $equityTotal = (float) (collect($summary)->firstWhere('class_name', 'Equity')['balance'] ?? 0);

        // Sum both naming variants (Revenue/Income and Expenses/Expense)
        $revenueTotal = (float) (collect($summary)->firstWhere('class_name', 'Revenue')['balance'] ?? 0)
            + (float) (collect($summary)->firstWhere('class_name', 'Income')['balance'] ?? 0);

        $expenseTotal = (float) (collect($summary)->firstWhere('class_name', 'Expenses')['balance'] ?? 0)
            + (float) (collect($summary)->firstWhere('class_name', 'Expense')['balance'] ?? 0);

        $profitLoss = $revenueTotal - $expenseTotal;

        // Add profit/loss into equity
        $equityTotal = $equityTotal + $profitLoss;

        // Ensure Liabilities always exist
        if (!collect($summary)->firstWhere('class_name', 'Liabilities')) {
            $summary->push((object)[
                'class_id' => null,
                'class_name' => 'Liabilities',
                'balance' => 0,
            ]);
        }

        // Detailed per account (always build for reliable exports)
        $detailed = [];
        $groupTotals = [];
        $comparativeGroupTotals = [];

        // Build per-class, per-group totals for current period (used by Summary and Detailed)
        $groupRows = (clone $base)
            ->select(
                'account_class.name as class_name',
                'account_class_groups.name as group_name',
                DB::raw('SUM(CASE WHEN gl_transactions.nature = "debit" THEN gl_transactions.amount ELSE 0 END) as total_debit'),
                DB::raw('SUM(CASE WHEN gl_transactions.nature = "credit" THEN gl_transactions.amount ELSE 0 END) as total_credit')
            )
            ->groupBy('account_class.name', 'account_class_groups.name')
            ->get();

        foreach ($groupRows as $gr) {
            $class = strtolower($gr->class_name);
            switch ($class) {
                case 'assets':
                    $bal = $gr->total_debit - $gr->total_credit;
                    break;
                case 'liabilities':
                case 'equity':
                case 'income':
                case 'revenue':
                    $bal = $gr->total_credit - $gr->total_debit;
                    break;
                case 'expenses':
                case 'expense':
                    $bal = $gr->total_debit - $gr->total_credit;
                    break;
                default:
                    $bal = $gr->total_debit - $gr->total_credit;
                    break;
            }
            $groupTotals[$gr->class_name][$gr->group_name] = ($groupTotals[$gr->class_name][$gr->group_name] ?? 0) + $bal;
        }
        if ($viewType === 'detailed') {
            $rows = (clone $base)
                ->select(
                    'chart_accounts.id as account_id',
                    'chart_accounts.account_name',
                    'account_class_groups.name as group_name',
                    'account_class.name as class_name',
                    DB::raw('SUM(CASE WHEN gl_transactions.nature = "debit" THEN gl_transactions.amount ELSE 0 END) as total_debit'),
                    DB::raw('SUM(CASE WHEN gl_transactions.nature = "credit" THEN gl_transactions.amount ELSE 0 END) as total_credit')
                )
                ->groupBy('chart_accounts.id', 'chart_accounts.account_name', 'account_class_groups.name', 'account_class.name')
                ->get();

            foreach ($rows as $r) {
                $class = strtolower($r->class_name);
                switch ($class) {
                    case 'assets':
                        $balance = $r->total_debit - $r->total_credit;
                        break;
                    case 'liabilities':
                    case 'equity':
                    case 'income':
                    case 'revenue':
                        $balance = $r->total_credit - $r->total_debit;
                        break;
                    case 'expenses':
                    case 'expense':
                        $balance = $r->total_debit - $r->total_credit;
                        break;
                    default:
                        $balance = $r->total_debit - $r->total_credit;
                        break;
                }

                $detailed[$r->class_name]['groups'][$r->group_name]['accounts'][] = [
                    'account_id' => $r->account_id,
                    'account_name' => $r->account_name,
                    'balance' => $balance,
                ];

                // Subtotals
                if (!isset($detailed[$r->class_name]['groups'][$r->group_name]['total'])) {
                    $detailed[$r->class_name]['groups'][$r->group_name]['total'] = 0;
                }
                $detailed[$r->class_name]['groups'][$r->group_name]['total'] += $balance;

                if (!isset($detailed[$r->class_name]['total'])) {
                    $detailed[$r->class_name]['total'] = 0;
                }
                $detailed[$r->class_name]['total'] += $balance;
            }

            // no-op: comparative group totals are built below for all views
        }

        // Build comparative group totals per date (class -> group -> total) for all views
        $allComparativeDatesForGroups = [];
        if (!empty($comparativeAsOf)) { $allComparativeDatesForGroups[] = $comparativeAsOf; }
        foreach ($comparatives as $c) { if (!empty($c)) { $allComparativeDatesForGroups[] = $c; } }
        $allComparativeDatesForGroups = array_values(array_unique($allComparativeDatesForGroups));

        foreach ($allComparativeDatesForGroups as $compDate) {
            $cmpQuery = DB::table('gl_transactions')
                ->join('chart_accounts', 'gl_transactions.chart_account_id', '=', 'chart_accounts.id')
                ->join('account_class_groups', 'chart_accounts.account_class_group_id', '=', 'account_class_groups.id')
                ->join('account_class', 'account_class_groups.class_id', '=', 'account_class.id')
                ->where('account_class_groups.company_id', $company->id)
                ->whereDate('gl_transactions.date', '<=', $compDate);

            if ($branchId && $branchId !== 'all') {
                $cmpQuery->where('gl_transactions.branch_id', $branchId);
            } else {
                $cmpQuery->whereIn('gl_transactions.branch_id', $assignedBranchIds);
            }

            $cmpRows = $cmpQuery
                ->select(
                    'account_class.name as class_name',
                    'account_class_groups.name as group_name',
                    DB::raw('SUM(CASE WHEN gl_transactions.nature = "debit" THEN gl_transactions.amount ELSE 0 END) as total_debit'),
                    DB::raw('SUM(CASE WHEN gl_transactions.nature = "credit" THEN gl_transactions.amount ELSE 0 END) as total_credit')
                )
                ->groupBy('account_class.name', 'account_class_groups.name')
                ->get();

            foreach ($cmpRows as $cr) {
                $class = strtolower($cr->class_name);
                switch ($class) {
                    case 'assets':
                        $bal = $cr->total_debit - $cr->total_credit;
                        break;
                    case 'liabilities':
                    case 'equity':
                    case 'income':
                    case 'revenue':
                        $bal = $cr->total_credit - $cr->total_debit;
                        break;
                    case 'expenses':
                    case 'expense':
                        $bal = $cr->total_debit - $cr->total_credit;
                        break;
                    default:
                        $bal = $cr->total_debit - $cr->total_credit;
                        break;
                }
                $comparativeGroupTotals[$compDate][$cr->class_name][$cr->group_name] = ($comparativeGroupTotals[$compDate][$cr->class_name][$cr->group_name] ?? 0) + $bal;
            }
        }

        // Build full list of comparative dates (ensure the single comparative is included)
        $comparativesData = [];
        $allComparativeDates = [];
        if (!empty($comparativeAsOf)) {
            $allComparativeDates[] = $comparativeAsOf;
        }
        foreach ($comparatives as $c) { if (!empty($c)) { $allComparativeDates[] = $c; } }
        $allComparativeDates = array_values(array_unique($allComparativeDates));

        if (!empty($allComparativeDates)) {
            foreach ($allComparativeDates as $compDate) {
                if (empty($compDate)) { continue; }

                $baseCmp2 = DB::table('gl_transactions')
                    ->join('chart_accounts', 'gl_transactions.chart_account_id', '=', 'chart_accounts.id')
                    ->join('account_class_groups', 'chart_accounts.account_class_group_id', '=', 'account_class_groups.id')
                    ->join('account_class', 'account_class_groups.class_id', '=', 'account_class.id')
                    ->where('account_class_groups.company_id', $company->id)
                    ->whereDate('gl_transactions.date', '<=', $compDate);

                if ($branchId && $branchId !== 'all') {
                    $baseCmp2->where('gl_transactions.branch_id', $branchId);
                } else {
                    $baseCmp2->whereIn('gl_transactions.branch_id', $assignedBranchIds);
                }

                $summary2 = (clone $baseCmp2)
                    ->select(
                        'account_class.id as class_id',
                        'account_class.name as class_name',
                        DB::raw('SUM(CASE WHEN gl_transactions.nature = "debit" THEN gl_transactions.amount ELSE 0 END) as total_debit'),
                        DB::raw('SUM(CASE WHEN gl_transactions.nature = "credit" THEN gl_transactions.amount ELSE 0 END) as total_credit')
                    )
                    ->groupBy('account_class.id', 'account_class.name')
                    ->get()
                    ->map(function ($row) {
                        $class = strtolower($row->class_name);
                        switch ($class) {
                            case 'assets':
                                $balance = $row->total_debit - $row->total_credit;
                                break;
                            case 'liabilities':
                            case 'equity':
                            case 'income':
                            case 'revenue':
                                $balance = $row->total_credit - $row->total_debit;
                                break;
                            case 'expenses':
                            case 'expense':
                                $balance = $row->total_debit - $row->total_credit;
                                break;
                            default:
                                $balance = $row->total_debit - $row->total_credit;
                                break;
                        }
                        return [
                            'class_id' => $row->class_id,
                            'class_name' => $row->class_name,
                            'balance' => $balance,
                        ];
                    });

                $assetsTotal2 = (float) (collect($summary2)->firstWhere('class_name', 'Assets')['balance'] ?? 0);
                $liabilitiesTotal2 = (float) (collect($summary2)->firstWhere('class_name', 'Liabilities')['balance'] ?? 0);
                $equityTotal2 = (float) (collect($summary2)->firstWhere('class_name', 'Equity')['balance'] ?? 0);
                $revenueTotal2 = (float) (collect($summary2)->firstWhere('class_name', 'Revenue')['balance'] ?? 0)
                    + (float) (collect($summary2)->firstWhere('class_name', 'Income')['balance'] ?? 0);
                $expenseTotal2 = (float) (collect($summary2)->firstWhere('class_name', 'Expenses')['balance'] ?? 0)
                    + (float) (collect($summary2)->firstWhere('class_name', 'Expense')['balance'] ?? 0);
                $profitLoss2 = $revenueTotal2 - $expenseTotal2;
                $equityTotal2 = $equityTotal2 + $profitLoss2;

                $comparativesData[] = [
                    'date' => $compDate,
                    'assetsTotal' => $assetsTotal2,
                    'liabilitiesTotal' => $liabilitiesTotal2,
                    'equityTotal' => $equityTotal2,
                    'profitLoss' => $profitLoss2,
                ];
            }
        }

        return view('accounting.reports.balance-sheet.index', [
            'summary' => $summary,
            'detailed' => $detailed,
            'viewType' => $viewType,
            'asOf' => $asOf,
            'branchId' => $branchId,
            'reportingType' => $reportingType,
            'assetsTotal' => $assetsTotal,
            'liabilitiesTotal' => $liabilitiesTotal,
            'equityTotal' => $equityTotal,
            'profitLoss' => $profitLoss,
            'comparativesData' => $comparativesData,
            'comparativeAsOf' => $comparativeAsOf,
            'comparativeGroupTotals' => $comparativeGroupTotals,
            'groupTotals' => $groupTotals,
        ]);
    }

    public function export(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        // Get filter parameters (same as index method)
        $asOf = $request->input('as_of', Carbon::today()->toDateString());
        $comparativeAsOf = $request->input('comparative_as_of', Carbon::parse($asOf)->copy()->subYear()->toDateString());
        $comparatives = (array) $request->input('comparatives', []);
        $branchId = $request->input('branch_id');
        $reportingType = $request->input('reporting_type', 'accrual');
        $viewType = strtolower($request->input('view_type', 'detailed'));
        $exportType = $request->input('export_type', 'pdf');

        // Get the same data as index method
        $data = $this->getBalanceSheetData($asOf, $comparativeAsOf, $comparatives, $branchId, $reportingType, $viewType, $user, $company);

        if ($exportType === 'pdf') {
            return $this->exportPdf($data, $company, $asOf, $comparativeAsOf, $comparatives, $viewType);
        } else {
            return $this->exportExcel($data, $company, $asOf, $comparativeAsOf, $comparatives, $viewType);
        }
    }

    private function exportPdf($data, $company, $asOf, $comparativeAsOf, $comparatives, $viewType)
    {
        $resolvedViewType = $data['viewType'] ?? $viewType;
        $pdf = Pdf::loadView('accounting.reports.balance-sheet.pdf', [
            'data' => $data,
            'company' => $company,
            'asOf' => $asOf,
            'comparativeAsOf' => $comparativeAsOf,
            'comparatives' => $comparatives,
            'viewType' => $resolvedViewType
        ]);

        $filename = 'balance_sheet_' . $asOf . '_' . $resolvedViewType . '.pdf';
        return $pdf->download($filename);
    }

    private function exportExcel($data, $company, $asOf, $comparativeAsOf, $comparatives, $viewType)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', $company->name ?? 'SmartFinance');
        $sheet->setCellValue('A2', 'BALANCE SHEET');
        $sheet->setCellValue('A3', 'As of: ' . Carbon::parse($asOf)->format('M d, Y'));
        if ($comparativeAsOf) {
            $sheet->setCellValue('A4', 'Comparative: ' . Carbon::parse($comparativeAsOf)->format('M d, Y'));
        }

        $row = 6;

        $resolvedViewType = $data['viewType'] ?? $viewType;
        if ($resolvedViewType === 'summary') {
            // Summary format with dynamic groups
            // ASSETS and groups
            $sheet->setCellValue('A' . $row, 'ASSETS');
            $sheet->setCellValue('B' . $row, number_format($data['assetsTotal'], 2));
            $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);
            $row++;
            foreach (($data['groupTotals']['Assets'] ?? []) as $groupName => $gTotal) {
                $sheet->setCellValue('A' . $row, '  ' . $groupName);
                $sheet->setCellValue('B' . $row, number_format($gTotal, 2));
                $row++;
            }
            $row++;

            // LIABILITIES and groups
            $sheet->setCellValue('A' . $row, 'LIABILITIES');
            $sheet->setCellValue('B' . $row, number_format($data['liabilitiesTotal'], 2));
            $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);
            $row++;
            foreach (($data['groupTotals']['Liabilities'] ?? []) as $groupName => $gTotal) {
                $sheet->setCellValue('A' . $row, '  ' . $groupName);
                $sheet->setCellValue('B' . $row, number_format($gTotal, 2));
                $row++;
            }
            $row++;

            // EQUITY and groups
            $sheet->setCellValue('A' . $row, 'EQUITY');
            $sheet->setCellValue('B' . $row, number_format($data['equityTotal'], 2));
            $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);
            $row++;
            foreach (($data['groupTotals']['Equity'] ?? []) as $groupName => $gTotal) {
                $sheet->setCellValue('A' . $row, '  ' . $groupName);
                $sheet->setCellValue('B' . $row, number_format($gTotal, 2));
                $row++;
            }
            $row++;

            // Total Liabilities + Equity
            $sheet->setCellValue('A' . $row, 'TOTAL LIABILITIES + EQUITY');
            $sheet->setCellValue('B' . $row, number_format($data['liabilitiesTotal'] + $data['equityTotal'], 2));
            $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);
        } else {
            // Detailed format
            $sheet->setCellValue('A' . $row, 'ASSETS');
            $row++;

            if (isset($data['detailed']['Assets'])) {
                foreach ($data['detailed']['Assets']['groups'] as $groupName => $group) {
                    $sheet->setCellValue('A' . $row, $groupName);
                    $sheet->getStyle('A' . $row)->getFont()->setBold(true);
                    $row++;

                    foreach ($group['accounts'] as $account) {
                        $sheet->setCellValue('A' . $row, $account['account_name']);
                        $sheet->setCellValue('B' . $row, number_format($account['balance'], 2));
                        $row++;
                    }

                    $sheet->setCellValue('A' . $row, 'Total ' . $groupName);
                    $sheet->setCellValue('B' . $row, number_format($group['total'], 2));
                    $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);
                    $row++;
                }
            }

            $sheet->setCellValue('A' . $row, 'TOTAL ASSETS');
            $sheet->setCellValue('B' . $row, number_format($data['assetsTotal'], 2));
            $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);
            $row += 2;

            // Liabilities
            $sheet->setCellValue('A' . $row, 'LIABILITIES');
            $row++;

            if (isset($data['detailed']['Liabilities'])) {
                foreach ($data['detailed']['Liabilities']['groups'] as $groupName => $group) {
                    $sheet->setCellValue('A' . $row, $groupName);
                    $sheet->getStyle('A' . $row)->getFont()->setBold(true);
                    $row++;

                    foreach ($group['accounts'] as $account) {
                        $sheet->setCellValue('A' . $row, $account['account_name']);
                        $sheet->setCellValue('B' . $row, number_format($account['balance'], 2));
                        $row++;
                    }

                    $sheet->setCellValue('A' . $row, 'Total ' . $groupName);
                    $sheet->setCellValue('B' . $row, number_format($group['total'], 2));
                    $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);
                    $row++;
                }
            }

            $sheet->setCellValue('A' . $row, 'TOTAL LIABILITIES');
            $sheet->setCellValue('B' . $row, number_format($data['liabilitiesTotal'], 2));
            $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);
            $row += 2;

            // Equity
            $sheet->setCellValue('A' . $row, 'EQUITY');
            $row++;

            if (isset($data['detailed']['Equity'])) {
                foreach ($data['detailed']['Equity']['groups'] as $groupName => $group) {
                    $sheet->setCellValue('A' . $row, $groupName);
                    $sheet->getStyle('A' . $row)->getFont()->setBold(true);
                    $row++;

                    foreach ($group['accounts'] as $account) {
                        $sheet->setCellValue('A' . $row, $account['account_name']);
                        $sheet->setCellValue('B' . $row, number_format($account['balance'], 2));
                        $row++;
                    }

                    $sheet->setCellValue('A' . $row, 'Total ' . $groupName);
                    $sheet->setCellValue('B' . $row, number_format($group['total'], 2));
                    $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);
                    $row++;
                }
            }

            $sheet->setCellValue('A' . $row, 'Profit / Loss');
            $sheet->setCellValue('B' . $row, number_format($data['profitLoss'], 2));
            $row++;

            $sheet->setCellValue('A' . $row, 'TOTAL EQUITY');
            $sheet->setCellValue('B' . $row, number_format($data['equityTotal'], 2));
            $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);
            $row++;

            $sheet->setCellValue('A' . $row, 'TOTAL LIABILITIES + EQUITY');
            $sheet->setCellValue('B' . $row, number_format($data['liabilitiesTotal'] + $data['equityTotal'], 2));
            $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);
        }

        // Auto-size columns
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);

        // Use resolved view type from data to avoid defaults
        $resolvedViewType = $data['viewType'] ?? $viewType;
        $filename = 'balance_sheet_' . $asOf . '_' . $resolvedViewType . '.xlsx';
        
        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'balance_sheet');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename)->deleteFileAfterSend();
    }

    private function getBalanceSheetData($asOf, $comparativeAsOf, $comparatives, $branchId, $reportingType, $viewType, $user, $company)
    {
        // This method contains the same logic as the index method
        // but returns the data array instead of a view
        $assignedBranchIds = $user->branches()
            ->where('branches.company_id', $company->id)
            ->pluck('branches.id')
            ->toArray();

        // Base transactions until as_of date (inclusive)
        $base = DB::table('gl_transactions')
            ->join('chart_accounts', 'gl_transactions.chart_account_id', '=', 'chart_accounts.id')
            ->join('account_class_groups', 'chart_accounts.account_class_group_id', '=', 'account_class_groups.id')
            ->join('account_class', 'account_class_groups.class_id', '=', 'account_class.id')
            ->where('account_class_groups.company_id', $company->id)
            ->whereDate('gl_transactions.date', '<=', $asOf);

        // Branch filter
        if ($branchId && $branchId !== 'all') {
            $base->where('gl_transactions.branch_id', $branchId);
        } else {
            $base->whereIn('gl_transactions.branch_id', $assignedBranchIds);
        }

        // Summary by account class
        $summary = (clone $base)
            ->select(
                'account_class.id as class_id',
                'account_class.name as class_name',
                DB::raw('SUM(CASE WHEN gl_transactions.nature = "debit" THEN gl_transactions.amount ELSE 0 END) as total_debit'),
                DB::raw('SUM(CASE WHEN gl_transactions.nature = "credit" THEN gl_transactions.amount ELSE 0 END) as total_credit')
            )
            ->groupBy('account_class.id', 'account_class.name')
            ->get()
            ->map(function ($row) {
                $class = strtolower($row->class_name);
                switch ($class) {
                    case 'assets':
                        $balance = $row->total_debit - $row->total_credit;
                        break;
                    case 'liabilities':
                    case 'equity':
                    case 'income':
                    case 'revenue':
                        $balance = $row->total_credit - $row->total_debit;
                        break;
                    case 'expenses':
                    case 'expense':
                        $balance = $row->total_debit - $row->total_credit;
                        break;
                    default:
                        $balance = $row->total_debit - $row->total_credit;
                        break;
                }
                return [
                    'class_id' => $row->class_id,
                    'class_name' => $row->class_name,
                    'balance' => $balance,
                ];
            });

        // Compute high-level totals and P&L
        $assetsTotal = (float) (collect($summary)->firstWhere('class_name', 'Assets')['balance'] ?? 0);
        $liabilitiesTotal = (float) (collect($summary)->firstWhere('class_name', 'Liabilities')['balance'] ?? 0);
        $equityTotal = (float) (collect($summary)->firstWhere('class_name', 'Equity')['balance'] ?? 0);

        // Sum both naming variants (Revenue/Income and Expenses/Expense)
        $revenueTotal = (float) (collect($summary)->firstWhere('class_name', 'Revenue')['balance'] ?? 0)
            + (float) (collect($summary)->firstWhere('class_name', 'Income')['balance'] ?? 0);

        $expenseTotal = (float) (collect($summary)->firstWhere('class_name', 'Expenses')['balance'] ?? 0)
            + (float) (collect($summary)->firstWhere('class_name', 'Expense')['balance'] ?? 0);

        $profitLoss = $revenueTotal - $expenseTotal;

        // Add profit/loss into equity
        $equityTotal = $equityTotal + $profitLoss;

        // Detailed per account (only if requested)
        $detailed = [];
        $groupTotals = [];
        $comparativeGroupTotals = [];

        // Build per-class, per-group totals for current period (used by Summary and Detailed)
        $groupRows = (clone $base)
            ->select(
                'account_class.name as class_name',
                'account_class_groups.name as group_name',
                DB::raw('SUM(CASE WHEN gl_transactions.nature = "debit" THEN gl_transactions.amount ELSE 0 END) as total_debit'),
                DB::raw('SUM(CASE WHEN gl_transactions.nature = "credit" THEN gl_transactions.amount ELSE 0 END) as total_credit')
            )
            ->groupBy('account_class.name', 'account_class_groups.name')
            ->get();

        foreach ($groupRows as $gr) {
            $class = strtolower($gr->class_name);
            switch ($class) {
                case 'assets':
                    $bal = $gr->total_debit - $gr->total_credit;
                    break;
                case 'liabilities':
                case 'equity':
                case 'income':
                case 'revenue':
                    $bal = $gr->total_credit - $gr->total_debit;
                    break;
                case 'expenses':
                case 'expense':
                    $bal = $gr->total_debit - $gr->total_credit;
                    break;
                default:
                    $bal = $gr->total_debit - $gr->total_credit;
                    break;
            }
            $groupTotals[$gr->class_name][$gr->group_name] = ($groupTotals[$gr->class_name][$gr->group_name] ?? 0) + $bal;
        }
        $rows = (clone $base)
            ->select(
                'chart_accounts.id as account_id',
                'chart_accounts.account_name',
                'account_class_groups.name as group_name',
                'account_class.name as class_name',
                DB::raw('SUM(CASE WHEN gl_transactions.nature = "debit" THEN gl_transactions.amount ELSE 0 END) as total_debit'),
                DB::raw('SUM(CASE WHEN gl_transactions.nature = "credit" THEN gl_transactions.amount ELSE 0 END) as total_credit')
            )
            ->groupBy('chart_accounts.id', 'chart_accounts.account_name', 'account_class_groups.name', 'account_class.name')
            ->get();

        foreach ($rows as $r) {
            $class = strtolower($r->class_name);
            switch ($class) {
                case 'assets':
                    $balance = $r->total_debit - $r->total_credit;
                    break;
                case 'liabilities':
                case 'equity':
                case 'income':
                case 'revenue':
                    $balance = $r->total_credit - $r->total_debit;
                    break;
                case 'expenses':
                case 'expense':
                    $balance = $r->total_debit - $r->total_credit;
                    break;
                default:
                    $balance = $r->total_debit - $r->total_credit;
                    break;
            }

            $detailed[$r->class_name]['groups'][$r->group_name]['accounts'][] = [
                'account_id' => $r->account_id,
                'account_name' => $r->account_name,
                'balance' => $balance,
            ];

            // Subtotals
            if (!isset($detailed[$r->class_name]['groups'][$r->group_name]['total'])) {
                $detailed[$r->class_name]['groups'][$r->group_name]['total'] = 0;
            }
            $detailed[$r->class_name]['groups'][$r->group_name]['total'] += $balance;

            if (!isset($detailed[$r->class_name]['total'])) {
                $detailed[$r->class_name]['total'] = 0;
            }
            $detailed[$r->class_name]['total'] += $balance;
        }

        // Compute additional comparatives if provided (class totals)
        $comparativesData = [];
        $allComparativeDates = [];
        if (!empty($comparativeAsOf)) {
            $allComparativeDates[] = $comparativeAsOf;
        }
        foreach ($comparatives as $c) { if (!empty($c)) { $allComparativeDates[] = $c; } }
        $allComparativeDates = array_values(array_unique($allComparativeDates));

        if (!empty($allComparativeDates)) {
            foreach ($allComparativeDates as $compDate) {
                if (empty($compDate)) { continue; }

                $baseCmp2 = DB::table('gl_transactions')
                    ->join('chart_accounts', 'gl_transactions.chart_account_id', '=', 'chart_accounts.id')
                    ->join('account_class_groups', 'chart_accounts.account_class_group_id', '=', 'account_class_groups.id')
                    ->join('account_class', 'account_class_groups.class_id', '=', 'account_class.id')
                    ->where('account_class_groups.company_id', $company->id)
                    ->whereDate('gl_transactions.date', '<=', $compDate);

                if ($branchId && $branchId !== 'all') {
                    $baseCmp2->where('gl_transactions.branch_id', $branchId);
                } else {
                    $baseCmp2->whereIn('gl_transactions.branch_id', $assignedBranchIds);
                }

                $summary2 = (clone $baseCmp2)
                    ->select(
                        'account_class.id as class_id',
                        'account_class.name as class_name',
                        DB::raw('SUM(CASE WHEN gl_transactions.nature = "debit" THEN gl_transactions.amount ELSE 0 END) as total_debit'),
                        DB::raw('SUM(CASE WHEN gl_transactions.nature = "credit" THEN gl_transactions.amount ELSE 0 END) as total_credit')
                    )
                    ->groupBy('account_class.id', 'account_class.name')
                    ->get()
                    ->map(function ($row) {
                        $class = strtolower($row->class_name);
                        switch ($class) {
                            case 'assets':
                                $balance = $row->total_debit - $row->total_credit;
                                break;
                            case 'liabilities':
                            case 'equity':
                            case 'income':
                            case 'revenue':
                                $balance = $row->total_credit - $row->total_debit;
                                break;
                            case 'expenses':
                            case 'expense':
                                $balance = $row->total_debit - $row->total_credit;
                                break;
                            default:
                                $balance = $row->total_debit - $row->total_credit;
                                break;
                        }
                        return [
                            'class_id' => $row->class_id,
                            'class_name' => $row->class_name,
                            'balance' => $balance,
                        ];
                    });

                $assetsTotal2 = (float) (collect($summary2)->firstWhere('class_name', 'Assets')['balance'] ?? 0);
                $liabilitiesTotal2 = (float) (collect($summary2)->firstWhere('class_name', 'Liabilities')['balance'] ?? 0);
                $equityTotal2 = (float) (collect($summary2)->firstWhere('class_name', 'Equity')['balance'] ?? 0);
                $revenueTotal2 = (float) (collect($summary2)->firstWhere('class_name', 'Revenue')['balance'] ?? 0)
                    + (float) (collect($summary2)->firstWhere('class_name', 'Income')['balance'] ?? 0);
                $expenseTotal2 = (float) (collect($summary2)->firstWhere('class_name', 'Expenses')['balance'] ?? 0)
                    + (float) (collect($summary2)->firstWhere('class_name', 'Expense')['balance'] ?? 0);
                $profitLoss2 = $revenueTotal2 - $expenseTotal2;
                $equityTotal2 = $equityTotal2 + $profitLoss2;

                $comparativesData[] = [
                    'date' => $compDate,
                    'assetsTotal' => $assetsTotal2,
                    'liabilitiesTotal' => $liabilitiesTotal2,
                    'equityTotal' => $equityTotal2,
                    'profitLoss' => $profitLoss2,
                ];
            }
        }

        // Build comparative group totals per date (class -> group -> total)
        foreach ($allComparativeDates as $compDate) {
            $cmpQuery = DB::table('gl_transactions')
                ->join('chart_accounts', 'gl_transactions.chart_account_id', '=', 'chart_accounts.id')
                ->join('account_class_groups', 'chart_accounts.account_class_group_id', '=', 'account_class_groups.id')
                ->join('account_class', 'account_class_groups.class_id', '=', 'account_class.id')
                ->where('account_class_groups.company_id', $company->id)
                ->whereDate('gl_transactions.date', '<=', $compDate);

            if ($branchId && $branchId !== 'all') {
                $cmpQuery->where('gl_transactions.branch_id', $branchId);
            } else {
                $cmpQuery->whereIn('gl_transactions.branch_id', $assignedBranchIds);
            }

            $cmpRows = $cmpQuery
                ->select(
                    'account_class.name as class_name',
                    'account_class_groups.name as group_name',
                    DB::raw('SUM(CASE WHEN gl_transactions.nature = "debit" THEN gl_transactions.amount ELSE 0 END) as total_debit'),
                    DB::raw('SUM(CASE WHEN gl_transactions.nature = "credit" THEN gl_transactions.amount ELSE 0 END) as total_credit')
                )
                ->groupBy('account_class.name', 'account_class_groups.name')
                ->get();

            foreach ($cmpRows as $cr) {
                $class = strtolower($cr->class_name);
                switch ($class) {
                    case 'assets':
                        $bal = $cr->total_debit - $cr->total_credit;
                        break;
                    case 'liabilities':
                    case 'equity':
                    case 'income':
                    case 'revenue':
                        $bal = $cr->total_credit - $cr->total_debit;
                        break;
                    case 'expenses':
                    case 'expense':
                        $bal = $cr->total_debit - $cr->total_credit;
                        break;
                    default:
                        $bal = $cr->total_debit - $cr->total_credit;
                        break;
                }
                $comparativeGroupTotals[$compDate][$cr->class_name][$cr->group_name] = ($comparativeGroupTotals[$compDate][$cr->class_name][$cr->group_name] ?? 0) + $bal;
            }
        }

        return [
            'summary' => $summary,
            'detailed' => $detailed,
            'viewType' => $viewType,
            'assetsTotal' => $assetsTotal,
            'liabilitiesTotal' => $liabilitiesTotal,
            'equityTotal' => $equityTotal,
            'profitLoss' => $profitLoss,
            'comparativesData' => $comparativesData,
            'comparativeAsOf' => $comparativeAsOf,
            'comparativeGroupTotals' => $comparativeGroupTotals,
            'groupTotals' => $groupTotals,
        ];
    }
}
