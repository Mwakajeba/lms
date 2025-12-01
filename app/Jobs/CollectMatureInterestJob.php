<?php

namespace App\Jobs;

use App\Models\Loan;
use App\Models\LoanSchedule;
use App\Models\GlTransaction;
use App\Models\ChartAccount;
use App\Helpers\SmsHelper;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class CollectMatureInterestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes timeout
    public $tries = 3;

    public function __construct()
    {
        //
    }

    public function handle()
    {
        Log::info('Starting mature interest & penalty collection job');

        try {
            DB::beginTransaction();

            $activeLoans = Loan::where('status', 'active')
                ->with(['product', 'customer', 'branch', 'schedule.repayments'])
                ->get();

            $totalProcessed = 0;

            foreach ($activeLoans as $loan) {
                $processedInterest = $this->processLoanMatureInterest($loan);
                $processedPenalty = $this->processLoanPenalty($loan);

                if ($processedInterest || $processedPenalty) {
                    $totalProcessed++;
                }
            }

            DB::commit();

            Log::info("Job completed. Processed {$totalProcessed} loans");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in job: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Process mature interest for a loan
     */
    private function processLoanMatureInterest(Loan $loan): bool
    {
        $maturedSchedules = $loan->schedule()
            ->where('due_date', '=', Carbon::today())
            ->where('interest', '>', 0)
            ->get();

        if ($maturedSchedules->isEmpty()) {
            return false;
        }

        $totalInterestPosted = 0;

        foreach ($maturedSchedules as $schedule) {
            $totalInterestPosted += $this->processScheduleMatureInterest($loan, $schedule);
        }

        if ($totalInterestPosted > 0) {
            Log::info("Posted mature interest for loan {$loan->loanNo}: TZS " . number_format($totalInterestPosted, 2));
        }

        return $totalInterestPosted > 0;
    }

    /**
     * Post mature interest for a single schedule
     */
    private function processScheduleMatureInterest(Loan $loan, LoanSchedule $schedule): float
    {
        // Ensure repayments relationship is available
        $schedule->loadMissing('repayments');

        $totalInterest = $schedule->interest;
        // Consider only repayments recorded against this schedule and matching the schedule due_date
        $paidInterest = $schedule->repayments
            ->where('due_date', Carbon::parse($schedule->due_date))
            ->sum('interest');
        $unpaidInterest = $totalInterest - $paidInterest;

        if ($unpaidInterest <= 0) {
            return 0;
        }

        $receivableId = $loan->product->interest_receivable_account_id;
        $incomeId = $loan->product->interest_revenue_account_id;

        if (!$receivableId || !$incomeId) {
            Log::warning("Missing interest accounts for product {$loan->product->id}");
            return 0;
        }

        // Prevent duplicate mature interest postings for this schedule
        $exists = GlTransaction::where('chart_account_id', $receivableId)
            ->where('customer_id', $loan->customer_id)
            ->where('transaction_id', $schedule->id)
            ->where('transaction_type', 'Mature Interest')
            ->exists();

        if ($exists) {
            return 0;
        }

        // Debit Receivable (record on schedule due date and mark as Mature Interest)
        GlTransaction::create([
            'chart_account_id' => $receivableId,
            'customer_id' => $loan->customer_id,
            'amount' => $unpaidInterest,
            'nature' => 'debit',
            'transaction_id' => $schedule->id,
            'transaction_type' => 'Mature Interest',
            'date' => $schedule->due_date,
            'description' => "Mature interest for loan {$loan->loanNo}, schedule {$schedule->id}",
            'branch_id' => $loan->branch_id,
            'user_id' => 1,
        ]);

        // Credit Revenue (record on schedule due date and mark as Mature Interest)
        GlTransaction::create([
            'chart_account_id' => $incomeId,
            'customer_id' => $loan->customer_id,
            'amount' => $unpaidInterest,
            'nature' => 'credit',
            'transaction_id' => $schedule->id,
            'transaction_type' => 'Mature Interest',
            'date' => $schedule->due_date,
            'description' => "Mature interest income for loan {$loan->loanNo}, schedule {$schedule->id}",
            'branch_id' => $loan->branch_id,
            'user_id' => 1,
        ]);

        return $unpaidInterest;
    }

    /**
     * Process penalty for overdue schedules
     */
    private function processLoanPenalty(Loan $loan): bool
    {
        $product = $loan->product;

        if (!$product || !$product->penalty) {
            return false;
        }

        $penaltyConfig = $product->penalty;

        $graceDays = $product->grace_period ?? 0;
        // frequency from penalty config; fallback to product setting ('daily_bases'|'full_amount')
        $frequency = $penaltyConfig->charge_frequency ?? null;
        if (!$frequency) {
            $frequency = ($product->penalt_deduction_criteria === 'daily_bases') ? 'daily' : 'one_time';
        }
        $penaltyRateType = $penaltyConfig->penalty_type ?? 'percentage'; // 'percentage' or 'fixed amount'
        $penaltyAmountSetting = $penaltyConfig->amount ?? 0;
        $criteria = $penaltyConfig->deduction_type;

        // Preload all schedules with repayments & also next schedule date
        $schedules = $loan->schedule()
            ->with(['repayments:id,loan_schedule_id,penalt_amount,fee_amount,interest,principal'])
            ->where('due_date', '<', Carbon::today())
            ->orderBy('due_date')
            ->get()
            ->values();

        if ($schedules->isEmpty()) {
            return false;
        }

        // Precompute next schedule dates for each schedule
        $dueDates = $schedules->pluck('due_date')->map(fn($d) => Carbon::parse($d))->values();
        $nextScheduleDates = [];
        foreach ($dueDates as $i => $date) {
            $nextScheduleDates[$i] = $dueDates->get($i + 1) ?? null;
        }

        // Process all unpaid schedules - charge penalty for each unpaid schedule
        foreach ($schedules as $i => $schedule) {
            // Respect grace period: skip schedules still within grace window
            $graceEndDate = Carbon::parse($schedule->due_date)->addDays($graceDays);
            if (Carbon::today()->lte($graceEndDate)) {
                continue;
            }
            
            // Check if schedule is fully paid - skip if paid
            $paidAmount = $schedule->repayments->sum(
                fn($rep) => $rep->penalt_amount + $rep->fee_amount + $rep->interest + $rep->principal
            );

            $installmentAmount = $schedule->interest + $schedule->principal + $schedule->fee_amount + $schedule->penalty_amount;
            if ($paidAmount >= $installmentAmount) {
                continue; // Skip fully paid schedules
            }

            // Penalty end date (day before next schedule)
            $penaltyEndDate = isset($nextScheduleDates[$i])
                ? Carbon::parse($nextScheduleDates[$i])->subDay()
                : null;

            if ($penaltyEndDate && Carbon::today()->gt($penaltyEndDate)) {
                continue;
            }

            // Skip if penalty already exists for this schedule today (for daily penalties)
            // or if penalty already exists for this schedule (for one-time penalties)
            $exists = GlTransaction::where('chart_account_id', $penaltyConfig->penalty_receivables_account_id)
                ->where('transaction_type', 'Penalty')
                ->where('transaction_id', $schedule->id)
                ->where('customer_id', $loan->customer_id)
                ->when($frequency === 'daily', fn($q) => $q->whereDate('date', Carbon::today()))
                ->exists();

            if ($exists) {
                continue; // Skip if penalty already charged for this schedule
            }

            // Determine base amount for penalty calculation
            $base = match ($criteria) {
                'over_due_principal_amount' => $schedule->principal,
                'over_due_interest_amount' => $schedule->interest,
                'over_due_principal_and_interest' => $schedule->principal + $schedule->interest,
                'total_principal_amount_released' => $loan->amount,
                default => $loan->amount,
            };

            // Calculate penalty for this unpaid schedule
            if ($frequency === 'daily') {
                $daysOverdue = max(1, Carbon::today()->diffInDays(
                    Carbon::parse($schedule->due_date)->addDays($graceDays)
                ));
                $dailyAmount = $penaltyRateType === 'percentage'
                    ? round(($base * $penaltyAmountSetting / 100) / 30, 2) // per day
                    : round($penaltyAmountSetting / 30, 2);
                $penaltyAmount = $dailyAmount * $daysOverdue;
            } else {
                // One-time penalty for this schedule
                $penaltyAmount = $penaltyRateType === 'percentage'
                    ? round($base * $penaltyAmountSetting / 100, 2)
                    : round($penaltyAmountSetting, 2);
            }

            if ($penaltyAmount <= 0) {
                continue; // Skip if no penalty amount
            }

            // Charge penalty for this unpaid schedule
            $glData = [
                [
                    'chart_account_id' => $penaltyConfig->penalty_receivables_account_id,
                    'customer_id' => $loan->customer_id,
                    'amount' => $penaltyAmount,
                    'nature' => 'debit',
                    'transaction_id' => $schedule->id,
                    'transaction_type' => 'Penalty',
                    'date' => Carbon::today(),
                    'description' => "Penalty for overdue schedule {$schedule->id} - loan {$loan->loanNo}",
                    'branch_id' => $loan->branch_id,
                    'user_id' => 1,
                ],
                [
                    'chart_account_id' => $penaltyConfig->penalty_income_account_id,
                    'customer_id' => $loan->customer_id,
                    'amount' => $penaltyAmount,
                    'nature' => 'credit',
                    'transaction_id' => $schedule->id,
                    'transaction_type' => 'Penalty',
                    'date' => Carbon::today(),
                    'description' => "Penalty income for overdue schedule {$schedule->id} - loan {$loan->loanNo}",
                    'branch_id' => $loan->branch_id,
                    'user_id' => 1,
                ]
            ];
            GlTransaction::insert($glData);

            // Update schedule penalty_amount
            $schedule->increment('penalty_amount', $penaltyAmount);

            // Send SMS notification to customer in Kiswahili
            $this->sendPenaltySms($loan, $penaltyAmount);
        }

        return true;
    }


    /**
     * Send SMS notification to customer about penalty
     */
    private function sendPenaltySms(Loan $loan, float $penaltyAmount): void
    {
        try {
            $customer = $loan->customer;
            if (!$customer || empty($customer->phone1)) {
                Log::warning("Cannot send penalty SMS - customer phone missing for loan {$loan->loanNo}");
                return;
            }

            $formattedAmount = number_format($penaltyAmount, 2);
            $message = "Habari {$customer->name}. Mkopo namba {$loan->loanNo} una deni la faini ya TZS {$formattedAmount} kwa kuchelewa kulipa. Tafadhali lipa haraka ili uepuke faini zaidi. Asante.";

            $phone = normalize_phone_number($customer->phone1);
            SmsHelper::send($phone, $message);
            Log::info("Penalty SMS sent to customer {$customer->id} for loan {$loan->loanNo}: TZS {$formattedAmount}");
        } catch (\Exception $e) {
            Log::error("Failed to send penalty SMS for loan {$loan->loanNo}: " . $e->getMessage());
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error('Job failed: ' . $exception->getMessage());
    }
}
