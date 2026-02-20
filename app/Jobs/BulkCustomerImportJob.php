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
        $failedRecords = []; // Store failed records with detailed error information

        // Process in chunks
        $chunks = array_chunk($this->rows, $this->chunkSize);

        foreach ($chunks as $chunkIndex => $chunk) {
            DB::beginTransaction();
            try {
                foreach ($chunk as $rowIndex => $rowData) {
                    try {
                        $rowNumber = $chunkIndex * $this->chunkSize + $rowIndex + 2;
                        $result = $this->processCustomerRow($rowData, $rowNumber);
                        
                        if ($result['success']) {
                            $successCount++;
                        } else {
                            $errorCount++;
                            $errors[] = $result['error'];
                            $failedRecords[] = [
                                'row_number' => $rowNumber,
                                'data' => $rowData,
                                'error' => $result['error']
                            ];
                        }
                    } catch (\Exception $e) {
                        $rowNumber = $chunkIndex * $this->chunkSize + $rowIndex + 2;
                        $errorMsg = "Database error: " . $e->getMessage();
                        $errorCount++;
                        $errors[] = "Row {$rowNumber}: {$errorMsg}";
                        $failedRecords[] = [
                            'row_number' => $rowNumber,
                            'data' => $rowData,
                            'error' => $errorMsg
                        ];
                        
                        // Log detailed error with full exception details
                        Log::error('Error processing customer row in job', [
                            'row_number' => $rowNumber,
                            'customer_name' => $rowData['name'] ?? 'N/A',
                            'phone1' => $rowData['phone1'] ?? 'N/A',
                            'exception_message' => $e->getMessage(),
                            'exception_trace' => $e->getTraceAsString(),
                            'error' => $errorMsg
                        ]);
                    }
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error in bulk customer import chunk', [
                    'chunk' => $chunkIndex,
                    'error' => $e->getMessage(),
                    'exception_trace' => $e->getTraceAsString()
                ]);
            }
        }
        
        // Store failed records in cache for export (valid for 24 hours)
        if (!empty($failedRecords)) {
            $cacheKey = 'failed_customer_records_' . $this->userId . '_' . time();
            cache()->put($cacheKey, $failedRecords, now()->addHours(24));
            Log::info('Failed records stored in cache', [
                'cache_key' => $cacheKey,
                'failed_count' => count($failedRecords),
                'user_id' => $this->userId
            ]);
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
            $missingFields = [];
            if (empty($rowData['name'])) $missingFields[] = 'name';
            if (empty($rowData['phone1'])) $missingFields[] = 'phone1';
            if (empty($rowData['dob'])) $missingFields[] = 'dob';
            if (empty($rowData['sex'])) $missingFields[] = 'sex';
            
            $errorMsg = "Row {$rowNumber}: Missing required fields: " . implode(', ', $missingFields);
            
            Log::warning('Customer bulk upload validation failed in job', [
                'row_number' => $rowNumber,
                'customer_name' => $rowData['name'] ?? 'N/A',
                'phone1' => $rowData['phone1'] ?? 'N/A',
                'error' => $errorMsg,
                'missing_fields' => $missingFields
            ]);
            
            return [
                'success' => false,
                'error' => $errorMsg
            ];
        }

        // Validate sex
        $sex = strtoupper(trim($rowData['sex']));
        if (!in_array($sex, ['M', 'F'])) {
            $errorMsg = "Row {$rowNumber}: Sex must be M or F (got: {$rowData['sex']})";
            
            Log::warning('Customer bulk upload validation failed in job - invalid sex', [
                'row_number' => $rowNumber,
                'customer_name' => $rowData['name'] ?? 'N/A',
                'phone1' => $rowData['phone1'] ?? 'N/A',
                'sex_provided' => $rowData['sex'] ?? 'empty',
                'error' => $errorMsg
            ]);
            
            return [
                'success' => false,
                'error' => $errorMsg
            ];
        }

        // Format and validate phone number
        $formattedPhone1 = $this->formatPhoneNumber(trim($rowData['phone1']));
        if (!str_starts_with($formattedPhone1, '255')) {
            $errorMsg = "Row {$rowNumber}: Phone number must start with prefix 255 (provided: {$formattedPhone1})";
            
            Log::warning('Customer bulk upload validation failed in job - invalid phone prefix', [
                'row_number' => $rowNumber,
                'customer_name' => $rowData['name'] ?? 'N/A',
                'phone1_original' => $rowData['phone1'] ?? 'N/A',
                'phone1_formatted' => $formattedPhone1,
                'error' => $errorMsg
            ]);
            
            return [
                'success' => false,
                'error' => $errorMsg
            ];
        }

        // Validate phone number uniqueness
        $existingCustomer = Customer::where('phone1', $formattedPhone1)->first();
        if ($existingCustomer) {
            $errorMsg = "Row {$rowNumber}: Phone number already exists: {$formattedPhone1} (Customer ID: {$existingCustomer->id}, Name: {$existingCustomer->name})";
            
            Log::warning('Customer bulk upload validation failed in job - duplicate phone', [
                'row_number' => $rowNumber,
                'customer_name' => $rowData['name'] ?? 'N/A',
                'phone1' => $formattedPhone1,
                'existing_customer_id' => $existingCustomer->id,
                'existing_customer_name' => $existingCustomer->name,
                'error' => $errorMsg
            ]);
            
            return [
                'success' => false,
                'error' => $errorMsg
            ];
        }

        // Validate age (must be at least 18 years old)
        try {
            $dob = Carbon::parse($rowData['dob']);
            $age = $dob->age;
            if ($age < 18) {
                $errorMsg = "Row {$rowNumber}: Customer must be at least 18 years old (DOB: {$rowData['dob']}, Age: {$age})";
                
                Log::warning('Customer bulk upload validation failed in job - age requirement', [
                    'row_number' => $rowNumber,
                    'customer_name' => $rowData['name'] ?? 'N/A',
                    'dob' => $rowData['dob'],
                    'age' => $age,
                    'error' => $errorMsg
                ]);
                
                return [
                    'success' => false,
                    'error' => $errorMsg
                ];
            }
        } catch (\Exception $e) {
            $errorMsg = "Row {$rowNumber}: Invalid date of birth format: {$rowData['dob']} - " . $e->getMessage();
            
            Log::warning('Customer bulk upload validation failed in job - invalid DOB format', [
                'row_number' => $rowNumber,
                'customer_name' => $rowData['name'] ?? 'N/A',
                'dob_provided' => $rowData['dob'] ?? 'empty',
                'exception_message' => $e->getMessage(),
                'error' => $errorMsg
            ]);
            
            return [
                'success' => false,
                'error' => $errorMsg
            ];
        }

        // Validate region and district
        $regionId = null;
        $districtId = null;
        
        if (!empty($rowData['region'])) {
            $region = Region::where('name', trim($rowData['region']))->first();
            if (!$region) {
                $errorMsg = "Row {$rowNumber}: Invalid region: {$rowData['region']}. Region must exist in the system.";
                
                Log::warning('Customer bulk upload validation failed in job - invalid region', [
                    'row_number' => $rowNumber,
                    'customer_name' => $rowData['name'] ?? 'N/A',
                    'region_provided' => $rowData['region'],
                    'available_regions' => Region::pluck('name')->toArray(),
                    'error' => $errorMsg
                ]);
                
                return [
                    'success' => false,
                    'error' => $errorMsg
                ];
            }
            $regionId = $region->id;

            if (!empty($rowData['district'])) {
                $district = District::where('name', trim($rowData['district']))
                    ->where('region_id', $regionId)
                    ->first();
                if (!$district) {
                    $errorMsg = "Row {$rowNumber}: Invalid district: {$rowData['district']} for region {$rowData['region']}";
                    $availableDistricts = District::where('region_id', $regionId)->pluck('name')->toArray();
                    
                    Log::warning('Customer bulk upload validation failed in job - invalid district', [
                        'row_number' => $rowNumber,
                        'customer_name' => $rowData['name'] ?? 'N/A',
                        'region' => $rowData['region'],
                        'region_id' => $regionId,
                        'district_provided' => $rowData['district'],
                        'available_districts' => $availableDistricts,
                        'error' => $errorMsg
                    ]);
                    
                    return [
                        'success' => false,
                        'error' => $errorMsg
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

