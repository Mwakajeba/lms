<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Penalty;
use App\Models\ChartAccount;
use Illuminate\Support\Facades\Validator;
use Vinkla\Hashids\Facades\Hashids;

class PenaltyController extends Controller
{
    public function index()
    {
        $penalties = Penalty::with(['penaltyIncomeAccount', 'penaltyReceivablesAccount', 'createdBy'])
            ->orderBy('name')
            ->get();

        $stats = [
            'total' => $penalties->count(),
            'active' => $penalties->where('status', 'active')->count(),
            'inactive' => $penalties->where('status', 'inactive')->count(),
            'fixed' => $penalties->where('penalty_type', 'fixed')->count(),
            'percentage' => $penalties->where('penalty_type', 'percentage')->count(),
            'outstanding_amount' => $penalties->where('deduction_type', 'outstanding_amount')->count(),
            'principal' => $penalties->where('deduction_type', 'principal')->count(),
        ];

        return view('accounting.penalties.index', compact('penalties', 'stats'));
    }

    public function create()
    {
        // Only chart accounts with class name Revenue for penalty income
        $penaltyIncomeAccounts = ChartAccount::whereHas('accountClassGroup.accountClass', function($q) {
            $q->where('name', 'Revenue');
        })->orderBy('account_name')->get();
        // Only chart accounts with class name Assets for penalty receivables
        $penaltyReceivablesAccounts = ChartAccount::whereHas('accountClassGroup.accountClass', function($q) {
            $q->where('name', 'Assets');
        })->orderBy('account_name')->get();
        $statusOptions = Penalty::getStatusOptions();
        $penaltyTypeOptions = Penalty::getPenaltyTypeOptions();
        $chargeFrequencyOptions = Penalty::getChargeFrequencyOptions();
        $deductionTypeOptions = Penalty::getDeductionTypeOptions();

        return view('accounting.penalties.create', compact('penaltyIncomeAccounts', 'penaltyReceivablesAccounts', 'statusOptions', 'penaltyTypeOptions', 'chargeFrequencyOptions', 'deductionTypeOptions'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'penalty_income_account_id' => 'required|exists:chart_accounts,id',
            'penalty_receivables_account_id' => 'required|exists:chart_accounts,id',
            'penalty_type' => 'required|in:fixed,percentage',
            'charge_frequency' => 'required|in:daily,one_time',
            'amount' => 'required|numeric|min:0',
            'deduction_type' => 'required|in:over_due_principal_amount,over_due_interest_amount,over_due_principal_and_interest,total_principal_amount_released',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = auth()->user();

        $penalty = Penalty::create([
            'name' => $request->name,
            'penalty_income_account_id' => $request->penalty_income_account_id,
            'penalty_receivables_account_id' => $request->penalty_receivables_account_id,
            'penalty_type' => $request->penalty_type,
            'charge_frequency' => $request->charge_frequency,
            'amount' => $request->amount,
            'deduction_type' => $request->deduction_type,
            'description' => $request->description,
            'status' => $request->status,
            'branch_id' => $user->branch_id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        return redirect()->route('accounting.penalties.index')->with('success', 'Penalty created successfully!');
    }

    public function show($encodedId)
    {
        // Decode the ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('accounting.penalties.index')->withErrors(['Penalty not found.']);
        }

        $penalty = Penalty::findOrFail($decoded[0]);
        $penalty->load(['penaltyIncomeAccount', 'penaltyReceivablesAccount', 'createdBy', 'updatedBy']);

        return view('accounting.penalties.show', compact('penalty'));
    }

    public function edit($encodedId)
    {
        // Decode the ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('accounting.penalties.index')->withErrors(['Penalty not found.']);
        }

        $penalty = Penalty::findOrFail($decoded[0]);
        // Only chart accounts with class name Revenue for penalty income
        $penaltyIncomeAccounts = ChartAccount::whereHas('accountClassGroup.accountClass', function($q) {
            $q->where('name', 'Revenue');
        })->orderBy('account_name')->get();
        // Only chart accounts with class name Assets for penalty receivables
        $penaltyReceivablesAccounts = ChartAccount::whereHas('accountClassGroup.accountClass', function($q) {
            $q->where('name', 'Assets');
        })->orderBy('account_name')->get();
        $statusOptions = Penalty::getStatusOptions();
        $penaltyTypeOptions = Penalty::getPenaltyTypeOptions();
        $chargeFrequencyOptions = Penalty::getChargeFrequencyOptions();
        $deductionTypeOptions = Penalty::getDeductionTypeOptions();

        return view('accounting.penalties.edit', compact('penalty', 'penaltyIncomeAccounts', 'penaltyReceivablesAccounts', 'statusOptions', 'penaltyTypeOptions', 'chargeFrequencyOptions', 'deductionTypeOptions'));
    }

    public function update(Request $request, $encodedId)
    {
        // Decode penalty ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('accounting.penalties.index')->withErrors(['Penalty not found.']);
        }

        $penalty = Penalty::findOrFail($decoded[0]);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'penalty_income_account_id' => 'required|exists:chart_accounts,id',
            'penalty_receivables_account_id' => 'required|exists:chart_accounts,id',
            'penalty_type' => 'required|in:fixed,percentage',
            'charge_frequency' => 'required|in:daily,one_time',
            'amount' => 'required|numeric|min:0',
            'deduction_type' => 'required|in:over_due_principal_amount,over_due_interest_amount,over_due_principal_and_interest,total_principal_amount_released',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = auth()->user();

        $penalty->update([
            'name' => $request->name,
            'penalty_income_account_id' => $request->penalty_income_account_id,
            'penalty_receivables_account_id' => $request->penalty_receivables_account_id,
            'penalty_type' => $request->penalty_type,
            'charge_frequency' => $request->charge_frequency,
            'amount' => $request->amount,
            'deduction_type' => $request->deduction_type,
            'description' => $request->description,
            'status' => $request->status,
            'branch_id' => $user->branch_id,
            'updated_by' => $user->id,
        ]);

        return redirect()->route('accounting.penalties.index')->with('success', 'Penalty updated successfully!');
    }

    public function destroy($encodedId)
    {
        // Decode the encoded ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('accounting.penalties.index')->withErrors(['Penalty not found.']);
        }

        $penalty = Penalty::findOrFail($decoded[0]);

        try {
            $penalty->delete();
            return redirect()->route('accounting.penalties.index')->with('success', 'Penalty deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete penalty. Please try again.');
        }
    }

    public function changeStatus(Request $request, $encodedId)
    {
        // Decode the encoded ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('accounting.penalties.index')->withErrors(['Penalty not found.']);
        }

        $penalty = Penalty::findOrFail($decoded[0]);

        $newStatus = $penalty->status === 'active' ? 'inactive' : 'active';
        $penalty->update(['status' => $newStatus]);

        $statusText = $newStatus === 'active' ? 'activated' : 'deactivated';
        return redirect()->route('accounting.penalties.index')->with('success', "Penalty {$statusText} successfully!");
    }
}
