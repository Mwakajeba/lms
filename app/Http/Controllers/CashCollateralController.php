<?php

namespace App\Http\Controllers;

use App\Models\CashCollateral;
use App\Models\Customer;
use App\Models\CashCollateralType;
use App\Models\BankAccount;
use App\Models\Receipt;
use App\Models\ReceiptItem;
use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\GlTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;
use App\Helpers\SmsHelper;

class CashCollateralController extends Controller
{
    public function index()
    {
        if (request()->ajax()) {
            $cashCollaterals = CashCollateral::with(['customer', 'type'])
                ->where('branch_id', Auth::user()->branch_id)
                ->where('company_id', Auth::user()->company_id)
                ->get();

            return datatables($cashCollaterals)
                ->addColumn('customer_name', function ($collateral) {
                    return $collateral->customer->name ?? 'N/A';
                })
                ->addColumn('type_name', function ($collateral) {
                    return $collateral->type->name ?? 'N/A';
                })
                ->addColumn('formatted_amount', function ($collateral) {
                    return number_format($collateral->amount, 2);
                })
                ->addColumn('formatted_date', function ($collateral) {
                    return $collateral->created_at->format('Y-m-d H:i');
                })
                ->addColumn('actions', function ($collateral) {
                    $encodedId = Hashids::encode($collateral->id);
                    $actions = '';
                    
                    if (auth()->user()->can('deposit cash collateral')) {
                        $actions .= '<a href="' . route('cash_collaterals.deposit', $encodedId) . '" class="btn btn-sm btn-primary me-1 mb-1">Deposit</a>';
                    }
                    
                    if (auth()->user()->can('withdraw cash collateral')) {
                        $actions .= '<a href="' . route('cash_collaterals.withdraw', $encodedId) . '" class="btn btn-sm btn-success me-1 mb-1">Withdraw</a>';
                    }
                    
                    if (auth()->user()->can('view cash collateral details')) {
                        $actions .= '<a href="' . route('cash_collaterals.show', $encodedId) . '" class="btn btn-sm btn-outline-info me-1 mb-1">View</a>';
                    }
                    
                    if (auth()->user()->can('edit cash collateral')) {
                        $actions .= '<a href="' . route('cash_collaterals.edit', $encodedId) . '" class="btn btn-sm btn-outline-warning me-1 mb-1">Edit</a>';
                    }
                    
                    if (auth()->user()->can('delete cash collateral')) {
                        $actions .= '<form action="' . route('cash_collaterals.destroy', $encodedId) . '" method="POST" class="d-inline delete-form">';
                        $actions .= csrf_field();
                        $actions .= method_field('DELETE');
                        $actions .= '<button type="submit" class="btn btn-sm btn-outline-danger mb-1" data-name="' . $collateral->id . '">Delete</button>';
                        $actions .= '</form>';
                    }
                    
                    return $actions;
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        $totalCollaterals = CashCollateral::where('branch_id', Auth::user()->branch_id)
            ->where('company_id', Auth::user()->company_id)
            ->count();

        return view('cash_collaterals.index', compact('totalCollaterals'));
    }

    public function create()
    {
        $customers = Customer::where('branch_id', Auth::user()->branch_id)
            ->where('company_id', Auth::user()->company_id)
            ->get();

        $types = CashCollateralType::where('is_active', true)->get();

        return view('cash_collaterals.create', compact('customers', 'types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'type_id' => 'required|exists:cash_collateral_types,id',
            'selected_account_types' => 'required|array|min:1',
            'selected_account_types.*' => 'exists:cash_collateral_types,id',
        ]);

        $data = $request->only(['customer_id', 'type_id', 'amount']);
        $user = Auth::user();
        $data['amount'] = 0;
        $data['branch_id'] = $user->branch_id;
        $data['company_id'] = $user->company_id;

        CashCollateral::create($data);

        return redirect()->route('cash_collaterals.index')->with('success', 'Cash Deposit created successfully.');
    }


    /////////////////////////////SHOW CASH COLLATERAL TRANACTIONS ///////////////////
    public function show($encodedId)
    {
        $id = Hashids::decode($encodedId)[0] ?? null;

        if (!$id) {
            abort(404, 'Invalid Cash Collateral ID.');
        }

        $cashCollateral = CashCollateral::with(['customer', 'type'])->findOrFail($id);

        $this->authorizeUserAccess($cashCollateral);

        // Handle Ajax request for DataTables
        if (request()->ajax()) {
            //////////////////// GET DEPOSIT TRANSACTION FOR CASH COLLATERAL OF CUSTOMER(RECIEPTS)////////////////////////////////////////////
            $deposits = Receipt::where('reference', $cashCollateral->id)
                ->where('reference_type', 'Deposit')
                ->with(['bankAccount', 'user'])
                ->get()
                ->map(function ($receipt) {
                    return [
                        'id' => $receipt->id,
                        'date' => $receipt->date,
                        'description' => $receipt->description,
                        'amount' => $receipt->amount,
                        'type' => 'Deposit',
                        'transaction_type' => 'receipt',
                        'bank_account' => $receipt->bankAccount->name ?? 'N/A',
                        'user' => $receipt->user->name ?? 'N/A',
                        'created_at' => $receipt->created_at,
                    ];
                });

            // ////////GET WITHDRAWAL TRANSACTION FOR CASH COLLATERAL OF CUSTOMER(PAYMENTS) ////////////////
            $withdrawals = Payment::where('reference', $cashCollateral->id)
                ->where('reference_type', 'Withdrawal')
                ->with(['bankAccount', 'user'])
                ->get()
                ->map(function ($payment) {
                    return [
                        'id' => $payment->id,
                        'date' => $payment->date,
                        'description' => $payment->description,
                        'amount' => $payment->amount,
                        'type' => 'Withdrawal',
                        'transaction_type' => 'payment',
                        'bank_account' => $payment->bankAccount->name ?? 'N/A',
                        'user' => $payment->user->name ?? 'N/A',
                        'created_at' => $payment->created_at,
                    ];
                });

            // Combine and sort by date
            $transactions = $deposits->concat($withdrawals)->sortByDesc('date');

            // Calculate running balance
            $balance = 0;
            $transactions = $transactions->map(function ($transaction) use (&$balance) {
                if ($transaction['type'] === 'Deposit') {
                    $balance += $transaction['amount'];
                } else {
                    $balance -= $transaction['amount'];
                }
                $transaction['balance'] = $balance;
                return $transaction;
            });

            return datatables($transactions)
                ->addColumn('formatted_date', function ($transaction) {
                    return $transaction['date']->format('d/m/Y');
                })
                ->addColumn('type_badge', function ($transaction) {
                    if ($transaction['type'] === 'Deposit') {
                        return '<span class="badge bg-success"><i class="bx bx-plus me-1"></i> Deposit</span>';
                    } else {
                        return '<span class="badge bg-warning"><i class="bx bx-minus me-1"></i> Withdrawal</span>';
                    }
                })
                ->addColumn('formatted_amount', function ($transaction) {
                    $class = $transaction['type'] === 'Deposit' ? 'text-success' : 'text-danger';
                    return '<span class="fw-bold ' . $class . '">TSHS ' . number_format($transaction['amount'], 2) . '</span>';
                })
                ->addColumn('formatted_balance', function ($transaction) {
                    $class = $transaction['balance'] >= 0 ? 'text-success' : 'text-danger';
                    return '<span class="fw-bold ' . $class . '">TSHS ' . number_format($transaction['balance'], 2) . '</span>';
                })
                ->addColumn('actions', function ($transaction) {
                    $encodedId = Hashids::encode($transaction['id']);
                    $actions = '';
                    
                    // Print Receipt Button
                    if ($transaction['type'] === 'Deposit') {
                        $actions .= '<button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="printDepositReceiptFromTable(' . $transaction['id'] . ')" title="Print Receipt">
                                        <i class="bx bx-printer"></i>
                                    </button>';
                    } else {
                        $actions .= '<button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="printWithdrawalReceiptFromTable(' . $transaction['id'] . ')" title="Print Receipt">
                                        <i class="bx bx-printer"></i>
                                    </button>';
                    }
                    
                    // Edit Button
                    if (auth()->user()->can('edit transaction')) {
                        if ($transaction['type'] === 'Deposit') {
                            $actions .= '<a href="' . route('receipts.edit', $encodedId) . '" class="btn btn-sm btn-outline-warning me-1" title="Edit ' . $transaction['type'] . '">
                                            <i class="bx bx-edit"></i>
                                        </a>';
                        } else {
                            $actions .= '<a href="' . route('payments.edit', $encodedId) . '" class="btn btn-sm btn-outline-warning me-1" title="Edit ' . $transaction['type'] . '">
                                            <i class="bx bx-edit"></i>
                                        </a>';
                        }
                    }
                    
                    // Delete Button
                    if (auth()->user()->can('delete transaction')) {
                        $actions .= '<button type="button" class="btn btn-sm btn-outline-danger" 
                                        onclick="deleteTransaction(\'' . $encodedId . '\', \'' . $transaction['type'] . '\', \'' . $transaction['transaction_type'] . '\')" 
                                        title="Delete Transaction">
                                        <i class="bx bx-trash"></i>
                                    </button>';
                    }
                    
                    return $actions;
                })
                ->rawColumns(['type_badge', 'formatted_amount', 'formatted_balance', 'actions'])
                ->make(true);
        }

        //////////////////// GET DEPOSIT TRANSACTION FOR CASH COLLATERAL OF CUSTOMER(RECIEPTS)////////////////////////////////////////////
        $deposits = Receipt::where('reference', $cashCollateral->id)
            ->where('reference_type', 'Deposit')
            ->with(['bankAccount', 'user'])
            ->get()
            ->map(function ($receipt) {
                return [
                    'id' => $receipt->id,
                    'date' => $receipt->date,
                    'description' => $receipt->description,
                    'amount' => $receipt->amount,
                    'type' => 'Deposit',
                    'transaction_type' => 'receipt',
                    'bank_account' => $receipt->bankAccount->name ?? 'N/A',
                    'user' => $receipt->user->name ?? 'N/A',
                    'created_at' => $receipt->created_at,
                ];
            });

        // ////////GET WITHDRAWAL TRANSACTION FOR CASH COLLATERAL OF CUSTOMER(PAYMENTS) ////////////////
        $withdrawals = Payment::where('reference', $cashCollateral->id)
            ->where('reference_type', 'Withdrawal')
            ->with(['bankAccount', 'user'])
            ->get()
            ->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'date' => $payment->date,
                    'description' => $payment->description,
                    'amount' => $payment->amount,
                    'type' => 'Withdrawal',
                    'transaction_type' => 'payment',
                    'bank_account' => $payment->bankAccount->name ?? 'N/A',
                    'user' => $payment->user->name ?? 'N/A',
                    'created_at' => $payment->created_at,
                ];
            });

        // Combine and sort by date
        $transactions = $deposits->concat($withdrawals)->sortByDesc('date');

        // Calculate running balance
        $balance = 0;
        $transactions = $transactions->map(function ($transaction) use (&$balance) {
            if ($transaction['type'] === 'Deposit') {
                $balance += $transaction['amount'];
            } else {
                $balance -= $transaction['amount'];
            }
            $transaction['balance'] = $balance;
            return $transaction;
        });

        return view('cash_collaterals.show', compact('cashCollateral', 'transactions'));
    }

    public function edit(CashCollateral $cashCollateral)
    {
        $this->authorizeUserAccess($cashCollateral);

        $customers = Customer::where('branch_id', Auth::user()->branch_id)
            ->where('company_id', Auth::user()->company_id)
            ->get();

        $types = CashCollateralType::where('is_active', true)->get();

        return view('cash_collaterals.edit', compact('cashCollateral', 'customers', 'types'));
    }

    public function update(Request $request, CashCollateral $cashCollateral)
    {
        $this->authorizeUserAccess($cashCollateral);

        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'type_id' => 'required|exists:cash_collateral_types,id',
            'selected_account_types' => 'required|array|min:1',
            'selected_account_types.*' => 'exists:cash_collateral_types,id',
        ]);

        $data = $request->only(['customer_id', 'type_id', 'amount']);
        $user = Auth::user();
        $data['amount'] = 0;
        $data['branch_id'] = $user->branch_id;
        $data['company_id'] = $user->company_id;

        $cashCollateral->update($data);

        return redirect()->route('cash_collaterals.index')->with('success', 'Cash Deposit updated successfully.');
    }

    public function destroy(CashCollateral $cashCollateral)
    {
        $this->authorizeUserAccess($cashCollateral);

        $cashCollateral->delete();

        return redirect()->route('cash_collaterals.index')->with('success', 'Cash Collateral deleted successfully.');
    }

    // Optional helper method to ensure users can only access their branch/company records
    protected function authorizeUserAccess(CashCollateral $cashCollateral)
    {
        $user = Auth::user();

        if ($cashCollateral->branch_id !== $user->branch_id || $cashCollateral->company_id !== $user->company_id) {
            abort(403, 'Unauthorized access.');
        }
    }

    //////////////DEPOSIT FOR CASH COLLATERAL OF CUSTOMER/////////
    /**
     * Show the deposit form for cash collateral
     */
    public function deposit(Request $request, $encodedId)
    {
        $id = Hashids::decode($encodedId)[0] ?? null;

        if (!$id) {
            abort(404, 'Invalid Cash Collateral ID.');
        }

        $collateral = CashCollateral::with('customer')->findOrFail($id);
        $customer = $collateral->customer;
        $bankAccounts = BankAccount::all();

        return view('cash_collaterals.deposit', compact('bankAccounts', 'customer', 'collateral'));
    }

    protected function sendSms($phone, $message)
    {
        SmsHelper::send($phone, $message);
    }

    /**
     * PROCESS CASH COLLATERAL FOR DEPOSIT OF CUSTOMER
     */
    public function depositStore(Request $request)
    {
        $request->validate([
            'collateral_id' => 'required|string', // Still encoded, decode later
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'deposit_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'required|string|max:500',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                // Decode Hashid
                $collateralId = Hashids::decode($request->collateral_id)[0] ?? null;

                if (!$collateralId) {
                    throw new \Exception('Invalid collateral ID.');
                }

                $user = Auth::user();
                $collateral = CashCollateral::with(['customer', 'type'])->findOrFail($collateralId);
                $bankAccount = BankAccount::findOrFail($request->bank_account_id);
                $notes = $request->notes;

                // Create receipt
                $receipt = Receipt::create([
                    'reference' => $collateralId,
                    'reference_type' => 'Deposit',
                    'reference_number' => null,
                    'amount' => $request->amount,
                    'date' => $request->deposit_date,
                    'description' => $notes,
                    'user_id' => $user->id,
                    'bank_account_id' => $bankAccount->id,
                    'customer_id' => $collateral->customer_id,
                    'branch_id' => $user->branch_id,
                    'approved' => true,
                    'approved_by' => $user->id,
                    'approved_at' => now(),
                ]);

                // Create receipt item
                ReceiptItem::create([
                    'receipt_id' => $receipt->id,
                    'chart_account_id' => $collateral->type->chart_account_id,
                    'amount' => $request->amount,
                    'description' => $notes,
                ]);

                // GL Transactions

                // Debit: Bank Account
                GlTransaction::create([
                    'chart_account_id' => $bankAccount->chart_account_id,
                    'customer_id' => $collateral->customer_id,
                    'amount' => $request->amount,
                    'nature' => 'debit',
                    'transaction_id' => $receipt->id,
                    'transaction_type' => 'receipt',
                    'date' => $request->deposit_date,
                    'description' => $notes,
                    'branch_id' => $user->branch_id,
                    'user_id' => $user->id,
                ]);

                // Credit: Cash Collateral Account
                GlTransaction::create([
                    'chart_account_id' => $collateral->type->chart_account_id,
                    'customer_id' => $collateral->customer_id,
                    'amount' => $request->amount,
                    'nature' => 'credit',
                    'transaction_id' => $receipt->id,
                    'transaction_type' => 'receipt',
                    'date' => $request->deposit_date,
                    'description' => $notes,
                    'branch_id' => $user->branch_id,
                    'user_id' => $user->id,
                ]);

                // Update collateral amount
                $collateral->increment('amount', $request->amount);

                // Send SMS to customer after successful deposit
                if ($collateral->customer && $collateral->customer->phone1) {
                    $smsMessage = "Cash deposit processed successfully. Amount: TSHS" . number_format($request->amount, 2);
                    $this->sendSms($collateral->customer->phone1, $smsMessage);
                }

                // Generate thermal receipt data
                $receiptData = [
                    'receipt_id' => $receipt->id,
                    'receipt_number' => $receipt->reference_number ?? 'DEP-' . str_pad($receipt->id, 6, '0', STR_PAD_LEFT),
                    'date' => $receipt->date,
                    'customer_name' => $collateral->customer->name,
                    'deposit_type' => $collateral->type->name,
                    'amount' => $request->amount,
                    'notes' => $notes,
                    'bank_account' => $bankAccount->name . ' - ' . $bankAccount->account_number,
                    'received_by' => $user->name,
                    'branch' => $user->branch->name ?? 'N/A',
                    'time' => now()->format('H:i:s'),
                ];

                return redirect()->route('customers.show', Hashids::encode($collateral->customer_id))
                    ->with('success', 'Cash deposit processed successfully. Amount: TSHS' . number_format($request->amount, 2))
                    ->with('print_receipt', true)
                    ->with('receipt_data', $receiptData);
            });
        } catch (\Throwable $th) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to process deposit: ' . $th->getMessage()])
                ->withInput();
        }
    }

    /////////////WITHDRAWAL FOR CASH COLLATERAL OF CUSTOMER////////////
    /**
     * Show the withdrawal form for cash collateral
     */
    public function withdraw(Request $request, $encodedId)
    {
        $id = Hashids::decode($encodedId)[0] ?? null;

        if (!$id) {
            abort(404, 'Invalid Cash Collateral ID.');
        }
        $collateral = CashCollateral::with('customer')->findOrFail($id);
        $customer = $collateral->customer;
        $bankAccounts = BankAccount::all();

        return view('cash_collaterals.withdraw', compact('bankAccounts', 'customer', 'collateral'));
    }

    /**
     * PROCESS CASH COLLATERAL WITHDRAWAL FOR CUSTOMER
     */
    public function withdrawStore(Request $request)
    {
        $request->validate([
            'collateral_id' => 'required|string', // Still encoded, decode later
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'withdrawal_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                // Decode Hashid
                $collateralId = Hashids::decode($request->collateral_id)[0] ?? null;

                if (!$collateralId) {
                    throw new \Exception('Invalid collateral ID.');
                }

                $user = Auth::user();
                $collateral = CashCollateral::with(['customer', 'type'])->findOrFail($collateralId);
                $bankAccount = BankAccount::findOrFail($request->bank_account_id);
                $notes =  "Being withdraw for {$collateral->type->name}, paid to {$collateral->customer->name}, TSHS.{$request->amount}";

                // Check if withdrawal amount is available
                if ($collateral->amount < $request->amount) {
                    throw new \Exception('Insufficient collateral amount for withdrawal.');
                }

                // Create payment
                $payment = Payment::create([
                    'reference' => $collateralId,
                    'reference_type' => 'Withdrawal',
                    'reference_number' => null,
                    'amount' => $request->amount,
                    'date' => $request->withdrawal_date,
                    'description' => $notes,
                    'user_id' => $user->id,
                    'bank_account_id' => $bankAccount->id,
                    'customer_id' => $collateral->customer_id,
                    'branch_id' => $user->branch_id,
                    'approved' => true,
                    'approved_by' => $user->id,
                    'approved_at' => now(),
                ]);

                // Create payment item
                PaymentItem::create([
                    'payment_id' => $payment->id,
                    'chart_account_id' => $collateral->type->chart_account_id,
                    'amount' => $request->amount,
                    'description' => $notes,
                ]);

                // GL Transactions

                // Credit: Bank Account (money paid out)
                GlTransaction::create([
                    'chart_account_id' => $bankAccount->chart_account_id,
                    'customer_id' => $collateral->customer_id,
                    'amount' => $request->amount,
                    'nature' => 'credit',
                    'transaction_id' => $payment->id,
                    'transaction_type' => 'payment',
                    'date' => $request->withdrawal_date,
                    'description' => $notes,
                    'branch_id' => $user->branch_id,
                    'user_id' => $user->id,
                ]);

                // Debit: Cash Collateral Account (liability reduced)
                GlTransaction::create([
                    'chart_account_id' => $collateral->type->chart_account_id,
                    'customer_id' => $collateral->customer_id,
                    'amount' => $request->amount,
                    'nature' => 'debit',
                    'transaction_id' => $payment->id,
                    'transaction_type' => 'payment',
                    'date' => $request->withdrawal_date,
                    'description' => $notes,
                    'branch_id' => $user->branch_id,
                    'user_id' => $user->id,
                ]);

                // Update collateral amount
                $collateral->decrement('amount', $request->amount);

                // Send SMS to customer after successful withdrawal
                if ($collateral->customer && $collateral->customer->phone1) {
                    $smsMessage = "Cash collateral withdrawal processed successfully. Amount: TSHS" . number_format($request->amount, 2);
                    $this->sendSms($collateral->customer->phone1, $smsMessage);
                }

                return redirect()->route('customers.show', Hashids::encode($collateral->customer_id))
                    ->with('success', 'Cash collateral withdrawal processed successfully. Amount: TSHS' . number_format($request->amount, 2));
            });
        } catch (\Throwable $th) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to process withdrawal: ' . $th->getMessage()])
                ->withInput();
        }
    }

    /**
     * DELETE FUNCTION FOR CASH COLLATERAL OF CUSTOMER DEPOSIT
     */
    public function deleteReceipt($encodedId)
    {
        try {
            return DB::transaction(function () use ($encodedId) {
                // Decode the encoded receipt ID
                $decodedId = Hashids::decode($encodedId)[0] ?? null;

                if (!$decodedId) {
                    throw new \Exception('Invalid receipt ID.');
                }

                // Find the receipt
                $receipt = Receipt::with(['glTransactions', 'receiptItems'])->findOrFail($decodedId);

                // Check if this receipt is related to a cash collateral
                $collateral = CashCollateral::find($receipt->reference);

                if (!$collateral) {
                    throw new \Exception('Receipt not found or not related to cash collateral.');
                }

                // Authorize access
                $this->authorizeUserAccess($collateral);

                // Delete GL transactions
                $receipt->glTransactions()->delete();

                // Delete receipt items
                $receipt->receiptItems()->delete();

                // Subtract the receipt amount from collateral
                $collateral->decrement('amount', $receipt->amount);

                // Delete the receipt
                $receipt->delete();

                return response()->json([
                    'success' => true,
                    'message' => 'Receipt deleted successfully.',
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete receipt: ' . $e->getMessage(),
            ], 500);
        }
    }


    /**
     * DELETE FUNCTION FOR CASH COLLATERAL OF CASTOMER WITHDRAWAL
     */

    public function deletePayment($encodedId)
    {
        try {
            return DB::transaction(function () use ($encodedId) {
                // Decode the encoded payment ID
                $decodedId = Hashids::decode($encodedId)[0] ?? null;

                if (!$decodedId) {
                    throw new \Exception('Invalid payment ID.');
                }

                // Find the payment
                $payment = Payment::with(['glTransactions', 'paymentItems'])->findOrFail($decodedId);

                // Get the related cash collateral
                $collateral = CashCollateral::find($payment->reference);

                if (!$collateral) {
                    throw new \Exception('Payment not found or not related to cash collateral.');
                }

                // Authorize access
                $this->authorizeUserAccess($collateral);

                // Delete GL transactions
                $payment->glTransactions()->delete();

                // Delete payment items
                $payment->paymentItems()->delete();

                // Add back the payment amount to the collateral
                $collateral->increment('amount', $payment->amount);

                // Delete the payment
                $payment->delete();

                return response()->json([
                    'success' => true,
                    'message' => 'Payment deleted successfully.',
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete payment: ' . $e->getMessage(),
            ], 500);
        }
    }


    /**
     * SHOW EDIT FORM FOR DEPOSIT OF CASH COLLATERAL
     */
    public function editReceipt($encodedId)
    {
        try {
            // Decode the Hashids-encoded receipt ID
            $decodedId = Hashids::decode($encodedId)[0] ?? null;

            if (!$decodedId) {
                abort(404, 'Invalid receipt ID.');
            }

            // Find the receipt
            $receipt = Receipt::with(['bankAccount', 'customer'])->findOrFail($decodedId);

            // Check if this receipt is related to a cash collateral
            $collateral = CashCollateral::find($receipt->reference);

            if (!$collateral) {
                abort(404, 'Receipt not related to any cash collateral.');
            }

            // Check authorization
            $this->authorizeUserAccess($collateral);

            $bankAccounts = BankAccount::all();

            return view('cash_collaterals.edit_receipt', compact('receipt', 'collateral', 'bankAccounts'));
        } catch (\Throwable $e) {
            abort(404, 'Receipt not found or invalid.');
        }
    }

    /**
     * UPDATE DEPOSIT FOR CASH COLLATERAL OF CUSTOMER
     */
    public function updateReceipt(Request $request, $encodedId)
    {
        try {
            return DB::transaction(function () use ($request, $encodedId) {
                $decodedId = Hashids::decode($encodedId)[0] ?? null;

                if (!$decodedId) {
                    throw new \Exception('Invalid receipt ID.');
                }

                $receipt = Receipt::with('receiptItems', 'glTransactions')->findOrFail($decodedId);

                // Check if this receipt is related to a cash collateral
                $collateral = CashCollateral::with('type','customer')->find($receipt->reference);
                if (!$collateral) {
                    throw new \Exception('Receipt not found or not related to cash collateral.');
                }

                // Check authorization
                $this->authorizeUserAccess($collateral);

                $request->validate([
                    'amount' => 'required|numeric|min:0.01',
                    'date' => 'required|date',
                    'description' => 'nullable|string|max:500',
                    'bank_account_id' => 'required|exists:bank_accounts,id',
                ]);

                $oldAmount = $receipt->amount;
                $newAmount = $request->amount;

                $notes = "Being deposit for {$collateral->type->name}, paid by {$collateral->customer->name}, TSHS {$request->amount}";
                // Update receipt
                $receipt->update([
                    'amount' => $newAmount,
                    'date' => $request->date,
                    'description' => $notes,
                    'bank_account_id' => $request->bank_account_id,
                ]);

                // Update receipt items
                $receipt->receiptItems()->update([
                    'amount' => $newAmount,
                    'description' => $notes,
                ]);

                // Remove old GL transactions
                $receipt->glTransactions()->delete();

                $user = Auth::user();

                // Create new GL transactions
                GlTransaction::create([
                    'chart_account_id' => $request->bank_account_id,
                    'customer_id' => $receipt->customer_id,
                    'amount' => $newAmount,
                    'nature' => 'debit',
                    'transaction_id' => $receipt->id,
                    'transaction_type' => 'receipt',
                    'date' => $request->date,
                    'description' => $notes,
                    'branch_id' => $user->branch_id,
                    'user_id' => $user->id,
                ]);

                GlTransaction::create([
                    'chart_account_id' => $collateral->type->chart_account_id,
                    'customer_id' => $receipt->customer_id,
                    'amount' => $newAmount,
                    'nature' => 'credit',
                    'transaction_id' => $receipt->id,
                    'transaction_type' => 'receipt',
                    'date' => $request->date,
                    'description' => $notes,
                    'branch_id' => $user->branch_id,
                    'user_id' => $user->id,
                ]);

                // Adjust the collateral amount
                $collateral->decrement('amount', $oldAmount);
                $collateral->increment('amount', $newAmount);

                return redirect()->route('cash_collaterals.show', Hashids::encode($collateral->id))
                    ->with('success', 'Receipt updated successfully.');
            });
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to update receipt: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * SHOW EDIT FORM FOR CASH COLLATERAL WITHDRAWAL
     */
    public function editPayment($encodedId)
    {
        try {
            $decodedId = Hashids::decode($encodedId)[0] ?? null;

            if (!$decodedId) {
                abort(404, 'Invalid payment ID.');
            }

            $payment = Payment::with(['bankAccount', 'customer'])->findOrFail($decodedId);

            // Check if this payment is related to a cash collateral
            $collateral = CashCollateral::find($payment->reference);

            if (!$collateral) {
                abort(404, 'Payment not related to any cash collateral.');
            }

            // Check authorization
            $this->authorizeUserAccess($collateral);

            $bankAccounts = BankAccount::all();

            return view('cash_collaterals.edit_payment', compact('payment', 'collateral', 'bankAccounts'));
        } catch (\Throwable $e) {
            abort(404, 'Payment not found or invalid.');
        }
    }

    /**
     * UPDATE a WITHDRAWAL FOR CUSTOMER CASH COLLATERAL
     */

    public function updatePayment(Request $request, $encodedId)
    {
        try {
            return DB::transaction(function () use ($request, $encodedId) {
                // Decode encoded ID
                $decodedId = Hashids::decode($encodedId)[0] ?? null;
                if (!$decodedId) {
                    throw new \Exception('Invalid payment ID.');
                }

                // Load payment and related data
                $payment = Payment::with(['paymentItems', 'glTransactions'])->findOrFail($decodedId);

                // Find related collateral
                $collateral = CashCollateral::with('type','customer')->find($payment->reference);
                if (!$collateral) {
                    throw new \Exception('Payment not related to cash collateral.');
                }

                // Check authorization
                $this->authorizeUserAccess($collateral);

                // Validate request
                $request->validate([
                    'amount' => 'required|numeric|min:0.01',
                    'date' => 'required|date',
                    'description' => 'nullable|string|max:500',
                    'bank_account_id' => 'required|exists:bank_accounts,id',
                ]);

                $oldAmount = $payment->amount;
                $newAmount = $request->amount;

                $notes =  "Being withdraw for {$collateral->type->name}, paid to {$collateral->customer->name}, TSHS.{$request->amount}";

                // Update payment
                $payment->update([
                    'amount' => $newAmount,
                    'date' => $request->date,
                    'description' => $notes,
                    'bank_account_id' => $request->bank_account_id,
                ]);

                // Update payment items
                $payment->paymentItems()->update([
                    'amount' => $newAmount,
                    'description' => $notes,
                ]);

                // Delete old GL transactions
                $payment->glTransactions()->delete();

                $user = Auth::user();

                // Create new GL transactions
                // Credit bank account
                GlTransaction::create([
                    'chart_account_id' => $request->bank_account_id,
                    'customer_id' => $payment->customer_id,
                    'amount' => $newAmount,
                    'nature' => 'credit',
                    'transaction_id' => $payment->id,
                    'transaction_type' => 'payment',
                    'date' => $request->date,
                    'description' => $notes,
                    'branch_id' => $user->branch_id,
                    'user_id' => $user->id,
                ]);

                // Debit collateral account
                GlTransaction::create([
                    'chart_account_id' => $collateral->type->chart_account_id,
                    'customer_id' => $payment->customer_id,
                    'amount' => $newAmount,
                    'nature' => 'debit',
                    'transaction_id' => $payment->id,
                    'transaction_type' => 'payment',
                    'date' => $request->date,
                    'description' => $notes,
                    'branch_id' => $user->branch_id,
                    'user_id' => $user->id,
                ]);

                // Adjust collateral balance
                $collateral->increment('amount', $oldAmount);
                $collateral->decrement('amount', $newAmount);

                return redirect()->route('cash_collaterals.show', Hashids::encode($collateral->id))
                    ->with('success', 'Payment updated successfully.');
            });
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to update payment: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Print receipt for cash deposit
     */
    public function printDepositReceipt($id)
    {
        try {
            $receipt = Receipt::with([
                'customer',
                'bankAccount',
                'user',
                'receiptItems.chartAccount'
            ])->findOrFail($id);

            // Get collateral information
            $collateral = CashCollateral::with(['type', 'customer'])->find($receipt->reference);

            // Get company and branch information with null checks
            $company_name = optional(optional(auth()->user())->company)->name ?? 'Smart Finance';
            $branch_name = optional(optional(auth()->user())->branch)->name ?? 'Main Branch';

            return view('cash_collaterals.print_receipt', compact(
                'receipt', 
                'collateral', 
                'company_name', 
                'branch_name'
            ));

        } catch (\Exception $e) {
            abort(404, 'Receipt not found: ' . $e->getMessage());
        }
    }

    public function printWithdrawalReceipt($id)
    {
        try {
            $payment = Payment::with([
                'customer',
                'bankAccount',
                'user',
                'paymentItems.chartAccount'
            ])->findOrFail($id);

            // Get collateral information
            $collateral = CashCollateral::with(['type', 'customer'])->find($payment->reference);

            // Get company and branch information with null checks
            $company_name = optional(optional(auth()->user())->company)->name ?? 'Smart Finance';
            $branch_name = optional(optional(auth()->user())->branch)->name ?? 'Main Branch';

            return view('cash_collaterals.print_withdrawal_receipt', compact(
                'payment', 
                'collateral', 
                'company_name', 
                'branch_name'
            ));

        } catch (\Exception $e) {
            abort(404, 'Withdrawal receipt not found: ' . $e->getMessage());
        }
    }
}
