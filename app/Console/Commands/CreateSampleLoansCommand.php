<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Loan;
use App\Models\LoanProduct;
use App\Models\BankAccount;
use App\Models\Branch;
use App\Models\User;
use App\Models\Group;
use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\GlTransaction;
use App\Models\ChartAccount;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateSampleLoansCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loans:create-sample {--count=100}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create sample loans directly in the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = $this->option('count');
        
        $this->info("Creating {$count} sample loans...");

        // Get available customers without active loans
        $customers = Customer::whereDoesntHave('loans', function($query) {
            $query->where('status', 'active');
        })->take($count)->get();

        if ($customers->count() < $count) {
            $this->warn("Only {$customers->count()} customers without active loans found. Creating loans for available customers.");
        }

        // Get required entities
        $loanProducts = LoanProduct::with('principalReceivableAccount')->get();
        $bankAccount = BankAccount::first();
        $branches = Branch::all();
        $users = User::take(10)->get();
        $groups = Group::all();

        if (!$bankAccount) {
            $this->error('No bank account found. Please create a bank account first.');
            return 1;
        }

        if ($loanProducts->isEmpty()) {
            $this->error('No loan products found. Please create loan products first.');
            return 1;
        }

        $successCount = 0;
        $errorCount = 0;

        DB::transaction(function () use ($customers, $loanProducts, $bankAccount, $branches, $users, $groups, &$successCount, &$errorCount) {
            foreach ($customers as $customer) {
                try {
                    // Random loan data
                    $product = $loanProducts->random();
                    $amount = rand(100000, 10000000);
                    $interest = rand(10, 25);
                    $period = rand(6, 60);
                    $applicationDate = now()->subDays(rand(1, 180))->format('Y-m-d');
                    $disbursementDate = now()->parse($applicationDate)->addDays(rand(7, 30))->format('Y-m-d');
                    $loanOfficer = $users->random();
                    $group = $groups->random();
                    $branch = $branches->random();
                    
                    $sectors = ['Agriculture', 'Trade', 'Manufacturing', 'Services', 'Transport', 'Construction', 'Education', 'Health'];
                    $interestCycles = ['Monthly', 'Weekly', 'Quarterly'];

                    // Create the loan
                    $loan = Loan::create([
                        'customer_id' => $customer->id,
                        'group_id' => $group->id,
                        'product_id' => $product->id,
                        'amount' => $amount,
                        'interest' => $interest,
                        'period' => $period,
                        'bank_account_id' => $bankAccount->id,
                        'date_applied' => $applicationDate,
                        'disbursed_on' => $disbursementDate,
                        'status' => 'active',
                        'sector' => $sectors[array_rand($sectors)],
                        'interest_cycle' => $interestCycles[array_rand($interestCycles)],
                        'loan_officer_id' => $loanOfficer->id,
                        'branch_id' => $branch->id,
                    ]);

                    // Calculate interest and dates
                    $interestAmount = $loan->calculateInterestAmount($interest);
                    $repaymentDates = $loan->getRepaymentDates();

                    // Update loan with calculated values
                    $loan->update([
                        'interest_amount' => $interestAmount,
                        'amount_total' => $amount + $interestAmount,
                        'first_repayment_date' => $repaymentDates['first_repayment_date'],
                        'last_repayment_date' => $repaymentDates['last_repayment_date'],
                    ]);

                    // Generate repayment schedule
                    $loan->generateRepaymentSchedule($interest);

                    // Create payment record
                    $notes = "Being disbursement for loan of {$product->name}, paid to {$customer->name}, TSHS.{$amount}";
                    
                    $payment = Payment::create([
                        'reference' => $loan->id,
                        'reference_type' => 'Loan Payment',
                        'reference_number' => null,
                        'date' => $disbursementDate,
                        'amount' => $amount,
                        'description' => $notes,
                        'user_id' => $loanOfficer->id,
                        'customer_id' => $customer->id,
                        'bank_account_id' => $bankAccount->id,
                        'branch_id' => $branch->id,
                        'approved' => true,
                        'approved_by' => $loanOfficer->id,
                        'approved_at' => now(),
                    ]);

                    // Create payment item
                    if ($product->principalReceivableAccount) {
                        PaymentItem::create([
                            'payment_id' => $payment->id,
                            'chart_account_id' => $product->principalReceivableAccount->id,
                            'amount' => $amount,
                            'description' => $notes,
                        ]);

                        // Create GL transactions
                        GlTransaction::insert([
                            [
                                'chart_account_id' => $bankAccount->chart_account_id,
                                'customer_id' => $customer->id,
                                'amount' => $amount,
                                'nature' => 'credit',
                                'transaction_id' => $loan->id,
                                'transaction_type' => 'Loan Disbursement',
                                'date' => $disbursementDate,
                                'description' => $notes,
                                'branch_id' => $branch->id,
                                'user_id' => $loanOfficer->id,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ],
                            [
                                'chart_account_id' => $product->principalReceivableAccount->id,
                                'customer_id' => $customer->id,
                                'amount' => $amount,
                                'nature' => 'debit',
                                'transaction_id' => $loan->id,
                                'transaction_type' => 'Loan Disbursement',
                                'date' => $disbursementDate,
                                'description' => $notes,
                                'branch_id' => $branch->id,
                                'user_id' => $loanOfficer->id,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]
                        ]);
                    }

                    $successCount++;
                    $this->info("Created loan #{$loan->loanNo} for customer {$customer->name} - Amount: TSHS " . number_format($amount));

                } catch (\Exception $e) {
                    $errorCount++;
                    $this->error("Failed to create loan for customer {$customer->name}: " . $e->getMessage());
                }
            }
        });

        $this->info("Loan creation completed!");
        $this->info("Successfully created: {$successCount} loans");
        if ($errorCount > 0) {
            $this->warn("Failed to create: {$errorCount} loans");
        }

        return 0;
    }
}
