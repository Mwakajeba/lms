<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use App\Models\JournalItem;
use App\Models\ChartAccount;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\GlTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use PDF;

class JournalController extends Controller
{
    public function index()
    {
        $journals = Journal::with('items')->latest()->get();
        return view('accounting.journals.index', compact('journals'));
    }

    public function create()
    {
        $branches = Branch::all();
        $customers = Customer::all();
        $accounts = ChartAccount::all();
        return view('accounting.journals.create', compact('accounts','customers', 'branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'description' => 'nullable|string',
            'items' => 'required|array|min:2', // Must have both debit and credit
            'items.*.account_id' => 'required|exists:chart_accounts,id',
            'items.*.amount' => 'required|numeric|min:0.01',
            'items.*.nature' => 'required|in:debit,credit',
            'items.*.description' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            // Generate a unique reference
            $nextId = Journal::max('id') + 1;
            $reference = 'JRN-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
            
            $journal = Journal::create([
                'date' => $request->date,
                'description' => $request->description,
                'branch_id' => Auth::user()->branch_id,
                'user_id' => Auth::id(),
                'reference_type' => 'Journal',
                'reference' => $reference,
            ]);

            foreach ($request->items as $item) {
                $journal->items()->create([
                    'chart_account_id' => $item['account_id'],
                    'amount' => $item['amount'],
                    'nature' => $item['nature'],
                    'description' => $item['description'] ?? null,
                ]);

                // Create GL transaction for each journal item
                GlTransaction::create([
                    'chart_account_id' => $item['account_id'],
                    'customer_id' => null,
                    'supplier_id' => null,
                    'amount' => $item['amount'],
                    'nature' => $item['nature'],
                    'transaction_id' => $journal->id,
                    'transaction_type' => 'journal',
                    'date' => $request->date,
                    'description' => $item['description'] ?? $request->description,
                    'branch_id' => Auth::user()->branch_id,
                    'user_id' => Auth::id(),
                ]);
            }
        });

        return redirect()->route('accounting.journals.index')->with('success', 'Journal entry created.');
    }

    public function show(Journal $journal)
    {
        $journal->load('items.chartAccount');
        return view('accounting.journals.show', compact('journal'));
    }

    public function edit(Journal $journal)
    {
        $accounts = ChartAccount::all();
        $journal->load('items');
        return view('accounting.journals.edit', compact('journal', 'accounts'));
    }

    public function update(Request $request, Journal $journal)
    {
        $request->validate([
            'date' => 'required|date',
            'description' => 'nullable|string',
            'items' => 'required|array|min:2',
            'items.*.account_id' => 'required|exists:chart_accounts,id',
            'items.*.amount' => 'required|numeric|min:0.01',
            'items.*.nature' => 'required|in:debit,credit',
            'items.*.description' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $journal) {
            $journal->update([
                'date' => $request->date,
                'description' => $request->description,
            ]);

            // Delete old GL transactions for this journal
            GlTransaction::where('transaction_id', $journal->id)
                        ->where('transaction_type', 'journal')
                        ->delete();

            $journal->items()->delete(); // Remove old items
            foreach ($request->items as $item) {
                $journal->items()->create([
                    'chart_account_id' => $item['account_id'],
                    'amount' => $item['amount'],
                    'nature' => $item['nature'],
                    'description' => $item['description'] ?? null,
                ]);

                // Create new GL transaction for each journal item
                GlTransaction::create([
                    'chart_account_id' => $item['account_id'],
                    'customer_id' => null,
                    'supplier_id' => null,
                    'amount' => $item['amount'],
                    'nature' => $item['nature'],
                    'transaction_id' => $journal->id,
                    'transaction_type' => 'journal',
                    'date' => $request->date,
                    'description' => $item['description'] ?? $request->description,
                    'branch_id' => Auth::user()->branch_id,
                    'user_id' => Auth::id(),
                ]);
            }
        });

        return redirect()->route('accounting.journals.index')->with('success', 'Journal entry updated.');
    }

    public function destroy(Journal $journal)
    {
        DB::transaction(function () use ($journal) {
            // Delete associated GL transactions
            GlTransaction::where('transaction_id', $journal->id)
                        ->where('transaction_type', 'journal')
                        ->delete();
            
            // Delete journal items
            $journal->items()->delete();
            
            // Delete the journal
        $journal->delete();
        });

        return redirect()->route('accounting.journals.index')->with('success', 'Journal entry deleted.');
    }

    public function exportPdf(Journal $journal)
    {
        $journal->load(['items.chartAccount', 'user', 'branch']);
        
        $pdf = PDF::loadView('accounting.journals.pdf', compact('journal'));
        $pdf->setPaper('A4', 'portrait');
        
        return $pdf->download('journal-' . $journal->reference . '.pdf');
    }
}

