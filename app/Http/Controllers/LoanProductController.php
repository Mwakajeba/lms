<?php

namespace App\Http\Controllers;

use App\Models\LoanProduct;
use App\Models\ChartAccount;
use App\Models\Fee;
use App\Models\Penalty;
use App\Models\CashCollateralType;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Vinkla\Hashids\Facades\Hashids;

class LoanProductController extends Controller
{
    /**
     * Display a listing of loan products
     */
    public function index()
    {
        $loanProducts = LoanProduct::with([
            'principalReceivableAccount',
            'interestReceivableAccount',
            'interestRevenueAccount'
        ])->latest()->get();

        return view('loan-products.index', compact('loanProducts'));
    }

    /**
     * Show the form for creating a new loan product
     */
    public function create()
    {
        // Get chart accounts for dropdowns
        $chartAccounts = ChartAccount::all();

        // Get fees and penalties for dropdowns
        $fees = Fee::where('status', 'active')->get();
        $penalties = Penalty::where('status', 'active')->get();

        // Get cash collateral types for dropdowns
        $cashCollateralTypes = CashCollateralType::where('is_active', 1)->get();

        // Get roles for approval levels
        $roles = Role::whereNotIn('name', ['admin', 'super-admin'])->orderBy('name')->get();

        // Define options for dropdowns
        $productTypes = [
            'personal' => 'Personal Loan',
            'business' => 'Business Loan',
            'mortgage' => 'Mortgage Loan',
            'vehicle' => 'Vehicle Loan',
            'education' => 'Education Loan',
            'agriculture' => 'Agriculture Loan'
        ];

        $interestCycles = [
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly',
            'semi_annually' => 'Semi Annually',
            'annually' => 'Annually'
        ];

        $penaltycriteriaDeductions = [
            'daily_bases' => 'daily bases',
            'full_amount' => 'full amount',
        ];

        $interestMethods = [
            'flat_rate' => 'Flat Rate',
            'reducing_balance_with_equal_installment' => 'Reducing Balance with Equal Installment',
            'reducing_balance_with_equal_principal' => 'Reducing Balance with Equal Principal',
        ];

        $topUpTypes = [
            'percentage' => 'Percentage',
            'fixed_amount' => 'Fixed Amount',
            'number_of_installments' => 'Number of Installments',
            'none' => 'None'
        ];

        $cashCollateralValueTypes = [
            'percentage' => 'Percentage',
            'fixed_amount' => 'Fixed Amount'
        ];

        return view('loan-products.create', compact(
            'chartAccounts',
            'fees',
            'penalties',
            'cashCollateralTypes',
            'roles',
            'productTypes',
            'interestCycles',
            'interestMethods',
            'topUpTypes',
            'cashCollateralValueTypes',
            'penaltycriteriaDeductions'
        ));
    }

    /**
     * Store a newly created loan product
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:loan_products',
            'product_type' => 'required|string|max:100',
            'minimum_interest_rate' => 'required|numeric|min:0|max:100',
            'maximum_interest_rate' => 'required|numeric|min:0|max:100|gte:minimum_interest_rate',
            'interest_cycle' => 'required|string|max:50',
            'interest_method' => 'required|string|max:50',
            'minimum_principal' => 'required|numeric|min:0',
            'maximum_principal' => 'required|numeric|min:0|gte:minimum_principal',
            'minimum_period' => 'required|integer|min:1',
            'maximum_period' => 'required|integer|min:1|gte:minimum_period',
            'grace_period' => 'nullable|integer|min:0',
            'maximum_number_of_loans' => 'nullable|integer|min:1',
            'penalt_deduction_criteria' => 'nullable|string',
            'has_top_up' => 'boolean',
            'top_up_type' => 'nullable|required_if:has_top_up,1|string|max:50',
            'top_up_type_value' => 'nullable|required_if:top_up_type,percentage,fixed_amount|numeric|min:0',
            'has_cash_collateral' => 'boolean',
            'cash_collateral_type' => 'nullable|string|max:100',
            'cash_collateral_value_type' => 'nullable|string|max:50',
            'cash_collateral_value' => 'nullable|numeric|min:0',
            'has_approval_levels' => 'boolean',
            'approval_levels' => 'nullable|array',
            'approval_levels.*' => 'integer|exists:roles,id',
            'principal_receivable_account_id' => 'required|exists:chart_accounts,id',
            'interest_receivable_account_id' => 'required|exists:chart_accounts,id',
            'interest_revenue_account_id' => 'required|exists:chart_accounts,id',
            'direct_writeoff_account_id' => 'nullable|exists:chart_accounts,id',
            'provision_writeoff_account_id' => 'nullable|exists:chart_accounts,id',
            'income_provision_account_id' => 'nullable|exists:chart_accounts,id',
            'fees_id' => 'nullable|array',
            'fees_id.*' => 'nullable|exists:fees,id',
            'penalty_id' => 'nullable|array',
            'penalty_id.*' => 'nullable|exists:penalties,id',
            'repayment_order' => 'nullable',
            'allow_push_to_ess' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Normalize repayment_order for validation and save
        $repaymentOrderHidden = $request->input('repayment_order_hidden');
        if (is_string($repaymentOrderHidden) && strlen(trim($repaymentOrderHidden)) > 0) {
            $repaymentComponents = array_map('trim', explode(',', $repaymentOrderHidden));
        } else {
            $repaymentComponents = [];
        }

        // Validate components if provided
        if (!empty($repaymentComponents)) {
            $validComponents = ['principal', 'interest', 'fees', 'penalties'];
            foreach ($repaymentComponents as $component) {
                if (!in_array($component, $validComponents, true)) {
                    return redirect()->back()
                        ->withErrors(['repayment_order' => 'Invalid component "' . $component . '" in repayment order.'])
                        ->withInput();
                }
            }
        }

        // Normalize approval_levels from hidden field if present
        $approvalLevelsHidden = $request->input('approval_levels_hidden');
        $approvalLevels = [];
        if (is_string($approvalLevelsHidden) && strlen(trim($approvalLevelsHidden)) > 0) {
            $approvalLevels = array_values(array_filter(array_map('trim', explode(',', $approvalLevelsHidden)), function ($v) {
                return $v !== '';
            }));
        } elseif (is_array($request->approval_levels)) {
            $approvalLevels = array_map('strval', $request->approval_levels);
        }

        // Validate approval levels if provided
        if (!empty($approvalLevels)) {
            $validRoleIds = \App\Models\Role::pluck('id')->map(fn($id) => (string) $id)->toArray();
            foreach ($approvalLevels as $rid) {
                if (!in_array((string) $rid, $validRoleIds, true)) {
                    return redirect()->back()
                        ->withErrors(['approval_levels' => 'Invalid role ID "' . $rid . '" in approval levels.'])
                        ->withInput();
                }
            }
        }

        DB::beginTransaction();
        try {
            $data = $request->all();
            $data['allow_push_to_ess'] = $request->has('allow_push_to_ess');
            $data['has_cash_collateral'] = $request->has('has_cash_collateral');
            $data['has_approval_levels'] = $request->has('has_approval_levels');

            // Handle fees_ids - map from fees_id array to fees_ids
            if ($request->has('fees_id')) {
                $data['fees_ids'] = array_filter($request->input('fees_id', []), function ($value) {
                    return !empty($value);
                });
            } else {
                $data['fees_ids'] = null;
            }

            // Handle penalty_ids - map from penalty_id array to penalty_ids
            if ($request->has('penalty_id')) {
                $data['penalty_ids'] = array_filter($request->input('penalty_id', []), function ($value) {
                    return !empty($value);
                });
            } else {
                $data['penalty_ids'] = null;
            }

            // Remove the old field names to avoid confusion
            unset($data['fees_id'], $data['penalty_id']);

            // Persist normalized repayment order as comma-separated string
            $data['repayment_order'] = !empty($repaymentComponents)
                ? implode(',', $repaymentComponents)
                : null;

            // Persist normalized approval levels as comma-separated string
            $data['approval_levels'] = !empty($approvalLevels)
                ? implode(',', $approvalLevels)
                : null;

            $loanProduct = LoanProduct::create($data);

            DB::commit();

            return redirect()->route('loan-products.index')
                ->with('success', 'Loan product created successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Error creating loan product: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified loan product
     */
    public function show($encodedId)
    {
        // Decode the ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('loan-products.index')->withErrors(['Loan product not found.']);
        }

        $loanProduct = LoanProduct::findOrFail($decoded[0]);

        $loanProduct->load([
            'principalReceivableAccount',
            'interestReceivableAccount',
            'interestRevenueAccount',
            'cashCollateralType'
            // TODO: Add loan_product_id to loans table and uncomment this
            // 'loans'
        ]);

        return view('loan-products.show', compact('loanProduct'));
    }

    /**
     * Show the form for editing the specified loan product
     */
    public function edit($encodedId)
    {
        // Decode the ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('loan-products.index')->withErrors(['Loan product not found.']);
        }

        $loanProduct = LoanProduct::findOrFail($decoded[0]);

        // Get chart accounts for dropdowns
        $chartAccounts = ChartAccount::all();

        // Get fees and penalties for dropdowns
        $fees = Fee::where('status', 'active')->get();
        $penalties = Penalty::where('status', 'active')->get();

        // Get cash collateral types for dropdowns
        $cashCollateralTypes = CashCollateralType::where('is_active', 1)->get();

        // Get roles for approval levels
        $roles = Role::whereNotIn('name', ['admin', 'super-admin'])->orderBy('name')->get();

        // Define options for dropdowns
        $productTypes = [
            'personal' => 'Personal Loan',
            'business' => 'Business Loan',
            'mortgage' => 'Mortgage Loan',
            'vehicle' => 'Vehicle Loan',
            'education' => 'Education Loan',
            'agriculture' => 'Agriculture Loan'
        ];

        $interestCycles = [
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly',
            'semi_annually' => 'Semi Annually',
            'annually' => 'Annually'
        ];

        $interestMethods = [
            'flat_rate' => 'Flat Rate',
            'reducing_balance_with_equal_installment' => 'Reducing Balance with Equal Installment',
            'reducing_balance_with_equal_principal' => 'Reducing Balance with Equal Principal',
        ];
        $penaltycriteriaDeductions = [
            'daily_bases' => 'daily bases',
            'full_amount' => 'full amount',
        ];

        $topUpTypes = [
            'percentage' => 'Percentage',
            'fixed_amount' => 'Fixed Amount',
            'none' => 'None'
        ];

        $cashCollateralValueTypes = [
            'percentage' => 'Percentage',
            'fixed_amount' => 'Fixed Amount'
        ];

        return view('loan-products.edit', compact(
            'loanProduct',
            'chartAccounts',
            'fees',
            'penalties',
            'cashCollateralTypes',
            'roles',
            'productTypes',
            'interestCycles',
            'interestMethods',
            'topUpTypes',
            'cashCollateralValueTypes',
            'penaltycriteriaDeductions'
        ));
    }

    /**
     * Update the specified loan product
     */
    public function update(Request $request, $encodedId)
    {
        // Decode loan product ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('loan-products.index')->withErrors(['Loan product not found.']);
        }

        $loanProduct = LoanProduct::findOrFail($decoded[0]);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:loan_products,name,' . $loanProduct->id,
            'product_type' => 'required|string|max:100',
            'minimum_interest_rate' => 'required|numeric|min:0|max:100',
            'maximum_interest_rate' => 'required|numeric|min:0|max:100|gte:minimum_interest_rate',
            'interest_cycle' => 'required|string|max:50',
            'interest_method' => 'required|string|max:50',
            'minimum_principal' => 'required|numeric|min:0',
            'maximum_principal' => 'required|numeric|min:0|gte:minimum_principal',
            'minimum_period' => 'required|integer|min:1',
            'maximum_period' => 'required|integer|min:1|gte:minimum_period',
            'grace_period' => 'nullable|integer|min:0', // Add grace period validation
            'maximum_number_of_loans' => 'nullable|integer|min:1',
            'has_top_up' => 'boolean',
            'top_up_type' => 'nullable|required_if:has_top_up,1|string|max:50',
            'top_up_type_value' => 'nullable|required_if:top_up_type,percentage,fixed_amount|numeric|min:0',
            'has_cash_collateral' => 'boolean',
            'cash_collateral_type' => 'nullable|string|max:100',
            'cash_collateral_value_type' => 'nullable|string|max:50',
            'cash_collateral_value' => 'nullable|numeric|min:0',
            'has_approval_levels' => 'boolean',
            'approval_levels' => 'nullable|array',
            'approval_levels.*' => 'integer|exists:roles,id',
            'principal_receivable_account_id' => 'required|exists:chart_accounts,id',
            'interest_receivable_account_id' => 'required|exists:chart_accounts,id',
            'interest_revenue_account_id' => 'required|exists:chart_accounts,id',
            'direct_writeoff_account_id' => 'nullable|exists:chart_accounts,id',
            'provision_writeoff_account_id' => 'nullable|exists:chart_accounts,id',
            'income_provision_account_id' => 'nullable|exists:chart_accounts,id',
            'fees_id' => 'nullable|array',
            'fees_id.*' => 'nullable|exists:fees,id',
            'penalty_id' => 'nullable|array',
            'penalty_id.*' => 'nullable|exists:penalties,id',
            'repayment_order' => 'nullable',
            'allow_push_to_ess' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Normalize repayment_order for validation and save
        $repaymentOrderHidden = $request->input('repayment_order_hidden');
        if (is_string($repaymentOrderHidden) && strlen(trim($repaymentOrderHidden)) > 0) {
            $repaymentComponents = array_map('trim', explode(',', $repaymentOrderHidden));
        } else {
            $repaymentComponents = [];
        }

        // Validate components if provided
        if (!empty($repaymentComponents)) {
            $validComponents = ['principal', 'interest', 'fees', 'penalties'];
            foreach ($repaymentComponents as $component) {
                if (!in_array($component, $validComponents, true)) {
                    return redirect()->back()
                        ->withErrors(['repayment_order' => 'Invalid component "' . $component . '" in repayment order.'])
                        ->withInput();
                }
            }
        }

        // Normalize approval_levels from hidden field if present
        $approvalLevelsHidden = $request->input('approval_levels_hidden');
        $approvalLevels = [];
        if (is_string($approvalLevelsHidden) && strlen(trim($approvalLevelsHidden)) > 0) {
            $approvalLevels = array_values(array_filter(array_map('trim', explode(',', $approvalLevelsHidden)), function ($v) {
                return $v !== '';
            }));
        } elseif (is_array($request->approval_levels)) {
            $approvalLevels = array_map('strval', $request->approval_levels);
        }

        // Validate approval levels if provided
        if (!empty($approvalLevels)) {
            $validRoleIds = \App\Models\Role::pluck('id')->map(fn($id) => (string) $id)->toArray();
            foreach ($approvalLevels as $rid) {
                if (!in_array((string) $rid, $validRoleIds, true)) {
                    return redirect()->back()
                        ->withErrors(['approval_levels' => 'Invalid role ID "' . $rid . '" in approval levels.'])
                        ->withInput();
                }
            }
        }

        DB::beginTransaction();
        try {
            $data = $request->all();
            $data['allow_push_to_ess'] = $request->has('allow_push_to_ess');
            $data['has_cash_collateral'] = $request->has('has_cash_collateral');
            $data['has_approval_levels'] = $request->has('has_approval_levels');

            // Handle fees_ids - map from fees_id array to fees_ids
            if ($request->has('fees_id')) {
                $data['fees_ids'] = array_filter($request->input('fees_id', []), function ($value) {
                    return !empty($value);
                });
            } else {
                $data['fees_ids'] = null;
            }

            // Handle penalty_ids - map from penalty_id array to penalty_ids
            if ($request->has('penalty_id')) {
                $data['penalty_ids'] = array_filter($request->input('penalty_id', []), function ($value) {
                    return !empty($value);
                });
            } else {
                $data['penalty_ids'] = null;
            }

            // Remove the old field names to avoid confusion
            unset($data['fees_id'], $data['penalty_id']);

            // Persist normalized repayment order as comma-separated string
            $data['repayment_order'] = !empty($repaymentComponents)
                ? implode(',', $repaymentComponents)
                : null;

            // Persist normalized approval levels as comma-separated string
            $data['approval_levels'] = !empty($approvalLevels)
                ? implode(',', $approvalLevels)
                : null;

            $loanProduct->update($data);

            DB::commit();

            return redirect()->route('loan-products.index')
                ->with('success', 'Loan product updated successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Error updating loan product: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified loan product
     */
    public function destroy($encodedId)
    {
        // Decode the encoded ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('loan-products.index')->withErrors(['Loan product not found.']);
        }

        $loanProduct = LoanProduct::findOrFail($decoded[0]);

        // Check if there are any loans using this product
        if ($loanProduct->loans()->count() > 0) {
            return redirect()->route('loan-products.index')
                ->with('error', 'Cannot delete loan product. There are existing loans using this product.');
        }

        DB::beginTransaction();
        try {
            $loanProduct->delete();

            DB::commit();

            return redirect()->route('loan-products.index')
                ->with('success', 'Loan product deleted successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('loan-products.index')
                ->with('error', 'Error deleting loan product: ' . $e->getMessage());
        }
    }

    /**
     * Toggle the active status of a loan product
     */
    public function toggleStatus($encodedId)
    {
        // Decode the encoded ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('loan-products.index')->withErrors(['Loan product not found.']);
        }

        $loanProduct = LoanProduct::findOrFail($decoded[0]);

        try {
            $loanProduct->update([
                'is_active' => !$loanProduct->is_active
            ]);

            $status = $loanProduct->is_active ? 'activated' : 'deactivated';
            return redirect()->back()->with('success', "Loan product {$status} successfully!");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error updating loan product status: ' . $e->getMessage());
        }
    }
}