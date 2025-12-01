<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BankAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        // Get bank accounts for the current company with their balances
        $bankAccounts = BankAccount::with([
            'chartAccount.accountClassGroup.accountClass',
            'glTransactions' // Eager load GL transactions to avoid N+1 queries
        ])
            ->whereHas('chartAccount.accountClassGroup', function ($query) use ($user) {
                $query->where('company_id', $user->company_id);
            })
            ->orderBy('name')
            ->paginate(15);

        // Calculate statistics
        $totalBalance = $bankAccounts->sum('balance');
        $totalAccounts = $bankAccounts->total();
        $positiveBalanceAccounts = $bankAccounts->filter(function ($account) {
            return $account->balance > 0;
        })->count();
        $negativeBalanceAccounts = $bankAccounts->filter(function ($account) {
            return $account->balance < 0;
        })->count();

        return view('bank-accounts.index', compact(
            'bankAccounts', 
            'totalBalance', 
            'totalAccounts',
            'positiveBalanceAccounts',
            'negativeBalanceAccounts'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource in storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
