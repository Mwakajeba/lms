<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Loan;
use App\Models\LoanSchedule;
use App\Models\Repayment;
use App\Models\Receipt;
use App\Models\ReceiptItem;
use App\Models\GlTransaction;
use App\Services\LoanRepaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LoanRepaymentController extends Controller
{
    protected $repaymentService;

    public function __construct(LoanRepaymentService $repaymentService)
    {
        $this->repaymentService = $repaymentService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        try {
            // Add debugging
            Log::info('Repayment request received', $request->all());

            $request->validate([
                'loan_id' => 'required|exists:loans,id',
                'schedule_id' => 'required|exists:loan_schedules,id',
                'payment_date' => 'required|date',
                'amount' => 'required|numeric|min:0.01',
                'payment_source' => 'required|in:bank,cash_deposit',
                'bank_account_id' => 'required_if:payment_source,bank|nullable|exists:bank_accounts,id',
                'cash_deposit_id' => 'required_if:payment_source,cash_deposit|nullable|exists:cash_collaterals,id',
            ]);

            Log::info('Validation passed');

            // Get loan and check if amount matches settle amount
            $loan = Loan::with(['product', 'customer', 'schedule'])->findOrFail($request->loan_id);
            $settleAmount = $loan->total_amount_to_settle;
            $paymentAmount = $request->amount;

            Log::info('Amount comparison', [
                'payment_amount' => $paymentAmount,
                'settle_amount' => $settleAmount,
                'difference' => abs($paymentAmount - $settleAmount)
            ]);


                Log::info('Processing normal repayment', [
                    'loan_id' => $request->loan_id,
                    'amount' => $paymentAmount,
                    'settle_amount' => $settleAmount
                ]);

                // Use normal repayment process
                $bankAccount = BankAccount::findOrFail($request->bank_account_id);
                $bankChartAccount = $bankAccount->chart_account_id;

                // Check cash deposit balance if using cash deposit
                if ($request->payment_source === 'cash_deposit') {
                    $cashDeposit = \App\Models\CashCollateral::findOrFail($request->cash_deposit_id);

                    if ($cashDeposit->amount < $request->amount) {
                        return redirect()->back()->with('error', 'Insufficient cash deposit balance. Available: TSHS ' . number_format($cashDeposit->amount, 2));
                    }
                }

                // Prepare payment data based on source
                $paymentData = [
                    'payment_date' => $request->payment_date,
                    'payment_source' => $request->payment_source,
                    'bank_chart_account_id' => $bankChartAccount,
                ];

                if ($request->payment_source === 'bank') {
                    $paymentData['bank_account_id'] = $request->bank_account_id;
                } else {
                    $paymentData['cash_deposit_id'] = $request->cash_deposit_id;
                }

                // Get calculation method from loan product
                $calculationMethod = $loan->product->interest_method ?? 'flat_rate';

                Log::info('Processing normal repayment', [
                    'loan_id' => $request->loan_id,
                    'amount' => $request->amount,
                    'calculation_method' => $calculationMethod,
                    'payment_source' => $request->payment_source
                ]);

                // Process repayment using service
                $result = $this->repaymentService->processRepayment(
                    $request->loan_id,
                    $request->amount,
                    $paymentData,
                    $calculationMethod
                );

                Log::info('Repayment processing result', $result);

                return redirect()->back()->with('success', 'Repayment recorded successfully!');


        } catch (\Exception $e) {
            Log::error('Loan repayment error: ' . $e->getMessage());
            Log::error('Repayment error stack trace: ' . $e->getTraceAsString());

            return redirect()->back()->with('error', 'Failed to record repayment: ' . $e->getMessage());
        }
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
    public function edit($id)
    {
        $repayment = Repayment::with(['loan', 'schedule', 'bankAccount', 'customer'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'repayment' => $repayment
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'payment_date' => 'required|date',
                'amount' => 'required|numeric|min:0.01',
                'bank_account_id' => 'required|exists:bank_accounts,id',
            ]);

            $repayment = Repayment::with(['loan', 'receipt', 'bankAccount'])->findOrFail($id);
            $bankAccount = BankAccount::findOrFail($request->bank_account_id);
            $bankChartAccount = $bankAccount->chart_account_id;


            // Store the loan and schedule info before deletion
            $loanId = $repayment->loan_id;
            $scheduleId = $repayment->loan_schedule_id;
            $customerId = $repayment->customer_id;
            $dueDate = $repayment->due_date;

            // Delete the existing repayment (this will also delete receipt and GL transactions)
            $this->deleteRepaymentInternal($repayment);

            // Create new repayment with updated details
            $paymentData = [
                'payment_date' => $request->payment_date,
                'bank_account_id' => $request->bank_account_id,
                'bank_chart_account_id' => $bankChartAccount,
            ];

            // Get calculation method from loan product
            $loan = Loan::with('product')->findOrFail($loanId);
            $calculationMethod = $loan->product->interest_method ?? 'flat_rate';

            // Process new repayment using service
            $result = $this->repaymentService->processRepayment(
                $loanId,
                $request->amount,
                $paymentData,
                $calculationMethod
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Repayment updated successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Repayment update error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update repayment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Internal method to delete repayment and associated records
     */
    private function deleteRepaymentInternal($repayment)
    {
        Log::info('Starting repayment deletion process', [
            'repayment_id' => $repayment->id,
            'loan_id' => $repayment->loan_id,
            'customer_id' => $repayment->customer_id
        ]);

        // Get loan before deletion for status updates
        $loan = $repayment->loan;
        $originalLoanStatus = $loan->status;

        // 1. Delete GL transactions associated with this repayment
        $this->deleteRepaymentGLTransactions($repayment);

        // 2. Delete receipt and associated data if exists
        if ($repayment->receipt) {
            $this->deleteRepaymentReceipt($repayment);
        }

        // 3. Handle cash deposit restoration if applicable
        $this->restoreCashDepositIfApplicable($repayment);

        // 4. Update loan status if it was closed due to this repayment
        $this->updateLoanStatusAfterDeletion($loan, $originalLoanStatus);

        // 5. Delete the repayment record
        $repayment->delete();

        Log::info('Repayment deletion completed successfully', [
            'repayment_id' => $repayment->id,
            'loan_id' => $loan->id
        ]);
    }

    /**
     * Delete all GL transactions associated with the repayment
     */
    private function deleteRepaymentGLTransactions($repayment)
    {
        // Delete GL transactions by repayment ID
        $repaymentGLCount = GlTransaction::where('transaction_id', $repayment->id)
            ->whereIn('transaction_type', ['receipt', 'journal repayment', 'Settle Interest', 'Settle Principal'])
            ->delete();


        // Delete GL transactions by receipt ID if receipt exists
        if ($repayment->receipt) {
            $receiptGLCount = GlTransaction::where('transaction_id', $repayment->receipt->id)
                ->where('transaction_type', 'receipt')
                ->delete();

            Log::info('Deleted GL transactions for receipt', [
                'receipt_id' => $repayment->receipt->id,
                'deleted_count' => $receiptGLCount
            ]);
        }
        //get loan schedule ids


        // These lines perform deletion of GL transactions relating to specific transaction types for the loan schedule
        $matureInterestGLCount = GlTransaction::where('transaction_id', $repayment->loan_schedule_id)
            ->where('transaction_type', 'Mature Interest')
            ->delete();

        Log::info('Deleted GL transactions for loan schedule', [
            'loan_schedule_id' => $repayment->loan_schedule_id,
            'transaction_type' => 'Mature Interest',
            'deleted_count' => $matureInterestGLCount
        ]);

        $penaltyGLCount = GlTransaction::where('transaction_id', $repayment->loan_schedule_id)
            ->where('transaction_type', 'Penalty')
            ->delete();
        Log::info('Deleted GL transactions for loan schedule', [
            'loan_schedule_id' => $repayment->loan_schedule_id,
            'transaction_type' => 'Penalty',
            'deleted_count' => $penaltyGLCount
        ]);

        // Summary log for repayment GL deletion
        Log::info('Deleted GL transactions for repayment', [
            'repayment_id' => $repayment->id,
            'deleted_count' => $repaymentGLCount
        ]);
    }

    /**
     * Delete receipt and all associated data
     */
    private function deleteRepaymentReceipt($repayment)
    {
        $receipt = $repayment->receipt;

        if (!$receipt) {
            return;
        }

        Log::info('Deleting receipt and associated data', [
            'receipt_id' => $receipt->id,
            'repayment_id' => $repayment->id
        ]);

        // Delete receipt items first
        $receiptItemsCount = ReceiptItem::where('receipt_id', $receipt->id)->delete();

        // Delete GL transactions for this receipt
        $receiptGLCount = GlTransaction::where('transaction_id', $receipt->id)
            ->where('transaction_type', 'receipt')
            ->delete();

        // Delete the receipt
        $receipt->delete();

        Log::info('Receipt deletion completed', [
            'receipt_id' => $receipt->id,
            'receipt_items_deleted' => $receiptItemsCount,
            'gl_transactions_deleted' => $receiptGLCount
        ]);
    }

    /**
     * Restore cash deposit if repayment was made from cash deposit
     */
    private function restoreCashDepositIfApplicable($repayment)
    {
        // Check if this repayment was made from cash deposit
        // This would be indicated by the presence of journal entries or specific fields
        $journalTransactions = GlTransaction::where('transaction_id', $repayment->id)
            ->where('transaction_type', 'journal repayment')
            ->get();

        if ($journalTransactions->isNotEmpty()) {
            // Find the cash deposit account from the journal entries
            $cashDepositAccountId = null;
            foreach ($journalTransactions as $transaction) {
                // Look for debit entries to cash deposit account
                if ($transaction->nature === 'debit') {
                    $cashDepositAccountId = $transaction->chart_account_id;
                    break;
                }
            }

            if ($cashDepositAccountId) {
                // Find the cash deposit record and restore the amount
                $cashDeposit = \App\Models\CashCollateral::whereHas('type', function($query) use ($cashDepositAccountId) {
                    $query->where('chart_account_id', $cashDepositAccountId);
                })->where('customer_id', $repayment->customer_id)->first();

                if ($cashDeposit) {
                    $amountToRestore = $repayment->principal + $repayment->interest + $repayment->fee_amount + $repayment->penalt_amount;
                    $cashDeposit->increment('amount', $amountToRestore);

                    Log::info('Restored cash deposit amount', [
                        'cash_deposit_id' => $cashDeposit->id,
                        'amount_restored' => $amountToRestore,
                        'new_balance' => $cashDeposit->amount
                    ]);
                }
            }
        }
    }

    /**
     * Update loan status after repayment deletion
     */
    private function updateLoanStatusAfterDeletion($loan, $originalStatus)
    {
        // If the loan was closed due to this repayment, we need to check if it should still be closed
        if ($originalStatus === 'complete' || $originalStatus === 'closed') {
            // Check if loan is still fully paid after this repayment deletion
            if (!$loan->isEligibleForClosing()) {
                $loan->status = 'active';
                $loan->save();

                Log::info('Loan status reverted to active after repayment deletion', [
                    'loan_id' => $loan->id,
                    'original_status' => $originalStatus
                ]);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $repayment = Repayment::with(['loan', 'receipt'])->findOrFail($id);

            // Delete repayment and associated records
            $this->deleteRepaymentInternal($repayment);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Repayment deleted successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Repayment deletion error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete repayment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk delete repayments
     */
    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:repayments,id',
        ]);

        DB::beginTransaction();
        try {
            $repayments = Repayment::with(['loan', 'receipt'])->whereIn('id', $validated['ids'])->get();
            $deletedCount = 0;

            foreach ($repayments as $repayment) {
                $this->deleteRepaymentInternal($repayment);
                $deletedCount++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Repayments deleted successfully.',
                'deleted' => $deletedCount,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Bulk repayment deletion error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete repayments: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get repayment history for a loan
     */
    public function getRepaymentHistory($loanId)
    {
        $repayments = Repayment::where('loan_id', $loanId)
            ->with(['schedule', 'bankAccount'])
            ->orderBy('payment_date', 'desc')
            ->get();

        return response()->json($repayments);
    }

    /**
     * Get schedule details for repayment
     */
    public function getScheduleDetails($scheduleId)
    {
        $schedule = LoanSchedule::with(['loan'])->findOrFail($scheduleId);

        return response()->json([
            'schedule' => $schedule,
            'total_due' => $schedule->principal + $schedule->interest + $schedule->fee_amount + $schedule->penalty_amount,
        ]);
    }

    /**
     * Remove penalty from schedule
     */
    public function removePenalty(Request $request, $scheduleId)
    {
        try {
            $request->validate([
                'amount' => 'required|numeric|min:0',
                'loan_id' => 'required|exists:loans,id',
                'schedule_id' => 'required|exists:loan_schedules,id',
                'reason' => 'nullable|string|max:500',
            ]);
            // Validate that the requested removal amount does not exceed current penalty
            $schedule = LoanSchedule::findOrFail($request->schedule_id);
            $currentPenaltyAmount = (float) $schedule->penalty_amount;
            $requestedAmount = (float) $request->amount;
            if ($requestedAmount > $currentPenaltyAmount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Amount cannot exceed current penalty amount.'
                ], 422);
            }

            $result = $this->repaymentService->removePenalty(
                $request->schedule_id,
                $request->reason,
                $request->amount,
                $request->loan_id
            );

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Penalty removal error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove penalty: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate loan schedule
     */
    public function calculateSchedule(Request $request, $loanId)
    {
        try {
            $request->validate([
                'method' => 'required|in:flat_rate,reducing_equal_installment,reducing_equal_principal',
            ]);

            $loan = Loan::findOrFail($loanId);
            $schedules = $this->repaymentService->calculateSchedule($loan, $request->method);

            return response()->json([
                'success' => true,
                'schedules' => $schedules
            ]);

        } catch (\Exception $e) {
            Log::error('Schedule calculation error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate schedule: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk repayment processing
     */
    public function bulkRepayment(Request $request)
    {
        try {
            $request->validate([
                'repayments' => 'required|array|min:1',
                'repayments.*.loan_id' => 'required|exists:loans,id',
                'repayments.*.amount' => 'required|numeric|min:0.01',
                'repayments.*.payment_date' => 'required|date',
                'repayments.*.bank_account_id' => 'required|exists:bank_accounts,id',
            ]);
            $bankAccount = BankAccount::findOrFail($request->repayments[0]['bank_account_id']);
            $bankChartAccount = $bankAccount->chart_account_id;

            $results = [];
            $successCount = 0;
            $errorCount = 0;

            foreach ($request->repayments as $repaymentData) {
                try {
                    $paymentData = [
                        'payment_date' => $repaymentData['payment_date'],
                        'bank_account_id' => $repaymentData['bank_account_id'],
                        'bank_chart_account_id' => $bankChartAccount,
                    ];

                    $loan = Loan::with('product')->findOrFail($repaymentData['loan_id']);
                    $calculationMethod = $loan->product->interest_method ?? 'flat_rate';

                    $result = $this->repaymentService->processRepayment(
                        $repaymentData['loan_id'],
                        $repaymentData['amount'],
                        $paymentData,
                        $calculationMethod
                    );

                    $results[] = [
                        'loan_id' => $repaymentData['loan_id'],
                        'success' => true,
                        'result' => $result
                    ];
                    $successCount++;

                } catch (\Exception $e) {
                    $results[] = [
                        'loan_id' => $repaymentData['loan_id'],
                        'success' => false,
                        'error' => $e->getMessage()
                    ];
                    $errorCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Processed {$successCount} repayments successfully, {$errorCount} failed",
                'results' => $results,
                'summary' => [
                    'total' => count($request->repayments),
                    'success' => $successCount,
                    'failed' => $errorCount
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Bulk repayment error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to process bulk repayment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Print receipt for repayment
     */
    public function printReceipt($id)
    {
        try {
            $repayment = Repayment::with([
                'loan.customer',
                'schedule',
                'chartAccount',
                'receipt.receiptItems.chartAccount'
            ])->findOrFail($id);

            // Generate receipt data for thermal printer
            $receiptData = [
                'receipt_number' => $repayment->receipt->reference ?? 'N/A',
                'date' => $repayment->payment_date,
                'customer_name' => $repayment->customer->name,
                'loan_number' => $repayment->loan->loanNo,
                'amount_paid' => $repayment->amount_paid,
                'schedule_number' => $repayment->schedule_number,
                'due_date' => $repayment->due_date,
                'remain_schedule' => $repayment->remain_schedule,
                'remaining_schedules_count' => $repayment->remaining_schedules_count,
                'remaining_schedules_amount' => $repayment->remaining_schedules_amount,
                'payment_breakdown' => [
                    'principal' => $repayment->principal,
                    'interest' => $repayment->interest,
                    'penalty' => $repayment->penalt_amount,
                    'fee' => $repayment->fee_amount,
                ],
                'bank_account' => $repayment->chartAccount()->name ?? 'N/A',
                'received_by' => Auth::check() ? Auth::user()->name : 'System',
                'branch' => Auth::check() && Auth::user()->branch ? Auth::user()->branch->name : 'N/A',
            ];

            return response()->json([
                'success' => true,
                'receipt_data' => $receiptData
            ]);

        } catch (\Exception $e) {
            Log::error('Receipt print error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate receipt: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeSettlementRepayment(Request $request)
    {
        try {
            $request->validate([
                'loan_id' => 'required|exists:loans,id',
                'payment_date' => 'required|date',
                'amount' => 'required|numeric|min:0.01',
                'payment_source' => 'required|in:bank,cash_deposit',
                'bank_account_id' => 'required_if:payment_source,bank|nullable|exists:bank_accounts,id',
                'cash_deposit_id' => 'required_if:payment_source,cash_deposit|nullable|exists:cash_collaterals,id',
            ]);

            // Get loan and check if amount matches settle amount
            $loan = Loan::with(['product', 'customer', 'schedule.repayments'])->findOrFail($request->loan_id);
            $settleAmount = $loan->total_amount_to_settle;
            $paymentAmount = $request->amount;
            $isSettleRepayment = abs($paymentAmount - $settleAmount) <= 0.01;

            if (!$isSettleRepayment) {
                return redirect()->back()->with('error', 'Amount does not match the settle amount. Expected: TZS ' . number_format($settleAmount, 2));
            }

            Log::info('Processing settle repayment', [
                'loan_id' => $request->loan_id,
                'amount' => $paymentAmount,
                'settle_amount' => $settleAmount,
                'payment_source' => $request->payment_source
            ]);

            // Check cash deposit balance if using cash deposit
            if ($request->payment_source === 'cash_deposit') {
                $cashDeposit = \App\Models\CashCollateral::findOrFail($request->cash_deposit_id);

                if ($cashDeposit->amount < $request->amount) {
                    return redirect()->back()->with('error', 'Insufficient cash deposit balance. Available: TSHS ' . number_format($cashDeposit->amount, 2));
                }
            }

            // Prepare payment data based on source
            $paymentData = [
                'payment_date' => $request->payment_date,
                'payment_source' => $request->payment_source,
                'notes' => 'Settle repayment - pays current interest and all remaining principal'
            ];

            if ($request->payment_source === 'bank') {
                $bankAccount = BankAccount::findOrFail($request->bank_account_id);
                $paymentData['bank_chart_account_id'] = $bankAccount->chart_account_id;
                $paymentData['bank_account_id'] = $request->bank_account_id;
            } else {
                $paymentData['cash_deposit_id'] = $request->cash_deposit_id;
            }

            $result = $this->repaymentService->processSettleRepayment($request->loan_id, $paymentAmount, $paymentData);

            if ($result['success']) {
                $message = "Loan settled successfully. ";
                $message .= "Interest paid: TZS " . number_format($result['current_interest_paid'], 2) . ". ";
                $message .= "Principal paid: TZS " . number_format($result['total_principal_paid'], 2) . ".";

                if ($result['loan_closed']) {
                    $message .= " Loan has been closed.";
                }

                return redirect()->back()->with('success', $message);
            } else {
                return redirect()->back()->with('error', 'Failed to process settle repayment.');
            }

        } catch (\Exception $e) {
            Log::error('Settle repayment error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to process settle repayment: ' . $e->getMessage());
        }
    }
}
