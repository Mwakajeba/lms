<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\CashCollateral;
use App\Models\Customer;
use App\Models\Filetype;
use App\Models\GlTransaction;
use App\Models\Group;
use App\Models\Loan;
use App\Models\LoanTopup;
use App\Models\LoanApproval;
use App\Models\LoanFile;
use App\Models\LoanProduct;
use App\Models\LoanSchedule;
use App\Models\ChartAccount;
use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Vinkla\Hashids\Facades\Hashids;
use Yajra\DataTables\Facades\DataTables;


class LoanTopUpController extends Controller
{
    /**
     * Show the loan top-up form.
     */
    public function show($encodedId)
    {
        $decoded = \Vinkla\Hashids\Facades\Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('loans.list')->withErrors(['Loan not found.']);
        }
        $loan = Loan::find($decoded[0]);
        if (!$loan) {
            return redirect()->route('loans.list')->withErrors(['Loan not found.']);
        }
        $loan->encodedId = $encodedId;
        return view('loans.top_up', compact('loan'));
    }

    /**
     * Handle the loan top-up submission.
     */
    public function store(Request $request, $encodedId)
    {
        try {
            // Debug logging
            Log::info('Top-up request received', [
                'encoded_id' => $encodedId,
                'request_data' => $request->all(),
                'is_ajax' => $request->ajax()
            ]);
            
        $decoded = \Vinkla\Hashids\Facades\Hashids::decode($encodedId);
            Log::info('Decoded ID', ['decoded' => $decoded]);
            
            if (empty($decoded)) {
                Log::error('Failed to decode loan ID', ['encoded_id' => $encodedId]);
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Loan not found - invalid ID.']);
                }
                return redirect()->route('loans.list')->withErrors(['Loan not found.']);
            }
            
            $loan = Loan::find($decoded[0]);
            if (!$loan) {
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Loan not found.']);
                }
                return redirect()->route('loans.list')->withErrors(['Loan not found.']);
            }

            // Validate the request
            $request->validate([
                'new_loan_amount' => 'required|numeric|min:' . ($loan->getCalculatedTopUpAmount() + 1),
                'purpose' => 'required|string|max:500',
                'period' => 'required|integer|min:1|max:60',
                'topup_type' => 'required|in:restructure,additional'
            ]);

            DB::beginTransaction();

            // Get current balance
            $currentBalance = $loan->getCalculatedTopUpAmount();
            $newLoanAmount = $request->new_loan_amount;
            $topupType = $request->topup_type;

            if ($topupType === 'restructure') {
                // RESTRUCTURE: Close old loan, create new larger loan
                $customerReceives = $newLoanAmount - $currentBalance;
                
                // Create new loan (replaces old loan)
            $newLoan = Loan::create([
                    'customer_id'      => $loan->customer_id,
                    'group_id'         => $loan->group_id,
                    'product_id'       => $loan->product_id,
                    'amount'           => $newLoanAmount,
                    'interest'         => $loan->interest,
                    'period'           => $loan->period + $request->period,
                    'bank_account_id'  => $loan->bank_account_id,
                'date_applied'     => now(),
                'disbursed_on'     => now(),
                'status'           => 'active',
                    'sector'           => $loan->sector,
                    'interest_cycle'   => $loan->interest_cycle,
                    'loan_officer_id'  => $loan->loan_officer_id,
                    'branch_id'        => $loan->branch_id,
                    'top_up_id'        => $loan->id,
                    'description'      => $request->purpose,
                ]);

                // Calculate interest and update loan
            $interestAmount = $newLoan->calculateInterestAmount($newLoan->interest);
            $repaymentDates = $newLoan->getRepaymentDates();
            $newLoan->update([
                'interest_amount' => $interestAmount,
                'amount_total' => $newLoan->amount + $interestAmount,
                'first_repayment_date' => $repaymentDates['first_repayment_date'],
                'last_repayment_date' => $repaymentDates['last_repayment_date'],
            ]);

                // Generate repayment schedule
            $newLoan->generateRepaymentSchedule($newLoan->interest);

                // Create GL Transactions for Restructure Top-Up
                $this->createRestructureTopUpGlTransactions($loan, $newLoan, $currentBalance, $customerReceives);

                // Close the old loan
                $loan->update(['status' => 'restructured']);

                // Create top-up record
            LoanTopup::create([
                    'old_loan_id'   => $loan->id,
                'new_loan_id'   => $newLoan->id,
                    'old_balance'   => $currentBalance,
                    'topup_amount'  => $customerReceives,
                'topup_type'    => 'restructure',
            ]);

            } else {
                // ADDITIONAL: Keep old loan active, create separate new loan
                $customerReceives = $newLoanAmount; // Customer receives full amount
                
                // Create new loan (separate from old loan)
            $newLoan = Loan::create([
                'customer_id'      => $loan->customer_id,
                'group_id'         => $loan->group_id,
                'product_id'       => $loan->product_id,
                    'amount'           => $newLoanAmount,
                'interest'         => $loan->interest,
                    'period'           => $request->period, // Only the additional period
                'bank_account_id'  => $loan->bank_account_id,
                'date_applied'     => now(),
                'disbursed_on'     => now(),
                'status'           => 'active',
                'sector'           => $loan->sector,
                'interest_cycle'   => $loan->interest_cycle,
                'loan_officer_id'  => $loan->loan_officer_id,
                'branch_id'        => $loan->branch_id,
                'top_up_id'        => $loan->id,
                    'description'      => $request->purpose,
            ]);

                // Calculate interest and update loan
            $interestAmount = $newLoan->calculateInterestAmount($newLoan->interest);
            $repaymentDates = $newLoan->getRepaymentDates();
            $newLoan->update([
                'interest_amount' => $interestAmount,
                'amount_total' => $newLoan->amount + $interestAmount,
                'first_repayment_date' => $repaymentDates['first_repayment_date'],
                'last_repayment_date' => $repaymentDates['last_repayment_date'],
            ]);

                // Generate repayment schedule
            $newLoan->generateRepaymentSchedule($newLoan->interest);

                // Create GL Transactions for Additional Top-Up
                $this->createAdditionalTopUpGlTransactions($loan, $newLoan, $customerReceives);

                // Old loan remains active (no status change)

                // Create top-up record
            LoanTopup::create([
                'old_loan_id'   => $loan->id,
                'new_loan_id'   => $newLoan->id,
                    'old_balance'   => $currentBalance,
                    'topup_amount'  => $customerReceives,
                'topup_type'    => 'additional',
                ]);
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true, 
                    'message' => 'Top-up loan created successfully!',
                    'new_loan_id' => $newLoan->id,
                    'new_loan_encoded_id' => Hashids::encode($newLoan->id)
                ]);
            }

            return redirect()->route('loans.show', $encodedId)
                ->with('success', 'Loan top-up submitted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Top-up creation failed: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to create top-up loan. Please try again.']);
            }
            
            return redirect()->back()->withErrors(['Failed to create top-up loan. Please try again.']);
        }
    }

    /**
     * Create GL transactions for restructure top-up loan
     */
    private function createRestructureTopUpGlTransactions($oldLoan, $newLoan, $currentBalance, $customerReceives)
    {
        $userId = auth()->id() ?? 1;
        $branchId = auth()->user()->branch_id ?? 1;
        $product = $oldLoan->product;
        $bankAccount = $oldLoan->bankAccount;

        // 1. Close old loan receivable (Credit the old loan receivable)
        GlTransaction::create([
            'chart_account_id' => $product->principal_receivable_account_id,
            'customer_id' => $oldLoan->customer_id,
            'amount' => $currentBalance,
            'nature' => 'credit',
            'transaction_id' => $oldLoan->id,
            'transaction_type' => 'Loan Top-Up - Restructure - Old Loan Closure',
            'date' => now(),
            'description' => "Restructure Top-up: Close old loan receivable (Loan #{$oldLoan->id})",
            'branch_id' => $branchId,
            'user_id' => $userId,
        ]);

        // 2. Create new loan receivable (Debit the new loan receivable)
        GlTransaction::create([
            'chart_account_id' => $product->principal_receivable_account_id,
            'customer_id' => $newLoan->customer_id,
            'amount' => $newLoan->amount,
            'nature' => 'debit',
            'transaction_id' => $newLoan->id,
            'transaction_type' => 'Loan Top-Up - Restructure - New Loan',
            'date' => now(),
            'description' => "Restructure Top-up: Create new loan receivable (Loan #{$newLoan->id})",
            'branch_id' => $branchId,
            'user_id' => $userId,
        ]);

        // 3. Disburse cash to customer (Credit bank account for amount customer receives)
        if ($customerReceives > 0) {
            GlTransaction::create([
                'chart_account_id' => $bankAccount->chart_account_id,
                'customer_id' => $newLoan->customer_id,
                'amount' => $customerReceives,
                'nature' => 'credit',
                'transaction_id' => $newLoan->id,
                'transaction_type' => 'Loan Top-Up - Restructure - Cash Disbursement',
                'date' => now(),
                'description' => "Restructure Top-up: Cash disbursement to customer (Loan #{$newLoan->id})",
                'branch_id' => $branchId,
                'user_id' => $userId,
            ]);
        }

        Log::info('Restructure Top-up GL transactions created', [
            'old_loan_id' => $oldLoan->id,
            'new_loan_id' => $newLoan->id,
            'current_balance' => $currentBalance,
            'customer_receives' => $customerReceives,
            'new_loan_amount' => $newLoan->amount
        ]);
    }

    /**
     * Create GL transactions for additional top-up loan
     */
    private function createAdditionalTopUpGlTransactions($oldLoan, $newLoan, $customerReceives)
    {
        $userId = auth()->id() ?? 1;
        $branchId = auth()->user()->branch_id ?? 1;
        $product = $oldLoan->product;
        $bankAccount = $oldLoan->bankAccount;

        // 1. Create new loan receivable (Debit the new loan receivable)
        GlTransaction::create([
            'chart_account_id' => $product->principal_receivable_account_id,
            'customer_id' => $newLoan->customer_id,
            'amount' => $newLoan->amount,
            'nature' => 'debit',
            'transaction_id' => $newLoan->id,
            'transaction_type' => 'Loan Top-Up - Additional - New Loan',
            'date' => now(),
            'description' => "Additional Top-up: Create new loan receivable (Loan #{$newLoan->id})",
            'branch_id' => $branchId,
            'user_id' => $userId,
        ]);

        // 2. Disburse cash to customer (Credit bank account for full amount)
        GlTransaction::create([
            'chart_account_id' => $bankAccount->chart_account_id,
            'customer_id' => $newLoan->customer_id,
            'amount' => $customerReceives,
            'nature' => 'credit',
            'transaction_id' => $newLoan->id,
            'transaction_type' => 'Loan Top-Up - Additional - Cash Disbursement',
            'date' => now(),
            'description' => "Additional Top-up: Cash disbursement to customer (Loan #{$newLoan->id})",
            'branch_id' => $branchId,
            'user_id' => $userId,
        ]);

        Log::info('Additional Top-up GL transactions created', [
            'old_loan_id' => $oldLoan->id,
            'new_loan_id' => $newLoan->id,
            'customer_receives' => $customerReceives,
            'new_loan_amount' => $newLoan->amount
        ]);
    }
}
