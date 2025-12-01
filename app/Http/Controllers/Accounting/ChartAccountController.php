<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AccountClassGroup;
use App\Models\AccountClass;
use App\Models\ChartAccount;
use App\Models\CashFlowCategory;
use App\Models\EquityCategory;
use Illuminate\Support\Facades\Auth;
use Vinkla\Hashids\Facades\Hashids;
use Yajra\DataTables\Facades\DataTables;

class ChartAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

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

    // Ajax endpoint for DataTables
    public function getChartAccountsData(Request $request)
    {
        try {
            $user = Auth::user();

            $chartAccounts = ChartAccount::with(['accountClassGroup.accountClass', 'cashFlowCategory', 'equityCategory'])
                ->whereHas('accountClassGroup', function ($query) use ($user) {
                    $query->where('company_id', $user->company_id);
                })
                ->select('chart_accounts.*');

        return DataTables::of($chartAccounts)
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
                
                return '<div class="text-center">' . $actions . '</div>';
            })
            ->rawColumns(['cash_flow_badge', 'cash_flow_category_name', 'equity_badge', 'equity_category_name', 'actions'])
            ->make(true);
        } catch (\Exception $e) {
            \Log::error('Chart Accounts DataTable Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load data: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $chartAccount = null;
        if ($id) {
            $chartAccount = \App\Models\ChartAccount::findOrFail($id);
        }
        $accountClassGroups = AccountClassGroup::with('accountClass')->get();
        $accountClasses = AccountClass::all();
        $cashFlowCategories = CashFlowCategory::all();
        $equityCategories = EquityCategory::all();
        return view('chart-accounts.edit', compact('chartAccount', 'accountClassGroups', 'accountClasses', 'cashFlowCategories', 'equityCategories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
