<?php

namespace App\Http\Controllers;

use App\Models\CashCollateralType;
use Illuminate\Http\Request;
use App\Models\ChartAccount;

class CashCollateralTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cashCollaterals = CashCollateralType::with('chartAccount')->latest()->paginate(10);

        return view('cash_collateral_types.index', compact('cashCollaterals'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Only chart accounts where class is liabilities (fix: use relationship chain)
        $chartAccounts = ChartAccount::whereHas('accountClassGroup.accountClass', function($q) {
            $q->where('name', 'Liabilities');
        })->get();
        return view('cash_collateral_types.create', compact('chartAccounts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:cash_collateral_types,name',
            'chart_account_id' => 'nullable|exists:chart_accounts,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        CashCollateralType::create($validated);

        return redirect()->route('cash_collateral_types.index')
                         ->with('success', 'Cash Collateral Type created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(CashCollateralType $cashCollateralType)
    {
        $cashCollateralType->load('chartAccount'); // eager load relationship

        return view('cash_collateral_types.show', [
            'cashCollateral' => $cashCollateralType
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CashCollateralType $cashCollateralType)
    {
        // Only chart accounts where class is liabilities (fix: use relationship chain)
        $chartAccounts = ChartAccount::whereHas('accountClassGroup.accountClass', function($q) {
            $q->where('name', 'Liabilities');
        })->get();
        return view('cash_collateral_types.edit', compact('cashCollateralType', 'chartAccounts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CashCollateralType $cashCollateralType)
    {
        $validated = $request->validate([
            'name' => 'required|unique:cash_collateral_types,name,' . $cashCollateralType->id,
            'chart_account_id' => 'nullable|exists:chart_accounts,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $cashCollateralType->update($validated);

        return redirect()->route('cash_collateral_types.index')
                         ->with('success', 'Cash Collateral Type updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CashCollateralType $cashCollateralType)
    {
        $cashCollateralType->delete();

        return redirect()->route('cash_collateral_types.index')
                         ->with('success', 'Cash Collateral Type deleted successfully.');
    }
}
