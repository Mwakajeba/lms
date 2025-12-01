<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Supplier;
use App\Models\ChartAccount;
use App\Models\GlTransaction;
use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\BankAccount;
use App\Models\Branch;
use App\Models\Company;
use App\Helpers\HashIdHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BillPurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $companyId = $user->company_id ?? null;

        if ($companyId) {
            $bills = Bill::with(['supplier', 'creditAccount', 'user', 'branch'])
                ->where('company_id', $companyId)
                ->orderBy('date', 'desc')
                ->get();
        } else {
            $bills = Bill::with(['supplier', 'creditAccount', 'user', 'branch'])
                ->orderBy('date', 'desc')
                ->get();
        }

        $stats = [
            'total' => $bills->count(),
            'paid' => $bills->where('status', 'paid')->count(),
            'pending' => $bills->where('status', 'pending')->count(),
            'overdue' => $bills->where('status', 'overdue')->count(),
        ];

        return view('accounting.bill-purchases.index', compact('bills', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = auth()->user();
        $companyId = $user->company_id ?? null;

        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();
        $chartAccounts = ChartAccount::orderBy('account_name')->get();

        return view('accounting.bill-purchases.create', compact('suppliers', 'chartAccounts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:date',
            'supplier_id' => 'required|exists:suppliers,id',
            'note' => 'nullable|string|max:1000',
            'credit_account' => 'required|exists:chart_accounts,id',
            'line_items' => 'required|array|min:1',
            'line_items.*.debit_account' => 'required|exists:chart_accounts,id',
            'line_items.*.amount' => 'required|numeric|min:0.01',
            'line_items.*.description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $user = auth()->user();
            $companyId = $user->company_id ?? Company::first()->id ?? 1;

            // Calculate total amount
            $totalAmount = collect($request->line_items)->sum('amount');

            // Generate reference
            $reference = 'BILL-' . date('Ymd') . '-' . str_pad(Bill::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);

            // Create bill
            $bill = Bill::create([
                'reference' => $reference,
                'date' => $request->date,
                'due_date' => $request->due_date,
                'supplier_id' => $request->supplier_id,
                'note' => $request->note,
                'credit_account' => $request->credit_account,
                'total_amount' => $totalAmount,
                'paid' => 0,
                'user_id' => $user->id,
                'branch_id' => $user->branch_id,
                'company_id' => $companyId,
            ]);

            // Create bill items
            $billItems = [];
            foreach ($request->line_items as $lineItem) {
                $billItems[] = [
                    'bill_id' => $bill->id,
                    'debit_account' => $lineItem['debit_account'],
                    'amount' => $lineItem['amount'],
                    'description' => $lineItem['description'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            BillItem::insert($billItems);

            // Create GL transactions
                            // Debit each account from line items
                foreach ($request->line_items as $lineItem) {
                    GlTransaction::create([
                        'chart_account_id' => $lineItem['debit_account'],
                        'supplier_id' => $request->supplier_id,
                        'amount' => $lineItem['amount'],
                        'nature' => 'debit',
                        'transaction_id' => $bill->id,
                        'transaction_type' => 'bill',
                        'date' => $request->date,
                        'description' => $lineItem['description'] ?: "Bill purchase {$bill->reference}",
                        'branch_id' => $user->branch_id,
                        'user_id' => $user->id,
                    ]);
                }

                // Credit the accounts payable (credit_account)
                GlTransaction::create([
                    'chart_account_id' => $request->credit_account,
                    'supplier_id' => $request->supplier_id,
                    'amount' => $totalAmount,
                    'nature' => 'credit',
                    'transaction_id' => $bill->id,
                    'transaction_type' => 'bill',
                    'date' => $request->date,
                    'description' => "Bill purchase {$bill->reference}",
                    'branch_id' => $user->branch_id,
                    'user_id' => $user->id,
                ]);

            DB::commit();

            return redirect()->route('accounting.bill-purchases.show', $bill)
                ->with('success', 'Bill purchase created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => 'Failed to create bill purchase: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Bill $billPurchase)
    {
        $billPurchase->load([
            'supplier', 
            'creditAccount', 
            'user', 
            'branch', 
            'company',
            'billItems.debitAccount',
            'payments.bankAccount',
            'payments.user',
            'glTransactions.chartAccount'
        ]);

        return view('accounting.bill-purchases.show', compact('billPurchase'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bill $billPurchase)
    {
        $billPurchase->load(['billItems']);

        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();
        $chartAccounts = ChartAccount::orderBy('account_name')->get();

        return view('accounting.bill-purchases.edit', compact('billPurchase', 'suppliers', 'chartAccounts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Bill $billPurchase)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:date',
            'supplier_id' => 'required|exists:suppliers,id',
            'note' => 'nullable|string|max:1000',
            'credit_account' => 'required|exists:chart_accounts,id',
            'line_items' => 'required|array|min:1',
            'line_items.*.debit_account' => 'required|exists:chart_accounts,id',
            'line_items.*.amount' => 'required|numeric|min:0.01',
            'line_items.*.description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Delete existing GL transactions
            $billPurchase->glTransactions()->delete();

            // Delete existing bill items
            $billPurchase->billItems()->delete();

            // Calculate total amount
            $totalAmount = collect($request->line_items)->sum('amount');

            // Update bill
            $billPurchase->update([
                'date' => $request->date,
                'due_date' => $request->due_date,
                'supplier_id' => $request->supplier_id,
                'note' => $request->note,
                'credit_account' => $request->credit_account,
                'total_amount' => $totalAmount,
                'branch_id' => auth()->user()->branch_id,
            ]);

            // Create new bill items
            $billItems = [];
            foreach ($request->line_items as $lineItem) {
                $billItems[] = [
                    'bill_id' => $billPurchase->id,
                    'debit_account' => $lineItem['debit_account'],
                    'amount' => $lineItem['amount'],
                    'description' => $lineItem['description'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            BillItem::insert($billItems);

                            // Create new GL transactions
                // Debit each account from line items
                foreach ($request->line_items as $lineItem) {
                    GlTransaction::create([
                        'chart_account_id' => $lineItem['debit_account'],
                        'supplier_id' => $request->supplier_id,
                        'amount' => $lineItem['amount'],
                        'nature' => 'debit',
                        'transaction_id' => $billPurchase->id,
                        'transaction_type' => 'bill',
                        'date' => $request->date,
                        'description' => $lineItem['description'] ?: "Bill purchase {$billPurchase->reference}",
                        'branch_id' => auth()->user()->branch_id,
                        'user_id' => auth()->id(),
                    ]);
                }

                // Credit the accounts payable (credit_account)
                GlTransaction::create([
                    'chart_account_id' => $request->credit_account,
                    'supplier_id' => $request->supplier_id,
                    'amount' => $totalAmount,
                    'nature' => 'credit',
                    'transaction_id' => $billPurchase->id,
                    'transaction_type' => 'bill',
                    'date' => $request->date,
                    'description' => "Bill purchase {$billPurchase->reference}",
                    'branch_id' => auth()->user()->branch_id,
                    'user_id' => auth()->id(),
                ]);

            DB::commit();

            return redirect()->route('accounting.bill-purchases.show', $billPurchase)
                ->with('success', 'Bill purchase updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => 'Failed to update bill purchase: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bill $billPurchase)
    {
        // Check if bill has payments
        if ($billPurchase->payments()->count() > 0) {
            return redirect()->back()
                ->withErrors(['error' => 'Cannot delete bill with existing payments. Please delete all payments first.']);
        }

        try {
            DB::beginTransaction();

            // Delete GL transactions
            $billPurchase->glTransactions()->delete();

            // Delete bill items
            $billPurchase->billItems()->delete();

            // Delete the bill
            $billPurchase->delete();

            DB::commit();

            return redirect()->route('accounting.bill-purchases')
                ->with('success', 'Bill purchase deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete bill purchase: ' . $e->getMessage()]);
        }
    }

    /**
     * Show payment form for bill
     */
    public function showPaymentForm(Bill $billPurchase)
    {
        $billPurchase->load(['supplier', 'creditAccount']);

        $bankAccounts = BankAccount::orderBy('name')->get();
        $chartAccounts = ChartAccount::orderBy('account_name')->get();

        return view('accounting.bill-purchases.payment', compact('billPurchase', 'bankAccounts', 'chartAccounts'));
    }

    /**
     * Process payment for bill
     */
    public function processPayment(Request $request, Bill $billPurchase)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01|max:' . $billPurchase->balance,
            'date' => 'required|date',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $user = auth()->user();
            $totalAmount = $request->amount;

            // Generate payment reference
            $reference = 'PAY-' . date('Ymd') . '-' . str_pad(Payment::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);

            // Create payment
            $payment = Payment::create([
                'reference' => $billPurchase->id,
                'reference_type' => 'Bill',
                'reference_number' => null,
                'amount' => $request->amount,
                'date' => $request->date,
                'description' => $request->description ?: "Payment for bill {$billPurchase->reference}",
                'bank_account_id' => $request->bank_account_id,
                'branch_id' => $user->branch_id,
                'user_id' => $user->id,
                'approved' => true,
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);



            // Create GL transactions
            $bankAccount = BankAccount::find($request->bank_account_id);

            // Credit bank account
            GlTransaction::create([
                'chart_account_id' => $bankAccount->chart_account_id,
                'supplier_id' => $billPurchase->supplier_id,
                'amount' => $totalAmount,
                'nature' => 'credit',
                'transaction_id' => $payment->id,
                'transaction_type' => 'payment',
                'date' => $request->date,
                'description' => $request->description ?: "Payment for bill {$billPurchase->reference}",
                'branch_id' => $user->branch_id,
                'user_id' => $user->id,
            ]);

            // Debit accounts payable (credit_account from bill)
            GlTransaction::create([
                'chart_account_id' => $billPurchase->credit_account,
                'supplier_id' => $billPurchase->supplier_id,
                'amount' => $totalAmount,
                'nature' => 'debit',
                'transaction_id' => $payment->id,
                'transaction_type' => 'payment',
                'date' => $request->date,
                'description' => $request->description ?: "Payment for bill {$billPurchase->reference}",
                'branch_id' => $user->branch_id,
                'user_id' => $user->id,
            ]);

            // Update bill paid amount
            $billPurchase->updatePaidAmount();

            DB::commit();

            return redirect()->route('accounting.bill-purchases.show', $billPurchase)
                ->with('success', 'Payment processed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => 'Failed to process payment: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Show payment details
     */
    public function showPayment(Payment $payment)
    {
        $payment->load(['bankAccount', 'supplier', 'user', 'branch', 'glTransactions.chartAccount']);
        
        return view('accounting.bill-purchases.payment-show', compact('payment'));
    }

    /**
     * Show form to edit payment
     */
    public function editPayment(Payment $payment)
    {
        $payment->load(['bankAccount', 'supplier']);

        $bankAccounts = BankAccount::orderBy('name')->get();
        $suppliers = \App\Models\Supplier::where('status', 'active')->orderBy('name')->get();

        return view('accounting.bill-purchases.payment-edit', compact('payment', 'bankAccounts', 'suppliers'));
    }

    /**
     * Update payment
     */
    public function updatePayment(Request $request, Payment $payment)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $user = auth()->user();
            $oldAmount = $payment->amount;
            $newAmount = $request->amount;

            // Update payment
            $payment->update([
                'amount' => $newAmount,
                'date' => $request->date,
                'description' => $request->description,
                'bank_account_id' => $request->bank_account_id,
                'supplier_id' => $request->supplier_id,
            ]);

            // Delete existing GL transactions
            $payment->glTransactions()->delete();

            // Create new GL transactions
            $bankAccount = BankAccount::find($request->bank_account_id);

            // Credit bank account
            GlTransaction::create([
                'chart_account_id' => $bankAccount->chart_account_id,
                'supplier_id' => $request->supplier_id,
                'amount' => $newAmount,
                'nature' => 'credit',
                'transaction_id' => $payment->id,
                'transaction_type' => 'payment',
                'date' => $request->date,
                'description' => $request->description ?: "Payment {$payment->reference}",
                'branch_id' => $user->branch_id,
                'user_id' => $user->id,
            ]);

            // Debit accounts payable (credit_account from bill)
            $bill = Bill::find($payment->reference);
            if ($bill) {
                GlTransaction::create([
                    'chart_account_id' => $bill->credit_account,
                    'supplier_id' => $request->supplier_id,
                    'amount' => $newAmount,
                    'nature' => 'debit',
                    'transaction_id' => $payment->id,
                    'transaction_type' => 'payment',
                    'date' => $request->date,
                    'description' => $request->description ?: "Payment {$payment->reference}",
                    'branch_id' => $user->branch_id,
                    'user_id' => $user->id,
                ]);

                // Update bill paid amount
                $bill->updatePaidAmount();
            }

            DB::commit();

            return redirect()->route('accounting.bill-purchases.payment.show', $payment)
                ->with('success', 'Payment updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => 'Failed to update payment: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Delete payment
     */
    public function deletePayment(Payment $payment)
    {
        try {
            DB::beginTransaction();

            $bill = Bill::find($payment->reference);

            // Delete GL transactions
            $payment->glTransactions()->delete();

            // Delete payment
            $payment->delete();

            // Update bill paid amount if bill exists
            if ($bill) {
                $bill->updatePaidAmount();
            }

            DB::commit();

            return redirect()->back()
                ->with('success', 'Payment deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete payment: ' . $e->getMessage()]);
        }
    }

    /**
     * Export bill to PDF
     */
    public function exportPdf(Bill $billPurchase)
    {
        // Load relationships
        $billPurchase->load(['supplier', 'creditAccount', 'user', 'branch', 'company', 'billItems.debitAccount']);

        $pdf = \PDF::loadView('accounting.bill-purchases.pdf', compact('billPurchase'));
        $pdf->setPaper('a4', 'portrait');
        
        return $pdf->download('bill-' . $billPurchase->reference . '.pdf');
    }

    /**
     * Export bill payment to PDF
     */
    public function exportPaymentPdf(Payment $payment)
    {
        // Load relationships
        $payment->load(['supplier', 'bankAccount', 'user', 'branch', 'paymentItems']);

        $pdf = \PDF::loadView('accounting.bill-purchases.payment-pdf', compact('payment'));
        $pdf->setPaper('a4', 'portrait');
        
        return $pdf->download('payment-' . $payment->reference . '.pdf');
    }
}
