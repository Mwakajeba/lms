<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Customer;
use App\Models\ChartAccount;
use App\Models\Receipt;
use App\Models\ReceiptItem;
use App\Models\GlTransaction;
use App\Traits\TransactionHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Vinkla\Hashids\Facades\Hashids;
use Yajra\DataTables\Facades\DataTables;

class ReceiptVoucherController extends Controller
{
    use TransactionHelper;

    /**
     * Debug method to test controller accessibility
     */
    public function debug()
    {
        return response()->json([
            'message' => 'ReceiptVoucherController is accessible',
            'user' => Auth::user()->name ?? 'No user',
            'timestamp' => now()
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        // Calculate stats only
        $receipts = Receipt::with(['bankAccount.chartAccount.accountClassGroup'])
            ->whereHas('bankAccount.chartAccount.accountClassGroup', function ($query) use ($user) {
                $query->where('company_id', $user->company_id);
            })
            ->when($user->branch_id, function ($query) use ($user) {
                return $query->where('branch_id', $user->branch_id);
            });

        $stats = [
            'total' => $receipts->count(),
            'this_month' => $receipts->where('date', '>=', now()->startOfMonth())->count(),
            'total_amount' => $receipts->sum('amount'),
            'this_month_amount' => $receipts->where('date', '>=', now()->startOfMonth())->sum('amount'),
        ];

        return view('accounting.receipt-vouchers.index', compact('stats'));
    }

    // Ajax endpoint for DataTables
    public function getReceiptVouchersData(Request $request)
    {
        $user = Auth::user();

        $receipts = Receipt::with(['bankAccount', 'user', 'customer', 'loan'])
            ->whereHas('bankAccount.chartAccount.accountClassGroup', function ($query) use ($user) {
                $query->where('company_id', $user->company_id);
            })
            ->when($user->branch_id, function ($query) use ($user) {
                return $query->where('branch_id', $user->branch_id);
            })
            ->select('receipts.*');

        return DataTables::eloquent($receipts)
            ->addColumn('formatted_date', function ($receipt) {
                return $receipt->date ? $receipt->date->format('M d, Y') : 'N/A';
            })
            ->addColumn('reference_link', function ($receipt) {
                return '<a href="' . route('accounting.receipt-vouchers.show', Hashids::encode($receipt->id)) . '" 
                            class="text-primary fw-bold">
                            ' . e($receipt->reference) . '
                        </a>';
            })
            ->addColumn('bank_account_name', function ($receipt) {
                return optional($receipt->bankAccount)->name ?? 'N/A';
            })
            ->addColumn('payee_info', function ($receipt) {
                if ($receipt->payee_type == 'customer' && $receipt->customer) {
                    return '<span class="badge bg-primary me-1">Customer</span>' . e($receipt->customer->name ?? 'N/A');
                } elseif ($receipt->payee_type == 'supplier' && $receipt->supplier) {
                    return '<span class="badge bg-success me-1">Supplier</span>' . e($receipt->supplier->name ?? 'N/A');
                } elseif ($receipt->payee_type == 'other') {
                    return '<span class="badge bg-warning me-1">Other</span>' . e($receipt->payee_name ?? 'N/A');
                } else {
                    return '<span class="text-muted">No payee</span>';
                }
            })
            ->addColumn('description_limited', function ($receipt) {
                return $receipt->description ? Str::limit($receipt->description, 50) : 'No description';
            })
            ->addColumn('formatted_amount', function ($receipt) {
                return '<span class="text-end fw-bold">' . number_format($receipt->amount, 2) . '</span>';
            })
            ->addColumn('user_name', function ($receipt) {
                return optional($receipt->user)->name ?? 'N/A';
            })
            ->addColumn('status_badge', function ($receipt) {
                return $receipt->status_badge;
            })
            ->addColumn('actions', function ($receipt) {
                $actions = '';
                
                // View action
                if (auth()->user()->can('view receipt voucher details')) {
                    $actions .= '<a href="' . route('accounting.receipt-vouchers.show', Hashids::encode($receipt->id)) . '" 
                                    class="btn btn-sm btn-outline-success me-1" 
                                    data-bs-toggle="tooltip" 
                                    data-bs-placement="top" 
                                    title="View receipt voucher">
                                    <i class="bx bx-show"></i>
                                </a>';
                }
                
                if ($receipt->reference_type === 'manual') {
                    // Edit action
                    if (auth()->user()->can('edit receipt voucher')) {
                        $actions .= '<a href="' . route('accounting.receipt-vouchers.edit', Hashids::encode($receipt->id)) . '" 
                                        class="btn btn-sm btn-outline-info me-1" 
                                        data-bs-toggle="tooltip" 
                                        data-bs-placement="top" 
                                        title="Edit receipt voucher">
                                        <i class="bx bx-edit"></i>
                                    </a>';
                    }
                    
                    // Delete action
                    if (auth()->user()->can('delete receipt voucher')) {
                        $actions .= '<button type="button" 
                                        class="btn btn-sm btn-outline-danger delete-receipt-btn"
                                        data-bs-toggle="tooltip" 
                                        data-bs-placement="top" 
                                        title="Delete receipt voucher"
                                        data-receipt-id="' . Hashids::encode($receipt->id) . '"
                                        data-receipt-reference="' . e($receipt->reference) . '">
                                        <i class="bx bx-trash"></i>
                                    </button>';
                    }
                } else {
                    $actions .= '<button type="button" 
                                    class="btn btn-sm btn-outline-secondary" 
                                    title="Edit/Delete locked: Source is ' . ucfirst($receipt->reference_type) . ' transaction" 
                                    disabled>
                                    <i class="bx bx-lock"></i>
                                </button>';
                }
                
                return '<div class="text-center">' . $actions . '</div>';
            })
            ->rawColumns(['reference_link', 'payee_info', 'formatted_amount', 'status_badge', 'actions'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();

        // Get bank accounts for the current company
        $bankAccounts = BankAccount::with('chartAccount')
            ->whereHas('chartAccount.accountClassGroup', function ($query) use ($user) {
                $query->where('company_id', $user->company_id);
            })
            ->orderBy('name')
            ->get();

        // Get customers for the current company/branch
        $customers = Customer::where('company_id', $user->company_id)
            ->when($user->branch_id, function ($query) use ($user) {
                return $query->where('branch_id', $user->branch_id);
            })
            ->orderBy('name')
            ->get();

        // Get chart accounts for the current company
        $chartAccounts = ChartAccount::whereHas('accountClassGroup', function ($query) use ($user) {
            $query->where('company_id', $user->company_id);
        })
            ->orderBy('account_name')
            ->get();

        return view('accounting.receipt-vouchers.create', compact('bankAccounts', 'customers', 'chartAccounts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        \Log::info('Receipt voucher store method called');

        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'reference' => 'nullable|string|max:255',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'payee_type' => 'required|in:customer,other',
            'customer_id' => 'nullable|required_if:payee_type,customer|exists:customers,id',
            'payee_name' => 'nullable|string|max:255|required_if:payee_type,other',
            'description' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf|max:2048',
            'line_items' => 'required|array|min:1',
            'line_items.*.chart_account_id' => 'required|exists:chart_accounts,id',
            'line_items.*.amount' => 'required|numeric|min:0.01',
            'line_items.*.description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            \Log::error('Receipt voucher validation failed:', $validator->errors()->toArray());
            \Log::error('Request data that failed validation:', $request->all());
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        \Log::info('Validation passed, proceeding with creation');
        \Log::info('Request data:', $request->all());

        try {
            return $this->runTransaction(function () use ($request) {
                $user = Auth::user();
                $totalAmount = collect($request->line_items)->sum('amount');

                \Log::info('Creating receipt voucher with total amount:', ['total' => $totalAmount]);

                // Handle file upload
                $attachmentPath = null;
                if ($request->hasFile('attachment')) {
                    $file = $request->file('attachment');
                    $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                    $attachmentPath = $file->storeAs('receipt-attachments', $fileName, 'public');
                }

                // Set payee information
                if ($request->payee_type === 'customer') {
                    $payeeType = 'customer';
                    $payeeId = $request->customer_id;
                    $payeeName = null;
                    $customerId = $request->customer_id;
                    $supplierId = null;
                } else {
                    $payeeType = 'other';
                    $payeeId = null;
                    $payeeName = $request->payee_name;
                    $customerId = null;
                    $supplierId = null;
                }

                \Log::info('Payee information:', [
                    'type' => $payeeType,
                    'id' => $payeeId,
                    'name' => $payeeName,
                    'payee_type_request' => $request->payee_type,
                    'payee_name_request' => $request->payee_name
                ]);

                // Create receipt
                $receiptData = [
                    'reference' => $request->reference ?: 'RV-' . strtoupper(uniqid()),
                    'reference_type' => 'manual',
                    'reference_number' => $request->reference,
                    'amount' => $totalAmount,
                    'date' => $request->date,
                    'description' => $request->description,
                    'attachment' => $attachmentPath,
                    'user_id' => $user->id,
                    'bank_account_id' => $request->bank_account_id,
                    'payee_type' => $payeeType,
                    'payee_id' => $payeeId,
                    'payee_name' => $payeeName,
                    'customer_id' => $customerId,
                    'supplier_id' => $supplierId,
                    'branch_id' => $user->branch_id,
                    'approved' => true, // Auto-approve for now
                    'approved_by' => $user->id,
                    'approved_at' => now(),
                ];

                \Log::info('Receipt data to be created:', $receiptData);

                $receipt = Receipt::create($receiptData);

                \Log::info('Receipt created successfully:', ['receipt_id' => $receipt->id]);

                // Create receipt items
                $receiptItems = [];
                foreach ($request->line_items as $lineItem) {
                    $receiptItems[] = [
                        'receipt_id' => $receipt->id,
                        'chart_account_id' => $lineItem['chart_account_id'],
                        'amount' => $lineItem['amount'],
                        'description' => $lineItem['description'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                ReceiptItem::insert($receiptItems);
                \Log::info('Receipt items created:', ['count' => count($receiptItems)]);

                // Create GL transactions
                $bankAccount = BankAccount::find($request->bank_account_id);

                // Prepare description for GL transactions
                $glDescription = $request->description ?: "Receipt voucher {$receipt->reference}";
                if ($payeeType === 'other' && $payeeName) {
                    $glDescription = $payeeName . ' - ' . $glDescription;
                }

                // Debit bank account
                GlTransaction::create([
                    'chart_account_id' => $bankAccount->chart_account_id,
                    'customer_id' => $customerId,
                    'supplier_id' => $supplierId,
                    'amount' => $totalAmount,
                    'nature' => 'debit',
                    'transaction_id' => $receipt->id,
                    'transaction_type' => 'receipt',
                    'date' => $request->date,
                    'description' => $glDescription,
                    'branch_id' => $user->branch_id,
                    'user_id' => $user->id,
                ]);

                // Credit each chart account
                foreach ($request->line_items as $lineItem) {
                    $lineItemDescription = $lineItem['description'] ?: "Receipt voucher {$receipt->reference}";
                    if ($payeeType === 'other' && $payeeName) {
                        $lineItemDescription = $payeeName . ' - ' . $lineItemDescription;
                    }
                    
                    GlTransaction::create([
                        'chart_account_id' => $lineItem['chart_account_id'],
                        'customer_id' => $customerId,
                        'supplier_id' => $supplierId,
                        'amount' => $lineItem['amount'],
                        'nature' => 'credit',
                        'transaction_id' => $receipt->id,
                        'transaction_type' => 'receipt',
                        'date' => $request->date,
                        'description' => $lineItemDescription,
                        'branch_id' => $user->branch_id,
                        'user_id' => $user->id,
                    ]);
                }

                \Log::info('GL transactions created successfully');

                return redirect()->route('accounting.receipt-vouchers.show', Hashids::encode($receipt->id))
                    ->with('success', 'Receipt voucher created successfully.');
            });
        } catch (\Exception $e) {
            \Log::error('Receipt voucher creation failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                ->withErrors(['error' => 'Failed to create receipt voucher: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($encodedId)
    {
        // Decode the ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('accounting.receipt-vouchers.index')->withErrors(['Receipt voucher not found.']);
        }

        $receiptVoucher = Receipt::findOrFail($decoded[0]);

        $receiptVoucher->load([
            'bankAccount',
            'customer.company',
            'customer.branch',
            'user',
            'receiptItems.chartAccount',
            'glTransactions.chartAccount',
            'branch'
        ]);

        // Only load loan relationship if this receipt is linked to a loan
        if ($receiptVoucher->reference_type === 'loan') {
            $receiptVoucher->load('loan.customer', 'loan.product');
        }

        return view('accounting.receipt-vouchers.show', compact('receiptVoucher'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($encodedId)
    {
        // Decode the ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('accounting.receipt-vouchers.index')->withErrors(['Receipt voucher not found.']);
        }

        $receiptVoucher = Receipt::findOrFail($decoded[0]);

        $user = Auth::user();

        // Get bank accounts for the current company
        $bankAccounts = BankAccount::with('chartAccount')
            ->whereHas('chartAccount.accountClassGroup', function ($query) use ($user) {
                $query->where('company_id', $user->company_id);
            })
            ->orderBy('name')
            ->get();

        // Get customers for the current company/branch
        $customers = Customer::where('company_id', $user->company_id)
            ->when($user->branch_id, function ($query) use ($user) {
                return $query->where('branch_id', $user->branch_id);
            })
            ->orderBy('name')
            ->get();

        // Get chart accounts for the current company
        $chartAccounts = ChartAccount::whereHas('accountClassGroup', function ($query) use ($user) {
            $query->where('company_id', $user->company_id);
        })
            ->orderBy('account_name')
            ->get();

        $receiptVoucher->load('receiptItems');

        return view('accounting.receipt-vouchers.edit', compact('receiptVoucher', 'bankAccounts', 'customers', 'chartAccounts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $encodedId)
    {
        // Decode receipt voucher ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('accounting.receipt-vouchers.index')->withErrors(['Receipt voucher not found.']);
        }

        $receiptVoucher = Receipt::findOrFail($decoded[0]);

        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'reference' => 'nullable|string|max:255',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'payee_type' => 'required|in:customer,other',
            'customer_id' => 'nullable|required_if:payee_type,customer|exists:customers,id',
            'payee_name' => 'nullable|string|max:255|required_if:payee_type,other',
            'description' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf|max:2048',
            'line_items' => 'required|array|min:1',
            'line_items.*.chart_account_id' => 'required|exists:chart_accounts,id',
            'line_items.*.amount' => 'required|numeric|min:0.01',
            'line_items.*.description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            return $this->runTransaction(function () use ($request, $receiptVoucher) {
                $user = Auth::user();
                $totalAmount = collect($request->line_items)->sum('amount');

                // Handle file upload and attachment removal
                $attachmentPath = $receiptVoucher->attachment;

                // Check if user wants to remove attachment
                if ($request->has('remove_attachment') && $request->remove_attachment == '1') {
                    // Delete old attachment if exists
                    if ($receiptVoucher->attachment && Storage::disk('public')->exists($receiptVoucher->attachment)) {
                        Storage::disk('public')->delete($receiptVoucher->attachment);
                    }
                    $attachmentPath = null;
                } elseif ($request->hasFile('attachment')) {
                    // Delete old attachment if exists
                    if ($receiptVoucher->attachment && Storage::disk('public')->exists($receiptVoucher->attachment)) {
                        Storage::disk('public')->delete($receiptVoucher->attachment);
                    }

                    $file = $request->file('attachment');
                    $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                    $attachmentPath = $file->storeAs('receipt-attachments', $fileName, 'public');
                }

                // Set payee information
                if ($request->payee_type === 'customer') {
                    $payeeType = 'customer';
                    $payeeId = $request->customer_id;
                    $payeeName = null;
                } else {
                    $payeeType = 'other';
                    $payeeId = null;
                    $payeeName = $request->payee_name;
                }

                // Update receipt
                $receiptVoucher->update([
                    'reference' => $request->reference ?: $receiptVoucher->reference,
                    'reference_number' => $request->reference,
                    'amount' => $totalAmount,
                    'date' => $request->date,
                    'description' => $request->description,
                    'attachment' => $attachmentPath,
                    'bank_account_id' => $request->bank_account_id,
                    'payee_type' => $payeeType,
                    'payee_id' => $payeeId,
                    'payee_name' => $payeeName,
                ]);

                // Delete existing receipt items and GL transactions
                $receiptVoucher->receiptItems()->delete();
                $receiptVoucher->glTransactions()->delete();

                // Create new receipt items
                $receiptItems = [];
                foreach ($request->line_items as $lineItem) {
                    $receiptItems[] = [
                        'receipt_id' => $receiptVoucher->id,
                        'chart_account_id' => $lineItem['chart_account_id'],
                        'amount' => $lineItem['amount'],
                        'description' => $lineItem['description'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                ReceiptItem::insert($receiptItems);

                // Create new GL transactions
                $bankAccount = BankAccount::find($request->bank_account_id);

                // Prepare description for GL transactions
                $glDescription = $request->description ?: "Receipt voucher {$receiptVoucher->reference}";
                if ($payeeType === 'other' && $payeeName) {
                    $glDescription = $payeeName . ' - ' . $glDescription;
                }

                // Debit bank account
                GlTransaction::create([
                    'chart_account_id' => $bankAccount->chart_account_id,
                    'customer_id' => $payeeType === 'customer' ? $payeeId : null,
                    'amount' => $totalAmount,
                    'nature' => 'debit',
                    'transaction_id' => $receiptVoucher->id,
                    'transaction_type' => 'receipt',
                    'date' => $request->date,
                    'description' => $glDescription,
                    'branch_id' => $user->branch_id,
                    'user_id' => $user->id,
                ]);

                // Credit each chart account
                foreach ($request->line_items as $lineItem) {
                    $lineItemDescription = $lineItem['description'] ?: "Receipt voucher {$receiptVoucher->reference}";
                    if ($payeeType === 'other' && $payeeName) {
                        $lineItemDescription = $payeeName . ' - ' . $lineItemDescription;
                    }
                    
                    GlTransaction::create([
                        'chart_account_id' => $lineItem['chart_account_id'],
                        'customer_id' => $payeeType === 'customer' ? $payeeId : null,
                        'amount' => $lineItem['amount'],
                        'nature' => 'credit',
                        'transaction_id' => $receiptVoucher->id,
                        'transaction_type' => 'receipt',
                        'date' => $request->date,
                        'description' => $lineItemDescription,
                        'branch_id' => $user->branch_id,
                        'user_id' => $user->id,
                    ]);
                }

                return redirect()->route('accounting.receipt-vouchers.show', Hashids::encode($receiptVoucher->id))
                    ->with('success', 'Receipt voucher updated successfully.');
            });
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to update receipt voucher: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($encodedId)
    {
        // Decode the encoded ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('accounting.receipt-vouchers.index')->withErrors(['Receipt voucher not found.']);
        }

        $receiptVoucher = Receipt::findOrFail($decoded[0]);

        try {
            return $this->runTransaction(function () use ($receiptVoucher) {
                // Delete attachment if exists
                if ($receiptVoucher->attachment && Storage::disk('public')->exists($receiptVoucher->attachment)) {
                    Storage::disk('public')->delete($receiptVoucher->attachment);
                }

                // Delete GL transactions first
                $receiptVoucher->glTransactions()->delete();

                // Delete receipt items
                $receiptVoucher->receiptItems()->delete();

                // Delete receipt
                $receiptVoucher->delete();

                return redirect()->route('accounting.receipt-vouchers.index')
                    ->with('success', 'Receipt voucher deleted successfully.');
            });
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete receipt voucher: ' . $e->getMessage()]);
        }
    }

    /**
     * Download attachment.
     */
    public function downloadAttachment($encodedId)
    {
        // Decode the ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('accounting.receipt-vouchers.index')->withErrors(['Receipt voucher not found.']);
        }

        $receiptVoucher = Receipt::findOrFail($decoded[0]);

        if (!$receiptVoucher->attachment) {
            return redirect()->back()->withErrors(['error' => 'No attachment found.']);
        }

        if (!Storage::disk('public')->exists($receiptVoucher->attachment)) {
            return redirect()->back()->withErrors(['error' => 'Attachment file not found.']);
        }

        return Storage::disk('public')->download($receiptVoucher->attachment);
    }

    /**
     * Remove attachment.
     */
    public function removeAttachment($encodedId)
    {
        // Decode the ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('accounting.receipt-vouchers.index')->withErrors(['Receipt voucher not found.']);
        }

        $receiptVoucher = Receipt::findOrFail($decoded[0]);

        try {
            // Delete attachment file if exists
            if ($receiptVoucher->attachment && Storage::disk('public')->exists($receiptVoucher->attachment)) {
                Storage::disk('public')->delete($receiptVoucher->attachment);
            }

            // Update receipt to remove attachment reference
            $receiptVoucher->update(['attachment' => null]);

            return redirect()->back()->with('success', 'Attachment removed successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to remove attachment: ' . $e->getMessage()]);
        }
    }

    /**
     * Export receipt voucher to PDF
     */
    public function exportPdf($encodedId)
    {
        try {
            // Decode the ID
            $decoded = Hashids::decode($encodedId);
            if (empty($decoded)) {
                return redirect()->route('accounting.receipt-vouchers.index')->withErrors(['Receipt voucher not found.']);
            }

            $receiptVoucher = Receipt::findOrFail($decoded[0]);

            // Check if user has access to this receipt voucher
            $user = Auth::user();
            if ($receiptVoucher->bankAccount->chartAccount->accountClassGroup->company_id !== $user->company_id) {
                abort(403, 'Unauthorized access to this receipt voucher.');
            }

            // Load relationships
            $receiptVoucher->load([
                'bankAccount.chartAccount',
                'customer',
                'user.company',
                'branch',
                'receiptItems.chartAccount'
            ]);

            // Generate PDF using DomPDF
            $pdf = \PDF::loadView('accounting.receipt-vouchers.pdf', compact('receiptVoucher'));

            // Set paper size and orientation
            $pdf->setPaper('A4', 'portrait');

            // Generate filename
            $filename = 'receipt_voucher_' . $receiptVoucher->reference . '_' . date('Y-m-d_H-i-s') . '.pdf';

            // Return PDF for download
            return $pdf->download($filename);

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to export PDF: ' . $e->getMessage()]);
        }
    }

    /**
     * Show the form for creating a receipt from a loan.
     */
    public function createFromLoan($encodedLoanId)
    {
        // Decode the loan ID
        $decoded = Hashids::decode($encodedLoanId);
        if (empty($decoded)) {
            return redirect()->route('loans.list')->withErrors(['Loan not found.']);
        }

        $loan = \App\Models\Loan::with(['customer', 'product', 'bankAccount'])->findOrFail($decoded[0]);
        $user = Auth::user();

        // Get bank accounts for the current company
        $bankAccounts = BankAccount::with('chartAccount')
            ->whereHas('chartAccount.accountClassGroup', function ($query) use ($user) {
                $query->where('company_id', $user->company_id);
            })
            ->orderBy('name')
            ->get();

        // Get customers for the current company/branch
        $customers = Customer::where('company_id', $user->company_id)
            ->when($user->branch_id, function ($query) use ($user) {
                return $query->where('branch_id', $user->branch_id);
            })
            ->orderBy('name')
            ->get();

        // Get chart accounts for the current company
        $chartAccounts = ChartAccount::whereHas('accountClassGroup', function ($query) use ($user) {
            $query->where('company_id', $user->company_id);
        })
            ->orderBy('account_name')
            ->get();

        return view('accounting.receipt-vouchers.create-from-loan', compact('loan', 'bankAccounts', 'customers', 'chartAccounts'));
    }

    /**
     * Store a receipt created from a loan.
     */
    public function storeFromLoan(Request $request, $encodedLoanId)
    {
        // Decode the loan ID
        $decoded = Hashids::decode($encodedLoanId);
        if (empty($decoded)) {
            return redirect()->route('loans.list')->withErrors(['Loan not found.']);
        }

        $loan = \App\Models\Loan::findOrFail($decoded[0]);

        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'payee_type' => 'required|in:customer,other',
            'customer_id' => 'nullable|required_if:payee_type,customer|exists:customers,id',
            'payee_name' => 'nullable|string|max:255|required_if:payee_type,other',
            'description' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf|max:2048',
            'line_items' => 'required|array|min:1',
            'line_items.*.chart_account_id' => 'required|exists:chart_accounts,id',
            'line_items.*.amount' => 'required|numeric|min:0.01',
            'line_items.*.description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            \Log::error('Receipt voucher validation failed:', $validator->errors()->toArray());
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        \Log::info('Validation passed, proceeding with creation from loan');

        try {
            return $this->runTransaction(function () use ($request, $loan) {
                $user = Auth::user();
                $totalAmount = collect($request->line_items)->sum('amount');

                \Log::info('Creating receipt voucher from loan with total amount:', ['total' => $totalAmount, 'loan_id' => $loan->id]);

                // Handle file upload
                $attachmentPath = null;
                if ($request->hasFile('attachment')) {
                    $file = $request->file('attachment');
                    $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                    $attachmentPath = $file->storeAs('receipt-attachments', $fileName, 'public');
                }

                // Set payee information
                if ($request->payee_type === 'customer') {
                    $payeeType = 'customer';
                    $payeeId = $request->customer_id;
                    $payeeName = null;
                } else {
                    $payeeType = 'other';
                    $payeeId = null;
                    $payeeName = $request->payee_name;
                }

                \Log::info('Payee information:', [
                    'type' => $payeeType,
                    'id' => $payeeId,
                    'name' => $payeeName
                ]);

                // Create receipt with loan reference
                $receipt = Receipt::create([
                    'reference' => $loan->id,
                    'reference_type' => 'loan',
                    'reference_number' => null,// Store loan ID as reference number
                    'amount' => $totalAmount,
                    'date' => $request->date,
                    'description' => $request->description,
                    'attachment' => $attachmentPath,
                    'user_id' => $user->id,
                    'bank_account_id' => $request->bank_account_id,
                    'payee_type' => $payeeType,
                    'payee_id' => $payeeId,
                    'payee_name' => $payeeName,
                    'branch_id' => $user->branch_id,
                    'approved' => true, // Auto-approve for now
                    'approved_by' => $user->id,
                    'approved_at' => now(),
                ]);

                \Log::info('Receipt created successfully from loan:', ['receipt_id' => $receipt->id, 'loan_id' => $loan->id]);

                // Create receipt items
                $receiptItems = [];
                foreach ($request->line_items as $lineItem) {
                    $receiptItems[] = [
                        'receipt_id' => $receipt->id,
                        'chart_account_id' => $lineItem['chart_account_id'],
                        'amount' => $lineItem['amount'],
                        'description' => $lineItem['description'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                ReceiptItem::insert($receiptItems);
                \Log::info('Receipt items created:', ['count' => count($receiptItems)]);

                // Create GL transactions
                $bankAccount = BankAccount::find($request->bank_account_id);

                // Debit bank account
                GlTransaction::create([
                    'chart_account_id' => $bankAccount->chart_account_id,
                    'customer_id' => $payeeType === 'customer' ? $payeeId : null,
                    'amount' => $totalAmount,
                    'nature' => 'debit',
                    'transaction_id' => $receipt->id,
                    'transaction_type' => 'receipt',
                    'date' => $request->date,
                    'description' => $request->description ?: "Receipt voucher {$receipt->reference} for loan {$loan->loanNo}",
                    'branch_id' => $user->branch_id,
                    'user_id' => $user->id,
                ]);

                // Credit each chart account
                foreach ($request->line_items as $lineItem) {
                    GlTransaction::create([
                        'chart_account_id' => $lineItem['chart_account_id'],
                        'customer_id' => $payeeType === 'customer' ? $payeeId : null,
                        'amount' => $lineItem['amount'],
                        'nature' => 'credit',
                        'transaction_id' => $receipt->id,
                        'transaction_type' => 'receipt',
                        'date' => $request->date,
                        'description' => $lineItem['description'] ?: "Receipt voucher {$receipt->reference} for loan {$loan->loanNo}",
                        'branch_id' => $user->branch_id,
                        'user_id' => $user->id,
                    ]);
                }

                \Log::info('GL transactions created successfully for loan receipt');

                return redirect()->route('accounting.receipt-vouchers.show', Hashids::encode($receipt->id))
                    ->with('success', 'Receipt voucher created successfully from loan.');
            });
        } catch (\Exception $e) {
            \Log::error('Receipt voucher creation from loan failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                ->withErrors(['error' => 'Failed to create receipt voucher: ' . $e->getMessage()])
                ->withInput();
        }
    }
}
