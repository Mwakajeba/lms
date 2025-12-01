<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\ChartAccount;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\GlTransaction;
use App\Models\Payment;
use App\Models\PaymentItem;
use App\Traits\TransactionHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class PaymentVoucherController extends Controller
{
    use TransactionHelper;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        // Calculate stats only
        $allPayments = Payment::with(['bankAccount.chartAccount.accountClassGroup'])
            ->whereHas('bankAccount.chartAccount.accountClassGroup', function ($query) use ($user) {
                $query->where('company_id', $user->company_id);
            })
            ->get();

        $stats = [
            'total' => $allPayments->count(),
            'this_month' => $allPayments->where('date', '>=', now()->startOfMonth())->count(),
            'total_amount' => $allPayments->sum('amount'),
            'this_month_amount' => $allPayments->where('date', '>=', now()->startOfMonth())->sum('amount'),
        ];

        return view('accounting.payment-vouchers.index', compact('stats'));
    }

    // Ajax endpoint for DataTables
    public function getPaymentVouchersData(Request $request)
    {
        $user = Auth::user();

        $payments = Payment::with(['bankAccount', 'customer', 'supplier', 'user', 'approvals'])
            ->whereHas('bankAccount.chartAccount.accountClassGroup', function ($query) use ($user) {
                $query->where('company_id', $user->company_id);
            })
            ->select('payments.*');

        return DataTables::eloquent($payments)
                ->addColumn('formatted_date', function ($payment) {
                    return $payment->date ? $payment->date->format('M d, Y') : 'N/A';
                })
                ->addColumn('reference_link', function ($payment) {
                    return '<a href="' . route('accounting.payment-vouchers.show', $payment->hash_id) . '" 
                                class="text-primary fw-bold">
                                ' . e($payment->reference) . '
                            </a>';
                })
                ->addColumn('bank_account_name', function ($payment) {
                    return optional($payment->bankAccount)->name ?? 'N/A';
                })
                ->addColumn('payee_info', function ($payment) {
                    if ($payment->payee_type == 'customer' && $payment->customer) {
                        return '<span class="badge bg-primary me-1">Customer</span>' . e($payment->customer->name ?? 'N/A');
                    } elseif ($payment->payee_type == 'supplier' && $payment->supplier) {
                        return '<span class="badge bg-success me-1">Supplier</span>' . e($payment->supplier->name ?? 'N/A');
                    } elseif ($payment->payee_type == 'other') {
                        return '<span class="badge bg-warning me-1">Other</span>' . e($payment->payee_name ?? 'N/A');
                    } else {
                        return '<span class="text-muted">No payee</span>';
                    }
                })
                ->addColumn('description_limited', function ($payment) {
                    return $payment->description ? Str::limit($payment->description, 50) : 'No description';
                })
                ->addColumn('reference_type_badge', function ($payment) {
                    if ($payment->reference_type === 'manual') {
                        return '<span class="badge bg-primary">Manual Payment Voucher</span>';
                    } else {
                        return '<span class="badge bg-secondary">' . ucfirst(str_replace(' ', ' ', $payment->reference_type)) . '</span>';
                    }
                })
                ->addColumn('formatted_amount', function ($payment) {
                    return '<span class="text-end fw-bold">' . number_format($payment->amount, 2) . '</span>';
                })
                ->addColumn('status_badge', function ($payment) {
                    return $payment->approval_status_badge;
                })
                ->addColumn('actions', function ($payment) {
                    $actions = '';

                    $canView = auth()->user()->can('view payment voucher details');
                    $canEdit = auth()->user()->can('edit payment voucher');
                    $canDelete = auth()->user()->can('delete payment voucher');
                    $canEditApproved = auth()->user()->can('edit approved payment voucher');
                    $canDeleteApproved = auth()->user()->can('delete approved payment voucher');

                    $isManual = $payment->reference_type === 'manual';
                    $settings = \App\Models\PaymentVoucherApprovalSetting::where('company_id', auth()->user()->company_id)->first();
                    $approvalsDisabled = $settings && !$settings->require_approval_for_all;
                    $isApproved = $payment->isFullyApproved();

                    // View action
                    if ($canView) {
                        $actions .= '<a href="' . route('accounting.payment-vouchers.show', $payment->hash_id) . '" 
                                        class="btn btn-sm btn-outline-success me-1" 
                                        data-bs-toggle="tooltip" 
                                        data-bs-placement="top" 
                                        title="View payment voucher">
                                        <i class="bx bx-show"></i>
                                    </a>';
                    }

                    // Edit action: always show if user has permission, enable if allowed
                    if ($canEdit) {
                        // Allow edit for manual vouchers regardless of approval; otherwise require override
                        $editAllowed = ($isManual) || $canEditApproved;
                        $editTitle = $editAllowed
                            ? 'Edit payment voucher'
                            : ($isManual ? 'Cannot edit: Payment voucher is approved' : 'Edit locked: Source is ' . ucfirst($payment->reference_type) . ' transaction');

                        if ($editAllowed) {
                            $actions .= '<a href="' . route('accounting.payment-vouchers.edit', $payment->hash_id) . '" 
                                            class="btn btn-sm btn-outline-info me-1" 
                                            data-bs-toggle="tooltip" 
                                            data-bs-placement="top" 
                                            title="' . e($editTitle) . '">
                                            <i class="bx bx-edit"></i>
                                        </a>';
                        } else {
                            $actions .= '<button type="button" 
                                            class="btn btn-sm btn-outline-secondary me-1" 
                                            data-bs-toggle="tooltip" 
                                            data-bs-placement="top" 
                                            title="' . e($editTitle) . '" 
                                            disabled>
                                            <i class="bx bx-edit"></i>
                                        </button>';
                        }
                    }

                    // Delete action: always show if user has permission, enable if allowed
                    if ($canDelete) {
                        // Allow delete for manual vouchers regardless of approval; otherwise require override
                        $deleteAllowed = ($isManual) || $canDeleteApproved;
                        $deleteTitle = $deleteAllowed
                            ? 'Delete payment voucher'
                            : ($isManual ? 'Cannot delete: Payment voucher is approved' : 'Delete locked: Source is ' . ucfirst($payment->reference_type) . ' transaction');

                        if ($deleteAllowed) {
                            $actions .= '<button type="button" 
                                            class="btn btn-sm btn-outline-danger delete-payment-btn"
                                            data-bs-toggle="tooltip" 
                                            data-bs-placement="top" 
                                            title="' . e($deleteTitle) . '"
                                            data-payment-id="' . $payment->hash_id . '"
                                            data-payment-reference="' . e($payment->reference) . '">
                                            <i class="bx bx-trash"></i>
                                        </button>';
                        } else {
                            $actions .= '<button type="button" 
                                            class="btn btn-sm btn-outline-secondary" 
                                            data-bs-toggle="tooltip" 
                                            data-bs-placement="top" 
                                            title="' . e($deleteTitle) . '" 
                                            disabled>
                                            <i class="bx bx-trash"></i>
                                        </button>';
                        }
                    }

                    return '<div class="text-center">' . $actions . '</div>';
                })
                ->rawColumns(['reference_link', 'payee_info', 'description_limited', 'reference_type_badge', 'formatted_amount', 'status_badge', 'actions'])
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

        // Get suppliers for the current company
        $suppliers = Supplier::where('company_id', $user->company_id)
            ->orderBy('name')
            ->get();

        // Get chart accounts for the current company - only expense accounts
        $chartAccounts = ChartAccount::whereHas('accountClassGroup', function ($query) use ($user) {
            $query->where('company_id', $user->company_id);
        })
            ->whereHas('accountClassGroup.accountClass', function ($query) {
                $query->where('name', 'like', '%expense%')
                      ->orWhere('name', 'like', '%cost%')
                      ->orWhere('name', 'like', '%expenditure%');
            })
            ->orderBy('account_name')
            ->get();

        return view('accounting.payment-vouchers.create', compact('bankAccounts', 'customers', 'suppliers', 'chartAccounts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'reference' => 'nullable|string|max:255',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'payee_type' => 'required|in:customer,supplier,other',
            'customer_id' => 'required_if:payee_type,customer|exists:customers,id',
            'supplier_id' => 'required_if:payee_type,supplier|exists:suppliers,id',
            'payee_name' => 'required_if:payee_type,other|nullable|string|max:255',
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
            return $this->runTransaction(function () use ($request) {
                $user = Auth::user();
                $totalAmount = collect($request->line_items)->sum('amount');

                // Handle file upload
                $attachmentPath = null;
                if ($request->hasFile('attachment')) {
                    $file = $request->file('attachment');
                    $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                    $attachmentPath = $file->storeAs('payment-attachments', $fileName, 'public');
                }

                // Handle payee information
                $payeeType = $request->payee_type;
                $payeeId = null;
                $payeeName = null;

                if ($request->payee_type === 'customer') {
                    $payeeId = $request->customer_id;
                } elseif ($request->payee_type === 'supplier') {
                    $payeeId = $request->supplier_id;
                } elseif ($request->payee_type === 'other') {
                    $payeeName = $request->payee_name;
                }

                // Create payment
                $payment = Payment::create([
                    'reference' => $request->reference ?: 'PV-' . strtoupper(uniqid()),
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
                    'customer_id' => $request->customer_id,
                    'supplier_id' => $request->supplier_id,
                    'branch_id' => $user->branch_id,
                    'approved' => false, // Will be set by approval workflow
                    'approved_by' => null,
                    'approved_at' => null,
                ]);

                // Initialize approval workflow (may auto-approve depending on settings)
                $payment->initializeApprovalWorkflow();

                // Create payment items
                $paymentItems = [];
                foreach ($request->line_items as $lineItem) {
                    $paymentItems[] = [
                        'payment_id' => $payment->id,
                        'chart_account_id' => $lineItem['chart_account_id'],
                        'amount' => $lineItem['amount'],
                        'description' => $lineItem['description'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                PaymentItem::insert($paymentItems);

                // Post to GL only if payment has been approved (either approvals disabled or auto-approved)
                $payment->refresh();
                if ($payment->approved) {
                    $bankAccount = BankAccount::find($request->bank_account_id);

                    // Prepare description for GL transactions
                    $glDescription = $request->description ?: "Payment voucher {$payment->reference}";
                    if ($payeeType === 'other' && $payeeName) {
                        $glDescription = $payeeName . ' - ' . $glDescription;
                    }

                    // Credit bank account
                    GlTransaction::create([
                        'chart_account_id' => $bankAccount->chart_account_id,
                        'customer_id' => $request->customer_id,
                        'supplier_id' => $request->supplier_id,
                        'amount' => $totalAmount,
                        'nature' => 'credit',
                        'transaction_id' => $payment->id,
                        'transaction_type' => 'payment',
                        'date' => $request->date,
                        'description' => $glDescription,
                        'branch_id' => $user->branch_id,
                        'user_id' => $user->id,
                    ]);

                    // Debit each chart account
                    foreach ($request->line_items as $lineItem) {
                        $lineItemDescription = $lineItem['description'] ?: "Payment voucher {$payment->reference}";
                        if ($payeeType === 'other' && $payeeName) {
                            $lineItemDescription = $payeeName . ' - ' . $lineItemDescription;
                        }
                        
                        GlTransaction::create([
                            'chart_account_id' => $lineItem['chart_account_id'],
                            'customer_id' => $request->customer_id,
                            'supplier_id' => $request->supplier_id,
                            'amount' => $lineItem['amount'],
                            'nature' => 'debit',
                            'transaction_id' => $payment->id,
                            'transaction_type' => 'payment',
                            'date' => $request->date,
                            'description' => $lineItemDescription,
                            'branch_id' => $user->branch_id,
                            'user_id' => $user->id,
                        ]);
                    }

                    return redirect()->route('accounting.payment-vouchers.show', $payment)
                        ->with('success', 'Payment voucher created and posted to GL successfully.');
                }

                // If not yet approved, inform user it's awaiting approval
                return redirect()->route('accounting.payment-vouchers.show', $payment)
                    ->with('success', 'Payment voucher created and is awaiting approval. GL posting will occur after final approval.');
            });
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to create payment voucher: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $paymentVoucher)
    {
        $paymentVoucher->load(['bankAccount', 'customer', 'supplier', 'user', 'branch', 'paymentItems.chartAccount', 'glTransactions.chartAccount']);

        return view('accounting.payment-vouchers.show', compact('paymentVoucher'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Payment $paymentVoucher)
    {
        // Authorization: require permission to edit payment vouchers
        if (!auth()->user()->can('edit payment voucher')) {
            abort(403, 'You do not have permission to edit payment vouchers.');
        }

        $user = Auth::user();

        // For manual vouchers, allow edit even if approved; otherwise require override
        if ($paymentVoucher->isFullyApproved() && $paymentVoucher->reference_type !== 'manual' && !auth()->user()->can('edit approved payment voucher')) {
            return redirect()->route('accounting.payment-vouchers.show', $paymentVoucher)
                ->withErrors(['error' => 'Cannot edit an approved payment voucher.']);
        }

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

        // Get suppliers for the current company
        $suppliers = Supplier::where('company_id', $user->company_id)
            ->orderBy('name')
            ->get();

        // Get chart accounts for the current company - only expense accounts
        $chartAccounts = ChartAccount::whereHas('accountClassGroup', function ($query) use ($user) {
            $query->where('company_id', $user->company_id);
        })
            ->whereHas('accountClassGroup.accountClass', function ($query) {
                $query->where('name', 'like', '%expense%')
                      ->orWhere('name', 'like', '%cost%')
                      ->orWhere('name', 'like', '%expenditure%');
            })
            ->orderBy('account_name')
            ->get();

        $paymentVoucher->load('paymentItems');

        return view('accounting.payment-vouchers.edit', compact('paymentVoucher', 'bankAccounts', 'customers', 'suppliers', 'chartAccounts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Payment $paymentVoucher)
    {
        // Authorization: require permission to edit payment vouchers
        if (!auth()->user()->can('edit payment voucher')) {
            abort(403, 'You do not have permission to edit payment vouchers.');
        }

        // For manual vouchers, allow update even if approved; otherwise require override
        if ($paymentVoucher->isFullyApproved() && $paymentVoucher->reference_type !== 'manual' && !auth()->user()->can('edit approved payment voucher')) {
            return redirect()->route('accounting.payment-vouchers.show', $paymentVoucher)
                ->withErrors(['error' => 'Cannot update an approved payment voucher.']);
        }

        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'reference' => 'nullable|string|max:255',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'payee_type' => 'required|in:customer,supplier,other',
            'customer_id' => 'required_if:payee_type,customer|exists:customers,id',
            'supplier_id' => 'required_if:payee_type,supplier|exists:suppliers,id',
            'payee_name' => 'required_if:payee_type,other|nullable|string|max:255',
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
            return $this->runTransaction(function () use ($request, $paymentVoucher) {
                $user = Auth::user();
                $totalAmount = collect($request->line_items)->sum('amount');

                // Handle file upload and attachment removal
                $attachmentPath = $paymentVoucher->attachment;
                
                // Check if user wants to remove attachment
                if ($request->has('remove_attachment') && $request->remove_attachment == '1') {
                    // Delete old attachment if exists
                    if ($paymentVoucher->attachment && Storage::disk('public')->exists($paymentVoucher->attachment)) {
                        Storage::disk('public')->delete($paymentVoucher->attachment);
                    }
                    $attachmentPath = null;
                } elseif ($request->hasFile('attachment')) {
                    // Delete old attachment if exists
                    if ($paymentVoucher->attachment && Storage::disk('public')->exists($paymentVoucher->attachment)) {
                        Storage::disk('public')->delete($paymentVoucher->attachment);
                    }

                    $file = $request->file('attachment');
                    $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                    $attachmentPath = $file->storeAs('payment-attachments', $fileName, 'public');
                }

                // Handle payee information
                $payeeType = $request->payee_type;
                $payeeId = null;
                $payeeName = null;

                if ($request->payee_type === 'customer') {
                    $payeeId = $request->customer_id;
                } elseif ($request->payee_type === 'supplier') {
                    $payeeId = $request->supplier_id;
                } elseif ($request->payee_type === 'other') {
                    $payeeName = $request->payee_name;
                }

                // Update payment
                $updateData = [
                    'reference' => $request->reference ?: $paymentVoucher->reference,
                    'amount' => $totalAmount,
                    'date' => $request->date,
                    'description' => $request->description,
                    'attachment' => $attachmentPath,
                    'bank_account_id' => $request->bank_account_id,
                    'payee_type' => $payeeType,
                    'payee_id' => $payeeId,
                    'payee_name' => $payeeName,
                    'customer_id' => $request->customer_id,
                    'supplier_id' => $request->supplier_id,
                ];

                $paymentVoucher->update($updateData);

                // Delete existing payment items and GL transactions
                $paymentVoucher->paymentItems()->delete();
                $paymentVoucher->glTransactions()->delete();

                // Create new payment items
                $paymentItems = [];
                foreach ($request->line_items as $lineItem) {
                    $paymentItems[] = [
                        'payment_id' => $paymentVoucher->id,
                        'chart_account_id' => $lineItem['chart_account_id'],
                        'amount' => $lineItem['amount'],
                        'description' => $lineItem['description'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                PaymentItem::insert($paymentItems);

                // Create new GL transactions
                $bankAccount = BankAccount::find($request->bank_account_id);

                // Prepare description for GL transactions
                $glDescription = $request->description ?: "Payment voucher {$paymentVoucher->reference}";
                if ($payeeType === 'other' && $payeeName) {
                    $glDescription = $payeeName . ' - ' . $glDescription;
                }

                // Credit bank account
                GlTransaction::create([
                    'chart_account_id' => $bankAccount->chart_account_id,
                    'customer_id' => $request->customer_id,
                    'supplier_id' => $request->supplier_id,
                    'amount' => $totalAmount,
                    'nature' => 'credit',
                    'transaction_id' => $paymentVoucher->id,
                    'transaction_type' => 'payment',
                    'date' => $request->date,
                    'description' => $glDescription,
                    'branch_id' => $user->branch_id,
                    'user_id' => $user->id,
                ]);

                // Debit each chart account
                foreach ($request->line_items as $lineItem) {
                    $lineItemDescription = $lineItem['description'] ?: "Payment voucher {$paymentVoucher->reference}";
                    if ($payeeType === 'other' && $payeeName) {
                        $lineItemDescription = $payeeName . ' - ' . $lineItemDescription;
                    }
                    
                    GlTransaction::create([
                        'chart_account_id' => $lineItem['chart_account_id'],
                        'customer_id' => $request->customer_id,
                        'supplier_id' => $request->supplier_id,
                        'amount' => $lineItem['amount'],
                        'nature' => 'debit',
                        'transaction_id' => $paymentVoucher->id,
                        'transaction_type' => 'payment',
                        'date' => $request->date,
                        'description' => $lineItemDescription,
                        'branch_id' => $user->branch_id,
                        'user_id' => $user->id,
                    ]);
                }

                return redirect()->route('accounting.payment-vouchers.show', $paymentVoucher)
                    ->with('success', 'Payment voucher updated successfully.');
            });
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to update payment voucher: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $paymentVoucher)
    {
        // Authorization: require permission to delete payment vouchers
        if (!auth()->user()->can('delete payment voucher')) {
            abort(403, 'You do not have permission to delete payment vouchers.');
        }

        // For manual vouchers, allow delete even if approved; otherwise require override
        if ($paymentVoucher->isFullyApproved() && $paymentVoucher->reference_type !== 'manual' && !auth()->user()->can('delete approved payment voucher')) {
            return redirect()->route('accounting.payment-vouchers.show', $paymentVoucher)
                ->withErrors(['error' => 'Cannot delete an approved payment voucher.']);
        }

        try {
            return $this->runTransaction(function () use ($paymentVoucher) {
                // Delete attachment if exists
                if ($paymentVoucher->attachment && Storage::disk('public')->exists($paymentVoucher->attachment)) {
                    Storage::disk('public')->delete($paymentVoucher->attachment);
                }

                // Delete related records
                $paymentVoucher->paymentItems()->delete();
                $paymentVoucher->glTransactions()->delete();
                $paymentVoucher->delete();

                return redirect()->route('accounting.payment-vouchers.index')
                    ->with('success', 'Payment voucher deleted successfully.');
            });
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete payment voucher: ' . $e->getMessage()]);
        }
    }

    /**
     * Download attachment.
     */
    public function downloadAttachment(Payment $paymentVoucher)
    {
        if (!$paymentVoucher->attachment) {
            return redirect()->back()->withErrors(['error' => 'No attachment found.']);
        }

        if (!Storage::disk('public')->exists($paymentVoucher->attachment)) {
            return redirect()->back()->withErrors(['error' => 'Attachment file not found.']);
        }

        return Storage::disk('public')->download($paymentVoucher->attachment);
    }

    /**
     * Remove attachment.
     */
    public function removeAttachment(Payment $paymentVoucher)
    {
        // Check if payment voucher is approved - if so, prevent attachment removal
        if ($paymentVoucher->isFullyApproved()) {
            return redirect()->route('accounting.payment-vouchers.show', $paymentVoucher)
                ->withErrors(['error' => 'Cannot modify an approved payment voucher.']);
        }

        try {
            // Delete attachment file if exists
            if ($paymentVoucher->attachment && Storage::disk('public')->exists($paymentVoucher->attachment)) {
                Storage::disk('public')->delete($paymentVoucher->attachment);
            }

            // Update payment to remove attachment reference
            $paymentVoucher->update(['attachment' => null]);

            return redirect()->back()->with('success', 'Attachment removed successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to remove attachment: ' . $e->getMessage()]);
        }
    }

    /**
     * Export payment voucher to PDF
     */
    public function exportPdf(Payment $paymentVoucher)
    {
        try {
            // Check if user has access to this payment voucher
            $user = Auth::user();
            if ($paymentVoucher->bankAccount->chartAccount->accountClassGroup->company_id !== $user->company_id) {
                abort(403, 'Unauthorized access to this payment voucher.');
            }

            // Load relationships
            $paymentVoucher->load([
                'bankAccount.chartAccount',
                'customer',
                'supplier',
                'user.company',
                'approvedBy',
                'branch',
                'paymentItems.chartAccount'
            ]);

            // Generate PDF using DomPDF
            $pdf = \PDF::loadView('accounting.payment-vouchers.pdf', compact('paymentVoucher'));

            // Set paper size and orientation
            $pdf->setPaper('A4', 'portrait');

            // Generate filename
            $filename = 'payment_voucher_' . $paymentVoucher->reference . '_' . date('Y-m-d_H-i-s') . '.pdf';

            // Return PDF for download
            return $pdf->download($filename);

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to export PDF: ' . $e->getMessage()]);
        }
    }

    /**
     * Show approval interface for payment voucher
     */
    public function showApproval(Payment $paymentVoucher)
    {
        $user = Auth::user();
        
        // Check if user can approve this payment
        $settings = \App\Models\PaymentVoucherApprovalSetting::where('company_id', $user->company_id)->first();
        
        if (!$settings) {
            return redirect()->back()->withErrors(['error' => 'No approval settings configured.']);
        }

        $currentApproval = $paymentVoucher->currentApproval();
        
        if (!$currentApproval) {
            return redirect()->back()->withErrors(['error' => 'No pending approval found for this payment voucher.']);
        }

        // Check if current user can approve at this level
        if (!$settings->canUserApproveAtLevel($user, $currentApproval->approval_level)) {
            return redirect()->back()->withErrors(['error' => 'You do not have permission to approve this payment voucher.']);
        }

        $paymentVoucher->load(['bankAccount', 'customer', 'supplier', 'user', 'branch', 'paymentItems.chartAccount', 'approvals.approver']);

        return view('accounting.payment-vouchers.approval', compact('paymentVoucher', 'currentApproval', 'settings'));
    }

    /**
     * Approve payment voucher
     */
    public function approve(Request $request, Payment $paymentVoucher)
    {
        $user = Auth::user();
        
        // Check if user can approve this payment
        $settings = \App\Models\PaymentVoucherApprovalSetting::where('company_id', $user->company_id)->first();
        
        if (!$settings) {
            return redirect()->back()->withErrors(['error' => 'No approval settings configured.']);
        }

        $currentApproval = $paymentVoucher->currentApproval();
        
        if (!$currentApproval) {
            return redirect()->back()->withErrors(['error' => 'No pending approval found for this payment voucher.']);
        }

        // Check if current user can approve at this level
        if (!$settings->canUserApproveAtLevel($user, $currentApproval->approval_level)) {
            return redirect()->back()->withErrors(['error' => 'You do not have permission to approve this payment voucher.']);
        }

        $request->validate([
            'comments' => 'nullable|string|max:500',
        ]);

        try {
            DB::transaction(function () use ($paymentVoucher, $currentApproval, $user, $request) {
                // Approve current level
                $currentApproval->approve($request->comments);
                
                // Check if this was the final approval level
                if ($paymentVoucher->isFullyApproved()) {
                    $paymentVoucher->update([
                        'approved' => true,
                        'approved_by' => $user->id,
                        'approved_at' => now(),
                    ]);

                    // Post to GL if not already posted
                    $alreadyPosted = \App\Models\GlTransaction::where('transaction_type', 'payment')
                        ->where('transaction_id', $paymentVoucher->id)
                        ->exists();

                    if (!$alreadyPosted) {
                        $paymentVoucher->loadMissing(['bankAccount', 'paymentItems']);
                        $bankAccount = $paymentVoucher->bankAccount;
                        $date = $paymentVoucher->date;
                        $description = $paymentVoucher->description ?: ("Payment voucher {$paymentVoucher->reference}");
                        $branchId = $paymentVoucher->branch_id;
                        $userId = $user->id;

                        // Credit bank account with total amount
                        \App\Models\GlTransaction::create([
                            'chart_account_id' => $bankAccount?->chart_account_id,
                            'customer_id' => $paymentVoucher->customer_id,
                            'supplier_id' => $paymentVoucher->supplier_id,
                            'amount' => $paymentVoucher->amount,
                            'nature' => 'credit',
                            'transaction_id' => $paymentVoucher->id,
                            'transaction_type' => 'payment',
                            'date' => $date,
                            'description' => $description,
                            'branch_id' => $branchId,
                            'user_id' => $userId,
                        ]);

                        // Debit each expense line
                        foreach ($paymentVoucher->paymentItems as $item) {
                            \App\Models\GlTransaction::create([
                                'chart_account_id' => $item->chart_account_id,
                                'customer_id' => $paymentVoucher->customer_id,
                                'supplier_id' => $paymentVoucher->supplier_id,
                                'amount' => $item->amount,
                                'nature' => 'debit',
                                'transaction_id' => $paymentVoucher->id,
                                'transaction_type' => 'payment',
                                'date' => $date,
                                'description' => $item->description ?: $description,
                                'branch_id' => $branchId,
                                'user_id' => $userId,
                            ]);
                        }
                    }
                }
            });

            // Check if this is an AJAX request
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment voucher approved successfully.',
                    'redirect' => route('accounting.payment-vouchers.show', $paymentVoucher)
                ]);
            }

            return redirect()->route('accounting.payment-vouchers.show', $paymentVoucher)
                ->with('success', 'Payment voucher approved successfully.');
        } catch (\Exception $e) {
            // Check if this is an AJAX request
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to approve payment voucher: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->withErrors(['error' => 'Failed to approve payment voucher: ' . $e->getMessage()]);
        }
    }

    /**
     * Reject payment voucher
     */
    public function reject(Request $request, Payment $paymentVoucher)
    {
        $user = Auth::user();
        
        // Check if user can approve this payment
        $settings = \App\Models\PaymentVoucherApprovalSetting::where('company_id', $user->company_id)->first();
        
        if (!$settings) {
            return redirect()->back()->withErrors(['error' => 'No approval settings configured.']);
        }

        $currentApproval = $paymentVoucher->currentApproval();
        
        if (!$currentApproval) {
            return redirect()->back()->withErrors(['error' => 'No pending approval found for this payment voucher.']);
        }

        // Check if current user can approve at this level
        if (!$settings->canUserApproveAtLevel($user, $currentApproval->approval_level)) {
            return redirect()->back()->withErrors(['error' => 'You do not have permission to reject this payment voucher.']);
        }

        $request->validate([
            'comments' => 'required|string|max:500',
        ]);

        try {
            DB::transaction(function () use ($paymentVoucher, $currentApproval, $request) {
                // Reject current level
                $currentApproval->reject($request->comments);
                
                // Reject all remaining pending approvals
                $paymentVoucher->pendingApprovals()->update([
                    'status' => 'rejected',
                    'comments' => 'Rejected by higher level approval',
                    'approved_at' => now(),
                ]);
            });

            // Check if this is an AJAX request
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment voucher rejected successfully.',
                    'redirect' => route('accounting.payment-vouchers.show', $paymentVoucher)
                ]);
            }

            return redirect()->route('accounting.payment-vouchers.show', $paymentVoucher)
                ->with('success', 'Payment voucher rejected successfully.');
        } catch (\Exception $e) {
            // Check if this is an AJAX request
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to reject payment voucher: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->withErrors(['error' => 'Failed to reject payment voucher: ' . $e->getMessage()]);
        }
    }

    /**
     * Show pending approvals for current user
     */
    public function pendingApprovals()
    {
        $user = Auth::user();
        
        $pendingApprovals = \App\Models\PaymentVoucherApproval::with(['payment.bankAccount', 'payment.user'])
            ->whereHas('payment.user', function ($query) use ($user) {
                $query->where('company_id', $user->company_id);
            })
            ->where(function ($query) use ($user) {
                $query->where('approver_id', $user->id)
                      ->orWhereIn('approver_name', $user->getRoleNames());
            })
            ->pending()
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('accounting.payment-vouchers.pending-approvals', compact('pendingApprovals'));
    }
}
