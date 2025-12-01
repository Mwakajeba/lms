<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\BankReconciliation;
use App\Models\BankReconciliationItem;
use App\Models\GlTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BankReconciliationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        $reconciliations = BankReconciliation::with(['bankAccount', 'user'])
            ->whereHas('bankAccount.chartAccount.accountClassGroup', function ($query) use ($user) {
                $query->where('company_id', $user->company_id);
            })
            ->orderBy('reconciliation_date', 'desc')
            ->paginate(15);

        // Calculate statistics
        $stats = [
            'total' => $reconciliations->total(),
            'completed' => BankReconciliation::whereHas('bankAccount.chartAccount.accountClassGroup', function ($query) use ($user) {
                $query->where('company_id', $user->company_id);
            })->where('status', 'completed')->count(),
            'in_progress' => BankReconciliation::whereHas('bankAccount.chartAccount.accountClassGroup', function ($query) use ($user) {
                $query->where('company_id', $user->company_id);
            })->where('status', 'in_progress')->count(),
            'draft' => BankReconciliation::whereHas('bankAccount.chartAccount.accountClassGroup', function ($query) use ($user) {
                $query->where('company_id', $user->company_id);
            })->where('status', 'draft')->count(),
        ];

        return view('accounting.bank-reconciliation.index', compact('reconciliations', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();

        // Get bank accounts for the current company
        $bankAccounts = BankAccount::with('chartAccount')
            ->whereHas('chartAccount.accountClassGroup', function ($query) use ($user) {
                $query->where('company_id', $user->company_id);
            })
            ->orderBy('name')
            ->get();

        return view('accounting.bank-reconciliation.create', compact('bankAccounts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'reconciliation_date' => 'required|date',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'bank_statement_balance' => 'required|numeric',
            'notes' => 'nullable|string',
            'bank_statement_document' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $user = Auth::user();

            // Handle document upload
            $documentPath = null;
            if ($request->hasFile('bank_statement_document')) {
                $document = $request->file('bank_statement_document');
                $documentName = time() . '_' . $document->getClientOriginalName();
                $documentPath = $document->storeAs('bank_statements', $documentName, 'public');
            }

            // Create bank reconciliation
            $reconciliation = BankReconciliation::create([
                'bank_account_id' => $request->bank_account_id,
                'user_id' => $user->id,
                'branch_id' => $user->branch_id,
                'reconciliation_date' => $request->reconciliation_date,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'bank_statement_balance' => $request->bank_statement_balance,
                'notes' => $request->notes,
                'bank_statement_document' => $documentPath,
                'status' => 'draft',
            ]);

            // Calculate book balance from GL transactions
            $reconciliation->calculateBookBalance();

            // Set initial adjusted balances
            $reconciliation->update([
                'adjusted_bank_balance' => $reconciliation->bank_statement_balance,
                'adjusted_book_balance' => $reconciliation->book_balance,
            ]);

            // Calculate difference
            $reconciliation->calculateDifference();

            // Import GL transactions as book entries
            $this->importBookEntries($reconciliation);

            DB::commit();

            return redirect()->route('accounting.bank-reconciliation.show', $reconciliation)
                ->with('success', 'Bank reconciliation created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => 'Failed to create bank reconciliation: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($hash)
    {
        $id = \App\Helpers\HashIdHelper::decode($hash);
        if (!$id) {
            return redirect()->back()->withErrors(['error' => 'Invalid reconciliation id.']);
        }

        $bankReconciliation = BankReconciliation::findOrFail($id);
        $bankReconciliation->load([
            'bankAccount.chartAccount',
            'user',
            'branch',
            'reconciliationItems.glTransaction.chartAccount',
            'reconciliationItems.matchedWithItem',
            'reconciliationItems.reconciledBy'
        ]);

        // Get unreconciled items (not in balance)
        $unreconciledBankItems = $bankReconciliation->reconciliationItems()
            ->where('is_bank_statement_item', true)
            ->where('is_reconciled', false)
            ->orderBy('transaction_date', 'asc')
            ->get();

        $unreconciledBookItems = $bankReconciliation->reconciliationItems()
            ->where('is_book_entry', true)
            ->where('is_reconciled', false)
            ->orderBy('transaction_date', 'asc')
            ->get();

        // Get reconciled pairs, show only the book entry side for clarity
        $reconciledItems = $bankReconciliation->reconciliationItems()
            ->where('is_reconciled', true)
            ->where('is_book_entry', true)
            ->with(['matchedWithItem', 'reconciledBy'])
            ->orderBy('reconciled_at', 'desc')
            ->limit(10)
            ->get();

        // Compute total reconciled PAIRS (count only book entries)
        $totalReconciledCount = $bankReconciliation->reconciliationItems()
            ->where('is_reconciled', true)
            ->where('is_book_entry', true)
            ->count();

        return view('accounting.bank-reconciliation.show', compact(
            'bankReconciliation',
            'unreconciledBankItems',
            'unreconciledBookItems',
            'reconciledItems',
            'totalReconciledCount'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($hash)
    {
        $id = \App\Helpers\HashIdHelper::decode($hash);
        if (!$id) {
            return redirect()->back()->withErrors(['error' => 'Invalid reconciliation id.']);
        }
        $bankReconciliation = BankReconciliation::findOrFail($id);
        $user = Auth::user();

        // Get bank accounts for the current company
        $bankAccounts = BankAccount::with('chartAccount')
            ->whereHas('chartAccount.accountClassGroup', function ($query) use ($user) {
                $query->where('company_id', $user->company_id);
            })
            ->orderBy('name')
            ->get();

        return view('accounting.bank-reconciliation.edit', compact('bankReconciliation', 'bankAccounts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BankReconciliation $bankReconciliation)
    {
        $validator = Validator::make($request->all(), [
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'reconciliation_date' => 'required|date',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'bank_statement_balance' => 'required|numeric',
            'adjusted_bank_balance' => 'required|numeric',
            'adjusted_book_balance' => 'required|numeric',
            'notes' => 'nullable|string',
            'bank_statement_document' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Handle document upload
            if ($request->hasFile('bank_statement_document')) {
                // Delete old document if exists
                if ($bankReconciliation->bank_statement_document) {
                    Storage::disk('public')->delete($bankReconciliation->bank_statement_document);
                }
                
                $document = $request->file('bank_statement_document');
                $documentName = time() . '_' . $document->getClientOriginalName();
                $documentPath = $document->storeAs('bank_statements', $documentName, 'public');
                
                $bankReconciliation->bank_statement_document = $documentPath;
            }

            // Update bank reconciliation
            $bankReconciliation->update([
                'bank_account_id' => $request->bank_account_id,
                'reconciliation_date' => $request->reconciliation_date,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'bank_statement_balance' => $request->bank_statement_balance,
                'adjusted_bank_balance' => $request->adjusted_bank_balance,
                'adjusted_book_balance' => $request->adjusted_book_balance,
                'notes' => $request->notes,
            ]);

            // Calculate difference
            $bankReconciliation->calculateDifference();

            DB::commit();

            return redirect()->route('accounting.bank-reconciliation.show', $bankReconciliation)
                ->with('success', 'Bank reconciliation updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => 'Failed to update bank reconciliation: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BankReconciliation $bankReconciliation)
    {
        try {
            DB::beginTransaction();

            // Delete reconciliation items
            $bankReconciliation->reconciliationItems()->delete();

            // Delete reconciliation
            $bankReconciliation->delete();

            DB::commit();

            return redirect()->route('accounting.bank-reconciliation.index')
                ->with('success', 'Bank reconciliation deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete bank reconciliation: ' . $e->getMessage()]);
        }
    }

    /**
     * Add bank statement item.
     */
    public function addBankStatementItem(Request $request, BankReconciliation $bankReconciliation)
    {
        $validator = Validator::make($request->all(), [
            'reference' => 'nullable|string|max:255',
            'description' => 'required|string',
            'transaction_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'nature' => 'required|in:debit,credit',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            BankReconciliationItem::create([
                'bank_reconciliation_id' => $bankReconciliation->id,
                'transaction_type' => 'bank_statement',
                'reference' => $request->reference,
                'description' => $request->description,
                'transaction_date' => $request->transaction_date,
                'amount' => $request->amount,
                'nature' => $request->nature,
                'is_bank_statement_item' => true,
                'is_book_entry' => false,
                'notes' => $request->notes,
            ]);

            // Recalculate adjusted bank balance
            $this->recalculateAdjustedBalances($bankReconciliation);

            return redirect()->back()
                ->with('success', 'Bank statement item added successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to add bank statement item: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Match items.
     */
    public function matchItems(Request $request, $hash)
    {
        $id = \App\Helpers\HashIdHelper::decode($hash);
        if (!$id) {
            return redirect()->back()->withErrors(['error' => 'Invalid reconciliation id.']);
        }
        $bankReconciliation = BankReconciliation::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'bank_item_id' => 'required|exists:bank_reconciliation_items,id',
            'book_item_id' => 'required|exists:bank_reconciliation_items,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        try {
            $bankItem = BankReconciliationItem::find($request->bank_item_id);
            $bookItem = BankReconciliationItem::find($request->book_item_id);

            // Match the items
            $bankItem->matchWith($bookItem->id);
            $bookItem->matchWith($bankItem->id);

            return redirect()->back()
                ->with('success', 'Items matched successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to match items: ' . $e->getMessage()]);
        }
    }

    /**
     * Unmatch items.
     */
    public function unmatchItems(Request $request, $hash)
    {
        $id = \App\Helpers\HashIdHelper::decode($hash);
        if (!$id) {
            return redirect()->back()->withErrors(['error' => 'Invalid reconciliation id.']);
        }
        $bankReconciliation = BankReconciliation::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'item_id' => 'required|exists:bank_reconciliation_items,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        try {
            $item = BankReconciliationItem::find($request->item_id);
            $matchedItem = $item->getMatchedItem();

            // Smart reverse rules:
            // - If either side is a placeholder bank statement (created during confirmation), delete that placeholder
            //   and only move the real system item back to unreconciled.
            // - Otherwise (real bank + real book), unmatch both.
            $isItemPlaceholderBank = $item->is_bank_statement_item
                && is_null($item->gl_transaction_id)
                && ((is_string($item->description) && str_starts_with($item->description, 'Statement confirmed'))
                    || (is_string($item->notes) && str_contains($item->notes, 'Confirmed from physical statement')));

            $isMatchPlaceholderBank = $matchedItem && $matchedItem->is_bank_statement_item
                && is_null($matchedItem->gl_transaction_id)
                && ((is_string($matchedItem->description) && str_starts_with($matchedItem->description, 'Statement confirmed'))
                    || (is_string($matchedItem->notes) && str_contains($matchedItem->notes, 'Confirmed from physical statement')));

            if ($isItemPlaceholderBank && $matchedItem) {
                // Delete placeholder (clicked item), unmatch and set real item to unreconciled
                $matchedItem->markAsUnreconciled();
                $item->delete();
            } elseif ($isMatchPlaceholderBank) {
                // Delete placeholder (paired item), unmatch and set clicked real item to unreconciled
                $item->markAsUnreconciled();
                $matchedItem->delete();
            } else {
                // Fallback: unmatch both
                $item->markAsUnreconciled();
                if ($matchedItem) {
                    $matchedItem->markAsUnreconciled();
                }
            }

            return redirect()->back()
                ->with('success', 'Items unmatched successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to unmatch items: ' . $e->getMessage()]);
        }
    }

    /**
     * Confirm a book entry against the physical bank statement by creating a matching
     * placeholder bank statement item and marking both as reconciled.
     */
    public function confirmBookItem(Request $request, $hash)
    {
        $id = \App\Helpers\HashIdHelper::decode($hash);
        if (!$id) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid reconciliation id.'
            ], 422);
        }
        $bankReconciliation = BankReconciliation::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'book_item_id' => 'required|exists:bank_reconciliation_items,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $bookItem = BankReconciliationItem::find($request->book_item_id);

            if ($bookItem->is_reconciled) {
                return response()->json([
                    'success' => true,
                    'already_reconciled' => true,
                ]);
            }

            // Create a placeholder bank statement item based on the book entry details
            $bankItem = BankReconciliationItem::create([
                'bank_reconciliation_id' => $bankReconciliation->id,
                'transaction_type' => 'bank_statement',
                'reference' => $bookItem->reference,
                'description' => 'Statement confirmed: ' . $bookItem->description,
                'transaction_date' => $bookItem->transaction_date,
                'amount' => $bookItem->amount,
                'nature' => $bookItem->nature,
                'is_bank_statement_item' => true,
                'is_book_entry' => false,
                'notes' => 'Confirmed from physical statement',
            ]);

            // Match the items both ways
            $bankItem->matchWith($bookItem->id);
            $bookItem->matchWith($bankItem->id);

            return response()->json([
                'success' => true,
                'bank_item_id' => $bankItem->id,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to confirm item: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Complete reconciliation.
     */
    public function completeReconciliation($hash)
    {
        $id = \App\Helpers\HashIdHelper::decode($hash);
        if (!$id) {
            return redirect()->back()->withErrors(['error' => 'Invalid reconciliation id.']);
        }
        $bankReconciliation = BankReconciliation::findOrFail($id);
        try {
            $bankReconciliation->update(['status' => 'completed']);

            return redirect()->route('accounting.bank-reconciliation.show', $bankReconciliation)
                ->with('success', 'Bank reconciliation completed successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to complete reconciliation: ' . $e->getMessage()]);
        }
    }

    /**
     * Import book entries from GL transactions.
     */
    private function importBookEntries(BankReconciliation $reconciliation)
    {
        $glTransactions = GlTransaction::where('chart_account_id', $reconciliation->bankAccount->chart_account_id)
            ->whereBetween('date', [$reconciliation->start_date, $reconciliation->end_date])
            ->get();

        foreach ($glTransactions as $glTransaction) {
            BankReconciliationItem::create([
                'bank_reconciliation_id' => $reconciliation->id,
                'gl_transaction_id' => $glTransaction->id,
                'transaction_type' => 'book_entry',
                'reference' => $glTransaction->transaction_id,
                'description' => $glTransaction->description,
                'transaction_date' => $glTransaction->date,
                'amount' => $glTransaction->amount,
                'nature' => $glTransaction->nature,
                'is_bank_statement_item' => false,
                'is_book_entry' => true,
                'notes' => 'Imported from GL transaction',
            ]);
        }
    }

    /**
     * Recalculate adjusted balances.
     */
    private function recalculateAdjustedBalances(BankReconciliation $reconciliation)
    {
        // Calculate adjusted bank balance
        $bankItems = $reconciliation->reconciliationItems()
            ->where('is_bank_statement_item', true)
            ->get();

        $bankDebits = $bankItems->where('nature', 'debit')->sum('amount');
        $bankCredits = $bankItems->where('nature', 'credit')->sum('amount');
        $adjustedBankBalance = $reconciliation->bank_statement_balance + $bankDebits - $bankCredits;

        // Calculate adjusted book balance
        $bookItems = $reconciliation->reconciliationItems()
            ->where('is_book_entry', true)
            ->where('is_reconciled', false)
            ->get();

        $bookDebits = $bookItems->where('nature', 'debit')->sum('amount');
        $bookCredits = $bookItems->where('nature', 'credit')->sum('amount');
        $adjustedBookBalance = $reconciliation->book_balance + $bookDebits - $bookCredits;

        $reconciliation->update([
            'adjusted_bank_balance' => $adjustedBankBalance,
            'adjusted_book_balance' => $adjustedBookBalance,
        ]);

        $reconciliation->calculateDifference();
    }

    /**
     * Update book balance when new transactions are added to the reconciliation period.
     */
    public function updateBookBalance($hash)
    {
        $id = \App\Helpers\HashIdHelper::decode($hash);
        if (!$id) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid reconciliation id.'
            ], 400);
        }
        $bankReconciliation = BankReconciliation::findOrFail($id);
        try {
            DB::beginTransaction();

            // Check if reconciliation is completed
            if ($bankReconciliation->status === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot update completed reconciliation.'
                ], 400);
            }

            // Recalculate book balance from GL transactions
            $bankReconciliation->calculateBookBalance();

            // Recalculate adjusted balances
            $this->recalculateAdjustedBalances($bankReconciliation);

            // Import only missing GL transactions as book entries
            $service = app(\App\Services\BankReconciliationService::class);
            $service->importMissingTransactions($bankReconciliation);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Book balance updated successfully.',
                'book_balance' => $bankReconciliation->formatted_book_balance,
                'difference' => $bankReconciliation->formatted_difference
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update book balance: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Refresh all active reconciliations
     */
    public function refreshAllReconciliations()
    {
        try {
            DB::beginTransaction();

            $user = Auth::user();
            $service = app(\App\Services\BankReconciliationService::class);

            // Get all active reconciliations for the current company
            $activeReconciliations = BankReconciliation::whereHas('bankAccount.chartAccount.accountClassGroup', function ($query) use ($user) {
                $query->where('company_id', $user->company_id);
            })
            ->whereIn('status', ['draft', 'in_progress'])
            ->get();

            $updatedCount = 0;
            $errors = [];

            foreach ($activeReconciliations as $reconciliation) {
                try {
                    // Recalculate book balance
                    $reconciliation->calculateBookBalance();
                    
                    // Recalculate adjusted balances
                    $this->recalculateAdjustedBalances($reconciliation);
                    
                    // Import missing transactions
                    $service->importMissingTransactions($reconciliation);
                    
                    $updatedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Reconciliation ID {$reconciliation->id}: " . $e->getMessage();
                }
            }

            DB::commit();

            $message = "Successfully refreshed {$updatedCount} reconciliations.";
            if (!empty($errors)) {
                $message .= " Errors: " . implode('; ', $errors);
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'updated_count' => $updatedCount,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to refresh reconciliations: ' . $e->getMessage()
            ], 500);
        }
    }
}
