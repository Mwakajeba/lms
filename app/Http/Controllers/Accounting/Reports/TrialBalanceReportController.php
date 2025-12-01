<?php

namespace App\Http\Controllers\Accounting\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class TrialBalanceReportController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->can('view trial balance report')) {
            abort(403, 'Unauthorized access to this report.');
        }
        
        $user = Auth::user();
        $company = $user->company;
        
        // Get branches visible to the user: only assigned branches
        $branches = $user->branches()
            ->where('branches.company_id', $company->id)
            ->select('branches.id', 'branches.name')
            ->get();

        // Set default values
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $reportingType = $request->get('reporting_type', 'accrual');
        $branchParam = $request->get('branch_id');
        $branchId = ($branches->count() > 1 && $branchParam === 'all') ? 'all' : ($branchParam ?: ($branches->first()->id ?? null));
        $layout = $request->get('layout', 'single_column');
        $levelOfDetail = $request->get('level_of_detail', 'detailed');

        // Get comparative columns from request
        $comparativeColumns = $request->get('comparative_columns', []);

        // Get trial balance data
        $trialBalanceData = $this->getTrialBalanceData($startDate, $endDate, $reportingType, $branchId, $layout, $levelOfDetail, $comparativeColumns);

        return view('accounting.reports.trial-balance.index', compact(
            'trialBalanceData',
            'branches',
            'startDate',
            'endDate',
            'reportingType',
            'branchId',
            'layout',
            'levelOfDetail',
            'comparativeColumns',
            'user'
        ));
    }

    private function getTrialBalanceData($startDate, $endDate, $reportingType, $branchId, $layout, $levelOfDetail, $comparativeColumns = [])
    {
        $user = Auth::user();
        $company = $user->company;

        // Get data based on layout type
        if ($layout === 'multi_column') {
            $currentData = $this->getMultipleColumnData($startDate, $endDate, $reportingType, $branchId, $levelOfDetail);
        } else {
            $currentData = $this->getPeriodData($startDate, $endDate, $reportingType, $branchId, $levelOfDetail);
        }

        // Get comparative period data (only for single and double column layouts)
        $comparativeData = [];
        if ($layout !== 'multi_column') {
            foreach ($comparativeColumns as $index => $column) {
                if (!empty($column['start_date']) && !empty($column['end_date'])) {
                    $label = isset($column['name']) && trim($column['name']) !== '' ? trim($column['name']) : ('Comparative ' . ($index + 1));
                    $comparativeData[$label] = $this->getPeriodData($column['start_date'], $column['end_date'], $reportingType, $branchId, $levelOfDetail);
                }
            }
        }

        return [
            'data' => $currentData,
            'comparative' => $comparativeData,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'reporting_type' => $reportingType,
            'branch_id' => $branchId,
            'layout' => $layout,
            'level_of_detail' => $levelOfDetail
        ];
    }

    private function getPeriodData($startDate, $endDate, $reportingType, $branchId, $levelOfDetail)
    {
        $user = Auth::user();
        $company = $user->company;

        // Build the base query
        $query = DB::table('gl_transactions')
            ->join('chart_accounts', 'gl_transactions.chart_account_id', '=', 'chart_accounts.id')
            ->join('account_class_groups', 'chart_accounts.account_class_group_id', '=', 'account_class_groups.id')
            ->join('account_class', 'account_class_groups.class_id', '=', 'account_class.id')
            ->where('account_class_groups.company_id', $company->id)
            ->whereBetween('gl_transactions.date', [$startDate, $endDate]);

        // Add branch filter for assigned branches / all assigned
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

        // Add reporting type filter (cash vs accrual)
        if ($reportingType === 'cash') {
            // For cash basis, select all GL transactions that are part of the same transaction when any bank account is involved
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

        // Select fields based on level of detail
        if ($levelOfDetail === 'detailed') {
            $query->select(
                'chart_accounts.id as account_id',
                'chart_accounts.account_name as account',
                'chart_accounts.account_code',
                'account_class.name as class_name',
                'account_class_groups.name as group_name',
                DB::raw('SUM(CASE WHEN gl_transactions.nature = "debit" THEN gl_transactions.amount ELSE 0 END) as debit_total'),
                DB::raw('SUM(CASE WHEN gl_transactions.nature = "credit" THEN gl_transactions.amount ELSE 0 END) as credit_total')
            )
            ->groupBy('chart_accounts.id', 'chart_accounts.account_name', 'chart_accounts.account_code', 'account_class.name', 'account_class_groups.name');
        } else {
            // Summary level - group by account class groups
            $query->select(
                'account_class_groups.id as group_id',
                'account_class_groups.name as account',
                'account_class.name as class_name',
                DB::raw('SUM(CASE WHEN gl_transactions.nature = "debit" THEN gl_transactions.amount ELSE 0 END) as debit_total'),
                DB::raw('SUM(CASE WHEN gl_transactions.nature = "credit" THEN gl_transactions.amount ELSE 0 END) as credit_total')
            )
            ->groupBy('account_class_groups.id', 'account_class_groups.name', 'account_class.name');
        }

        $data = $query->get();

        // Calculate balances and determine nature
        $data = $data->map(function ($item) {
            $item->sum = $item->debit_total - $item->credit_total;
            $item->nature = $item->sum >= 0 ? 'debit' : 'credit';
            $item->account_code = $item->account_code ?? '';
            return $item;
        });

        // Sort by account code or group name
        if ($levelOfDetail === 'detailed') {
            $data = $data->sortBy('account_code');
        } else {
            $data = $data->sortBy('account');
        }

        // Group by class
        $groupedData = $data->groupBy('class_name');

        return $groupedData;
    }

    private function getMultipleColumnData($startDate, $endDate, $reportingType, $branchId, $levelOfDetail)
    {
        $user = Auth::user();
        $company = $user->company;

        // Calculate opening period (from start of year to start date)
        $openingStartDate = Carbon::parse($startDate)->startOfYear()->format('Y-m-d');
        $openingEndDate = Carbon::parse($startDate)->subDay()->format('Y-m-d');

        // Current period is from start date to end date
        $currentStartDate = $startDate;
        $currentEndDate = $endDate;

        // Closing period is from start of year to end date
        $closingStartDate = Carbon::parse($startDate)->startOfYear()->format('Y-m-d');
        $closingEndDate = $endDate;

        // Get opening balances
        $openingData = $this->getPeriodDataForMultiple($openingStartDate, $openingEndDate, $reportingType, $branchId, $levelOfDetail);
        
        // Get current year changes
        $currentData = $this->getPeriodDataForMultiple($currentStartDate, $currentEndDate, $reportingType, $branchId, $levelOfDetail);
        
        // Get closing balances
        $closingData = $this->getPeriodDataForMultiple($closingStartDate, $closingEndDate, $reportingType, $branchId, $levelOfDetail);

        // Merge all data
        $allAccounts = collect();
        
        // Collect all unique accounts
        $openingData->each(function ($accounts, $class) use ($allAccounts) {
            $accounts->each(function ($account) use ($allAccounts) {
                $allAccounts->put($account->account_id ?? $account->group_id, $account);
            });
        });
        
        $currentData->each(function ($accounts, $class) use ($allAccounts) {
            $accounts->each(function ($account) use ($allAccounts) {
                if (!$allAccounts->has($account->account_id ?? $account->group_id)) {
                    $allAccounts->put($account->account_id ?? $account->group_id, $account);
                }
            });
        });
        
        $closingData->each(function ($accounts, $class) use ($allAccounts) {
            $accounts->each(function ($account) use ($allAccounts) {
                if (!$allAccounts->has($account->account_id ?? $account->group_id)) {
                    $allAccounts->put($account->account_id ?? $account->group_id, $account);
                }
            });
        });

        // Combine data for each account
        $combinedData = $allAccounts->map(function ($account) use ($openingData, $currentData, $closingData) {
            $accountId = $account->account_id ?? $account->group_id;
            
            // Find opening data
            $openingAccount = null;
            $openingData->each(function ($accounts) use ($accountId, &$openingAccount) {
                $found = $accounts->first(function ($acc) use ($accountId) {
                    return ($acc->account_id ?? $acc->group_id) == $accountId;
                });
                if ($found) $openingAccount = $found;
            });
            
            // Find current data
            $currentAccount = null;
            $currentData->each(function ($accounts) use ($accountId, &$currentAccount) {
                $found = $accounts->first(function ($acc) use ($accountId) {
                    return ($acc->account_id ?? $acc->group_id) == $accountId;
                });
                if ($found) $currentAccount = $found;
            });
            
            // Find closing data
            $closingAccount = null;
            $closingData->each(function ($accounts) use ($accountId, &$closingAccount) {
                $found = $accounts->first(function ($acc) use ($accountId) {
                    return ($acc->account_id ?? $acc->group_id) == $accountId;
                });
                if ($found) $closingAccount = $found;
            });

            $obj = new \stdClass();
            $obj->account = $account->account;
            $obj->account_code = $account->account_code ?? '';
            $obj->opening_debit = $openingAccount ? $openingAccount->debit_total : 0;
            $obj->opening_credit = $openingAccount ? $openingAccount->credit_total : 0;
            $obj->change_debit = $currentAccount ? $currentAccount->debit_total : 0;
            $obj->change_credit = $currentAccount ? $currentAccount->credit_total : 0;
            $obj->closing_debit = $closingAccount ? $closingAccount->debit_total : 0;
            $obj->closing_credit = $closingAccount ? $closingAccount->credit_total : 0;
            return $obj;
        });

        // Group by class - simplified approach
        $groupedData = collect();
        
        foreach ($combinedData as $account) {
            // Find the class name from the original data
            $className = 'Unknown';
            foreach ([$openingData, $currentData, $closingData] as $data) {
                foreach ($data as $class => $accounts) {
                    $found = $accounts->first(function ($acc) use ($account) {
                        return $acc->account == $account->account;
                    });
                    if ($found) {
                        $className = $class;
                        break 2;
                    }
                }
            }
            
            if (!$groupedData->has($className)) {
                $groupedData->put($className, collect());
            }
            $groupedData->get($className)->push($account);
        }

        // Debug: Log the structure
        \Log::info('Multiple column data structure:', [
            'total_accounts' => $combinedData->count(),
            'grouped_classes' => $groupedData->keys()->toArray(),
            'sample_account' => $combinedData->first() ? (array) $combinedData->first() : null
        ]);
        
        return $groupedData;
    }

    private function getPeriodDataForMultiple($startDate, $endDate, $reportingType, $branchId, $levelOfDetail)
    {
        $user = Auth::user();
        $company = $user->company;

        // Build the base query
        $query = DB::table('gl_transactions')
            ->join('chart_accounts', 'gl_transactions.chart_account_id', '=', 'chart_accounts.id')
            ->join('account_class_groups', 'chart_accounts.account_class_group_id', '=', 'account_class_groups.id')
            ->join('account_class', 'account_class_groups.class_id', '=', 'account_class.id')
            ->where('account_class_groups.company_id', $company->id)
            ->whereBetween('gl_transactions.date', [$startDate, $endDate]);

        // Add branch filter for assigned branches / all assigned
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

        // Add reporting type filter (cash vs accrual)
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

        // Select fields based on level of detail
        if ($levelOfDetail === 'detailed') {
            $query->select(
                'chart_accounts.id as account_id',
                'chart_accounts.account_name as account',
                'chart_accounts.account_code',
                'account_class.name as class_name',
                'account_class_groups.name as group_name',
                DB::raw('SUM(CASE WHEN gl_transactions.nature = "debit" THEN gl_transactions.amount ELSE 0 END) as debit_total'),
                DB::raw('SUM(CASE WHEN gl_transactions.nature = "credit" THEN gl_transactions.amount ELSE 0 END) as credit_total')
            )
            ->groupBy('chart_accounts.id', 'chart_accounts.account_name', 'chart_accounts.account_code', 'account_class.name', 'account_class_groups.name');
        } else {
            $query->select(
                'account_class_groups.id as group_id',
                'account_class_groups.name as account',
                'account_class.name as class_name',
                DB::raw('SUM(CASE WHEN gl_transactions.nature = "debit" THEN gl_transactions.amount ELSE 0 END) as debit_total'),
                DB::raw('SUM(CASE WHEN gl_transactions.nature = "credit" THEN gl_transactions.amount ELSE 0 END) as credit_total')
            )
            ->groupBy('account_class_groups.id', 'account_class_groups.name', 'account_class.name');
        }

        $data = $query->get();

        // Group by class
        $groupedData = $data->groupBy('class_name');

        return $groupedData;
    }

    public function export(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        // Get filter parameters
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $reportingType = $request->get('reporting_type', 'accrual');
        $branchId = $request->get('branch_id', $user->branch_id);
        $layout = $request->get('layout', 'single_column');
        $levelOfDetail = $request->get('level_of_detail', 'detailed');
        $exportType = $request->get('export_type', 'pdf');

        // Get comparative columns from request
        $comparativeColumns = $request->get('comparative_columns', []);

        // Get trial balance data
        $trialBalanceData = $this->getTrialBalanceData($startDate, $endDate, $reportingType, $branchId, $layout, $levelOfDetail, $comparativeColumns);

        if ($exportType === 'excel') {
            return $this->exportExcel($trialBalanceData, $company, $startDate, $endDate, $reportingType, $levelOfDetail, $layout);
        } else {
            return $this->exportPdf($trialBalanceData, $company, $startDate, $endDate, $reportingType, $levelOfDetail);
        }
    }

    private function exportPdf($trialBalanceData, $company, $startDate, $endDate, $reportingType, $levelOfDetail)
    {
        $user = Auth::user();
        
        // Get branches for header - load for all users, not just admins
        $branches = $user->branches()
            ->where('branches.company_id', $company->id)
            ->select('branches.id', 'branches.name')
            ->get();
        
        // Get layout and branchId from trial balance data
        $layout = $trialBalanceData['layout'] ?? 'single';
        $branchId = $trialBalanceData['branch_id'] ?? 'all';
        
        $dompdf = new Dompdf();
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $dompdf->setOptions($options);

        $html = view('accounting.reports.trial-balance.pdf', compact(
            'trialBalanceData', 
            'company', 
            'startDate', 
            'endDate', 
            'reportingType',
            'branches',
            'layout',
            'branchId',
            'levelOfDetail'
        ))->render();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'trial_balance_' . $startDate . '_to_' . $endDate . '_' . $reportingType . '.pdf';
        
        return response($dompdf->output())
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    private function exportExcel($trialBalanceData, $company, $startDate, $endDate, $reportingType, $levelOfDetail, $layout = 'single_column')
    {
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        // Set title
        $worksheet->setCellValue('A1', 'TRIAL BALANCE REPORT');
        $worksheet->mergeCells('A1:F1');
        $worksheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $worksheet->getStyle('A1')->getAlignment()->setHorizontal('center');

        // Set company info
        $worksheet->setCellValue('A2', $company->name ?? 'SmartFinance');
        $worksheet->mergeCells('A2:F2');
        $worksheet->getStyle('A2')->getFont()->setBold(true);
        $worksheet->getStyle('A2')->getAlignment()->setHorizontal('center');

        // Set period info
        $worksheet->setCellValue('A3', 'Period: ' . Carbon::parse($startDate)->format('M d, Y') . ' to ' . Carbon::parse($endDate)->format('M d, Y'));
        $worksheet->mergeCells('A3:F3');
        $worksheet->getStyle('A3')->getAlignment()->setHorizontal('center');

        // Set reporting type and level of detail
        $worksheet->setCellValue('A4', 'Basis: ' . ucfirst($reportingType) . ' | Level: ' . ucfirst($levelOfDetail));
        $worksheet->mergeCells('A4:F4');
        $worksheet->getStyle('A4')->getAlignment()->setHorizontal('center');

        $row = 6;

        // Calculate total columns including comparative
        $totalColumns = 6; // Base columns: Account Code, Account Name, Class, Debits, Credits, Balance
        $comparativeColumns = [];
        if (isset($trialBalanceData['comparative']) && count($trialBalanceData['comparative']) > 0) {
            $comparativeColumns = array_keys($trialBalanceData['comparative']);
            $totalColumns += count($comparativeColumns) * 2; // Each comparative column adds 2 columns (Debit, Credit)
        }

        // Set headers
        if ($layout === 'multi_column') {
            $worksheet->setCellValue('A' . $row, 'Account Name');
            $worksheet->setCellValue('B' . $row, 'Account Code');
            $worksheet->setCellValue('C' . $row, 'Opening DR');
            $worksheet->setCellValue('D' . $row, 'Opening CR');
            $worksheet->setCellValue('E' . $row, 'Current DR');
            $worksheet->setCellValue('F' . $row, 'Current CR');
            $worksheet->setCellValue('G' . $row, 'Closing DR');
            $worksheet->setCellValue('H' . $row, 'Closing CR');
            $worksheet->setCellValue('I' . $row, 'Difference');
        } elseif ($levelOfDetail === 'detailed') {
            $worksheet->setCellValue('A' . $row, 'Account Code');
            $worksheet->setCellValue('B' . $row, 'Account Name');
            $worksheet->setCellValue('C' . $row, 'Class');
            $worksheet->setCellValue('D' . $row, 'Debits');
            $worksheet->setCellValue('E' . $row, 'Credits');
            $worksheet->setCellValue('F' . $row, 'Balance');
            
            // Add comparative column headers
            $col = 'G';
            foreach ($comparativeColumns as $index => $columnName) {
                $worksheet->setCellValue($col . $row, 'Comparative ' . ($index + 1) . ' Debit');
                $col++;
                $worksheet->setCellValue($col . $row, 'Comparative ' . ($index + 1) . ' Credit');
                $col++;
            }
        } else {
            $worksheet->setCellValue('A' . $row, 'Group Name');
            $worksheet->setCellValue('B' . $row, 'Class');
            $worksheet->setCellValue('C' . $row, 'Debits');
            $worksheet->setCellValue('D' . $row, 'Credits');
            $worksheet->setCellValue('E' . $row, 'Balance');
            
            // Add comparative column headers
            $col = 'F';
            foreach ($comparativeColumns as $index => $columnName) {
                $worksheet->setCellValue($col . $row, 'Comparative ' . ($index + 1) . ' Debit');
                $col++;
                $worksheet->setCellValue($col . $row, 'Comparative ' . ($index + 1) . ' Credit');
                $col++;
            }
        }
        
        $worksheet->getStyle('A' . $row . ':' . $worksheet->getHighestColumn() . $row)->getFont()->setBold(true);
        $worksheet->getStyle('A' . $row . ':' . $worksheet->getHighestColumn() . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('E9ECEF');
        $row++;

        // Add data
        if ($layout === 'multi_column') {
            $totOpenDr = 0; $totOpenCr = 0; $totChgDr = 0; $totChgCr = 0; $totCloseDr = 0; $totCloseCr = 0; $totDiff = 0;
            foreach ($trialBalanceData['data'] as $class => $accounts) {
                foreach ($accounts as $account) {
                    $a = is_array($account) ? (object)$account : $account;
                    $openingDr = isset($a->opening_debit) ? (float)$a->opening_debit : 0.0;
                    $openingCr = isset($a->opening_credit) ? (float)$a->opening_credit : 0.0;
                    $changeDr  = isset($a->change_debit)  ? (float)$a->change_debit  : 0.0;
                    $changeCr  = isset($a->change_credit) ? (float)$a->change_credit : 0.0;
                    $closingDr = isset($a->closing_debit) ? (float)$a->closing_debit : 0.0;
                    $closingCr = isset($a->closing_credit)? (float)$a->closing_credit: 0.0;
                    if (($openingDr+$openingCr+$changeDr+$changeCr+$closingDr+$closingCr) == 0.0) {
                        $sumVal = isset($a->sum) ? (float)$a->sum : 0.0;
                        if ($sumVal !== 0.0) {
                            $changeDr = $sumVal > 0 ? $sumVal : 0.0;
                            $changeCr = $sumVal < 0 ? abs($sumVal) : 0.0;
                            $closingDr = $changeDr; $closingCr = $changeCr;
                        }
                    }
                    $openingDiff = $openingDr - $openingCr;
                    $changeDiff  = $changeDr  - $changeCr;
                    $closingDiff = $closingDr - $closingCr;
                    $difference  = $closingDiff;
                    if (!($openingDiff==0 && $changeDiff==0 && $closingDiff==0)) {
                        $worksheet->setCellValue('A' . $row, ($a->account ?? $a->account_name ?? ''));
                        $worksheet->setCellValue('B' . $row, $a->account_code ?? '');
                        $worksheet->setCellValue('C' . $row, $openingDiff > 0 ? $openingDiff : 0);
                        $worksheet->setCellValue('D' . $row, $openingDiff < 0 ? abs($openingDiff) : 0);
                        $worksheet->setCellValue('E' . $row, $changeDiff  > 0 ? $changeDiff  : 0);
                        $worksheet->setCellValue('F' . $row, $changeDiff  < 0 ? abs($changeDiff)  : 0);
                        $worksheet->setCellValue('G' . $row, $closingDiff > 0 ? $closingDiff : 0);
                        $worksheet->setCellValue('H' . $row, $closingDiff < 0 ? abs($closingDiff) : 0);
                        $worksheet->setCellValue('I' . $row, abs($difference));
                        // number formats
                        foreach (['C','D','E','F','G','H','I'] as $c) { $worksheet->getStyle($c.$row)->getNumberFormat()->setFormatCode('#,##0.00'); }
                        $row++;
                    }
                    $totOpenDr += $openingDiff > 0 ? $openingDiff : 0;
                    $totOpenCr += $openingDiff < 0 ? abs($openingDiff) : 0;
                    $totChgDr  += $changeDiff  > 0 ? $changeDiff  : 0;
                    $totChgCr  += $changeDiff  < 0 ? abs($changeDiff)  : 0;
                    $totCloseDr+= $closingDiff > 0 ? $closingDiff : 0;
                    $totCloseCr+= $closingDiff < 0 ? abs($closingDiff) : 0;
                    $totDiff   += $difference;
                }
            }
            // Totals row
            $worksheet->setCellValue('A' . $row, 'TOTAL');
            $worksheet->getStyle('A' . $row)->getFont()->setBold(true);
            $worksheet->setCellValue('C' . $row, $totOpenDr);
            $worksheet->setCellValue('D' . $row, $totOpenCr);
            $worksheet->setCellValue('E' . $row, $totChgDr);
            $worksheet->setCellValue('F' . $row, $totChgCr);
            $worksheet->setCellValue('G' . $row, $totCloseDr);
            $worksheet->setCellValue('H' . $row, $totCloseCr);
            $worksheet->setCellValue('I' . $row, abs($totDiff));
            foreach (['C','D','E','F','G','H','I'] as $c) { $worksheet->getStyle($c.$row)->getNumberFormat()->setFormatCode('#,##0.00'); }
        } else {
            foreach ($trialBalanceData['data'] as $class => $accounts) {
                foreach ($accounts as $account) {
                    $a = is_array($account) ? (object)$account : $account;
                    $sumVal = isset($a->sum) ? (float)$a->sum : 0.0;
                    if ($sumVal === 0.0) {
                        continue;
                    }
                    $isCredit = isset($a->nature) ? ($a->nature === 'credit') : ($sumVal < 0);
                    $debit = $isCredit ? 0 : abs($sumVal);
                    $credit = $isCredit ? abs($sumVal) : 0;
                    $balance = $sumVal;

                    if ($levelOfDetail === 'detailed') {
                        $worksheet->setCellValue('A' . $row, $a->account_code ?? '');
                        $worksheet->setCellValue('B' . $row, ($a->account ?? $a->account_name ?? ''));
                        $worksheet->setCellValue('C' . $row, $class);
                        $worksheet->setCellValue('D' . $row, $debit);
                        $worksheet->setCellValue('E' . $row, $credit);
                        $worksheet->setCellValue('F' . $row, $balance);
                    } else {
                        $worksheet->setCellValue('A' . $row, $class);
                        $worksheet->setCellValue('B' . $row, '');
                        $worksheet->setCellValue('C' . $row, $debit);
                        $worksheet->setCellValue('D' . $row, $credit);
                        $worksheet->setCellValue('E' . $row, $balance);
                    }

                    // Add comparative data
                    $col = $levelOfDetail === 'detailed' ? 'G' : 'F';
                    foreach ($comparativeColumns as $columnName) {
                        $compAccounts = $trialBalanceData['comparative'][$columnName][$class] ?? [];
                        $compAccount = collect($compAccounts)->first(function($x) use ($a) {
                            $xObj = is_array($x) ? (object)$x : $x;
                            return isset($xObj->account_code) && $xObj->account_code == ($a->account_code ?? null);
                        });
                        $compSum = $compAccount ? (float)((is_array($compAccount) ? $compAccount['sum'] ?? 0 : ($compAccount->sum ?? 0))) : 0.0;
                        $compIsCredit = $compSum < 0;
                        $compDebit = $compIsCredit ? 0 : abs($compSum);
                        $compCredit = $compIsCredit ? abs($compSum) : 0;

                        $worksheet->setCellValue($col . $row, $compDebit);
                        $col++;
                        $worksheet->setCellValue($col . $row, $compCredit);
                        $col++;
                    }

                    // Format numbers
                    if ($levelOfDetail === 'detailed') {
                        $worksheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
                        $worksheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
                        $worksheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
                    } else {
                        $worksheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
                        $worksheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
                        $worksheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
                    }

                    // Format comparative numbers
                    $col = $levelOfDetail === 'detailed' ? 'G' : 'F';
                    foreach ($comparativeColumns as $columnName) {
                        $worksheet->getStyle($col . $row)->getNumberFormat()->setFormatCode('#,##0.00');
                        $col++;
                        $worksheet->getStyle($col . $row)->getNumberFormat()->setFormatCode('#,##0.00');
                        $col++;
                    }

                    $row++;
                }
            }
        }

        // Add totals row
        $worksheet->setCellValue('A' . $row, 'TOTAL');
        $worksheet->getStyle('A' . $row)->getFont()->setBold(true);
        
        // Calculate and add current period totals
        $totalDebit = 0; $totalCredit = 0;
        foreach ($trialBalanceData['data'] as $class => $accounts) {
            foreach ($accounts as $account) {
                $a = is_array($account) ? (object)$account : $account;
                $sumVal = isset($a->sum) ? (float)$a->sum : 0.0;
                if ($sumVal < 0) { $totalCredit += abs($sumVal); } else { $totalDebit += $sumVal; }
            }
        }
        
        if ($levelOfDetail === 'detailed') {
            $worksheet->setCellValue('D' . $row, $totalDebit);
            $worksheet->setCellValue('E' . $row, $totalCredit);
            $worksheet->setCellValue('F' . $row, $totalDebit - $totalCredit);
        } else {
            $worksheet->setCellValue('C' . $row, $totalDebit);
            $worksheet->setCellValue('D' . $row, $totalCredit);
            $worksheet->setCellValue('E' . $row, $totalDebit - $totalCredit);
        }
        
        // Add comparative totals
        $col = $levelOfDetail === 'detailed' ? 'G' : 'F';
        foreach ($comparativeColumns as $columnName) {
            $compTotalDebit = 0; $compTotalCredit = 0;
            foreach (($trialBalanceData['comparative'][$columnName] ?? []) as $class => $accounts) {
                foreach ($accounts as $account) {
                    $a = is_array($account) ? (object)$account : $account;
                    $sumVal = isset($a->sum) ? (float)$a->sum : 0.0;
                    if ($sumVal < 0) { $compTotalCredit += abs($sumVal); } else { $compTotalDebit += $sumVal; }
                }
            }
            $worksheet->setCellValue($col . $row, $compTotalDebit);
            $col++;
            $worksheet->setCellValue($col . $row, $compTotalCredit);
            $col++;
        }
        
        // Format totals
        $worksheet->getStyle('A' . $row . ':' . $worksheet->getHighestColumn() . $row)->getFont()->setBold(true);
        $worksheet->getStyle('A' . $row . ':' . $worksheet->getHighestColumn() . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('E9ECEF');

        // Auto-size columns
        foreach (range('A', $worksheet->getHighestColumn()) as $col) {
            $worksheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'trial_balance_' . $startDate . '_to_' . $endDate . '_' . $reportingType . '.xlsx';
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename);
    }
}
