<?php

namespace App\Http\Controllers;

use App\Models\GlTransaction;
use App\Models\Loan;
use App\Models\LoanApproval;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;

class LoanApprovalController extends Controller
{
    /**
     * Check loan application (First level approval)
     */
    public function checkLoan($encodedId, Request $request)
    {
        try {
            $decoded = Hashids::decode($encodedId);
            if (empty($decoded)) {
                return redirect()->route('loans.application.index')->withErrors(['Loan application not found.']);
            }

            $loan = Loan::findOrFail($decoded[0]);
            $user = auth()->user();

            // Validate loan can be checked
            if ($loan->status !== Loan::STATUS_APPLIED) {
                return redirect()->route('loans.application.index')->withErrors(['Only applied loans can be checked.']);
            }

            // Validate user has permission to check
            if (!$loan->canBeApprovedByUser($user)) {
                return redirect()->route('loans.application.index')->withErrors(['You do not have permission to check this loan.']);
            }

            $validated = $request->validate([
                'comments' => 'nullable|string|max:1000',
            ]);

            DB::transaction(function () use ($loan, $user, $validated) {
                // Create approval record
                LoanApproval::create([
                    'loan_id' => $loan->id,
                    'user_id' => $user->id,
                    'role_name' => $user->roles->first()->name ?? 'unknown',
                    'approval_level' => 1,
                    'action' => 'checked',
                    'comments' => $validated['comments'] ?? null,
                    'approved_at' => now(),
                ]);

                // Update loan status
                $loan->update(['status' => Loan::STATUS_CHECKED]);
            });

            return redirect()->route('loans.application.index')->with('success', 'Loan application checked successfully.');
        } catch (\Throwable $th) {
            return redirect()->route('loans.application.index')->withErrors(['Failed to check loan: ' . $th->getMessage()]);
        }
    }

    /**
     * Approve loan application (Second level approval)
     */
    public function approveLoan($encodedId, Request $request)
    {
        try {
            $decoded = Hashids::decode($encodedId);
            if (empty($decoded)) {
                return redirect()->route('loans.application.index')->withErrors(['Loan application not found.']);
            }

            $loan = Loan::findOrFail($decoded[0]);
            $user = auth()->user();

            // Validate loan can be approved
            if ($loan->status !== Loan::STATUS_CHECKED) {
                return redirect()->route('loans.application.index')->withErrors(['Only checked loans can be approved.']);
            }

            // Validate user has permission to approve
            if (!$loan->canBeApprovedByUser($user)) {
                return redirect()->route('loans.application.index')->withErrors(['You do not have permission to approve this loan.']);
            }

            $validated = $request->validate([
                'comments' => 'nullable|string|max:1000',
            ]);

            DB::transaction(function () use ($loan, $user, $validated) {
                // Create approval record
                LoanApproval::create([
                    'loan_id' => $loan->id,
                    'user_id' => $user->id,
                    'role_name' => $user->roles->first()->name ?? 'unknown',
                    'approval_level' => 2,
                    'action' => 'approved',
                    'comments' => $validated['comments'] ?? null,
                    'approved_at' => now(),
                ]);

                // Update loan status
                $loan->update(['status' => Loan::STATUS_APPROVED]);
            });

            return redirect()->route('loans.application.index')->with('success', 'Loan application approved successfully.');
        } catch (\Throwable $th) {
            return redirect()->route('loans.application.index')->withErrors(['Failed to approve loan: ' . $th->getMessage()]);
        }
    }

    /**
     * Authorize loan application (Final level approval)
     */
    public function authorizeLoan($encodedId, Request $request)
    {
        try {
            $decoded = Hashids::decode($encodedId);
            if (empty($decoded)) {
                return redirect()->route('loans.application.index')->withErrors(['Loan application not found.']);
            }

            $loan = Loan::findOrFail($decoded[0]);
            $user = auth()->user();

            // Validate loan can be authorized
            if ($loan->status !== Loan::STATUS_APPROVED) {
                return redirect()->route('loans.application.index')->withErrors(['Only approved loans can be authorized.']);
            }

            // Validate user has permission to authorize
            if (!$loan->canBeApprovedByUser($user)) {
                return redirect()->route('loans.application.index')->withErrors(['You do not have permission to authorize this loan.']);
            }

            $validated = $request->validate([
                'comments' => 'nullable|string|max:1000',
            ]);

            DB::transaction(function () use ($loan, $user, $validated) {
                // Create approval record
                LoanApproval::create([
                    'loan_id' => $loan->id,
                    'user_id' => $user->id,
                    'role_name' => $user->roles->first()->name ?? 'unknown',
                    'approval_level' => 3,
                    'action' => 'approved',
                    'comments' => $validated['comments'] ?? null,
                    'approved_at' => now(),
                ]);

                // Update loan status
                $loan->update(['status' => Loan::STATUS_AUTHORIZED]);
            });

            return redirect()->route('loans.application.index')->with('success', 'Loan application authorized successfully.');
        } catch (\Throwable $th) {
            return redirect()->route('loans.application.index')->withErrors(['Failed to authorize loan: ' . $th->getMessage()]);
        }
    }

    /**
     * Disburse authorized loan (Accountant action)
     */
    public function disburseLoan($encodedId, Request $request)
    {
        try {
            $decoded = Hashids::decode($encodedId);
            if (empty($decoded)) {
                return redirect()->route('loans.application.index')->withErrors(['Loan application not found.']);
            }

            $loan = Loan::findOrFail($decoded[0]);
            $user = auth()->user();

            // Validate loan can be disbursed
            if ($loan->status !== Loan::STATUS_AUTHORIZED) {
                return redirect()->route('loans.application.index')->withErrors(['Only authorized loans can be disbursed.']);
            }

            // Validate user has accountant role
            if (!$user->hasRole('accountant')) {
                return redirect()->route('loans.application.index')->withErrors(['Only accountants can disburse loans.']);
            }

            $validated = $request->validate([
                'comments' => 'nullable|string|max:1000',
            ]);

            DB::transaction(function () use ($loan, $user, $validated) {
                // Update loan status to active
                $loan->update([
                    'status' => Loan::STATUS_ACTIVE,
                    'disbursed_on' => now(),
                ]);

                // Calculate interest and repayment dates
                $interestAmount = $loan->calculateInterestAmount($loan->interest);
                $repaymentDates = $loan->getRepaymentDates();

                // Update loan with totals and schedule
                $loan->update([
                    'interest_amount' => $interestAmount,
                    'amount_total' => $loan->amount + $interestAmount,
                    'first_repayment_date' => $repaymentDates['first_repayment_date'],
                    'last_repayment_date' => $repaymentDates['last_repayment_date'],
                ]);

                // Generate repayment schedule
                $loan->generateRepaymentSchedule($loan->interest);

                // Process disbursement
                $this->processLoanDisbursement($loan);
            });

            return redirect()->route('loans.application.index')->with('success', 'Loan disbursed successfully.');
        } catch (\Throwable $th) {
            return redirect()->route('loans.application.index')->withErrors(['Failed to disburse loan: ' . $th->getMessage()]);
        }
    }

    /**
     * Reject loan application
     */
    public function rejectLoan($encodedId, Request $request)
    {
        try {
            $decoded = Hashids::decode($encodedId);
            if (empty($decoded)) {
                return redirect()->route('loans.application.index')->withErrors(['Loan application not found.']);
            }

            $loan = Loan::findOrFail($decoded[0]);
            $user = auth()->user();

            // Validate loan can be rejected
            if (!$loan->canBeRejected()) {
                return redirect()->route('loans.application.index')->withErrors(['This loan cannot be rejected at its current status.']);
            }

            // Validate user has permission to reject
            if (!$loan->canBeApprovedByUser($user)) {
                return redirect()->route('loans.application.index')->withErrors(['You do not have permission to reject this loan.']);
            }

            $validated = $request->validate([
                'comments' => 'required|string|max:1000',
            ]);

            DB::transaction(function () use ($loan, $user, $validated) {
                // Create rejection record
                LoanApproval::create([
                    'loan_id' => $loan->id,
                    'user_id' => $user->id,
                    'role_name' => $user->roles->first()->name ?? 'unknown',
                    'approval_level' => $loan->getNextApprovalLevel() ?? 1,
                    'action' => 'rejected',
                    'comments' => $validated['comments'],
                    'approved_at' => now(),
                ]);

                // Update loan status
                $loan->update(['status' => Loan::STATUS_REJECTED]);
            });

            return redirect()->route('loans.application.index')->with('success', 'Loan application rejected successfully.');
        } catch (\Throwable $th) {
            return redirect()->route('loans.application.index')->withErrors(['Failed to reject loan: ' . $th->getMessage()]);
        }
    }

    /**
     * Mark loan as defaulted
     */
    public function defaultLoan($encodedId, Request $request)
    {
        try {
            $decoded = Hashids::decode($encodedId);
            if (empty($decoded)) {
                return redirect()->route('loans.list')->withErrors(['Loan not found.']);
            }

            $loan = Loan::findOrFail($decoded[0]);

            // Validate loan can be defaulted
            if ($loan->status !== Loan::STATUS_ACTIVE) {
                return redirect()->route('loans.list')->withErrors(['Only active loans can be marked as defaulted.']);
            }

            $validated = $request->validate([
                'comments' => 'required|string|max:1000',
            ]);

            $loan->update([
                'status' => Loan::STATUS_DEFAULTED,
            ]);

            return redirect()->route('loans.list')->with('success', 'Loan marked as defaulted successfully.');
        } catch (\Throwable $th) {
            return redirect()->route('loans.list')->withErrors(['Failed to mark loan as defaulted: ' . $th->getMessage()]);
        }
    }

    /**
     * Process loan disbursement (moved from LoanController)
     */
    private function processLoanDisbursement($loan)
    {
        $userId = auth()->id();
        $branchId = auth()->user()->branch_id;
        $product = $loan->product;
        $bankAccount = $loan->bankAccount;

        $notes = "Being disbursement for loan of {$product->name}, paid to {$loan->customer->name}, TSHS.{$loan->amount}";
        $principalReceivable = optional($product->principalReceivableAccount)->id;

        if (!$principalReceivable) {
            throw new \Exception('Principal receivable account not set for this loan product.');
        }

        // Create Payment record
        $payment = Payment::create([
            'reference' => $loan->id,
            'reference_type' => 'Loan Payment',
            'reference_number' => null,
            'date' => $loan->date_applied,
            'amount' => $loan->amount,
            'description' => $notes,
            'user_id' => $userId,
            'customer_id' => $loan->customer_id,
            'bank_account_id' => $loan->bank_account_id,
            'branch_id' => $branchId,
            'approved' => true,
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);

        Payment::create([
            'payment_id' => $payment->id,
            'chart_account_id' => $principalReceivable,
            'amount' => $loan->amount,
            'description' => $notes,
        ]);

        // Create GL Transactions
        GlTransaction::insert([
            [
                'chart_account_id' => $bankAccount->chart_account_id,
                'customer_id' => $loan->customer_id,
                'amount' => $loan->amount,
                'nature' => 'credit',
                'transaction_id' => $loan->id,
                'transaction_type' => 'Loan Disbursement',
                'date' => $loan->date_applied,
                'description' => $notes,
                'branch_id' => $branchId,
                'user_id' => $userId,
            ],
            [
                'chart_account_id' => $principalReceivable,
                'customer_id' => $loan->customer_id,
                'amount' => $loan->amount,
                'nature' => 'debit',
                'transaction_id' => $loan->id,
                'transaction_type' => 'Loan Disbursement',
                'date' => $loan->date_applied,
                'description' => $notes,
                'branch_id' => $branchId,
                'user_id' => $userId,
            ]
        ]);
    }
}