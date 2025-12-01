<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Branch;
use App\Models\LoanProduct;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateLoanCsvCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loan:generate-csv {--count=100}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate loan CSV file with customer data for import';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = $this->option('count');
        
        $this->info("Generating loan CSV for {$count} customers...");

        // Get customers with required relationships, excluding those with active loans
        $customers = Customer::with(['branch', 'region', 'district'])
            ->whereDoesntHave('loans', function($query) {
                $query->where('status', 'active');
            })
            ->take($count)
            ->get();

        if ($customers->isEmpty()) {
            $this->error('No customers found in the database.');
            return 1;
        }

        // Get some default data for loans
        $branches = Branch::all();
        $loanProducts = LoanProduct::all();
        $loanOfficers = User::whereHas('roles', function($query) {
            $query->where('name', 'like', '%officer%');
        })->get();
        
        // Get available groups
        $groups = \App\Models\Group::all();

        // If no specific loan officers, get any users
        if ($loanOfficers->isEmpty()) {
            $loanOfficers = User::take(10)->get();
        }

        $csvData = [];
        
        // CSV Headers - matching import expected format
        $headers = [
            'customer_no',
            'amount',
            'period',
            'interest',
            'date_applied',
            'interest_cycle',
            'loan_officer',
            'group_id',
            'sector'
        ];

        $csvData[] = $headers;

        // Generate loan data for each customer
        foreach ($customers as $index => $customer) {
            // Random loan amounts between 100,000 and 10,000,000
            $loanAmount = rand(100000, 10000000);
            
            // Random interest rates between 10% and 25%
            $interestRate = rand(10, 25);
            
            // Random periods between 6 and 60 months
            $period = rand(6, 60);
            
            // Random sectors
            $sectors = [
                'Agriculture',
                'Trade',
                'Manufacturing',
                'Services',
                'Transport',
                'Construction',
                'Education',
                'Health'
            ];
            
            // Random application date within last 6 months
            $applicationDate = now()->subDays(rand(1, 180))->format('Y-m-d');
            
            // Interest cycles
            $interestCycles = ['Monthly', 'Weekly', 'Quarterly'];

            // Get a random loan officer ID (need actual user ID, not name)
            $loanOfficerId = $loanOfficers->isNotEmpty() ? $loanOfficers->random()->id : rand(1, 9);
            
            // Get a random group ID (only use existing group IDs)
            $groupId = $groups->isNotEmpty() ? $groups->random()->id : rand(1, 6);

            $csvData[] = [
                $customer->customerNo ?? 'CUST' . str_pad($index + 1, 6, '0', STR_PAD_LEFT),
                $loanAmount,
                $period,
                $interestRate,
                $applicationDate,
                $interestCycles[array_rand($interestCycles)],
                $loanOfficerId,
                $groupId,
                $sectors[array_rand($sectors)]
            ];
        }

        // Generate CSV content
        $csvContent = '';
        foreach ($csvData as $row) {
            $csvContent .= implode(',', array_map(function($field) {
                // Escape fields that contain commas or quotes
                if (strpos($field, ',') !== false || strpos($field, '"') !== false) {
                    return '"' . str_replace('"', '""', $field) . '"';
                }
                return $field;
            }, $row)) . "\n";
        }

        // Save to public directory
        $fileName = 'loan_import_' . now()->format('Y_m_d_His') . '.csv';
        $filePath = 'public/downloads/' . $fileName;
        
        // Ensure downloads directory exists
        Storage::makeDirectory('public/downloads');
        
        // Save the file
        Storage::put($filePath, $csvContent);
        
        // Get the public URL
        $publicPath = storage_path('app/' . $filePath);
        $webPath = asset('storage/downloads/' . $fileName);

        $this->info("CSV file generated successfully!");
        $this->info("File saved to: {$publicPath}");
        $this->info("Download URL: {$webPath}");
        $this->info("Total records: " . count($csvData) - 1); // Minus header row

        return 0;
    }
}
