<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Branch;
use App\Models\ChartAccount;
use App\Models\GlTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Vinkla\Hashids\Facades\Hashids;

class BankAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bankAccounts = BankAccount::with('chartAccount.accountClassGroup.accountClass')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Calculate balance for each bank account in the paginated result
        $bankAccounts->getCollection()->transform(function ($bankAccount) {
            $debits = GlTransaction::where('chart_account_id', $bankAccount->chart_account_id)
                ->where('nature', 'debit')
                ->sum('amount');
            $credits = GlTransaction::where('chart_account_id', $bankAccount->chart_account_id)
                ->where('nature', 'credit')
                ->sum('amount');
            $bankAccount->balance = $debits - $credits;
            return $bankAccount;
        });

        // Calculate statistics
        $totalAccounts = BankAccount::count();

        // Calculate balances from GL transactions for statistics
        $allBankAccounts = BankAccount::with('chartAccount')->get()->map(function ($bankAccount) {
            $debits = GlTransaction::where('chart_account_id', $bankAccount->chart_account_id)
                ->where('nature', 'debit')
                ->sum('amount');
            $credits = GlTransaction::where('chart_account_id', $bankAccount->chart_account_id)
                ->where('nature', 'credit')
                ->sum('amount');
            $bankAccount->balance = $debits - $credits;
            return $bankAccount;
        });

        $totalBalance = $allBankAccounts->sum('balance');
        $positiveBalanceAccounts = $allBankAccounts->where('balance', '>', 0)->count();
        $negativeBalanceAccounts = $allBankAccounts->where('balance', '<', 0)->count();

        return view('bank-accounts.index', compact('bankAccounts', 'totalAccounts', 'totalBalance', 'positiveBalanceAccounts', 'negativeBalanceAccounts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        
        $chartAccounts = ChartAccount::with('accountClassGroup.accountClass')
            ->whereHas('accountClassGroup.accountClass', function ($q) {
                $q->whereIn('name', ['Assets', 'Equity']);
            })
            ->get();

        // Get branches for the current company
        $branches = Branch::where('company_id', $user->company_id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('bank-accounts.create', compact('chartAccounts', 'branches'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'chart_account_id' => 'required|exists:chart_accounts,id',
            'name' => 'required|string|max:255',
            'account_number' => 'required|string|max:255|unique:bank_accounts,account_number',
            'branches' => 'nullable|array',
            'branches.*' => 'exists:branches,id',
        ]);

        $bankAccount = BankAccount::create($request->only(['chart_account_id', 'name', 'account_number']));

        // Sync branches if provided
        if ($request->has('branches')) {
            $bankAccount->branches()->sync($request->branches);
        }

        return redirect()->route('accounting.bank-accounts')
            ->with('success', 'Bank account created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show($encodedId)
    {
        // Decode the ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('accounting.bank-accounts')->withErrors(['Bank account not found.']);
        }

        $bankAccount = BankAccount::findOrFail($decoded[0]);
        $bankAccount->load('chartAccount.accountClassGroup.accountClass');

        return view('bank-accounts.show', compact('bankAccount'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($encodedId)
    {
        // Decode the ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('accounting.bank-accounts')->withErrors(['Bank account not found.']);
        }

        $user = Auth::user();
        $bankAccount = BankAccount::with('branches')->findOrFail($decoded[0]);
        $chartAccounts = ChartAccount::with('accountClassGroup.accountClass')
            ->orderBy('account_name')
            ->get();

        // Get branches for the current company
        $branches = Branch::where('company_id', $user->company_id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('bank-accounts.edit', compact('bankAccount', 'chartAccounts', 'branches'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $encodedId)
    {
        // Decode the ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('accounting.bank-accounts')->withErrors(['Bank account not found.']);
        }

        $bankAccount = BankAccount::findOrFail($decoded[0]);

        $request->validate([
            'chart_account_id' => 'required|exists:chart_accounts,id',
            'name' => 'required|string|max:255',
            'account_number' => 'required|string|max:255|unique:bank_accounts,account_number,' . $bankAccount->id,
            'branches' => 'nullable|array',
            'branches.*' => 'exists:branches,id',
        ]);

        $bankAccount->update($request->only(['chart_account_id', 'name', 'account_number']));

        // Sync branches - if no branches provided, sync empty array to remove all
        $bankAccount->branches()->sync($request->branches ?? []);

        return redirect()->route('accounting.bank-accounts')
            ->with('success', 'Bank account updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($encodedId)
    {
        // Decode the ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('accounting.bank-accounts')->withErrors(['Bank account not found.']);
        }

        $bankAccount = BankAccount::findOrFail($decoded[0]);

        // Prevent delete if used in GL Transactions
        $hasGlTransactions = $bankAccount->glTransactions()->exists();
        if ($hasGlTransactions) {
            return redirect()->route('accounting.bank-accounts')->withErrors(['This bank account cannot be deleted because its chart account is used in GL Transactions.']);
        }

        try {
            $bankAccount->delete();
            return redirect()->route('accounting.bank-accounts')->with('success', 'Bank account deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete bank account. Please try again.');
        }
    }
}