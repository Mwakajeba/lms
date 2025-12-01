<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\Company;
use App\Models\Branch;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Vinkla\Hashids\Facades\Hashids;
use Yajra\DataTables\Facades\DataTables;

class SupplierController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $companyId = $user->company_id ?? null;

        // Get stats only for dashboard display
        if ($companyId) {
            $suppliers = Supplier::byCompany($companyId);
        } else {
            $suppliers = Supplier::query();
        }

        $stats = [
            'total' => $suppliers->count(),
            'active' => $suppliers->where('status', 'active')->count(),
            'inactive' => $suppliers->where('status', 'inactive')->count(),
            'blacklisted' => $suppliers->where('status', 'blacklisted')->count(),
        ];

        return view('accounting.suppliers.index', compact('stats'));
    }

    // Ajax endpoint for DataTables
    public function getSuppliersData(Request $request)
    {
        if ($request->ajax()) {
            $user = auth()->user();
            $companyId = $user->company_id ?? null;

            if ($companyId) {
                $suppliers = Supplier::with(['company', 'branch', 'createdBy'])
                    ->byCompany($companyId)
                    ->select('suppliers.*');
            } else {
                $suppliers = Supplier::with(['company', 'branch', 'createdBy'])
                    ->select('suppliers.*');
            }

            return DataTables::eloquent($suppliers)
                ->addColumn('supplier_name', function ($supplier) {
                    return '<div class="d-flex align-items-center">
                                <div class="avatar-sm bg-light-primary text-primary rounded-circle d-flex align-items-center justify-content-center me-3">
                                    <i class="bx bx-store font-size-18"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">' . e($supplier->name) . '</h6>
                                    ' . ($supplier->company_registration_name ? '<small class="text-muted">' . e($supplier->company_registration_name) . '</small>' : '') . '
                                </div>
                            </div>';
                })
                ->addColumn('contact_info', function ($supplier) {
                    $contact = '';
                    if ($supplier->email) {
                        $contact .= '<div><i class="bx bx-envelope me-1"></i>' . e($supplier->email) . '</div>';
                    }
                    if ($supplier->phone) {
                        $contact .= '<div><i class="bx bx-phone me-1"></i>' . e($supplier->phone) . '</div>';
                    }
                    return $contact ?: '<span class="text-muted">No contact info</span>';
                })
                ->addColumn('location', function ($supplier) {
                    return $supplier->address ? '<div><i class="bx bx-map me-1"></i>' . e($supplier->address) . '</div>' : '<span class="text-muted">No address</span>';
                })
                ->addColumn('business_details', function ($supplier) {
                    $details = '';
                    if ($supplier->tin_number) {
                        $details .= '<div><strong>TIN:</strong> ' . e($supplier->tin_number) . '</div>';
                    }
                    if ($supplier->vat_number) {
                        $details .= '<div><strong>VAT:</strong> ' . e($supplier->vat_number) . '</div>';
                    }
                    if ($supplier->products_or_services) {
                        $details .= '<div><strong>Services:</strong> ' . e(Str::limit($supplier->products_or_services, 50)) . '</div>';
                    }
                    return $details ?: '<span class="text-muted">No business details</span>';
                })
                ->addColumn('status_badge', function ($supplier) {
                    $statusColors = [
                        'active' => 'success',
                        'inactive' => 'warning',
                        'blacklisted' => 'danger'
                    ];
                    $color = $statusColors[$supplier->status] ?? 'secondary';
                    return '<span class="badge bg-' . $color . '">' . ucfirst($supplier->status) . '</span>';
                })
                ->addColumn('branch_name', function ($supplier) {
                    return optional($supplier->branch)->name ?? '<span class="text-muted">No branch</span>';
                })
                ->addColumn('actions', function ($supplier) {
                    $actions = '';
                    $encodedId = Hashids::encode($supplier->id);
                    
                    // View action
                    if (auth()->user()->can('view supplier details')) {
                        $actions .= '<a href="' . route('accounting.suppliers.show', $encodedId) . '" 
                                        class="btn btn-sm btn-outline-primary me-1" 
                                        data-bs-toggle="tooltip" 
                                        data-bs-placement="top" 
                                        title="View supplier details">
                                        <i class="bx bx-show"></i>
                                    </a>';
                    }
                    
                    // Edit action
                    if (auth()->user()->can('edit supplier')) {
                        $actions .= '<a href="' . route('accounting.suppliers.edit', $encodedId) . '" 
                                        class="btn btn-sm btn-outline-warning me-1" 
                                        data-bs-toggle="tooltip" 
                                        data-bs-placement="top" 
                                        title="Edit supplier">
                                        <i class="bx bx-edit"></i>
                                    </a>';
                    }
                    
                    // Delete action
                    if (auth()->user()->can('delete supplier')) {
                        $actions .= '<button type="button"
                                        class="btn btn-sm btn-outline-danger delete-supplier-btn"
                                        data-bs-toggle="tooltip" 
                                        data-bs-placement="top" 
                                        title="Delete supplier"
                                        data-supplier-id="' . $encodedId . '"
                                        data-supplier-name="' . e($supplier->name) . '">
                                        <i class="bx bx-trash"></i>
                                    </button>';
                    }
                    
                    return '<div class="text-center">' . $actions . '</div>';
                })
                ->rawColumns(['supplier_name', 'contact_info', 'location', 'business_details', 'status_badge', 'branch_name', 'actions'])
                ->make(true);
        }
        
        return response()->json(['error' => 'Invalid request'], 400);
    }

    public function create()
    {
        $user = auth()->user();
        $companyId = $user->company_id ?? null;

        $companies = Company::orderBy('name')->get();

        if ($companyId) {
            $branches = Branch::where('company_id', $companyId)->orderBy('name')->get();
        } else {
            $branches = Branch::orderBy('name')->get();
        }

        $regions = Region::orderBy('name')->get();
        $statusOptions = Supplier::getStatusOptions();

        return view('accounting.suppliers.create', compact('companies', 'branches', 'regions', 'statusOptions'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|size:12|regex:/^[0-9]+$/',
            'address' => 'nullable|string|max:500',
            'region' => 'nullable|string|max:100',
            'company_registration_name' => 'nullable|string|max:255',
            'tin_number' => 'nullable|string|max:50|regex:/^[0-9]+$/',
            'vat_number' => 'nullable|string|max:50|regex:/^[0-9]+$/',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:50',
            'account_name' => 'nullable|string|max:255',
            'products_or_services' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive,blacklisted',
            'branch_id' => 'nullable|exists:branches,id',
        ], [
            'phone.size' => 'Phone number must be exactly 12 digits.',
            'phone.regex' => 'Phone number must contain only numbers.',
            'tin_number.regex' => 'TIN number must contain only numbers.',
            'vat_number.regex' => 'VAT number must contain only numbers.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = auth()->user();
        $companyId = $user->company_id ?? Company::first()->id ?? 1;

        $supplier = Supplier::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'region' => $request->region,
            'company_registration_name' => $request->company_registration_name,
            'tin_number' => $request->tin_number,
            'vat_number' => $request->vat_number,
            'bank_name' => $request->bank_name,
            'bank_account_number' => $request->bank_account_number,
            'account_name' => $request->account_name,
            'products_or_services' => $request->products_or_services,
            'status' => $request->status,
            'company_id' => $companyId,
            'branch_id' => $request->branch_id,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('accounting.suppliers.index')
            ->with('success', 'Supplier created successfully!');
    }

    public function show($encodedId)
    {
        // Decode the ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('accounting.suppliers.index')->withErrors(['Supplier not found.']);
        }

        $supplier = Supplier::findOrFail($decoded[0]);
        $supplier->load(['company', 'branch', 'createdBy', 'updatedBy']);

        return view('accounting.suppliers.show', compact('supplier'));
    }

    public function edit($encodedId)
    {
        // Decode the ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('accounting.suppliers.index')->withErrors(['Supplier not found.']);
        }

        $supplier = Supplier::findOrFail($decoded[0]);

        $user = auth()->user();
        $companyId = $user->company_id ?? null;

        $companies = Company::orderBy('name')->get();

        if ($companyId) {
            $branches = Branch::where('company_id', $companyId)->orderBy('name')->get();
        } else {
            $branches = Branch::orderBy('name')->get();
        }

        $regions = Region::orderBy('name')->get();
        $statusOptions = Supplier::getStatusOptions();

        return view('accounting.suppliers.edit', compact('supplier', 'companies', 'branches', 'regions', 'statusOptions'));
    }

    public function update(Request $request, $encodedId)
    {
        // Decode supplier ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('accounting.suppliers.index')->withErrors(['Supplier not found.']);
        }

        $supplier = Supplier::findOrFail($decoded[0]);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|size:12|regex:/^[0-9]+$/',
            'address' => 'nullable|string|max:500',
            'region' => 'nullable|string|max:100',
            'company_registration_name' => 'nullable|string|max:255',
            'tin_number' => 'nullable|string|max:50|regex:/^[0-9]+$/',
            'vat_number' => 'nullable|string|max:50|regex:/^[0-9]+$/',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:50',
            'account_name' => 'nullable|string|max:255',
            'products_or_services' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive,blacklisted',
            'branch_id' => 'nullable|exists:branches,id',
        ], [
            'phone.size' => 'Phone number must be exactly 12 digits.',
            'phone.regex' => 'Phone number must contain only numbers.',
            'tin_number.regex' => 'TIN number must contain only numbers.',
            'vat_number.regex' => 'VAT number must contain only numbers.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $supplier->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'region' => $request->region,
            'company_registration_name' => $request->company_registration_name,
            'tin_number' => $request->tin_number,
            'vat_number' => $request->vat_number,
            'bank_name' => $request->bank_name,
            'bank_account_number' => $request->bank_account_number,
            'account_name' => $request->account_name,
            'products_or_services' => $request->products_or_services,
            'status' => $request->status,
            'branch_id' => $request->branch_id,
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('accounting.suppliers.index')
            ->with('success', 'Supplier updated successfully!');
    }

    public function destroy($encodedId)
    {
        // Decode the encoded ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('accounting.suppliers.index')->withErrors(['Supplier not found.']);
        }

        $supplier = Supplier::findOrFail($decoded[0]);
        $supplier->delete();

        return redirect()->route('accounting.suppliers.index')
            ->with('success', 'Supplier deleted successfully!');
    }

    public function changeStatus(Request $request, $encodedId)
    {
        // Decode the encoded ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('accounting.suppliers.index')->withErrors(['Supplier not found.']);
        }

        $supplier = Supplier::findOrFail($decoded[0]);

        $request->validate([
            'status' => 'required|in:active,inactive,blacklisted'
        ]);

        $supplier->update([
            'status' => $request->status,
            'updated_by' => auth()->id(),
        ]);

        return redirect()->back()
            ->with('success', 'Supplier status updated successfully!');
    }
}