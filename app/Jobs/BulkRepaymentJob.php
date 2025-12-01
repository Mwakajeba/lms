<?php

namespace App\Jobs;

use App\Models\Journal;
use App\Models\JournalItem;
use App\Models\Loan;
use App\Models\LoanSchedule;
use App\Models\Repayment;
use App\Models\GlTransaction;
use App\Services\LoanRepaymentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BulkRepaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $repaymentData;
    protected $userId;
    protected $chartAccountId;
    protected $chunkSize = 25;

    /**
     * Create a new job instance.
     */
    public function __construct($repaymentData, $userId, $chartAccountId)
    {
        $this->repaymentData = $repaymentData;
        $this->userId = $userId;
        $this->chartAccountId = $chartAccountId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting bulk repayment job', [
            'total_repayments' => count($this->repaymentData),
            'user_id' => $this->userId
        ]);

        Log::info('Repayment data', ['repayment_data' => $this->repaymentData]);

        $processedRepayments = [];
        $failedRepayments = [];

        // Process repayments in chunks
        $chunks = array_chunk($this->repaymentData, $this->chunkSize);

        foreach ($chunks as $chunkIndex => $chunk) {
            Log::info("Processing repayment chunk {$chunkIndex}", ['chunk_size' => count($chunk)]);

            foreach ($chunk as $repaymentIndex => $repaymentInfo) {
                try {
                    $loan = Loan::with(['product', 'customer', 'schedule'])->find($repaymentInfo['loan_id']);

                    if (!$loan) {
                        throw new \Exception("Loan not found: {$repaymentInfo['loan_id']}");
                    }

                    // Skip if amount is zero or negative
                    if ($repaymentInfo['amount'] <= 0) {
                        Log::info("Skipping repayment for loan {$repaymentInfo['loan_id']} - amount is zero or negative");
                        continue;
                    }

                    $result = $this->processJournalRepayment($loan, $repaymentInfo);

                    if ($result['success']) {
                        $processedRepayments[] = [
                            'loan_id' => $repaymentInfo['loan_id'],
                            'amount' => $result['paid_amount']
                        ];

                        Log::info("Repayment processed successfully", [
                            'loan_id' => $repaymentInfo['loan_id'],
                            'paid_amount' => $result['paid_amount']
                        ]);

                        // Check if loan is now fully paid and close it automatically
                        $this->checkAndCloseLoan($loan);
                    } else {
                        throw new \Exception("Repayment processing failed");
                    }

                } catch (\Exception $e) {
                    $failedRepayments[] = [
                        'loan_id' => $repaymentInfo['loan_id'],
                        'amount' => $repaymentInfo['amount'],
                        'error' => $e->getMessage()
                    ];

                    Log::error('Failed to process repayment', [
                        'loan_id' => $repaymentInfo['loan_id'],
                        'amount' => $repaymentInfo['amount'],
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        Log::info('Bulk repayment job completed', [
            'processed_repayments' => count($processedRepayments),
            'failed_repayments' => count($failedRepayments)
        ]);
    }

    /**
     * Process repayment using journal entries instead of receipts
     */
    private function processJournalRepayment($loan, $repaymentInfo)
    {
        return DB::transaction(function () use ($loan, $repaymentInfo) {
            $remainingAmount = $repaymentInfo['amount'];
            $totalPaidAmount = 0;

            // Get unpaid schedules ordered by due date
            $unpaidSchedules = $this->getUnpaidSchedules($loan);

            if ($unpaidSchedules->count() === 0) {
                throw new \Exception('No unpaid schedules found for this loan.');
            }

            foreach ($unpaidSchedules as $schedule) {
                if ($remainingAmount <= 0) {
                    break;
                }

                $schedulePayment = $this->processSchedulePayment($loan, $schedule, $remainingAmount);

                if (empty($schedulePayment) || !isset($schedulePayment['amount']) || $schedulePayment['amount'] <= 0) {
                    break;
                }

                $remainingAmount -= $schedulePayment['amount'];
                $totalPaidAmount += $schedulePayment['amount'];

                // Create repayment record
                $repayment = $this->createRepaymentRecord($loan, $schedule, $schedulePayment, $repaymentInfo);

                // Create journal entry for repayment
                $this->createRepaymentJournal($loan, $repayment, $schedulePayment, $repaymentInfo);
            }

            return [
                'success' => true,
                'paid_amount' => $totalPaidAmount
            ];
        });
    }

    /**
     * Get unpaid schedules for a loan
     */
    private function getUnpaidSchedules($loan)
    {
        return $loan->schedule()
            ->whereRaw('(
                SELECT COALESCE(SUM(principal), 0) + COALESCE(SUM(interest), 0) + COALESCE(SUM(fee_amount), 0) + COALESCE(SUM(penalt_amount), 0)
                FROM repayments
                WHERE repayments.loan_schedule_id = loan_schedules.id
            ) < (loan_schedules.principal + loan_schedules.interest + loan_schedules.fee_amount + loan_schedules.penalty_amount)')
            ->orderBy('due_date')
            ->get();
    }

    /**
     * Process payment for a single schedule
     */
    private function processSchedulePayment($loan, $schedule, $remainingAmount)
    {
        // Get already paid amounts for this schedule
        $paidAmounts = $this->getPaidAmountsForSchedule($schedule);

        // Calculate remaining amounts
        $remainingAmounts = [
            'principal' => $schedule->principal - $paidAmounts['principal'],
            'interest' => $schedule->interest - $paidAmounts['interest'],
            'fee_amount' => $schedule->fee_amount - $paidAmounts['fee_amount'],
            'penalty_amount' => $schedule->penalty_amount - $paidAmounts['penalty_amount']
        ];

        // Get repayment order from loan product
        $repaymentOrder = $this->getRepaymentOrder($loan);

        $allocatedAmounts = [
            'principal' => 0,
            'interest' => 0,
            'fee_amount' => 0,
            'penalty_amount' => 0
        ];

        $currentAmount = $remainingAmount;

        // Allocate payment according to repayment order
        foreach ($repaymentOrder as $component) {
            if ($currentAmount <= 0)
                break;

            if (isset($remainingAmounts[$component]) && $remainingAmounts[$component] > 0) {
                $amountToPay = min($currentAmount, $remainingAmounts[$component]);
                $allocatedAmounts[$component] = $amountToPay;
                $currentAmount -= $amountToPay;
            }
        }

        return [
            'schedule_id' => $schedule->id,
            'amount' => $remainingAmount - $currentAmount,
            'principal' => $allocatedAmounts['principal'],
            'interest' => $allocatedAmounts['interest'],
            'fee_amount' => $allocatedAmounts['fee_amount'],
            'penalty_amount' => $allocatedAmounts['penalty_amount']
        ];
    }

    /**
     * Get repayment order from loan product
     */
    private function getRepaymentOrder($loan)
    {
        $defaultOrder = ['penalty_amount', 'fee_amount', 'interest', 'principal'];

        if ($loan->product && $loan->product->repayment_order) {
            $rawOrder = $loan->product->repayment_order;

            if (is_array($rawOrder)) {
                $repaymentComponents = $rawOrder;
            } else if (is_string($rawOrder)) {
                $trimmed = trim($rawOrder);
                if ($trimmed !== '' && ($trimmed[0] === '[' || $trimmed[0] === '{')) {
                    $decoded = json_decode($trimmed, true);
                    $repaymentComponents = is_array($decoded) ? $decoded : explode(',', $rawOrder);
                } else {
                    $repaymentComponents = explode(',', $rawOrder);
                }
            } else {
                $repaymentComponents = [];
            }

            $validComponents = [];

            foreach ($repaymentComponents as $component) {
                $component = is_string($component) ? trim($component) : $component;
                switch ($component) {
                    case 'penalties':
                    case 'penalty':
                    case 'penalty_amount':
                        $validComponents[] = 'penalty_amount';
                        break;
                    case 'fees':
                    case 'fee':
                    case 'fee_amount':
                        $validComponents[] = 'fee_amount';
                        break;
                    case 'interest':
                        $validComponents[] = 'interest';
                        break;
                    case 'principal':
                        $validComponents[] = 'principal';
                        break;
                }
            }

            return !empty($validComponents) ? $validComponents : $defaultOrder;
        }

        return $defaultOrder;
    }

    /**
     * Get paid amounts for a schedule
     */
    private function getPaidAmountsForSchedule($schedule)
    {
        $repayments = $schedule->repayments;

        return [
            'principal' => $repayments->sum('principal'),
            'interest' => $repayments->sum('interest'),
            'fee_amount' => $repayments->sum('fee_amount'),
            'penalty_amount' => $repayments->sum('penalt_amount')
        ];
    }

    /**
     * Create repayment record
     */
    private function createRepaymentRecord($loan, $schedule, $schedulePayment, $repaymentInfo)
    {
        $repaymentData = [
            'customer_id' => $loan->customer_id,
            'loan_id' => $loan->id,
            'loan_schedule_id' => $schedule->id,
            'bank_account_id' => $this->chartAccountId, // Not using bank account for opening balance
            'payment_date' => $repaymentInfo['payment_date'],
            'due_date' => $schedule->due_date,
            'principal' => $schedulePayment['principal'],
            'interest' => $schedulePayment['interest'],
            'fee_amount' => $schedulePayment['fee_amount'],
            'penalt_amount' => $schedulePayment['penalty_amount'],
            'cash_deposit' => $schedulePayment['amount'],
        ];

        return Repayment::create($repaymentData);
    }

    /**
     * Create journal entry for repayment
     */
    private function createRepaymentJournal($loan, $repayment, $schedulePayment, $repaymentInfo)
    {
        $notes = "Opening balance repayment for loan of {$loan->product->name}, from {$loan->customer->name}, TSHS.{$schedulePayment['amount']}";

        // Generate a unique reference for the journal
        $nextId = Journal::max('id') + 1;
        $reference = 'JRN-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);

        $journal = Journal::create([
            'date' => $repaymentInfo['payment_date'],
            'description' => $notes,
            'branch_id' => $loan->branch_id,
            'user_id' => $this->userId,
            'reference_type' => 'Loan Repayment',
            'reference' => $loan->id,
        ]);

        // Debit: Chart account (source of funds)
        JournalItem::create([
            'journal_id' => $journal->id,
            'chart_account_id' => $this->chartAccountId,
            'amount' => $schedulePayment['amount'],
            'nature' => 'debit',
            'description' => $notes,
        ]);

        // Credit: Loan component accounts
        $components = [
            'principal' => $schedulePayment['principal'],
            'interest' => $schedulePayment['interest'],
            'fee_amount' => $schedulePayment['fee_amount'],
            'penalty_amount' => $schedulePayment['penalty_amount']
        ];

        $chartAccounts = [
            'principal' => $loan->product->principal_receivable_account_id ?? null,
            'interest' => $loan->product->interest_revenue_account_id ?? null,
            'fee_amount' => $loan->product->fee_income_account_id ?? null,
            'penalty_amount' => $loan->product->penalty_receivables_account_id ?? null
        ];

        // If matured interest receivable and income have already been posted on the due date with the same amount,
        // credit the receivable instead of income to avoid double-posting income
        $receivableId = $loan->product->interest_receivable_account_id ?? null;
        $incomeId = $loan->product->interest_revenue_account_id ?? null;
        if ($receivableId && $incomeId && $components['interest'] > 0) {
            $receivableExists = GlTransaction::where('chart_account_id', $receivableId)
                ->where('customer_id', $loan->customer_id)
                ->where('date', $repayment->due_date)
                ->where('amount', $components['interest'])
                ->where('transaction_type', 'Mature Interest')
                ->exists();

            $incomeExists = GlTransaction::where('chart_account_id', $incomeId)
                ->where('customer_id', $loan->customer_id)
                ->where('date', $repayment->due_date)
                ->where('amount', $components['interest'])
                ->where('transaction_type', 'Interest')
                ->exists();

            if ($receivableExists && $incomeExists) {
                $chartAccounts['interest'] = $receivableId;
            }
        }

        // Create GL transactions: DR selected chart account (total), CR components
        GlTransaction::create([
            'chart_account_id' => $this->chartAccountId,
            'customer_id' => $loan->customer_id,
            'amount' => $schedulePayment['amount'],
            'nature' => 'debit',
            'transaction_id' => $journal->id,
            'transaction_type' => 'journal repayment',
            'date' => $repaymentInfo['payment_date'],
            'description' => $notes,
            'branch_id' => $loan->branch_id,
            'user_id' => $this->userId,
        ]);

        foreach ($components as $component => $amount) {
            if ($amount > 0 && !empty($chartAccounts[$component])) {
                JournalItem::create([
                    'journal_id' => $journal->id,
                    'chart_account_id' => $chartAccounts[$component],
                    'amount' => $amount,
                    'nature' => 'credit',
                    'description' => ucfirst($component) . " repayment for loan #{$loan->id}",
                ]);

                GlTransaction::create([
                    'chart_account_id' => $chartAccounts[$component],
                    'customer_id' => $loan->customer_id,
                    'amount' => $amount,
                    'nature' => 'credit',
                    'transaction_id' => $journal->id,
                    'transaction_type' => 'journal repayment',
                    'date' => $repaymentInfo['payment_date'],
                    'description' => ucfirst($component) . " repayment for loan #{$loan->id}",
                    'branch_id' => $loan->branch_id,
                    'user_id' => $this->userId,
                ]);
            }
        }
    }

    /**
     * Check if loan is fully paid and close it automatically
     */
    private function checkAndCloseLoan($loan)
    {
        try {
            // Refresh the loan to get updated data
            $loan->refresh();

            // Check if loan is eligible for closing
            if ($loan->isEligibleForClosing()) {
                $closed = $loan->closeLoan();

                if ($closed) {
                    Log::info("Loan automatically closed after complete repayment", [
                        'loan_id' => $loan->id,
                        'loan_no' => $loan->loanNo,
                        'customer' => $loan->customer->name ?? 'Unknown'
                    ]);
                } else {
                    Log::warning("Failed to close loan despite being eligible", [
                        'loan_id' => $loan->id,
                        'loan_no' => $loan->loanNo
                    ]);
                }
            } else {
                // Log remaining outstanding amount for tracking
                $outstanding = $loan->getTotalOutstandingAmount();
                Log::info("Loan not yet fully paid", [
                    'loan_id' => $loan->id,
                    'loan_no' => $loan->loanNo,
                    'outstanding_amount' => $outstanding
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Error checking/closing loan after repayment", [
                'loan_id' => $loan->id,
                'loan_no' => $loan->loanNo,
                'error' => $e->getMessage()
            ]);
        }
    }
}
