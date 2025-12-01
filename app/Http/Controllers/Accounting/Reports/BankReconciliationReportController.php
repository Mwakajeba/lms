<?php

namespace App\Http\Controllers\Accounting\Reports;

use App\Http\Controllers\Controller;
use App\Models\BankReconciliation;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class BankReconciliationReportController extends Controller
{
    /**
     * Display the bank reconciliation report index.
     */
    public function index()
    {
        if (!auth()->user()->can('view bank reconciliation report')) {
            abort(403, 'Unauthorized access to this report.');
        }
        
        $user = Auth::user();

        // Get bank accounts for the current company
        $bankAccounts = BankAccount::with('chartAccount')
            ->whereHas('chartAccount.accountClassGroup', function ($query) use ($user) {
                $query->where('company_id', $user->company_id);
            })
            ->orderBy('name')
            ->get();

        // Get recent reconciliations for statistics
        $recentReconciliations = BankReconciliation::with(['bankAccount', 'user'])
            ->whereHas('bankAccount.chartAccount.accountClassGroup', function ($query) use ($user) {
                $query->where('company_id', $user->company_id);
            })
            ->orderBy('reconciliation_date', 'desc')
            ->limit(5)
            ->get();

        // Calculate statistics
        $stats = [
            'total_reconciliations' => BankReconciliation::whereHas('bankAccount.chartAccount.accountClassGroup', function ($query) use ($user) {
                $query->where('company_id', $user->company_id);
            })->count(),
            'completed_reconciliations' => BankReconciliation::whereHas('bankAccount.chartAccount.accountClassGroup', function ($query) use ($user) {
                $query->where('company_id', $user->company_id);
            })->where('status', 'completed')->count(),
            'pending_reconciliations' => BankReconciliation::whereHas('bankAccount.chartAccount.accountClassGroup', function ($query) use ($user) {
                $query->where('company_id', $user->company_id);
            })->whereIn('status', ['draft', 'in_progress'])->count(),
        ];

        return view('accounting.reports.bank-reconciliation.index', compact('bankAccounts', 'recentReconciliations', 'stats'));
    }

    /**
     * Generate and display the bank reconciliation report.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|in:draft,in_progress,completed,cancelled',
            'export_format' => 'nullable|in:pdf,excel',
        ]);

        $user = Auth::user();
        $query = BankReconciliation::with([
            'bankAccount.chartAccount',
            'user',
            'branch',
            'reconciliationItems.glTransaction.chartAccount',
            'reconciliationItems.matchedWithItem',
            'reconciliationItems.reconciledBy'
        ])
        ->whereHas('bankAccount.chartAccount.accountClassGroup', function ($query) use ($user) {
            $query->where('company_id', $user->company_id);
        });

        // Apply filters
        if ($request->filled('bank_account_id')) {
            $query->where('bank_account_id', $request->bank_account_id);
        }

        if ($request->filled('start_date')) {
            $query->where('reconciliation_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('reconciliation_date', '<=', $request->end_date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reconciliations = $query->orderBy('reconciliation_date', 'desc')->get();

        // Get filter data for the view
        $bankAccounts = BankAccount::with('chartAccount')
            ->whereHas('chartAccount.accountClassGroup', function ($query) use ($user) {
                $query->where('company_id', $user->company_id);
            })
            ->orderBy('name')
            ->get();

        $filters = $request->only(['bank_account_id', 'start_date', 'end_date', 'status']);

        // Calculate report statistics
        $reportStats = [
            'total_reconciliations' => $reconciliations->count(),
            'total_bank_balance' => $reconciliations->sum('bank_statement_balance'),
            'total_book_balance' => $reconciliations->sum('book_balance'),
            'total_difference' => $reconciliations->sum('difference'),
            'completed_count' => $reconciliations->where('status', 'completed')->count(),
            'pending_count' => $reconciliations->whereIn('status', ['draft', 'in_progress'])->count(),
        ];

        // If PDF export is requested
        if ($request->export_format === 'pdf') {
            return $this->exportToPdf($reconciliations, $filters, $reportStats, $user);
        }

        return view('accounting.reports.bank-reconciliation.generate', compact(
            'reconciliations',
            'bankAccounts',
            'filters',
            'reportStats'
        ));
    }

    /**
     * Export bank reconciliation report to PDF.
     */
    private function exportToPdf($reconciliations, $filters, $reportStats, $user)
    {
        $company = $user->company;
        $branch = $user->branch;

        // Prepare data for PDF
        $pdfData = [
            'reconciliations' => $reconciliations,
            'filters' => $filters,
            'reportStats' => $reportStats,
            'company' => $company,
            'branch' => $branch,
            'user' => $user,
            'generated_at' => now(),
        ];

        // Generate PDF
        $pdf = Pdf::loadView('accounting.reports.bank-reconciliation.pdf', $pdfData);

        // Set PDF options
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('margin-top', 10);
        $pdf->setOption('margin-right', 10);
        $pdf->setOption('margin-bottom', 10);
        $pdf->setOption('margin-left', 10);

        // Generate filename
        $filename = 'bank_reconciliation_report_' . date('Y-m-d_H-i-s') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Show detailed reconciliation report for a specific reconciliation.
     */
    public function show(BankReconciliation $bankReconciliation)
    {
        $user = Auth::user();

        // Check if user has access to this reconciliation
        if ($bankReconciliation->bankAccount->chartAccount->accountClassGroup->company_id !== $user->company_id) {
            abort(403, 'Unauthorized access to this reconciliation.');
        }

        $bankReconciliation->load([
            'bankAccount.chartAccount',
            'user',
            'branch',
            'reconciliationItems.glTransaction.chartAccount',
            'reconciliationItems.matchedWithItem',
            'reconciliationItems.reconciledBy'
        ]);

        // Get unreconciled items
        $unreconciledBankItems = $bankReconciliation->reconciliationItems()
            ->where('is_bank_statement_item', true)
            ->where('is_reconciled', false)
            ->get();

        $unreconciledBookItems = $bankReconciliation->reconciliationItems()
            ->where('is_book_entry', true)
            ->where('is_reconciled', false)
            ->get();

        $reconciledItems = $bankReconciliation->reconciliationItems()
            ->where('is_reconciled', true)
            ->with(['matchedWithItem', 'reconciledBy'])
            ->get();

        return view('accounting.reports.bank-reconciliation.show', compact(
            'bankReconciliation',
            'unreconciledBankItems',
            'unreconciledBookItems',
            'reconciledItems'
        ));
    }

    /**
     * Export specific reconciliation to PDF.
     */
    public function exportReconciliation(BankReconciliation $bankReconciliation, Request $request)
    {
        $user = Auth::user();

        // Check if user has access to this reconciliation
        if ($bankReconciliation->bankAccount->chartAccount->accountClassGroup->company_id !== $user->company_id) {
            abort(403, 'Unauthorized access to this reconciliation.');
        }

        $bankReconciliation->load([
            'bankAccount.chartAccount',
            'user',
            'branch',
            'reconciliationItems.glTransaction.chartAccount',
            'reconciliationItems.matchedWithItem',
            'reconciliationItems.reconciledBy'
        ]);

        // Get all items
        $unreconciledBankItems = $bankReconciliation->reconciliationItems()
            ->where('is_bank_statement_item', true)
            ->where('is_reconciled', false)
            ->get();

        $unreconciledBookItems = $bankReconciliation->reconciliationItems()
            ->where('is_book_entry', true)
            ->where('is_reconciled', false)
            ->get();

        $reconciledItems = $bankReconciliation->reconciliationItems()
            ->where('is_reconciled', true)
            ->with(['matchedWithItem', 'reconciledBy'])
            ->get();

        $company = $user->company;
        $branch = $user->branch;

        // Prepare data for PDF
        $pdfData = [
            'bankReconciliation' => $bankReconciliation,
            'unreconciledBankItems' => $unreconciledBankItems,
            'unreconciledBookItems' => $unreconciledBookItems,
            'reconciledItems' => $reconciledItems,
            'company' => $company,
            'branch' => $branch,
            'user' => $user,
            'generated_at' => now(),
        ];

        // Generate PDF
        $pdf = Pdf::loadView('accounting.reports.bank-reconciliation.reconciliation-pdf', $pdfData);

        // Set PDF options
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('margin-top', 10);
        $pdf->setOption('margin-right', 10);
        $pdf->setOption('margin-bottom', 10);
        $pdf->setOption('margin-left', 10);

        // Generate filename
        $filename = 'bank_reconciliation_' . $bankReconciliation->bankAccount->name . '_' . $bankReconciliation->reconciliation_date->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }
}
