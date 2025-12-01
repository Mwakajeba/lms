<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use App\Models\Loan;
use App\Models\BankAccount;
use App\Models\LoanProduct;
use App\Models\GlTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SmsNotification;

class ReceiptController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'loan_id' => 'required|exists:loans,id',
            'amount' => 'required|numeric|min:0.01',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'description' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $loan = Loan::findOrFail($request->loan_id);
            $bankAccount = BankAccount::findOrFail($request->bank_account_id);
            $product = LoanProduct::findOrFail($loan->loan_product_id);
            $incomeProvisionAccountId = $product->income_provision_account_id;

            // Create receipt
            $receipt = Receipt::create([
                'reference' => 'LOAN-'.$loan->id,
                'reference_type' => 'loan',
                'reference_number' => $loan->loanNo ?? $loan->id,
                'amount' => $request->amount,
                'date' => now(),
                'description' => $request->description,
                'user_id' => Auth::id(),
                'bank_account_id' => $bankAccount->id,
                'payee_type' => 'customer',
                'payee_id' => $loan->customer_id,
                'payee_name' => $loan->customer->name,
                'branch_id' => $loan->branch_id,
                'approved' => true,
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            // GL Transactions
            // Debit Bank
            GlTransaction::create([
                'chart_account_id' => $bankAccount->chart_account_id,
                'customer_id' => $loan->customer_id,
                'amount' => $request->amount,
                'nature' => 'debit',
                'transaction_id' => $receipt->id,
                'transaction_type' => 'receipt',
                'date' => now(),
                'description' => 'Repayment Receipt for Loan #'.$loan->loanNo,
                'branch_id' => $loan->branch_id,
                'user_id' => Auth::id(),
            ]);
            // Credit Income Provision
            GlTransaction::create([
                'chart_account_id' => $incomeProvisionAccountId,
                'customer_id' => $loan->customer_id,
                'amount' => $request->amount,
                'nature' => 'credit',
                'transaction_id' => $receipt->id,
                'transaction_type' => 'receipt',
                'date' => now(),
                'description' => 'Repayment Receipt for Loan #'.$loan->loanNo,
                'branch_id' => $loan->branch_id,
                'user_id' => Auth::id(),
            ]);

            // Send SMS
            $customer = $loan->customer;
            if ($customer && $customer->phone) {
                $message = "Dear {$customer->name}, your repayment of TZS {$request->amount} has been received. Thank you.";
                Notification::send($customer, new SmsNotification($message));
            }

            DB::commit();
            return redirect()->back()->with('success', 'Repayment receipt posted and SMS sent.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error posting receipt: '.$e->getMessage());
        }
    }
}
