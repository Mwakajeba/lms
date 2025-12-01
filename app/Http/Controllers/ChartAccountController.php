<?php

namespace App\Http\Controllers;

use App\Models\AccountClass;
use App\Models\AccountClassGroup;
use App\Models\ChartAccount;
use App\Models\CashFlowCategory;
use App\Models\EquityCategory;
use App\Models\GlTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Vinkla\Hashids\Facades\Hashids;
use Yajra\DataTables\Facades\DataTables;

class ChartAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $user = auth()->user();
        
        // Calculate stats only
        $baseQuery = ChartAccount::whereHas('accountClassGroup', function ($query) use ($user) {
            $query->where('company_id', $user->company_id);
        });

        $stats = [
            'total' => $baseQuery->count(),
            'cash_flow' => (clone $baseQuery)->where('has_cash_flow', true)->count(),
            'equity' => (clone $baseQuery)->where('has_equity', true)->count(),
        ];

        return view('chart-accounts.index', compact('stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $accountClassGroups = AccountClassGroup::with('accountClass')->get();
        $accountClasses = AccountClass::all();
        $cashFlowCategories = CashFlowCategory::all();
        $equityCategories = EquityCategory::all();
        return view('chart-accounts.create', compact('accountClassGroups', 'accountClasses', 'cashFlowCategories', 'equityCategories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'account_class_group_id' => 'required|exists:account_class_groups,id',
            'account_code' => 'required|string|max:255|unique:chart_accounts,account_code',
            'account_name' => 'required|string|max:255',
            'has_cash_flow' => 'boolean',
            'has_equity' => 'boolean',
            'cash_flow_category_id' => 'nullable|exists:cash_flow_categories,id',
            'equity_category_id' => 'nullable|exists:equity_categories,id',
        ]);

        // Range validation
        $group = AccountClassGroup::with('accountClass')->find($request->account_class_group_id);
        $class = $group ? $group->accountClass : null;
        $rangeFrom = $class ? $class->range_from : null;
        $rangeTo = $class ? $class->range_to : null;
        $accountCode = (int) $request->account_code;
        if ($rangeFrom !== null && $rangeTo !== null && ($accountCode < $rangeFrom || $accountCode > $rangeTo)) {
            return back()->withErrors(['account_code' => "Account code must be between $rangeFrom and $rangeTo for the selected class."])->withInput();
        }

        // Handle boolean fields properly for unchecked checkboxes
        $data = $request->all();
        $data['has_cash_flow'] = $request->has('has_cash_flow');
        $data['has_equity'] = $request->has('has_equity');

        // Set category IDs to null if checkboxes are unchecked
        if (!$data['has_cash_flow']) {
            $data['cash_flow_category_id'] = null;
        }
        if (!$data['has_equity']) {
            $data['equity_category_id'] = null;
        }

        ChartAccount::create($data);

        return redirect()->route('accounting.chart-accounts.index')
            ->with('success', 'Chart Account created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($encodedId)
    {
        // Decode the ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('accounting.chart-accounts.index')->withErrors(['Chart Account not found.']);
        }

        $chartAccount = ChartAccount::findOrFail($decoded[0]);
        $chartAccount->load(['accountClassGroup.accountClass', 'cashFlowCategory', 'equityCategory']);
        
        // Calculate account balance from GL transactions
        $accountBalance = GlTransaction::where('chart_account_id', $chartAccount->id)
            ->selectRaw('SUM(CASE WHEN nature = "debit" THEN amount ELSE -amount END) as balance')
            ->value('balance') ?? 0;
        
        return view('chart-accounts.show', compact('chartAccount', 'accountBalance'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($encodedId)
    {
        // Decode the ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('accounting.chart-accounts.index')->withErrors(['Chart Account not found.']);
        }

        $chartAccount = ChartAccount::findOrFail($decoded[0]);
        $accountClassGroups = AccountClassGroup::with('accountClass')->get();
        $accountClasses = AccountClass::all();
        $cashFlowCategories = CashFlowCategory::all();
        $equityCategories = EquityCategory::all();
        return view('chart-accounts.edit', compact('chartAccount', 'accountClassGroups', 'accountClasses', 'cashFlowCategories', 'equityCategories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $encodedId)
    {
        // Decode chart account ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('accounting.chart-accounts.index')->withErrors(['Chart Account not found.']);
        }

        $chartAccount = ChartAccount::findOrFail($decoded[0]);

        $request->validate([
            'account_class_group_id' => 'required|exists:account_class_groups,id',
            'account_code' => 'required|string|max:255|unique:chart_accounts,account_code,' . $chartAccount->id,
            'account_name' => 'required|string|max:255',
            'has_cash_flow' => 'boolean',
            'has_equity' => 'boolean',
            'cash_flow_category_id' => 'nullable|exists:cash_flow_categories,id',
            'equity_category_id' => 'nullable|exists:equity_categories,id',
        ]);

        // Range validation
        $group = AccountClassGroup::with('accountClass')->find($request->account_class_group_id);
        $class = $group ? $group->accountClass : null;
        $rangeFrom = $class ? $class->range_from : null;
        $rangeTo = $class ? $class->range_to : null;
        $accountCode = (int) $request->account_code;
        if ($rangeFrom !== null && $rangeTo !== null && ($accountCode < $rangeFrom || $accountCode > $rangeTo)) {
            return back()->withErrors(['account_code' => "Account code must be between $rangeFrom and $rangeTo for the selected class."])->withInput();
        }

        // Handle boolean fields properly for unchecked checkboxes
        $data = $request->all();
        $data['has_cash_flow'] = $request->has('has_cash_flow');
        $data['has_equity'] = $request->has('has_equity');

        // Set category IDs to null if checkboxes are unchecked
        if (!$data['has_cash_flow']) {
            $data['cash_flow_category_id'] = null;
        }
        if (!$data['has_equity']) {
            $data['equity_category_id'] = null;
        }

        $chartAccount->update($data);

        return redirect()->route('accounting.chart-accounts.index')
            ->with('success', 'Chart Account updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($encodedId)
    {
        // Decode the encoded ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('accounting.chart-accounts.index')->withErrors(['Chart Account not found.']);
        }

        $chartAccount = ChartAccount::findOrFail($decoded[0]);

        // Check if chart account is being used in any related models
        $usageChecks = [
            'GL Transactions' => GlTransaction::where('chart_account_id', $chartAccount->id)->exists(),
            'Bank Accounts' => \App\Models\BankAccount::where('chart_account_id', $chartAccount->id)->exists(),
            'Payment Items' => \App\Models\PaymentItem::where('chart_account_id', $chartAccount->id)->exists(),
            'Receipt Items' => \App\Models\ReceiptItem::where('chart_account_id', $chartAccount->id)->exists(),
            'Journal Items' => \App\Models\JournalItem::where('chart_account_id', $chartAccount->id)->exists(),
            'Fees' => \App\Models\Fee::where('chart_account_id', $chartAccount->id)->exists(),
            'Penalties (Income Account)' => \App\Models\Penalty::where('penalty_income_account_id', $chartAccount->id)->exists(),
            'Penalties (Receivables Account)' => \App\Models\Penalty::where('penalty_receivables_account_id', $chartAccount->id)->exists(),
            'Cash Collateral Types' => \App\Models\CashCollateralType::where('chart_account_id', $chartAccount->id)->exists(),
            'Loan Products (Principal Receivable)' => \App\Models\LoanProduct::where('principal_receivable_account_id', $chartAccount->id)->exists(),
            'Loan Products (Interest Receivable)' => \App\Models\LoanProduct::where('interest_receivable_account_id', $chartAccount->id)->exists(),
            'Loan Products (Interest Revenue)' => \App\Models\LoanProduct::where('interest_revenue_account_id', $chartAccount->id)->exists(),
            'Budget Lines' => \App\Models\BudgetLine::where('account_id', $chartAccount->id)->exists(),
        ];

        $usedIn = [];
        foreach ($usageChecks as $model => $isUsed) {
            if ($isUsed) {
                $usedIn[] = $model;
            }
        }

        if (!empty($usedIn)) {
            $usageList = implode(', ', $usedIn);
            return redirect()->route('accounting.chart-accounts.index')
                ->withErrors(["This account cannot be deleted because it is used in: {$usageList}"]);
        }

        $chartAccount->delete();

        return redirect()->route('accounting.chart-accounts.index')
            ->with('success', 'Chart Account deleted successfully.');
    }

    // Ajax endpoint for DataTables
    public function getChartAccountsData(Request $request)
    {
        try {
            $user = auth()->user();

            $chartAccounts = ChartAccount::with(['accountClassGroup.accountClass', 'cashFlowCategory', 'equityCategory'])
                ->whereHas('accountClassGroup', function ($query) use ($user) {
                    $query->where('company_id', $user->company_id);
                })
                ->select('chart_accounts.*');

            return \DataTables::of($chartAccounts)
                ->addColumn('account_class_name', function ($account) {
                    return $account->accountClassGroup && $account->accountClassGroup->accountClass 
                        ? $account->accountClassGroup->accountClass->name 
                        : 'N/A';
                })
                ->addColumn('account_group_name', function ($account) {
                    return $account->accountClassGroup ? $account->accountClassGroup->name : 'N/A';
                })
                ->addColumn('cash_flow_badge', function ($account) {
                    return $account->has_cash_flow 
                        ? '<span class="badge bg-success">Yes</span>'
                        : '<span class="badge bg-secondary">No</span>';
                })
                ->addColumn('cash_flow_category_name', function ($account) {
                    if ($account->has_cash_flow && $account->cashFlowCategory) {
                        return '<span class="badge bg-info" title="' . e($account->cashFlowCategory->description ?? '') . '">
                                    <i class="bx bx-money-withdraw me-1"></i>' . e($account->cashFlowCategory->name) . '
                                </span>';
                    }
                    return '<span class="text-muted">-</span>';
                })
                ->addColumn('equity_badge', function ($account) {
                    return $account->has_equity 
                        ? '<span class="badge bg-success">Yes</span>'
                        : '<span class="badge bg-secondary">No</span>';
                })
                ->addColumn('equity_category_name', function ($account) {
                    if ($account->has_equity && $account->equityCategory) {
                        return '<span class="badge bg-warning" title="' . e($account->equityCategory->description ?? '') . '">
                                    <i class="bx bx-pie-chart-alt me-1"></i>' . e($account->equityCategory->name) . '
                                </span>';
                    }
                    return '<span class="text-muted">-</span>';
                })
                ->addColumn('formatted_created_at', function ($account) {
                    return $account->created_at ? $account->created_at->format('M d, Y') : 'N/A';
                })
                ->addColumn('actions', function ($account) {
                    $actions = '';
                    $encodedId = Hashids::encode($account->id);
                    
                    // View action
                    if (auth()->user()->can('view chart account details')) {
                        $actions .= '<a href="' . route('accounting.chart-accounts.show', $encodedId) . '" 
                                        class="btn btn-sm btn-outline-success me-1" 
                                        data-bs-toggle="tooltip" 
                                        data-bs-placement="top" 
                                        title="View account details">
                                        <i class="bx bx-show"></i>
                                    </a>';
                    }
                    
                    // Edit action
                    if (auth()->user()->can('edit chart account')) {
                        $actions .= '<a href="' . route('accounting.chart-accounts.edit', $encodedId) . '" 
                                        class="btn btn-sm btn-outline-info me-1" 
                                        data-bs-toggle="tooltip" 
                                        data-bs-placement="top" 
                                        title="Edit account">
                                        <i class="bx bx-edit"></i>
                                    </a>';
                    }
                    
                    // Delete action
                    if (auth()->user()->can('delete chart account')) {
                        // Check if account is being used
                        $usageChecks = [
                            'GL Transactions' => GlTransaction::where('chart_account_id', $account->id)->exists(),
                            'Bank Accounts' => \App\Models\BankAccount::where('chart_account_id', $account->id)->exists(),
                            'Payment Items' => \App\Models\PaymentItem::where('chart_account_id', $account->id)->exists(),
                            'Receipt Items' => \App\Models\ReceiptItem::where('chart_account_id', $account->id)->exists(),
                            'Journal Items' => \App\Models\JournalItem::where('chart_account_id', $account->id)->exists(),
                            'Fees' => \App\Models\Fee::where('chart_account_id', $account->id)->exists(),
                            'Penalties (Income Account)' => \App\Models\Penalty::where('penalty_income_account_id', $account->id)->exists(),
                            'Penalties (Receivables Account)' => \App\Models\Penalty::where('penalty_receivables_account_id', $account->id)->exists(),
                            'Cash Collateral Types' => \App\Models\CashCollateralType::where('chart_account_id', $account->id)->exists(),
                            'Loan Products (Principal Receivable)' => \App\Models\LoanProduct::where('principal_receivable_account_id', $account->id)->exists(),
                            'Loan Products (Interest Receivable)' => \App\Models\LoanProduct::where('interest_receivable_account_id', $account->id)->exists(),
                            'Loan Products (Interest Revenue)' => \App\Models\LoanProduct::where('interest_revenue_account_id', $account->id)->exists(),
                            'Budget Lines' => \App\Models\BudgetLine::where('account_id', $account->id)->exists(),
                        ];

                        $usedIn = [];
                        foreach ($usageChecks as $model => $isUsed) {
                            if ($isUsed) {
                                $usedIn[] = $model;
                            }
                        }

                        if (!empty($usedIn)) {
                            // Account is being used - show disabled button with tooltip
                            $usageList = implode(', ', $usedIn);
                            $actions .= '<button type="button" 
                                            class="btn btn-sm btn-outline-secondary"
                                            disabled
                                            data-bs-toggle="tooltip" 
                                            data-bs-placement="top" 
                                            title="Cannot delete - Account is used in: ' . e($usageList) . '">
                                            <i class="bx bx-lock"></i>
                                        </button>';
                        } else {
                            // Account is not being used - show delete button
                            $actions .= '<button type="button" 
                                            class="btn btn-sm btn-outline-danger delete-account-btn"
                                            data-bs-toggle="tooltip" 
                                            data-bs-placement="top" 
                                            title="Delete account"
                                            data-account-id="' . $encodedId . '"
                                            data-account-name="' . e($account->account_name) . '"
                                            data-account-code="' . e($account->account_code) . '">
                                            <i class="bx bx-trash"></i>
                                        </button>';
                        }
                    }
                    
                    return '<div class="text-center">' . $actions . '</div>';
                })
                ->rawColumns(['cash_flow_badge', 'cash_flow_category_name', 'equity_badge', 'equity_category_name', 'actions'])
                ->make(true);
        } catch (\Exception $e) {
            \Log::error('Chart Accounts DataTable Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load data: ' . $e->getMessage()], 500);
        }
    }
}
