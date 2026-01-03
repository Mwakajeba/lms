<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\Region;
use App\Models\District;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BulkCustomerImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $rows;
    protected $options;
    protected $userId;
    protected $branchId;
    protected $companyId;
    protected $chunkSize = 50;

    /**
     * Create a new job instance.
     */
    public function __construct($rows, $options, $userId, $branchId, $companyId)
    {
        $this->rows = $rows;
        $this->options = $options;
        $this->userId = $userId;
        $this->branchId = $branchId;
        $this->companyId = $companyId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting bulk customer import job', [
            'total_rows' => count($this->rows),
            'user_id' => $this->userId
        ]);

        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        // Process in chunks
        $chunks = array_chunk($this->rows, $this->chunkSize);

        foreach ($chunks as $chunkIndex => $chunk) {
            DB::beginTransaction();
            try {
                foreach ($chunk as $rowIndex => $rowData) {
                    try {
                        $result = $this->processCustomerRow($rowData, $chunkIndex * $this->chunkSize + $rowIndex + 2);
                        
                        if ($result['success']) {
                            $successCount++;
                        } else {
                            $errorCount++;
                            $errors[] = $result['error'];
                        }
                    } catch (\Exception $e) {
                        $errorCount++;
                        $errors[] = "Row " . ($chunkIndex * $this->chunkSize + $rowIndex + 2) . ": " . $e->getMessage();
                        Log::error('Error processing customer row', [
                            'row' => $rowIndex,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error in bulk customer import chunk', [
                    'chunk' => $chunkIndex,
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info('Completed bulk customer import job', [
            'success_count' => $successCount,
            'error_count' => $errorCount
        ]);
    }

    /**
     * Process a single customer row
     */
    protected function processCustomerRow($rowData, $rowNumber)
    {
        // Validate required fields
        if (empty($rowData['name']) || empty($rowData['phone1']) || empty($rowData['dob']) || empty($rowData['sex'])) {
            return [
                'success' => false,
                'error' => "Row {$rowNumber}: Missing required fields"
            ];
        }

        // Validate sex
        $sex = strtoupper(trim($rowData['sex']));
        if (!in_array($sex, ['M', 'F'])) {
            return [
                'success' => false,
                'error' => "Row {$rowNumber}: Sex must be M or F (got: {$rowData['sex']})"
            ];
        }

        // Format and validate phone number
        $formattedPhone1 = $this->formatPhoneNumber(trim($rowData['phone1']));
        if (!str_starts_with($formattedPhone1, '255')) {
            return [
                'success' => false,
                'error' => "Row {$rowNumber}: Phone number must start with prefix 255"
            ];
        }

        // Validate phone number uniqueness
        $existingCustomer = Customer::where('phone1', $formattedPhone1)->first();
        if ($existingCustomer) {
            return [
                'success' => false,
                'error' => "Row {$rowNumber}: Phone number already exists: {$formattedPhone1}"
            ];
        }

        // Validate age (must be at least 18 years old)
        try {
            $dob = Carbon::parse($rowData['dob']);
            $age = $dob->age;
            if ($age < 18) {
                return [
                    'success' => false,
                    'error' => "Row {$rowNumber}: Customer must be at least 18 years old (DOB: {$rowData['dob']}, Age: {$age})"
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => "Row {$rowNumber}: Invalid date of birth format: {$rowData['dob']}"
            ];
        }

        // Validate region and district
        $regionId = null;
        $districtId = null;
        
        if (!empty($rowData['region'])) {
            $region = Region::where('name', trim($rowData['region']))->first();
            if (!$region) {
                return [
                    'success' => false,
                    'error' => "Row {$rowNumber}: Invalid region: {$rowData['region']}. Region must exist in the system."
                ];
            }
            $regionId = $region->id;

            if (!empty($rowData['district'])) {
                $district = District::where('name', trim($rowData['district']))
                    ->where('region_id', $regionId)
                    ->first();
                if (!$district) {
                    return [
                        'success' => false,
                        'error' => "Row {$rowNumber}: Invalid district: {$rowData['district']} for region {$rowData['region']}"
                    ];
                }
                $districtId = $district->id;
            }
        }

        // Create customer data
        $customerData = [
            'phone1' => $formattedPhone1,
            'phone2' => !empty($rowData['phone2']) ? $this->formatPhoneNumber(trim($rowData['phone2'])) : null,
            'name' => trim($rowData['name']),
            'dob' => $rowData['dob'],
            'sex' => $sex,
            'region_id' => $regionId,
            'district_id' => $districtId,
            'work' => !empty($rowData['work']) ? trim($rowData['work']) : null,
            'workAddress' => !empty($rowData['workaddress']) ? trim($rowData['workaddress']) : null,
            'idType' => !empty($rowData['idtype']) ? trim($rowData['idtype']) : null,
            'idNumber' => !empty($rowData['idnumber']) ? trim($rowData['idnumber']) : null,
            'relation' => !empty($rowData['relation']) ? trim($rowData['relation']) : null,
            'description' => !empty($rowData['description']) ? trim($rowData['description']) : null,
            'customerNo' => 100000 + (Customer::max('id') ?? 0) + 1,
            'password' => Hash::make('12345'),
            'branch_id' => $this->branchId,
            'company_id' => $this->companyId,
            'registrar' => $this->userId,
            'dateRegistered' => now()->toDateString(),
            'has_cash_collateral' => $this->options['has_cash_collateral'] ?? false,
            'category' => 'Borrower',
        ];

        $customer = Customer::create($customerData);

        // Add cash collateral if selected
        if ($this->options['has_cash_collateral'] && !empty($this->options['collateral_type_id'])) {
            \App\Models\CashCollateral::create([
                'customer_id' => $customer->id,
                'type_id' => $this->options['collateral_type_id'],
                'amount' => 0,
                'branch_id' => $this->branchId,
                'company_id' => $this->companyId,
            ]);
        }

        // Assign to individual group
        $existingMembership = DB::table('group_members')->where('customer_id', $customer->id)->first();
        if (!$existingMembership) {
            DB::table('group_members')->insert([
                'group_id' => 1,
                'customer_id' => $customer->id,
                'status' => 'active',
                'joined_date' => now()->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return ['success' => true];
    }

    /**
     * Format phone number to standard format
     */
    protected function formatPhoneNumber($phoneNumber)
    {
        if (empty($phoneNumber)) {
            return $phoneNumber;
        }

        // Remove any spaces, dashes, or special characters except +
        $phoneNumber = preg_replace("/[^0-9+]/", "", $phoneNumber);

        // If starts with 0, remove 0 and add 255
        if (substr($phoneNumber, 0, 1) === "0") {
            return "255" . substr($phoneNumber, 1);
        }

        // If starts with +255, remove +
        if (substr($phoneNumber, 0, 4) === "+255") {
            return substr($phoneNumber, 1);
        }

        // Return as is if already in correct format
        return $phoneNumber;
    }
}

