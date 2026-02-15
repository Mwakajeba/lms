<?php




namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Customer;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Region;
use App\Models\District;
use App\Models\User;
use App\Models\CashCollateralType;
use App\Models\Filetype;
use App\Services\LoanPenaltyService;
use App\Exports\CustomerImportTemplateExport;
use App\Exports\FailedCustomerRecordsExport;
use App\Jobs\BulkCustomerImportJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

set_time_limit(0);              // no limit for this request
ini_set('max_execution_time', 0);

class CustomerController extends Controller
{
    /**
     * Format phone number to standard format
     * - If starts with 0, remove 0 and add 255
     * - If starts with +255, remove +
     * - Otherwise return as is
     */
    private function formatPhoneNumber($phoneNumber)
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

    // Display all customers
    public function index()
    {
        $branchId = auth()->user()->branch_id;
        $borrowerCount = Customer::where('category', 'Borrower')->where('branch_id', $branchId)->count();
        $guarantorCount = Customer::where('category', 'Guarantor')->where('branch_id', $branchId)->count();
        $customerCount = Customer::where('branch_id', $branchId)->count();

        return view('customers.index', compact('borrowerCount', 'guarantorCount', 'customerCount'));
    }

    // Ajax endpoint for DataTables
    public function getCustomersData(Request $request)
    {
        if ($request->ajax()) {
            $branchId = auth()->user()->branch_id;

            $customers = Customer::with(['branch', 'company', 'user', 'region', 'district'])
                ->where('branch_id', $branchId)
                ->select('customers.*');

            return DataTables::eloquent($customers)
                ->addColumn('avatar_name', function ($customer) {
                    $isGuarantor = isset($customer->category) && strtolower($customer->category) === 'guarantor';
                    $avatarClass = $isGuarantor ? 'bg-success' : 'bg-primary';
                    $initial = strtoupper(substr($customer->name, 0, 1));

                    return '<div class="d-flex align-items-center">
                                <div class="avatar avatar-sm ' . $avatarClass . ' rounded-circle me-2 d-flex align-items-center justify-content-center shadow" style="width:36px; height:36px;">
                                    <span class="avatar-title text-white fw-bold" style="font-size:1.25rem;">' . $initial . '</span>
                                </div>
                                <div>
                                    <div class="fw-bold">' . e($customer->name) . '</div>
                                </div>
                            </div>';
                })
                ->addColumn('region_name', function ($customer) {
                    return $customer->region->name ?? '';
                })
                ->addColumn('district_name', function ($customer) {
                    return $customer->district->name ?? '';
                })
                ->addColumn('branch_name', function ($customer) {
                    return optional($customer->branch)->name ?? '';
                })
                ->addColumn('actions', function ($customer) {
                    $actions = '';
                    $encodedId = \Vinkla\Hashids\Facades\Hashids::encode($customer->id);

                    // View action
                    if (auth()->user()->can('view customer profile')) {
                        $actions .= '<a href="' . route('customers.show', $encodedId) . '" class="btn btn-sm btn-outline-info me-1" title="View"><i class="bx bx-show"></i> Show</a>';
                    }

                    // Edit action
                    if (auth()->user()->can('edit customer')) {
                        $actions .= '<a href="' . route('customers.edit', $encodedId) . '" class="btn btn-sm btn-outline-primary me-1" title="Edit"><i class="bx bx-edit"></i> Edit</a>';
                    }

                    // Delete action
                    if (auth()->user()->can('delete customer')) {
                        $actions .= '<button class="btn btn-sm btn-outline-danger delete-btn" data-id="' . $encodedId . '" data-name="' . e($customer->name) . '" title="Delete"><i class="bx bx-trash"></i> Delete</button>';
                    }

                    return '<div class="text-center">' . $actions . '</div>';
                })
                ->rawColumns(['avatar_name', 'actions'])
                ->make(true);
        }

        return response()->json(['error' => 'Invalid request'], 400);
    }



    /////////DISPLAY ALL CUSTOMER WITH PENALTY AMOUNT  FOR THEIR LAON ///////
    public function penaltList()
    {
        $customerPenalties = LoanPenaltyService::getCustomerPenaltyBalances();
        $penaltyBalance = LoanPenaltyService::getTotalPenaltyBalance();
        return view('customers.penalty', compact('customerPenalties', 'penaltyBalance'));
    }

    // Show form to create a new customer
    public function create()
    {
        $branchId = auth()->user()->branch_id;
        $loanOfficers = User::where('branch_id', $branchId)->get();
        $filetypes = Filetype::orderBy('name')->get();
        $collateralTypes = CashCollateralType::where('is_active', 1)->get(); // active types only
        $branches = Branch::all();
        $companies = Company::all();
        $registrars = User::all();
        $regions = Region::all();
        $groups = \App\Models\Group::where('branch_id', $branchId)->where('id', '!=', 1)->get();

        $customer = null;
        return view('customers.create', compact('branches', 'companies', 'registrars', 'regions', 'loanOfficers', 'collateralTypes', 'filetypes', 'groups', 'customer'));
    }

    // Store a new customer
    public function store(Request $request)
    {
        // Basic validation rules
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'phone1' => [
                'required',
                'string',
                'max:20',
                'unique:customers,phone1',
                function ($attribute, $value, $fail) {
                    // Format phone number first to check the prefix
                    $formatted = $this->formatPhoneNumber($value);
                    if (!str_starts_with($formatted, '255')) {
                        $fail('The phone number must start with prefix 255.');
                    }
                },
            ],
            'phone2' => 'nullable|string|max:20',
            'dob' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    $dob = \Carbon\Carbon::parse($value);
                    $age = $dob->age;
                    if ($age < 18) {
                        $fail('The customer must be at least 18 years old.');
                    }
                },
            ],
            'sex' => 'required|in:M,F',
            'region_id' => 'required|exists:regions,id',
            'district_id' => 'required|exists:districts,id',
            'work' => 'nullable|string|max:255',
            'workAddress' => 'nullable|string|max:500',
            'idType' => 'nullable|string|max:100',
            'idNumber' => [
                'nullable',
                'string',
                'max:100',
                function ($attribute, $value, $fail) {
                    if (!empty($value)) {
                        // Remove formatting for uniqueness check
                        $idNumber = preg_replace('/[^0-9A-Za-z]/', '', $value);
                        $exists = Customer::whereRaw('REPLACE(REPLACE(idNumber, "-", ""), " ", "") = ?', [$idNumber])
                            ->whereNotNull('idNumber')
                            ->where('idNumber', '!=', '')
                            ->exists();
                        
                        if ($exists) {
                            $fail('This ID number is already registered to another customer.');
                        }
                    }
                },
            ],
            'relation' => 'nullable|string|max:255',
            'category' => 'required|in:Guarantor,Borrower',
            'group_id' => 'nullable|exists:groups,id',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'loan_officer_ids' => 'nullable|array',
            'loan_officer_ids.*' => 'exists:users,id',
            'has_cash_collateral' => 'nullable|boolean',
            'collateral_type_id' => 'nullable|exists:cash_collateral_types,id',

            // Dynamic filetypes + documents
            'filetypes' => 'nullable|array',
            'filetypes.*' => 'exists:filetypes,id',
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ];

        $validated = $request->validate($rules);

        // Prepare customer data
        $data = $request->except(['customerNo', 'loan_officer_ids', 'collateral_type_id', 'filetypes', 'documents', 'group_id']);
        // Format phone numbers
        $data["phone1"] = $this->formatPhoneNumber($data["phone1"]);
        if (!empty($data["phone2"])) {
            $data["phone2"] = $this->formatPhoneNumber($data["phone2"]);
        }
        $data['category'] = $request->category;
        $password = 12345;
        $date = now()->toDateString();

        $data['customerNo'] = 100000 + (\App\Models\Customer::max('id') ?? 0) + 1;
        $data['password'] = Hash::make($password);
        $data['branch_id'] = auth()->user()->branch_id;
        $data['company_id'] = auth()->user()->company_id;
        $data['registrar'] = auth()->id();
        $data['dateRegistered'] = $date;
        $data['has_cash_collateral'] = $request->has('has_cash_collateral');

        // Upload photo
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('photos', 'public');
        }

        // Upload document
        if ($request->hasFile('document')) {
            $data['document'] = $request->file('document')->store('documents', 'public');
        }

        DB::beginTransaction();
        try {
            $customer = \App\Models\Customer::create($data);

            // Save group membership - check if customer is already in a group first
            $existingMembership = DB::table('group_members')->where('customer_id', $customer->id)->first();

            if (!$existingMembership) {
                if ($request->filled('group_id')) {
                    DB::table('group_members')->insert([
                        'group_id' => $request->group_id,
                        'customer_id' => $customer->id,
                        'status' => 'active',
                        'joined_date' => now()->toDateString(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    DB::table('group_members')->insert([
                        'group_id' => 1,
                        'customer_id' => $customer->id,
                        'status' => 'active',
                        'joined_date' => now()->toDateString(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Attach loan officers
            if ($request->has('loan_officer_ids')) {
                foreach ($request->loan_officer_ids as $officerId) {
                    DB::table('customer_officer')->insert([
                        'customer_id' => $customer->id,
                        'officer_id' => $officerId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Add cash collateral
            if ($request->has('has_cash_collateral') && $request->input('collateral_type_id')) {
                \App\Models\CashCollateral::create([
                    'customer_id' => $customer->id,
                    'type_id' => $request->input('collateral_type_id'),
                    'amount' => 0,
                    'branch_id' => auth()->user()->branch_id,
                    'company_id' => auth()->user()->company_id,
                ]);
            }

            // Save uploaded filetypes + documents
            if ($request->has('filetypes') && $request->hasFile('documents')) {
                $filetypes = $request->input('filetypes');
                $documents = $request->file('documents');

                foreach ($filetypes as $index => $filetypeId) {
                    if (isset($documents[$index])) {
                        $file = $documents[$index];
                        $path = $file->store('documents', 'public');

                        DB::table('customer_file_types')->insert([
                            'customer_id' => $customer->id,
                            'filetype_id' => $filetypeId,
                            'document_path' => $path,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create customer: ' . $e->getMessage());
        }
    }


    // Display one customer
    public function show($encodedId)
    {
        $id = Hashids::decode($encodedId)[0] ?? null;

        if (!$id) {
            abort(404);
        }

        $customer = Customer::with('collaterals.type', 'loans', 'loanOfficers', 'filetypes')->findOrFail($id);

        return view('customers.show', compact('customer'));
    }

    // Show form to edit a customer
    public function edit($encodedId)
    {
        $id = \Vinkla\Hashids\Facades\Hashids::decode($encodedId)[0] ?? null;
        if (!$id) {
            abort(404);
        }
        $customer = Customer::findOrFail($id);
        $branchId = auth()->user()->branch_id;
        $loanOfficers = User::where('branch_id', $branchId)->get();
        $collateralTypes = \App\Models\CashCollateralType::where('is_active', 1)->get();
        $branches = \App\Models\Branch::all();
        $companies = \App\Models\Company::all();
        $registrars = \App\Models\User::all();
        $regions = \App\Models\Region::all();
        $filetypes = \App\Models\Filetype::orderBy('name')->get();
        $groups = \App\Models\Group::where('branch_id', $branchId)->get();
        $customer->load('loanOfficers', 'filetypes');
        return view('customers.edit', compact('branches', 'companies', 'registrars', 'regions', 'loanOfficers', 'collateralTypes', 'customer', 'filetypes', 'groups'));
    }

    // Update customer data
    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000', // Added description validation
            'phone1' => [
                'required',
                'string',
                'max:20',
                'unique:customers,phone1,' . $customer->id,
                function ($attribute, $value, $fail) {
                    // Format phone number first to check the prefix
                    $formatted = $this->formatPhoneNumber($value);
                    if (!str_starts_with($formatted, '255')) {
                        $fail('The phone number must start with prefix 255.');
                    }
                },
            ],
            'phone2' => 'nullable|string|max:20',
            'dob' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    $dob = \Carbon\Carbon::parse($value);
                    $age = $dob->age;
                    if ($age < 18) {
                        $fail('The customer must be at least 18 years old.');
                    }
                },
            ],
            'sex' => 'required|in:M,F',
            'region_id' => 'required|exists:regions,id',
            'district_id' => 'required|exists:districts,id',
            'work' => 'nullable|string|max:255',
            'workAddress' => 'nullable|string|max:500',
            'idType' => 'nullable|string|max:100',
            'idNumber' => [
                'nullable',
                'string',
                'max:100',
                function ($attribute, $value, $fail) use ($customer) {
                    if (!empty($value)) {
                        // Remove formatting for uniqueness check
                        $idNumber = preg_replace('/[^0-9A-Za-z]/', '', $value);
                        $exists = Customer::whereRaw('REPLACE(REPLACE(idNumber, "-", ""), " ", "") = ?', [$idNumber])
                            ->where('id', '!=', $customer->id)
                            ->whereNotNull('idNumber')
                            ->where('idNumber', '!=', '')
                            ->exists();
                        
                        if ($exists) {
                            $fail('This ID number is already registered to another customer.');
                        }
                    }
                },
            ],
            'relation' => 'nullable|string|max:255',
            'category' => 'required|in:Guarantor,Borrower',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'password' => 'nullable|min:6',
            'loan_officer_ids' => 'nullable|array',
            'loan_officer_ids.*' => 'exists:users,id',
            'has_cash_collateral' => 'nullable|boolean',
            'collateral_type_id' => 'nullable|exists:cash_collateral_types,id', // Made optional

            'filetypes' => 'nullable|array',
            'filetypes.*' => 'exists:filetypes,id',
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);

        $data = $request->except(['customerNo', 'loan_officer_ids', 'collateral_type_id']);
        // Format phone numbers
        $data["phone1"] = $this->formatPhoneNumber($data["phone1"]);
        if (!empty($data["phone2"])) {
            $data["phone2"] = $this->formatPhoneNumber($data["phone2"]);
        }
        $data['category'] = $request->category;

        // Set these from logged-in user
        $data['branch_id'] = auth()->user()->branch_id;
        $data['company_id'] = auth()->user()->company_id;
        $data['registrar'] = auth()->id();
        $data['has_cash_collateral'] = $request->has('has_cash_collateral') ? true : false; // Set boolean value

        // Hash password only if provided
        if (!empty($request->password)) {
            $data['password'] = Hash::make($request->password);
        } else {
            unset($data['password']); // Don't overwrite with null
        }

        // Photo upload
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('photos', 'public');
        }

        // Document upload
        if ($request->hasFile('document')) {
            $data['document'] = $request->file('document')->store('documents', 'public');
        }

        DB::beginTransaction();
        try {
            $customer->update($data);

            // Sync group membership
            DB::table('group_members')->where('customer_id', $customer->id)->delete();
            // Save group membership
            if ($request->filled('group_id')) {
                DB::table('group_members')->insert([
                    'group_id' => $request->group_id,
                    'customer_id' => $customer->id,
                    'status' => 'active',
                    'joined_date' => now()->toDateString(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('group_members')->insert([
                    'group_id' => 1,
                    'customer_id' => $customer->id,
                    'status' => 'active',
                    'joined_date' => now()->toDateString(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Sync loan officers
            if ($request->has('loan_officer_ids')) {
                // Delete previous ones
                DB::table('customer_officer')->where('customer_id', $customer->id)->delete();

                // Insert new ones
                if (!empty($request->loan_officer_ids)) {
                    foreach ($request->loan_officer_ids as $officerId) {
                        DB::table('customer_officer')->insert([
                            'customer_id' => $customer->id,
                            'officer_id' => $officerId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            } else {
                // If none selected, remove all previous
                DB::table('customer_officer')->where('customer_id', $customer->id)->delete();
            }

            // Handle cash collateral
            if ($request->has('has_cash_collateral') && $request->has('collateral_type_id') && $request->collateral_type_id) {
                // Check if collateral already exists
                $existingCollateral = \App\Models\CashCollateral::where('customer_id', $customer->id)->first();

                if ($existingCollateral) {
                    $existingCollateral->update([
                        'type_id' => $request->input('collateral_type_id'),
                    ]);
                } else {
                    \App\Models\CashCollateral::create([
                        'customer_id' => $customer->id,
                        'type_id' => $request->input('collateral_type_id'),
                        'amount' => 0,
                        'branch_id' => auth()->user()->branch_id,
                        'company_id' => auth()->user()->company_id,
                    ]);
                }
            } else {
                // If not checked, remove existing collateral
                \App\Models\CashCollateral::where('customer_id', $customer->id)->delete();
            }

            // Sync File Types and Uploaded Documents
            if ($request->has('filetypes') && $request->hasFile('documents')) {
                $filetypes = $request->filetypes;
                $documents = $request->file('documents');

                // Delete existing filetype entries to prevent duplicates
                DB::table('customer_file_types')->where('customer_id', $customer->id)->delete();

                foreach ($filetypes as $index => $filetypeId) {
                    if (isset($documents[$index])) {
                        $file = $documents[$index];
                        $path = $file->store('documents', 'public');

                        DB::table('customer_file_types')->insert([
                            'customer_id' => $customer->id,
                            'filetype_id' => $filetypeId,
                            'document_path' => $path,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update customer: ' . $e->getMessage());
        }
    }

    // Delete customer
    public function destroy($id)
    {
        $decoded = Hashids::decode($id)[0] ?? null;
        try {
            $customer = Customer::findOrFail($decoded);

            // Check for existing loans, cash collaterals, or GL transactions

            //check if member is in any group then he need to delete that mmeber from that group
            $existingMembership = DB::table('group_members')->where('customer_id', $customer->id)->first();
            if ($existingMembership) {
                return redirect()->route('customers.index')->with('error', 'Customer is a member of a group. Please remove them from the group first.');
            }
            $hasLoans = $customer->loans()->exists();
            $hasCollaterals = $customer->collaterals()->exists();
            $hasGLTransactions = \DB::table('gl_transactions')->where('customer_id', $customer->id)->exists();

            if ($hasLoans || $hasCollaterals || $hasGLTransactions) {
                $msg = 'Cannot delete customer: ';
                if ($hasLoans) {
                    $msg .= 'Customer has existing loans. ';
                }
                if ($hasCollaterals) {
                    $msg .= 'Customer has cash collaterals. ';
                }
                if ($hasGLTransactions) {
                    $msg .= 'Customer has transactions.';
                }
                return redirect()->route('customers.index')->with('error', $msg);
            }

            $customer->delete();

            return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete customer: ' . $e->getMessage());
        }
    }

    // Show bulk upload form
    public function bulkUpload()
    {
        $collateralTypes = CashCollateralType::where('is_active', 1)->get();
        return view('customers.bulk-upload', compact('collateralTypes'));
    }

    // Download Excel template
    public function downloadExcelTemplate()
    {
        return Excel::download(new CustomerImportTemplateExport(), 'customer_import_template.xlsx');
    }

    // Process bulk upload
    public function bulkUploadStore(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:xlsx,xls|max:10240', // 10MB max, Excel only
            'has_cash_collateral' => 'nullable|boolean',
            'collateral_type_id' => 'nullable|exists:cash_collateral_types,id',
        ]);

        if ($request->has('has_cash_collateral') && !$request->collateral_type_id) {
            return back()->withErrors(['collateral_type_id' => 'Please select a collateral type when applying cash collateral.']);
        }

        try {
            $file = $request->file('csv_file');
            $path = $file->getRealPath();
            $extension = strtolower($file->getClientOriginalExtension());

            // Read Excel file
            if (!in_array($extension, ['xlsx', 'xls'])) {
                return back()->withErrors(['csv_file' => 'Only Excel files (.xlsx or .xls) are allowed.']);
            }

            $spreadsheet = IOFactory::load($path);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, false);
            
            // Filter out completely empty rows
            $data = array_filter($rows, function($row) {
                return !empty(array_filter($row, function($cell) {
                    return trim((string)$cell) !== '';
                }));
            });
            $data = array_values($data); // Re-index array

            if (empty($data)) {
                return back()->withErrors(['csv_file' => 'The file is empty or contains no valid data.']);
            }

            $header = array_shift($data); // Remove header row
            
            // Clean header - remove null values and trim
            $header = array_map(function($h) {
                return trim((string)($h ?? ''));
            }, $header);
            
            // Log original headers for debugging
            Log::info('Original headers from file', ['headers' => $header]);
            
            // Normalize headers - map Excel descriptive headers to simple names
            $normalizedHeader = [];
            foreach ($header as $index => $h) {
                if (empty($h)) {
                    $normalizedHeader[] = 'column_' . ($index + 1);
                    continue;
                }
                
                $hLower = strtolower(trim((string)$h));
                
                // Remove parentheses and extra text, then map
                $hClean = preg_replace('/\(.*?\)/', '', $hLower); // Remove content in parentheses
                $hClean = trim($hClean);
                
                // Map headers intelligently (check most specific/matching patterns first)
                // Check for Date of Birth / DOB
                if (stripos($hLower, 'date of birth') !== false || stripos($hLower, 'dob') !== false || stripos($hClean, 'date of birth') !== false) {
                    $normalizedHeader[] = 'dob';
                } 
                // Check for Sex / Gender
                elseif (stripos($hLower, 'sex') !== false || stripos($hLower, 'gender') !== false || stripos($hClean, 'sex') !== false) {
                    $normalizedHeader[] = 'sex';
                } 
                // Check for Phone2
                elseif (stripos($hLower, 'phone2') !== false || (stripos($hLower, 'phone') !== false && (stripos($hLower, 'alternative') !== false || stripos($hLower, '2') !== false || stripos($hLower, 'phone2') !== false))) {
                    $normalizedHeader[] = 'phone2';
                } 
                // Check for Phone1 or just Phone
                elseif (stripos($hLower, 'phone1') !== false || (stripos($hLower, 'phone') !== false && stripos($hLower, 'phone2') === false)) {
                    $normalizedHeader[] = 'phone1';
                } 
                // Check for Name
                elseif (stripos($hLower, 'name') !== false && stripos($hLower, 'customer') === false) {
                    $normalizedHeader[] = 'name';
                } 
                // Check for Region (not region_id)
                elseif ((stripos($hLower, 'region') !== false || stripos($hClean, 'region') !== false) && stripos($hLower, 'region_id') === false && stripos($hLower, 'region id') === false) {
                    $normalizedHeader[] = 'region';
                } 
                // Check for District (not district_id)
                elseif ((stripos($hLower, 'district') !== false || stripos($hClean, 'district') !== false) && stripos($hLower, 'district_id') === false && stripos($hLower, 'district id') === false) {
                    $normalizedHeader[] = 'district';
                } 
                // Check for region_id
                elseif (stripos($hLower, 'region_id') !== false || stripos($hLower, 'region id') !== false) {
                    $normalizedHeader[] = 'region_id';
                } 
                // Check for district_id
                elseif (stripos($hLower, 'district_id') !== false || stripos($hLower, 'district id') !== false) {
                    $normalizedHeader[] = 'district_id';
                } 
                // Check for Work Address
                elseif (stripos($hLower, 'work address') !== false || stripos($hLower, 'workaddress') !== false) {
                    $normalizedHeader[] = 'workaddress';
                } 
                // Check for Work
                elseif (stripos($hLower, 'work') !== false && stripos($hLower, 'address') === false) {
                    $normalizedHeader[] = 'work';
                } 
                // Check for ID Type
                elseif (stripos($hLower, 'id type') !== false || stripos($hLower, 'idtype') !== false) {
                    $normalizedHeader[] = 'idtype';
                } 
                // Check for ID Number
                elseif (stripos($hLower, 'id number') !== false || stripos($hLower, 'idnumber') !== false) {
                    $normalizedHeader[] = 'idnumber';
                } 
                // Check for Relation
                elseif (stripos($hLower, 'relation') !== false) {
                    $normalizedHeader[] = 'relation';
                } 
                // Check for Description
                elseif (stripos($hLower, 'description') !== false) {
                    $normalizedHeader[] = 'description';
                } 
                else {
                    // Fallback: use cleaned version
                    $cleaned = preg_replace('/[^a-z0-9_]/', '', $hLower);
                    $normalizedHeader[] = !empty($cleaned) ? $cleaned : 'column_' . ($index + 1);
                }
            }
            
            // Log normalized headers for debugging
            Log::info('Normalized headers', ['headers' => $normalizedHeader]);
            
            $header = $normalizedHeader;

            // Validate CSV structure
            $requiredColumns = ['name', 'phone1', 'dob', 'sex'];
            $missingColumns = array_diff($requiredColumns, $header);

            if (!empty($missingColumns)) {
                $foundHeaders = implode(', ', array_filter($header, function($h) {
                    return !empty($h) && !str_starts_with($h, 'column_');
                }));
                $missingHeadersList = implode(', ', $missingColumns);
                return back()->withErrors([
                    'csv_file' => "Missing required columns: {$missingHeadersList}. Found columns: " . ($foundHeaders ?: 'none') . ". Please use the Excel template downloaded from this page or ensure your file has columns: name, phone1, dob, sex"
                ]);
            }

            // Normalize row data - handle both CSV and Excel formats
            $processedRows = [];
            $actualRowNumber = 2; // Start from row 2 (after header row)
            
            foreach ($data as $rowIndex => $row) {
                $actualRowNumber++; // Increment actual row number in file
                
                // Ensure row has same length as header
                while (count($row) < count($header)) {
                    $row[] = '';
                }
                
                // Convert row to array and trim all values
                $row = array_map(function($cell) {
                    return trim((string)($cell ?? ''));
                }, array_slice($row, 0, count($header)));
                
                // Skip completely empty rows (no data at all)
                $hasData = false;
                foreach ($row as $cell) {
                    if (!empty($cell)) {
                        $hasData = true;
                        break;
                    }
                }
                if (!$hasData) {
                    continue;
                }
                
                $rowData = array_combine($header, $row);
                
                // Skip rows that are clearly sample data or instruction rows
                $firstCell = strtolower(trim($rowData['name'] ?? ''));
                $allCells = strtolower(implode(' ', array_filter($row, function($cell) {
                    return !empty($cell);
                })));
                
                if (stripos($firstCell, 'instruction') !== false || 
                    stripos($firstCell, 'sample') !== false ||
                    stripos($firstCell, 'n.b') !== false ||
                    stripos($firstCell, 'note') !== false ||
                    stripos($allCells, 'instruction') !== false ||
                    $firstCell === 'john doe' || 
                    $firstCell === 'jane smith' ||
                    stripos($firstCell, 'delete') !== false) {
                    continue;
                }
                
                // Skip rows that are missing all required fields (completely empty row)
                if (empty($rowData['name']) && empty($rowData['phone1']) && empty($rowData['dob']) && empty($rowData['sex'])) {
                    continue;
                }
                
                // Store the actual row number for error reporting
                $rowData['_row_number'] = $actualRowNumber;
                
                // Map region/district columns (support both 'region'/'district' and 'region_id'/'district_id')
                if (isset($rowData['region']) && !empty($rowData['region']) && empty($rowData['region_id'])) {
                    // Region is provided as name
                } elseif (isset($rowData['region_id']) && is_numeric($rowData['region_id'])) {
                    // Region is provided as ID, convert to name if needed
                    $region = Region::find($rowData['region_id']);
                    if ($region) {
                        $rowData['region'] = $region->name;
                    }
                }
                
                if (isset($rowData['district']) && !empty($rowData['district']) && empty($rowData['district_id'])) {
                    // District is provided as name
                } elseif (isset($rowData['district_id']) && is_numeric($rowData['district_id'])) {
                    // District is provided as ID, convert to name if needed
                    $district = District::find($rowData['district_id']);
                    if ($district) {
                        $rowData['district'] = $district->name;
                    }
                }
                
                $processedRows[] = $rowData;
            }

            // Determine if we should use queue job (for large files > 100 rows)
            $rowCount = count($processedRows);
            $useQueue = $rowCount > 100;

            if ($useQueue) {
                // Dispatch to queue job
                BulkCustomerImportJob::dispatch(
                    $processedRows,
                    [
                        'has_cash_collateral' => $request->has('has_cash_collateral'),
                        'collateral_type_id' => $request->input('collateral_type_id')
                    ],
                    auth()->id(),
                    auth()->user()->branch_id,
                    auth()->user()->company_id
                )->onQueue('default');

                return redirect()->route('customers.index')
                    ->with('success', "Import job queued successfully. {$rowCount} customers will be processed in the background. You will be notified when complete.");
            }

            // Process synchronously for smaller files
            $successCount = 0;
            $errorCount = 0;
            $errors = [];
            $failedRecords = []; // Store failed records with original data and error reasons

            DB::beginTransaction();

            foreach ($processedRows as $rowIndex => $rowData) {
                try {
                    // Get actual row number from file (if stored)
                    $actualRowNumber = $rowData['_row_number'] ?? ($rowIndex + 2);
                    $originalRowData = $rowData; // Keep original for failed records
                    unset($rowData['_row_number']); // Remove from row data

                    // Validate required fields
                    if (
                        empty($rowData['name']) || empty($rowData['phone1']) || empty($rowData['dob']) ||
                        empty($rowData['sex'])
                    ) {
                        $errorMsg = "Missing required fields (name, phone1, dob, or sex is empty)";
                        $errors[] = "Row {$actualRowNumber}: {$errorMsg}";
                        $failedRecords[] = [
                            'row_number' => $actualRowNumber,
                            'data' => $originalRowData,
                            'error' => $errorMsg
                        ];
                        $errorCount++;
                        continue;
                    }

                    // Validate sex
                    $sex = strtoupper(trim($rowData['sex'] ?? ''));
                    if (!in_array($sex, ['M', 'F'])) {
                        $errorMsg = "Sex must be M or F (got: " . ($rowData['sex'] ?? 'empty') . ")";
                        $errors[] = "Row {$actualRowNumber}: {$errorMsg}";
                        $failedRecords[] = [
                            'row_number' => $actualRowNumber,
                            'data' => $originalRowData,
                            'error' => $errorMsg
                        ];
                        $errorCount++;
                        continue;
                    }

                    // Format phone number for validation
                    $formattedPhone1 = $this->formatPhoneNumber(trim($rowData["phone1"] ?? ''));
                    
                    // Validate phone number prefix
                    if (!str_starts_with($formattedPhone1, '255')) {
                        $errorMsg = "Phone number must start with prefix 255";
                        $errors[] = "Row {$actualRowNumber}: {$errorMsg}";
                        $failedRecords[] = [
                            'row_number' => $actualRowNumber,
                            'data' => $originalRowData,
                            'error' => $errorMsg
                        ];
                        $errorCount++;
                        continue;
                    }

                    // Validate phone number uniqueness
                    $existingCustomer = Customer::where('phone1', $formattedPhone1)->first();
                    if ($existingCustomer) {
                        $errorMsg = "Phone number already exists";
                        $errors[] = "Row {$actualRowNumber}: {$errorMsg}";
                        $failedRecords[] = [
                            'row_number' => $actualRowNumber,
                            'data' => $originalRowData,
                            'error' => $errorMsg
                        ];
                        $errorCount++;
                        continue;
                    }

                    // Validate ID number uniqueness if provided
                    if (!empty($rowData['idnumber'])) {
                        $idNumber = preg_replace('/[^0-9A-Za-z]/', '', trim($rowData['idnumber']));
                        if (!empty($idNumber)) {
                            $existingId = Customer::whereRaw('REPLACE(REPLACE(idNumber, "-", ""), " ", "") = ?', [$idNumber])
                                ->whereNotNull('idNumber')
                                ->where('idNumber', '!=', '')
                                ->first();
                            
                            if ($existingId) {
                                $errorMsg = "ID number already exists";
                                $errors[] = "Row {$actualRowNumber}: {$errorMsg}";
                                $failedRecords[] = [
                                    'row_number' => $actualRowNumber,
                                    'data' => $originalRowData,
                                    'error' => $errorMsg
                                ];
                                $errorCount++;
                                continue;
                            }
                        }
                    }

                    // Validate age (must be at least 18 years old)
                    try {
                        $dob = \Carbon\Carbon::parse($rowData['dob']);
                        $age = $dob->age;
                        if ($age < 18) {
                            $errorMsg = "Customer must be at least 18 years old (DOB: " . $rowData['dob'] . ", Age: {$age})";
                            $errors[] = "Row {$actualRowNumber}: {$errorMsg}";
                            $failedRecords[] = [
                                'row_number' => $actualRowNumber,
                                'data' => $originalRowData,
                                'error' => $errorMsg
                            ];
                            $errorCount++;
                            continue;
                        }
                    } catch (\Exception $e) {
                        $errorMsg = "Invalid date of birth format: " . ($rowData['dob'] ?? 'empty');
                        $errors[] = "Row {$actualRowNumber}: {$errorMsg}";
                        $failedRecords[] = [
                            'row_number' => $actualRowNumber,
                            'data' => $originalRowData,
                            'error' => $errorMsg
                        ];
                        $errorCount++;
                        continue;
                    }

                    // Validate region and district
                    $regionId = null;
                    $districtId = null;
                    
                    if (!empty($rowData['region'])) {
                        $region = Region::where('name', trim($rowData['region']))->first();
                        if (!$region) {
                            $errorMsg = "Invalid region: {$rowData['region']}. Region must exist in the system (from seeders).";
                            $errors[] = "Row {$actualRowNumber}: {$errorMsg}";
                            $failedRecords[] = [
                                'row_number' => $actualRowNumber,
                                'data' => $originalRowData,
                                'error' => $errorMsg
                            ];
                            $errorCount++;
                            continue;
                        }
                        $regionId = $region->id;

                        if (!empty($rowData['district'])) {
                            $district = District::where('name', trim($rowData['district']))
                                ->where('region_id', $regionId)
                                ->first();
                            if (!$district) {
                                $errorMsg = "Invalid district: {$rowData['district']} for region {$rowData['region']}. District must exist in the system (from seeders).";
                                $errors[] = "Row {$actualRowNumber}: {$errorMsg}";
                                $failedRecords[] = [
                                    'row_number' => $actualRowNumber,
                                    'data' => $originalRowData,
                                    'error' => $errorMsg
                                ];
                                $errorCount++;
                                continue;
                            }
                            $districtId = $district->id;
                        }
                    } elseif (!empty($rowData['region_id']) && is_numeric($rowData['region_id'])) {
                        $regionId = $rowData['region_id'];
                        $region = Region::find($regionId);
                        if (!$region) {
                            $errorMsg = "Invalid region_id: {$rowData['region_id']}";
                            $errors[] = "Row {$actualRowNumber}: {$errorMsg}";
                            $failedRecords[] = [
                                'row_number' => $actualRowNumber,
                                'data' => $originalRowData,
                                'error' => $errorMsg
                            ];
                            $errorCount++;
                            continue;
                        }
                        
                        if (!empty($rowData['district_id']) && is_numeric($rowData['district_id'])) {
                            $districtId = $rowData['district_id'];
                            $district = District::where('id', $districtId)
                                ->where('region_id', $regionId)
                                ->first();
                            if (!$district) {
                                $errorMsg = "Invalid district_id: {$rowData['district_id']} for region_id {$regionId}";
                                $errors[] = "Row {$actualRowNumber}: {$errorMsg}";
                                $failedRecords[] = [
                                    'row_number' => $actualRowNumber,
                                    'data' => $originalRowData,
                                    'error' => $errorMsg
                                ];
                                $errorCount++;
                                continue;
                            }
                        }
                    }

                    // Create customer data
                    $customerData = [
                        // Format phone numbers
                        "phone1" => $formattedPhone1,
                        "phone2" => !empty($rowData["phone2"]) ? $this->formatPhoneNumber(trim($rowData["phone2"])) : null,
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
                        'branch_id' => auth()->user()->branch_id,
                        'company_id' => auth()->user()->company_id,
                        'registrar' => auth()->id(),
                        'dateRegistered' => now()->toDateString(),
                        'has_cash_collateral' => $request->has('has_cash_collateral'),
                        'category' => 'Borrower', // Always assign Borrower in bulk upload
                    ];

                    $customer = Customer::create($customerData);

                    // Add cash collateral if selected
                    if ($request->has('has_cash_collateral') && $request->collateral_type_id) {
                        \App\Models\CashCollateral::create([
                            'customer_id' => $customer->id,
                            'type_id' => $request->collateral_type_id,
                            'amount' => 0,
                            'branch_id' => auth()->user()->branch_id,
                            'company_id' => auth()->user()->company_id,
                        ]);
                    }
                    //assign all member to the individual group - check if customer is already in a group first
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

                    $successCount++;
                } catch (\Exception $e) {
                    $actualRowNumber = $originalRowData['_row_number'] ?? ($rowIndex + 2);
                    $errorMsg = $e->getMessage();
                    $errors[] = "Row {$actualRowNumber}: {$errorMsg}";
                    $failedRecords[] = [
                        'row_number' => $actualRowNumber,
                        'data' => $originalRowData,
                        'error' => $errorMsg
                    ];
                    $errorCount++;
                }
            }

            if ($errorCount > 0) {
                DB::rollBack();
                // Store failed records in session for export
                session(['failed_customer_records' => $failedRecords]);
                return back()
                    ->withErrors(['csv_file' => 'Upload completed with errors. ' . $errorCount . ' rows failed.'])
                    ->with('upload_errors', $errors)
                    ->with('failed_count', $errorCount)
                    ->with('success_count', $successCount);
            }

            DB::commit();

            $message = "Successfully uploaded {$successCount} customers.";
            if ($request->has('has_cash_collateral')) {
                $message .= " Cash collateral applied to all customers.";
            }

            return redirect()->route('customers.index')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['csv_file' => 'Failed to process CSV file: ' . $e->getMessage()]);
        }
    }

    // Export failed customer records
    public function exportFailedRecords()
    {
        $failedRecords = session('failed_customer_records', []);
        
        if (empty($failedRecords)) {
            return back()->withErrors(['error' => 'No failed records to export.']);
        }

        $filename = 'failed_customer_records_' . date('Y-m-d_His') . '.xlsx';
        
        return Excel::download(new FailedCustomerRecordsExport($failedRecords), $filename);
    }

    // Download sample CSV
    public function downloadSample()
    {
        $filename = 'customer_bulk_upload_sample.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');

            // Add headers
            fputcsv($file, [
                'name',
                'phone1',
                'phone2',
                'dob',
                'sex',
                'work',
                'workaddress',
                'idtype',
                'idnumber',
                'relation',
                'description'
            ]);

            // Add sample data
            fputcsv($file, [
                'John Doe',
                '0712345678',
                '0755123456',
                '1990-01-15',
                'M',
                'Teacher',
                'ABC School, Dar es Salaam',
                'National ID',
                '123456789',
                'Spouse',
                'Sample customer'
            ]);

            fputcsv($file, [
                'Jane Smith',
                '0723456789',
                '',
                '1985-05-20',
                'F',
                'Nurse',
                'City Hospital',
                'License',
                '987654321',
                'Parent',
                'Another sample'
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Send SMS message to customer
     */
    public function sendMessage(Request $request, $customerId)
    {
        try {
            // Decode the customer ID
            $decodedId = Hashids::decode($customerId);
            if (empty($decodedId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid customer ID'
                ], 400);
            }

            $customer = Customer::findOrFail($decodedId[0]);

            // Validate request
            $request->validate([
                'phone_number' => 'required|string',
                'message_content' => 'required|string|max:500',
            ]);

            $phoneNumber = $request->phone_number;
            $message = $request->message_content;

            // Use phone number as provided since it's already in clean format
            // Remove any spaces, dashes, or special characters except +
            $phoneNumber = preg_replace('/[^0-9+]/', '', $phoneNumber);

            // Ensure phone number is not empty after cleaning
            if (empty($phoneNumber)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid phone number provided.'
                ], 400);
            }

            // Send SMS using SmsHelper
            $smsResponse = \App\Helpers\SmsHelper::send($phoneNumber, $message);

            // Log the SMS activity (optional)
            \DB::table('sms_logs')->insert([
                'customer_id' => $customer->id,
                'phone_number' => $phoneNumber,
                'message' => $message,
                'response' => $smsResponse,
                'sent_by' => auth()->id(),
                'sent_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Return a simple success message for SweetAlert or toast notification
            return response()->json([
                'success' => true,
                'message' => 'Successfully sent SMS to ' . $customer->name
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('SMS sending failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to send SMS: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload multiple documents for a customer
     */
    public function uploadDocuments(Request $request, $encodedCustomerId)
    {
        try {
            $decoded = Hashids::decode($encodedCustomerId);
            if (empty($decoded)) {
                return response()->json(['success' => false, 'message' => 'Invalid customer id'], 400);
            }

            $customer = Customer::findOrFail($decoded[0]);

            $request->validate([
                'filetypes' => 'required|array',
                'filetypes.*' => 'required|exists:filetypes,id',
                'documents' => 'required|array',
                'documents.*' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
            ]);

            $filetypes = $request->input('filetypes', []);
            $documents = $request->file('documents', []);

            DB::beginTransaction();
            $uploadedCount = 0;
            $uploadedDocuments = [];
            $errors = [];

            foreach ($filetypes as $index => $filetypeId) {
                if (!isset($documents[$index])) {
                    continue;
                }

                try {
                    $file = $documents[$index];
                    $path = $file->store('documents', 'public');

                    // Check if this filetype already exists for this customer
                    $existing = DB::table('customer_file_types')
                        ->where('customer_id', $customer->id)
                        ->where('filetype_id', $filetypeId)
                        ->first();

                    if ($existing) {
                        // If filetype already exists, use "Multiple Documents" filetype instead
                        $filetypeId = 8; // Multiple Documents filetype
                    }

                    // Create new record
                    DB::table('customer_file_types')->insert([
                        'customer_id' => $customer->id,
                        'filetype_id' => $filetypeId,
                        'document_path' => $path,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $uploadedDocuments[] = [
                        'name' => $file->getClientOriginalName(),
                        'type' => \App\Models\Filetype::find($filetypeId)->name ?? 'Unknown',
                        'size' => $this->formatFileSize($file->getSize())
                    ];

                    $uploadedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Failed to upload {$documents[$index]->getClientOriginalName()}: " . $e->getMessage();
                }
            }

            if ($uploadedCount === 0) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'No files were uploaded successfully',
                    'errors' => $errors
                ], 400);
            }

            DB::commit();

            $message = "Successfully uploaded {$uploadedCount} document(s)";
            if (!empty($errors)) {
                $message .= ". Some files failed: " . implode(', ', $errors);
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'uploaded_count' => $uploadedCount,
                'documents' => $uploadedDocuments,
                'errors' => $errors
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Document upload failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload documents: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Format file size in human readable format
     */
    private function formatFileSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Delete a single customer document (pivot row)
     */
    public function deleteDocument(Request $request, $encodedCustomerId, $pivotId)
    {
        try {
            $decoded = Hashids::decode($encodedCustomerId);
            if (empty($decoded)) {
                return response()->json(['success' => false, 'message' => 'Invalid customer id'], 400);
            }

            $customerId = $decoded[0];
            $pivot = DB::table('customer_file_types')->where('id', $pivotId)->where('customer_id', $customerId)->first();
            if (!$pivot) {
                return response()->json(['success' => false, 'message' => 'Document not found'], 404);
            }

            // Delete file from storage if exists
            if (!empty($pivot->document_path)) {
                try {
                    \Storage::disk('public')->delete($pivot->document_path);
                } catch (\Exception $e) {
                    // ignore storage deletion errors
                }
            }

            DB::table('customer_file_types')->where('id', $pivotId)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Document deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete document: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Stream a customer document for viewing in the browser
     */
    public function viewDocument($encodedCustomerId, $pivotId)
    {
        $decoded = \Vinkla\Hashids\Facades\Hashids::decode($encodedCustomerId);
        if (empty($decoded)) {
            abort(404);
        }

        $customerId = $decoded[0];
        $pivot = \DB::table('customer_file_types')
            ->where('id', $pivotId)
            ->where('customer_id', $customerId)
            ->first();

        if (!$pivot || empty($pivot->document_path)) {
            abort(404);
        }

        $disk = \Storage::disk('public');
        if (!$disk->exists($pivot->document_path)) {
            abort(404);
        }

        $mimeType = $disk->mimeType($pivot->document_path) ?: 'application/octet-stream';
        $contents = $disk->get($pivot->document_path);
        return response($contents, 200)->header('Content-Type', $mimeType);
    }

    /**
     * Download a customer document as attachment
     */
    public function downloadDocument($encodedCustomerId, $pivotId)
    {
        $decoded = \Vinkla\Hashids\Facades\Hashids::decode($encodedCustomerId);
        if (empty($decoded)) {
            abort(404);
        }

        $customerId = $decoded[0];
        $pivot = \DB::table('customer_file_types')
            ->where('id', $pivotId)
            ->where('customer_id', $customerId)
            ->first();

        if (!$pivot || empty($pivot->document_path)) {
            abort(404);
        }

        $disk = \Storage::disk('public');
        if (!$disk->exists($pivot->document_path)) {
            abort(404);
        }

        $filename = basename($pivot->document_path);
        return response()->streamDownload(function () use ($disk, $pivot) {
            echo $disk->get($pivot->document_path);
        }, $filename);
    }

    /**
     * Validate phone number uniqueness
     */
    public function validatePhone(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'customerId' => 'nullable|integer'
        ]);

        $phone = $this->formatPhoneNumber($request->phone);
        $query = Customer::where('phone1', $phone);

        if ($request->customerId) {
            $query->where('id', '!=', $request->customerId);
        }

        $exists = $query->exists();

        return response()->json(['exists' => $exists]);
    }

    /**
     * Validate ID number uniqueness
     */
    public function validateId(Request $request)
    {
        $request->validate([
            'idNumber' => 'required|string',
            'customerId' => 'nullable|integer'
        ]);

        // Remove formatting (dashes) for comparison
        $idNumber = preg_replace('/[^0-9A-Za-z]/', '', $request->idNumber);
        
        $query = Customer::whereRaw('REPLACE(REPLACE(idNumber, "-", ""), " ", "") = ?', [$idNumber])
            ->whereNotNull('idNumber')
            ->where('idNumber', '!=', '');

        if ($request->customerId) {
            $query->where('id', '!=', $request->customerId);
        }

        $exists = $query->exists();

        return response()->json(['exists' => $exists]);
    }

}
