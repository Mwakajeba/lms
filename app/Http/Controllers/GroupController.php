<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use App\Models\Branch;
use App\Models\GlTransaction;
use App\Models\Loan;
use App\Models\LoanSchedule;
use App\Models\Receipt;
use App\Models\ReceiptItem;
use App\Models\Repayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Vinkla\Hashids\Facades\Hashids;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $branchId = auth()->user()->branch_id;
        $groups = Group::with(['loanOfficer', 'branch'])
            ->where('branch_id', $branchId)
            ->where('id', '!=', 1)
            ->get();
        return view('groups.index', compact('groups'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $branchId = auth()->user()->branch_id;

        $loanOfficers = User::whereHas('roles', function ($q) {
            $q->where('name', 'like', '%officer%');
        })->get();

        // Get all customer IDs who are already members of any group
        $allGroupMemberIds = \DB::table('group_members')
            ->where('group_id', '!=', 1)
            ->pluck('customer_id')
            ->toArray();

        // Only customers in 'Borrower' category who are not in any group can be group leaders
        $groupLeaders = Customer::where('branch_id', $branchId)
            ->where('category', 'Borrower')
            ->whereNotIn('id', $allGroupMemberIds)
            ->get();

        return view('groups.create', compact('loanOfficers', 'groupLeaders'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:groups,name',
            'loan_officer' => 'required|exists:users,id',
            'minimum_members' => 'nullable|integer|min:1|max:1000000',
            'maximum_members' => 'nullable|integer|min:1|max:1000000',
            'group_leader' => [
                'nullable',
                'exists:customers,id',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $customer = Customer::find($value);
                        if (!$customer || $customer->category !== 'Borrower') {
                            $fail('The selected group leader must be a customer in the Borrower category.');
                        }
                    }
                }
            ],
            'meeting_day' => 'nullable|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday,every_day,every_week,every_month',
            'meeting_time' => 'nullable|date_format:H:i',
        ], [
            'name.required' => 'Group name is required.',
            'name.unique' => 'A group with this name already exists.',
            'loan_officer.required' => 'Please select a loan officer.',
            'loan_officer.exists' => 'The selected loan officer is invalid.',
            'group_leader.exists' => 'The selected group leader is invalid.',
            'meeting_day.in' => 'Please select a valid meeting day.',
            'meeting_time.date_format' => 'Please enter a valid meeting time.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $group = Group::create([
                'name' => $request->name,
                'loan_officer' => $request->loan_officer,
                'branch_id' => Auth::user()->branch_id,
                'minimum_members' => $request->minimum_members,
                'maximum_members' => $request->maximum_members,
                'group_leader' => $request->group_leader,
                'meeting_day' => $request->meeting_day,
                'meeting_time' => $request->meeting_time,
            ]);

            // Only create a GroupMember if a group leader was provided and is valid
            if ($request->filled('group_leader')) {
                // Check if group leader is a member in the individual group (group_id = 1)
                $individualGroupId = Group::getIndividualGroupId();
                $existing = GroupMember::where('group_id', $individualGroupId)
                    ->where('customer_id', $request->group_leader)
                    ->first();

                if ($existing) {
                    $existing->delete();
                }

                GroupMember::create([
                    'group_id' => $group->id,
                    'customer_id' => $request->group_leader,
                    'joined_date' => now()->format('Y-m-d')
                ]);
            }

            DB::commit();

            return redirect()->route('groups.index')->with('success', 'Group created successfully!');
        } catch (\Exception $e) {
            \Log::error("Group update failed", [
                "group_id" => $group->id,
                "error" => $e->getMessage(),
                "request_data" => $request->all()
            ]);
            DB::rollBack();
            \Log::error('Group creation failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create group. Please try again.')->withInput();
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
            return redirect()->route('groups.index')->withErrors(['Group not found.']);
        }

        // Only allow showing a group if its id is not 1
        if ($decoded[0] == 1) {
            return redirect()->route('groups.index')->withErrors(['Group not found.']);
        }
        $group = Group::findOrFail($decoded[0]);

        // Load related data with proper eager loading
        $group->load([
            'loanOfficer',
            'groupLeader',
            'branch',
            'members' => function ($query) {
                // Load the Customer model through the pivot table
                $query->withPivot(['joined_date', 'notes']);
            },
            'loans.customer', // Load loans with their customers
            'loans.product'   // Load loans with their products
        ]);

        // Get available groups for transfer (excluding current group and individual group)
        $availableGroups = Group::where('id', '!=', $group->id)
            ->where('id', '!=', Group::getIndividualGroupId())
            ->where('branch_id', $group->branch_id)
            ->get();

        return view('groups.show', compact('group', 'availableGroups'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($encodedId)
    {
        // Decode the ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('groups.index')->withErrors(['Group not found.']);
        }

        $group = Group::findOrFail($decoded[0]);

        $branchId = auth()->user()->branch_id;

        $loanOfficers = User::where('branch_id', $branchId)->get();

        $branchId = auth()->user()->branch_id;

        // Get all customer IDs who are already members of any group
        $allGroupMemberIds = \DB::table('group_members')->pluck('customer_id')->toArray();

        // Only customers in 'Borrower' category who are not in any group can be group leaders
        // But include the current group leader even if they're in a group (for editing existing groups)
        $currentGroupLeaderId = $group->group_leader;
        // Only allow group leader to be selected from members of this group
        $groupMemberIds = \DB::table('group_members')
            ->where('group_id', $group->id)
            ->where('group_id', '!=', 1)
            ->pluck('customer_id')
            ->toArray();

        $groupLeaders = Customer::where('branch_id', $branchId)
            ->where('category', 'Borrower')
            ->whereIn('id', $groupMemberIds)
            ->get();

        return view('groups.edit', compact('group', 'loanOfficers', 'groupLeaders'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $encodedId)
    {
        // Decode group ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('groups.index')->withErrors(['Group not found.']);
        }

        $group = Group::findOrFail($decoded[0]);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:groups,name,' . $group->id,
            'loan_officer' => 'required|exists:users,id',
            'minimum_members' => 'nullable|integer|min:1|max:1000000',
            'maximum_members' => 'nullable|integer|min:1|max:1000000',
            'group_leader' => [
                'nullable',
                'exists:customers,id',
                function ($attribute, $value, $fail) use ($group) {
                    if ($value) {
                        $customer = Customer::find($value);
                        if (!$customer || $customer->category !== 'Borrower') {
                            $fail('The selected group leader must be a customer in the Borrower category.');
                        }

                        // Check if customer is not a member of this group
                        if ($value != $group->group_leader) {
                            $isNotMemberOfThisGroup = !\DB::table('group_members')
                                ->where('customer_id', $value)
                                ->where('group_id', $group->id)
                                ->exists();
                            if ($isNotMemberOfThisGroup) {
                                $fail('The selected group leader must be a member of this group.');
                            }
                        }
                    }
                }
            ],
            'meeting_day' => 'nullable|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday,every_month,every_day,every_week',
            'meeting_time' => 'nullable|date_format:H:i',
        ], [
            'name.required' => 'Group name is required.',
            'name.unique' => 'A group with this name already exists.',
            'loan_officer.required' => 'Please select a loan officer.',
            'loan_officer.exists' => 'The selected loan officer is invalid.',
            'group_leader.exists' => 'The selected group leader is invalid.',
            'meeting_day.in' => 'Please select a valid meeting day.',
            'meeting_time.date_format' => 'Please enter a valid meeting time.',
        ]);



        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $group->update([
                'name' => $request->name,
                'loan_officer' => $request->loan_officer,
                'branch_id' => Auth::user()->branch_id,
                'minimum_members' => $request->minimum_members,
                'maximum_members' => $request->maximum_members,
                'group_leader' => $request->group_leader,
                'meeting_day' => $request->meeting_day,
                'meeting_time' => $request->meeting_time,
            ]);

            // Only create a GroupMember if a group leader was provided and is valid
            if ($request->filled('group_leader')) {
                // Check if group leader is a member in the individual group (group_id = 1)
                $individualGroupId = Group::getIndividualGroupId();
                $existing = GroupMember::where('group_id', $individualGroupId)
                    ->where('customer_id', $request->group_leader)
                    ->first();

                if ($existing) {
                    $existing->delete();
                }
                $groupMember = GroupMember::where('group_id', $group->id);

                $groupMember->update([
                    'group_id' => $group->id,
                    'customer_id' => $request->group_leader,
                    'joined_date' => now()->format('Y-m-d')
                ]);
            }

            return redirect()->route('groups.index')->with('success', 'Group updated successfully!');
        } catch (\Exception $e) {
            \Log::error("Group update failed", [
                "group_id" => $group->id,
                "error" => $e->getMessage(),
                "request_data" => $request->all()
            ]);
            return redirect()->back()->with('error', 'Failed to update group: ' . $e->getMessage())->withInput();
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
            return redirect()->route('groups.index')->withErrors(['Group not found.']);
        }

        $group = Group::findOrFail($decoded[0]);

        try {
            if ($group->loans()->count() > 0) {
                return redirect()->back()->with('error', 'Cannot delete group. It has associated loans.');
            }

            // Check for any assigned customers (members or group leader)
            $hasMembers = $group->members()->count() > 0;
            $hasGroupLeader = $group->group_leader !== null;

            if ($hasMembers || $hasGroupLeader) {
                $message = 'Cannot delete group. It has assigned customers';
                if ($hasMembers && $hasGroupLeader) {
                    $message .= ' (members and group leader)';
                } elseif ($hasMembers) {
                    $message .= ' (members)';
                } elseif ($hasGroupLeader) {
                    $message .= ' (group leader)';
                }
                $message .= '.';
                return redirect()->back()->with('error', $message);
            }

            $group->delete();
            return redirect()->route('groups.index')->with('success', 'Group deleted successfully!');
        } catch (\Exception $e) {
            \Log::error("Group deletion failed", [
                "group_id" => $group->id,
                "error" => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Failed to delete group. Please try again.');
        }
    }

    public function payment($encodedId)
    {
        // Tumia Hashids kupata Group ID na kutafuta group husika
        $ids = Hashids::decode($encodedId)[0] ?? null;
        $group = Group::findOrFail($ids);

        // Pata wateja wote walio kwenye kundi kupitia uhusiano wa `GroupMember`.
        // Kisha pakia (eager load) uhusiano wa customer, mikopo, schedules, na repayments.
        $customers = $group->members()->with([
            'customer.loans' => function ($query) {
                $query->where('status', 'Active') // Chagua mikopo iliyo "Active" tu
                    ->with(['schedule.repayments']); // Pakia schedules na repayments zake
            }
        ])->get()->pluck('customer'); // Chukua tu objects za customers

        $repaymentData = [];
        $totalAmountToPay = 0;

        foreach ($customers as $customer) {
            // Hapa tunaangalia tena ikiwa mteja ana mikopo iliyo active baada ya Eager Loading
            if ($customer->loans->isNotEmpty()) {
                $customerData = [
                    'customer' => $customer,
                    'loans' => [],
                ];

                foreach ($customer->loans as $loan) {
                    // Pata schedule ya kwanza ambayo haijalipwa kikamilifu
                    $unpaidSchedule = $loan->schedule
                        ->sortBy('due_date')
                        ->first(function ($schedule) {
                            $amountDue = $schedule->principal
                                + $schedule->interest
                                + $schedule->fee_amount
                                + $schedule->penalty_amount;

                            $totalPaid = $schedule->repayments->sum(function ($repayment) {
                                return $repayment->principal
                                    + $repayment->interest
                                    + $repayment->penalt_amount
                                    + $repayment->fee_amount;
                            });

                            // Iwe haijalipwa kabisa au imelipwa nusu
                            return $totalPaid < $amountDue;
                        });


                    if ($unpaidSchedule) {
                        $totalDue = $unpaidSchedule->principal
                            + $unpaidSchedule->interest
                            + $unpaidSchedule->penalty_amount
                            + $unpaidSchedule->fee_amount;

                        $amountAlreadyPaid = $unpaidSchedule->repayments->sum(function ($repayment) {
                            return $repayment->principal
                                + $repayment->interest
                                + $repayment->penalt_amount
                                + $repayment->fee_amount;
                        });

                        $remainingAmountToPay = $totalDue - $amountAlreadyPaid;
                        $totalAmountToPay += $remainingAmountToPay;

                        $customerData['loans'][] = [
                            'loan' => $loan,
                            'schedule' => $unpaidSchedule,
                            'amount_to_pay' => $remainingAmountToPay,
                            'installment_amount' => $unpaidSchedule->principal + $unpaidSchedule->interest,
                            'penalty_amount' => $unpaidSchedule->penalty_amount,
                            'fee_amount' => $unpaidSchedule->fee_amount,
                            'total_due' => $totalDue,
                            'amount_already_paid' => $amountAlreadyPaid,
                        ];
                    }
                }

                // Ongeza mteja kwenye data ya malipo tu ikiwa ana schedules ambazo hazijalipwa
                if (!empty($customerData['loans'])) {
                    $repaymentData[] = $customerData;
                }
            }
        }

        return view('groups.payment', compact('group', 'repaymentData', 'totalAmountToPay'));
    }
    public function groupStore(Request $request, $encodedId)
    {
        // Validation logic kwa data ya fomu
        $request->validate([
            'repayments.*.*.schedule_id' => 'required|exists:loan_schedules,id',
            'repayments.*.*.amount_paid' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Initialize arrays for bulk inserts
            $allReceiptItems = [];
            $allGlTransactions = [];

            foreach ($request->repayments as $customerId => $loans) {
                foreach ($loans as $loanId => $repaymentData) {
                    // Pata schedule husika
                    $schedule = LoanSchedule::with('loan.product', 'loan.bankAccount')->findOrFail($repaymentData['schedule_id']);

                    // Kiasi kilicholipwa sasa hivi
                    $amountPaid = (float) $repaymentData['amount_paid'];

                    // Ikiwa hakuna kiasi kilicholipwa, ruka malipo haya
                    if ($amountPaid <= 0) {
                        continue;
                    }

                    ////GET BANK ACCOUNT ID ///

                    $user = Auth::user();
                    $bankId = $schedule->loan->bankAccount->id;
                    $bankAccountId = $schedule->loan->bankAccount->id ?? null;
                    $loanProduct = $schedule->loan->loanProduct;
                    $customer = Customer::findOrFail($customerId);
                    // Pata payment order kutoka kwenye LoanProduct
                    $loanProduct = $schedule->loan->product;
                    $paymentOrder = explode(',', $loanProduct->repayment_order);

                    // Anzisha kiasi kitakacholipwa kwa kila sehemu
                    $principalPaid = 0;
                    $interestPaid = 0;
                    $penaltyPaid = 0;
                    $feePaid = 0;

                    // Pata salio lililobaki la schedule
                    $totalPaidOnSchedule = $schedule->repayments->sum(function ($r) {
                        return $r->principal + $r->interest + $r->penalt_amount + $r->fee_amount;
                    });
                    $balance = ($schedule->principal + $schedule->interest + $schedule->fee_amount + $schedule->penalty_amount) - $totalPaidOnSchedule;

                    // Hakikisha kiasi kinacholipwa hakizidi salio
                    $amountToDistribute = min($amountPaid, $balance);
                    $remainingAmount = $amountToDistribute;

                    // Gawa kiasi kulingana na payment order
                    foreach ($paymentOrder as $item) {
                        switch (trim($item)) {
                            case 'fees':
                                $feeBalance = $schedule->fee_amount - $schedule->repayments->sum('fee_amount');
                                $payment = min($remainingAmount, $feeBalance);
                                $feePaid += $payment;
                                $remainingAmount -= $payment;
                                break;
                            case 'penalties':
                                $penaltyBalance = $schedule->penalty_amount - $schedule->repayments->sum('penalt_amount');
                                $payment = min($remainingAmount, $penaltyBalance);
                                $penaltyPaid += $payment;
                                $remainingAmount -= $payment;
                                break;
                            case 'interest':
                                $interestBalance = $schedule->interest - $schedule->repayments->sum('interest');
                                $payment = min($remainingAmount, $interestBalance);
                                $interestPaid += $payment;
                                $remainingAmount -= $payment;
                                break;
                            case 'principal':
                                $principalBalance = $schedule->principal - $schedule->repayments->sum('principal');
                                $payment = min($remainingAmount, $principalBalance);
                                $principalPaid += $payment;
                                $remainingAmount -= $payment;
                                break;
                        }
                    }

                    // Hifadhi malipo kwenye `repayments` table
                    $repayment = Repayment::create([
                        'customer_id' => $customerId,
                        'loan_id' => $loanId,
                        'loan_schedule_id' => $schedule->id,
                        'principal' => $principalPaid,
                        'interest' => $interestPaid,
                        'penalt_amount' => $penaltyPaid,
                        'bank_account_id' => $bankId,
                        'fee_amount' => $feePaid,
                        'cash_deposit' => $amountPaid,
                        'due_date' => $schedule->due_date,
                        'payment_date' => now(),
                    ]);



                    // *** 3. Kuhifadhi Receipt na ReceiptItem ***
                    $notes = "Being Repayment for {$loanProduct->name} Loan from {$customer->name}, of TSHS {$amountPaid}";

                    $receipt = Receipt::create([
                        'reference' => $repayment->id,
                        'reference_type' => 'Repayment',
                        'reference_number' => null,
                        'amount' => $amountPaid,
                        'date' => now(),
                        'description' => $notes,
                        'user_id' => $user->id,
                        'bank_account_id' => $bankAccountId,
                        'customer_id' => $customerId,
                        'branch_id' => $user->branch_id,
                        'approved' => true,
                        'approved_by' => $user->id,
                        'approved_at' => now(),
                    ]);

                    if ($principalPaid > 0) {
                        $allReceiptItems[] = [
                            'receipt_id' => $receipt->id,
                            'chart_account_id' => $loanProduct->principal_receivable_account_id,
                            'amount' => $principalPaid,
                            'description' => $notes,
                        ];
                    }
                    if ($interestPaid > 0) {
                        $allReceiptItems[] = [
                            'receipt_id' => $receipt->id,
                            'chart_account_id' => $loanProduct->interest_revenue_account_id,
                            'amount' => $interestPaid,
                            'description' => $notes,
                        ];
                    }
                    if ($feePaid > 0) {
                        $allReceiptItems[] = [
                            'receipt_id' => $receipt->id,
                            'chart_account_id' => $loanProduct->fee->chart_account_id,
                            'amount' => $feePaid,
                            'description' => $notes,
                        ];
                    }
                    if ($penaltyPaid > 0) {
                        $allReceiptItems[] = [
                            'receipt_id' => $receipt->id,
                            'chart_account_id' => $loanProduct->penalty->penalty_receivables_account_id,
                            'amount' => $penaltyPaid,
                            'description' => $notes,
                        ];
                    }

                    // *** 4. Kuhifadhi GL Transactions ***
                    $bankChartAccountId = $schedule->loan->bankAccount->chart_account_id ?? null;

                    // Debit: Bank Account na kiasi chote kilicholipwa
                    $allGlTransactions[] = [
                        'chart_account_id' => $bankChartAccountId,
                        'customer_id' => $customerId,
                        'amount' => $amountPaid,
                        'nature' => 'debit',
                        'transaction_id' => $repayment->id,
                        'transaction_type' => 'Repayment',
                        'date' => now(),
                        'description' => $notes,
                        'branch_id' => $user->branch_id,
                        'user_id' => $user->id,
                    ];

                    // Credit transactions kulingana na kiasi kilicholipwa kwa kila sehemu
                    if ($principalPaid > 0) {
                        $allGlTransactions[] = [
                            'chart_account_id' => $loanProduct->principal_receivable_account_id,
                            'customer_id' => $customerId,
                            'amount' => $principalPaid,
                            'nature' => 'credit',
                            'transaction_id' => $repayment->id,
                            'transaction_type' => 'Repayment',
                            'date' => now(),
                            'description' => $notes,
                            'branch_id' => $user->branch_id,
                            'user_id' => $user->id,
                        ];
                    }

                    if ($interestPaid > 0) {
                        $allGlTransactions[] = [
                            'chart_account_id' => $loanProduct->interest_revenue_account_id,
                            'customer_id' => $customerId,
                            'amount' => $interestPaid,
                            'nature' => 'credit',
                            'transaction_id' => $repayment->id,
                            'transaction_type' => 'Repayment',
                            'date' => now(),
                            'description' => $notes,
                            'branch_id' => $user->branch_id,
                            'user_id' => $user->id,
                        ];
                    }

                    if ($feePaid > 0) {
                        $allGlTransactions[] = [
                            'chart_account_id' => $loanProduct->fee->chart_account_id,
                            'customer_id' => $customerId,
                            'amount' => $feePaid,
                            'nature' => 'credit',
                            'transaction_id' => $repayment->id,
                            'transaction_type' => 'Repayment',
                            'date' => now(),
                            'description' => $notes,
                            'branch_id' => $user->branch_id,
                            'user_id' => $user->id,
                        ];
                    }

                    if ($penaltyPaid > 0) {
                        $allGlTransactions[] = [
                            'chart_account_id' => $loanProduct->penalty->penalty_receivables_account_id,
                            'customer_id' => $customerId,
                            'amount' => $penaltyPaid,
                            'nature' => 'credit',
                            'transaction_id' => $repayment->id,
                            'transaction_type' => 'Repayment',
                            'date' => now(),
                            'description' => $notes,
                            'branch_id' => $user->branch_id,
                            'user_id' => $user->id,
                        ];
                    }
                }
            }

            // Hifadhi GL Transactions na ReceiptItems zote kwa pamoja
            if (!empty($allReceiptItems)) {
                ReceiptItem::insert($allReceiptItems);
            }
            if (!empty($allGlTransactions)) {
                GlTransaction::insert($allGlTransactions);
            }



            DB::commit();
            return redirect()->route('groups.show', $encodedId)->with('success', 'Group repayment processed successfully!');
        } catch (\Exception $e) {
            \Log::error("Group repayment failed", [
                "encoded_id" => $encodedId,
                "error" => $e->getMessage(),
                "request_data" => $request->all()
            ]);
            DB::rollBack();
            return back()->with('error', 'Failed to process repayment. ' . $e->getMessage());
        }
    }

    /**
     * Remove a member from the group and assign to individual group if eligible
     */
    public function removeMember(Request $request, $encodedId, $memberId)
    {
        // Decode group ID
        $decoded = Hashids::decode($encodedId);
        $decodeMemberId = Hashids::decode($memberId)[0] ?? null;
        info("data that come", ['memberId' => $decodeMemberId, 'encodedId' => $encodedId, 'decoded' => $decoded]);
        if (empty($decoded)) {
            \Log::warning('[GroupRemove] Group decode failed', ['encoded' => $encodedId]);
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Group not found.'], 404);
            }
            return redirect()->route('groups.index')->withErrors(['Group not found.']);
        }

        $group = Group::findOrFail($decoded[0]);
        $member = Customer::findOrFail($memberId);

        try {
            // Check if member has ongoing loans in this group
            if ($group->memberHasOngoingLoans($memberId)) {
                \Log::info('[GroupRemove] Blocked: member has ongoing loans', [
                    'member_id' => $memberId,
                    'group_id' => $group->id
                ]);
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Cannot remove member. You have active loans in this group.'], 422);
                }
                return redirect()->back()->with('error', 'Cannot remove member. They have ongoing loans in this group that must be completed first.');
            }

            // Remove member from group
            $group->members()->detach($memberId);

            // If member was group leader, clear the group leader
            if ($group->group_leader == $memberId) {
                $group->update(['group_leader' => null]);
            }

            // Assign member to individual group (group ID 1)
            $individualGroupId = Group::getIndividualGroupId();
            $individualGroup = Group::find($individualGroupId);

            if ($individualGroup) {
                $individualGroup->members()->attach($memberId, [
                    'joined_date' => now()->format('Y M D')
                ]);
            }

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Member removed and assigned to individual group successfully.']);
            }
            return redirect()->back()->with('success', 'Member removed from group and assigned to individual group successfully!');
        } catch (\Exception $e) {
            \Log::error("Member removal failed", [
                "group_id" => $group->id,
                "member_id" => $memberId,
                "error" => $e->getMessage()
            ]);
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to remove member. ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Failed to remove member. Please try again.');
        }
    }

    /**
     * Get members for transfer modal
     */
    public function getMembersForTransfer($encodedId)
    {
        // Decode group ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return response()->json(['error' => 'Group not found.'], 404);
        }

        $group = Group::findOrFail($decoded[0]);
        $members = $group->members()->get();

        $data = $members->map(function ($member) {
            return [
                'id' => $member->id,
                'name' => $member->name,
                'phone' => $member->phone1 ?? 'No phone'
            ];
        });

        return response()->json(['data' => $data]);
    }

    /**
     * Transfer a member from one group to another
     */
    public function transferMember(Request $request, $encodedId)
    {
        \Log::info('[GroupTransfer] Incoming request', [
            'encoded_group_id' => $encodedId,
            'payload' => $request->all(),
            'is_ajax' => $request->ajax(),
        ]);

        $request->validate([
            'member_id' => 'required|exists:customers,id',
            'target_group_id' => 'required|exists:groups,id',
        ]);

        // Decode group ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            \Log::warning('[GroupTransfer] Group decode failed', ['encoded' => $encodedId]);
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Group not found.'], 404);
            }
            return redirect()->route('groups.index')->withErrors(['Group not found.']);
        }

        $sourceGroup = Group::findOrFail($decoded[0]);
        $targetGroup = Group::findOrFail($request->target_group_id);
        $member = Customer::findOrFail($request->member_id);

        try {
            \Log::info('[GroupTransfer] Preconditions', [
                'source_group_id' => $sourceGroup->id,
                'target_group_id' => $targetGroup->id,
                'member_id' => $member->id,
            ]);
            // Check if member has ongoing loans in source group
            if ($sourceGroup->memberHasOngoingLoans($request->member_id)) {
                \Log::info('[GroupTransfer] Blocked: member has ongoing loans', [
                    'member_id' => $member->id,
                    'source_group_id' => $sourceGroup->id
                ]);
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Cannot transfer member. They have ongoing loans in the current group that must be completed first.'], 422);
                }
                return redirect()->back()->with('error', 'Cannot transfer member. They have ongoing loans in the current group that must be completed first.');
            }

            // Check if target group can accept more members
            if (!$targetGroup->canAcceptMoreMembers()) {
                \Log::info('[GroupTransfer] Blocked: target at capacity', [
                    'target_group_id' => $targetGroup->id
                ]);
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Target group has reached its maximum member limit.'], 422);
                }
                return redirect()->back()->with('error', 'Target group has reached its maximum member limit.');
            }

            // Remove member from source group
            $sourceGroup->members()->detach($request->member_id);
            \Log::info('[GroupTransfer] Detached member from source group', [
                'member_id' => $member->id,
                'source_group_id' => $sourceGroup->id
            ]);

            // If member was group leader in source group, clear the group leader
            if ($sourceGroup->group_leader == $request->member_id) {
                $sourceGroup->update(['group_leader' => null]);
                \Log::info('[GroupTransfer] Cleared group leader from source group', [
                    'source_group_id' => $sourceGroup->id
                ]);
            }

            // Add member to target group
            $targetGroup->members()->attach($request->member_id, [
                'joined_date' => now()->format('Y-m-d')
            ]);
            \Log::info('[GroupTransfer] Attached member to target group', [
                'member_id' => $member->id,
                'target_group_id' => $targetGroup->id
            ]);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Member transferred successfully!']);
            }
            return redirect()->back()->with('success', 'Member transferred successfully!');
        } catch (\Exception $e) {
            \Log::error("Member transfer failed", [
                "source_group_id" => $sourceGroup->id,
                "target_group_id" => $request->target_group_id,
                "member_id" => $request->member_id,
                "error" => $e->getMessage()
            ]);
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to transfer member. ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Failed to transfer member. Please try again.');
        }
    }
}
