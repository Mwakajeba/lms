<?php

namespace App\Http\Controllers\Accounting\Reports;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\BudgetLine;
use App\Models\GlTransaction;
use App\Models\Branch;
use App\Models\ChartAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BudgetReportController extends Controller
{
    /**
     * Display the budget report index page.
     */
    public function index(Request $request)
    {
        if (!auth()->user()->can('view budget report')) {
            abort(403, 'Unauthorized access to this report.');
        }
        
        $user = Auth::user();
        
        // Get filter parameters
        $year = $request->get('year', date('Y'));
        $budgetId = $request->get('budget_id');
        $branchId = $request->get('branch_id', $user->branch_id);
        $accountClassId = $request->get('account_class_id');
        $accountGroupId = $request->get('account_group_id');
        $category = $request->get('category');
        $dateType = $request->get('date_type', 'yearly');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $showVariance = $request->get('show_variance', true);
        $showOnlyOverBudget = $request->get('show_only_over_budget', false);
        $showOnlyUnderBudget = $request->get('show_only_under_budget', false);

        // Calculate date range based on date type
        $dateRange = $this->calculateDateRange($dateType, $year, $dateFrom, $dateTo);
        $dateFrom = $dateRange['from'];
        $dateTo = $dateRange['to'];

        // Get available budgets for the year
        $availableBudgets = Budget::where('year', $year)
            ->where('branch_id', $branchId)
            ->orderBy('name')
            ->get();

        // Get available branches
        $branches = Branch::where('company_id', $user->company_id)->orderBy('name')->get();

        // Get account classes and groups for filtering
        $accountClasses = DB::table('account_class')
            ->orderBy('name')
            ->get();

        $accountGroups = DB::table('account_class_groups')
            ->where('company_id', $user->company_id)
            ->orderBy('name')
            ->get();

        // Get budget report data
        $budgetData = $this->getBudgetReportData($request);

        return view('accounting.reports.budget-report.index', compact(
            'budgetData',
            'availableBudgets',
            'branches',
            'accountClasses',
            'accountGroups',
            'year',
            'budgetId',
            'branchId',
            'accountClassId',
            'accountGroupId',
            'category',
            'dateType',
            'dateFrom',
            'dateTo',
            'showVariance',
            'showOnlyOverBudget',
            'showOnlyUnderBudget'
        ));
    }

    /**
     * Export budget report to Excel.
     */
    public function export(Request $request)
    {
        $budgetData = $this->getBudgetReportData($request);
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set title
        $sheet->setCellValue('A1', 'BUDGET REPORT');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

        // Report Information
        $sheet->setCellValue('A3', 'Report Information');
        $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(14);

        $sheet->setCellValue('A4', 'Year:');
        $sheet->setCellValue('B4', $request->get('year', date('Y')));
        $sheet->setCellValue('A5', 'Branch:');
        $sheet->setCellValue('B5', Branch::find($request->get('branch_id'))->name ?? 'All Branches');
        $sheet->setCellValue('A6', 'Date Range:');
        $sheet->setCellValue('B6', $request->get('date_from', '01-01-' . $request->get('year', date('Y'))) . ' to ' . $request->get('date_to', '31-12-' . $request->get('year', date('Y'))));
        $sheet->setCellValue('A7', 'Generated:');
        $sheet->setCellValue('B7', now()->format('d M Y, H:i'));

        // Headers
        $sheet->setCellValue('A9', 'Account Code');
        $sheet->setCellValue('B9', 'Account Name');
        $sheet->setCellValue('C9', 'Account Class');
        $sheet->setCellValue('D9', 'Account Group');
        $sheet->setCellValue('E9', 'Budgeted Amount');
        $sheet->setCellValue('F9', 'Actual Amount');
        $sheet->setCellValue('G9', 'Variance');
        $sheet->setCellValue('H9', 'Variance %');

        // Style headers
        $sheet->getStyle('A9:H9')->getFont()->setBold(true);
        $sheet->getStyle('A9:H9')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $sheet->getStyle('A9:H9')->getFill()->getStartColor()->setRGB('E9ECEF');

        // Add data
        $row = 10;
        $totalBudgeted = 0;
        $totalActual = 0;
        $totalVariance = 0;

        foreach ($budgetData['items'] as $item) {
            $sheet->setCellValue('A' . $row, $item->account_code);
            $sheet->setCellValue('B' . $row, $item->account_name);
            $sheet->setCellValue('C' . $row, $item->account_class);
            $sheet->setCellValue('D' . $row, $item->account_group);
            $sheet->setCellValue('E' . $row, $item->budgeted_amount);
            $sheet->setCellValue('F' . $row, $item->actual_amount);
            $sheet->setCellValue('G' . $row, $item->variance);
            $sheet->setCellValue('H' . $row, $item->variance_percentage);

            // Color code variance
            if ($item->variance < 0) {
                $sheet->getStyle('G' . $row)->getFont()->getColor()->setRGB('DC3545'); // Red for over budget
            } elseif ($item->variance > 0) {
                $sheet->getStyle('G' . $row)->getFont()->getColor()->setRGB('28A745'); // Green for under budget
            }

            $totalBudgeted += $item->budgeted_amount;
            $totalActual += $item->actual_amount;
            $totalVariance += $item->variance;
            $row++;
        }

        // Add totals
        $sheet->setCellValue('A' . $row, 'TOTAL');
        $sheet->setCellValue('B' . $row, '');
        $sheet->setCellValue('C' . $row, '');
        $sheet->setCellValue('D' . $row, '');
        $sheet->setCellValue('E' . $row, $totalBudgeted);
        $sheet->setCellValue('F' . $row, $totalActual);
        $sheet->setCellValue('G' . $row, $totalVariance);
        $sheet->setCellValue('H' . $row, $totalBudgeted > 0 ? round(($totalVariance / $totalBudgeted) * 100, 2) . '%' : '0%');

        // Style total row
        $sheet->getStyle('A' . $row . ':H' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':H' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $sheet->getStyle('A' . $row . ':H' . $row)->getFill()->getStartColor()->setRGB('D1ECF1');

        // Auto-size columns
        foreach (range('A', 'H') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Create the file
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filename = 'budget_report_' . $request->get('year', date('Y')) . '_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        $tempFile = tempnam(sys_get_temp_dir(), 'budget_report');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename)->deleteFileAfterSend();
    }

    /**
     * Export budget report as PDF.
     */
    public function exportPdf(Request $request)
    {
        $budgetData = $this->getBudgetReportData($request);
        
        $user = Auth::user();
        $company = $user->company ?? \App\Models\Company::find($user->company_id);
        
        $pdf = \PDF::loadView('accounting.reports.budget-report.pdf', [
            'items' => $budgetData['items'],
            'summary' => $budgetData['summary'],
            'filters' => $budgetData['filters'],
            'company' => $company,
            'generated_at' => now()->format('d/m/Y H:i:s'),
        ]);

        $filename = 'budget_report_' . $request->get('year', date('Y')) . '_' . date('Y-m-d_H-i-s') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Get budget report data with all filtrations.
     */
    private function getBudgetReportData(Request $request)
    {
        $user = Auth::user();
        
        // Get filter parameters
        $year = $request->get('year', date('Y'));
        $budgetId = $request->get('budget_id');
        $branchId = $request->get('branch_id', $user->branch_id);
        $accountClassId = $request->get('account_class_id');
        $accountGroupId = $request->get('account_group_id');
        $category = $request->get('category');
        $dateType = $request->get('date_type', 'yearly');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $showVariance = $request->get('show_variance', true);
        $showOnlyOverBudget = $request->get('show_only_over_budget', false);
        $showOnlyUnderBudget = $request->get('show_only_under_budget', false);

        // Calculate date range based on date type
        $dateRange = $this->calculateDateRange($dateType, $year, $dateFrom, $dateTo);
        $dateFrom = Carbon::createFromFormat('Y-m-d', $dateRange['from'])->startOfDay();
        $dateTo = Carbon::createFromFormat('Y-m-d', $dateRange['to'])->endOfDay();

        // Calculate budget division factor
        $budgetDivisionFactor = 1;
        if ($dateType === 'monthly') {
            $budgetDivisionFactor = 12;
        } elseif ($dateType === 'quarterly') {
            $budgetDivisionFactor = 4;
        }

        // Build the query
        $query = DB::table('budget_lines as bl')
            ->join('chart_accounts as ca', 'bl.account_id', '=', 'ca.id')
            ->join('account_class_groups as acg', 'ca.account_class_group_id', '=', 'acg.id')
            ->join('account_class as ac', 'acg.class_id', '=', 'ac.id')
            ->join('budgets as b', 'bl.budget_id', '=', 'b.id')
            ->leftJoin(DB::raw('(
                SELECT 
                    gl.chart_account_id,
                    SUM(
                        CASE 
                            WHEN ac.name = "Revenue" THEN 
                                CASE WHEN gl.nature = "credit" THEN gl.amount ELSE -gl.amount END
                            WHEN ac.name = "Expenses" THEN 
                                CASE WHEN gl.nature = "debit" THEN gl.amount ELSE -gl.amount END
                            ELSE 
                                CASE WHEN gl.nature = "debit" THEN gl.amount ELSE -gl.amount END
                        END
                    ) as actual_amount
                FROM gl_transactions gl
                JOIN chart_accounts ca ON gl.chart_account_id = ca.id
                JOIN account_class_groups acg ON ca.account_class_group_id = acg.id
                JOIN account_class ac ON acg.class_id = ac.id
                WHERE gl.date BETWEEN ? AND ?
                GROUP BY gl.chart_account_id
            ) as gl'), 'ca.id', '=', 'gl.chart_account_id')
            ->select([
                'ca.account_code',
                'ca.account_name',
                'ac.name as account_class',
                'acg.name as account_group',
                DB::raw("ROUND(bl.amount / {$budgetDivisionFactor}, 2) as budgeted_amount"),
                'bl.category',
                DB::raw('COALESCE(gl.actual_amount, 0) as actual_amount'),
                DB::raw("
                    CASE 
                        WHEN ac.name = 'Revenue' THEN 
                            (COALESCE(gl.actual_amount, 0) - ROUND(bl.amount / {$budgetDivisionFactor}, 2))
                        ELSE 
                            (ROUND(bl.amount / {$budgetDivisionFactor}, 2) - COALESCE(gl.actual_amount, 0))
                    END as variance
                "),
                DB::raw("
                    CASE 
                        WHEN ac.name = 'Revenue' THEN 
                            CASE WHEN ROUND(bl.amount / {$budgetDivisionFactor}, 2) > 0 
                            THEN ROUND(((COALESCE(gl.actual_amount, 0) - ROUND(bl.amount / {$budgetDivisionFactor}, 2)) / ROUND(bl.amount / {$budgetDivisionFactor}, 2)) * 100, 2) 
                            ELSE 0 END
                        ELSE 
                            CASE WHEN ROUND(bl.amount / {$budgetDivisionFactor}, 2) > 0 
                            THEN ROUND(((ROUND(bl.amount / {$budgetDivisionFactor}, 2) - COALESCE(gl.actual_amount, 0)) / ROUND(bl.amount / {$budgetDivisionFactor}, 2)) * 100, 2) 
                            ELSE 0 END
                    END as variance_percentage
                ")
            ])
            ->where('b.year', $year)
            ->where('b.branch_id', $branchId)
            ->where('b.company_id', $user->company_id)
            ->addBinding([$dateFrom, $dateTo], 'join');

        // Apply additional filters
        if ($budgetId) {
            $query->where('b.id', $budgetId);
        }

        if ($accountClassId) {
            $query->where('ac.id', $accountClassId);
        }

        if ($accountGroupId) {
            $query->where('acg.id', $accountGroupId);
        }

        if ($category) {
            $query->where('bl.category', $category);
        }

        // Apply variance filters
        if ($showOnlyOverBudget) {
            $query->whereRaw("
                CASE 
                    WHEN ac.name = 'Revenue' THEN 
                        (COALESCE(gl.actual_amount, 0) - ROUND(bl.amount / {$budgetDivisionFactor}, 2)) < 0
                    ELSE 
                        (ROUND(bl.amount / {$budgetDivisionFactor}, 2) - COALESCE(gl.actual_amount, 0)) < 0
                END
            ");
        }

        if ($showOnlyUnderBudget) {
            $query->whereRaw("
                CASE 
                    WHEN ac.name = 'Revenue' THEN 
                        (COALESCE(gl.actual_amount, 0) - ROUND(bl.amount / {$budgetDivisionFactor}, 2)) > 0
                    ELSE 
                        (ROUND(bl.amount / {$budgetDivisionFactor}, 2) - COALESCE(gl.actual_amount, 0)) > 0
                END
            ");
        }

        $items = $query->orderBy('ac.name')
            ->orderBy('acg.name')
            ->orderBy('ca.account_code')
            ->get();

        // Calculate summary statistics
        $summary = [
            'total_budgeted' => $items->sum('budgeted_amount'),
            'total_actual' => $items->sum('actual_amount'),
            'total_variance' => $items->sum('variance'),
            'over_budget_count' => $items->filter(function($item) {
                // For Revenue: variance < 0 means under target (bad)
                // For Expenses: variance < 0 means over budget (bad)
                return $item->variance < 0;
            })->count(),
            'under_budget_count' => $items->filter(function($item) {
                // For Revenue: variance > 0 means over target (good)
                // For Expenses: variance > 0 means under budget (good)
                return $item->variance > 0;
            })->count(),
            'on_budget_count' => $items->where('variance', '=', 0)->count(),
            'total_accounts' => $items->count(),
        ];

        $summary['variance_percentage'] = $summary['total_budgeted'] > 0 
            ? round(($summary['total_variance'] / $summary['total_budgeted']) * 100, 2) 
            : 0;

        return [
            'items' => $items,
            'summary' => $summary,
            'filters' => [
                'year' => $year,
                'budget_id' => $budgetId,
                'branch_id' => $branchId,
                'account_class_id' => $accountClassId,
                'account_group_id' => $accountGroupId,
                'category' => $category,
                'date_from' => $dateFrom->format('d-m-Y'),
                'date_to' => $dateTo->format('d-m-Y'),
                'show_variance' => $showVariance,
                'show_only_over_budget' => $showOnlyOverBudget,
                'show_only_under_budget' => $showOnlyUnderBudget,
            ]
        ];
    }

    /**
     * Calculate date range based on date type.
     */
    private function calculateDateRange($dateType, $year, $customDateFrom = null, $customDateTo = null)
    {
        $now = Carbon::now();
        
        switch ($dateType) {
            case 'monthly':
                // Current month
                $from = $now->copy()->startOfMonth();
                $to = $now->copy()->endOfMonth();
                break;
                
            case 'quarterly':
                // Current quarter
                $quarter = ceil($now->month / 3);
                $from = $now->copy()->startOfYear()->addMonths(($quarter - 1) * 3);
                $to = $from->copy()->addMonths(3)->subDay();
                break;
                
            case 'yearly':
                // Current year
                $from = $now->copy()->startOfYear();
                $to = $now->copy()->endOfYear();
                break;
                
            case 'custom':
            default:
                // Use custom dates or default to full year
                if ($customDateFrom && $customDateTo) {
                    $from = Carbon::createFromFormat('Y-m-d', $customDateFrom)->startOfDay();
                    $to = Carbon::createFromFormat('Y-m-d', $customDateTo)->endOfDay();
                } else {
                    $from = Carbon::createFromFormat('d-m-Y', '01-01-' . $year)->startOfDay();
                    $to = Carbon::createFromFormat('d-m-Y', '31-12-' . $year)->endOfDay();
                }
                break;
        }
        
        return [
            'from' => $from->format('Y-m-d'),
            'to' => $to->format('Y-m-d')
        ];
    }
} 