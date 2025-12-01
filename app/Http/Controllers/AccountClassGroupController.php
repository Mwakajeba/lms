<?php

namespace App\Http\Controllers;

use App\Models\AccountClass;
use App\Models\AccountClassGroup;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Vinkla\Hashids\Facades\Hashids;

class AccountClassGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $user = auth()->user();
        $accountClassGroups = AccountClassGroup::with('accountClass')
            ->where('company_id', $user->company_id)
            ->get();
        return view('account-class-groups.index', compact('accountClassGroups'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $accountClasses = AccountClass::all(); // Account classes are global
        return view('account-class-groups.create', compact('accountClasses'));
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'class_id' => 'required|exists:account_class,id',
            'group_code' => 'nullable|string|max:255|unique:account_class_groups,group_code',
            'name' => 'required|string|max:255|unique:account_class_groups,name',
        ]);

        $user = auth()->user();

        AccountClassGroup::create([
            'class_id' => $request->class_id,
            'group_code' => $request->group_code,
            'name' => $request->name,
            'company_id' => $user->company_id,
        ]);

        return redirect()->route('accounting.account-class-groups.index')
            ->with('success', 'Account Class Group created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($encodedId)
    {
        // Decode the ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('accounting.account-class-groups.index')->withErrors(['Account Class Group not found.']);
        }

        $accountClassGroup = AccountClassGroup::findOrFail($decoded[0]);
        $user = auth()->user();

        // Ensure the account class group belongs to the current user's company
        if ($accountClassGroup->company_id !== $user->company_id) {
            abort(403, 'Unauthorized access to this account class group.');
        }

        $accountClassGroup->load('accountClass');
        return view('account-class-groups.show', compact('accountClassGroup'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($encodedId)
    {
        // Decode the ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('accounting.account-class-groups.index')->withErrors(['Account Class Group not found.']);
        }

        $accountClassGroup = AccountClassGroup::findOrFail($decoded[0]);
        $user = auth()->user();

        // Ensure the account class group belongs to the current user's company
        if ($accountClassGroup->company_id !== $user->company_id) {
            abort(403, 'Unauthorized access to this account class group.');
        }

        $accountClasses = AccountClass::all(); // Account classes are global
        return view('account-class-groups.edit', compact('accountClassGroup', 'accountClasses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $encodedId)
    {
        // Decode account class group ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('accounting.account-class-groups.index')->withErrors(['Account Class Group not found.']);
        }

        $accountClassGroup = AccountClassGroup::findOrFail($decoded[0]);

        $request->validate([
            'class_id' => 'required|exists:account_class,id',
            'group_code' => 'nullable|string|max:255|unique:account_class_groups,group_code,' . $accountClassGroup->id,
            'name' => 'required|string|max:255|unique:account_class_groups,name,' . $accountClassGroup->id,
        ]);

        $accountClassGroup->update([
            'class_id' => $request->class_id,
            'group_code' => $request->group_code,
            'name' => $request->name,
        ]);

        return redirect()->route('accounting.account-class-groups.index')
            ->with('success', 'Account Class Group updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($encodedId)
    {
        // Decode the encoded ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('accounting.account-class-groups.index')->withErrors(['Account Class Group not found.']);
        }

        $accountClassGroup = AccountClassGroup::findOrFail($decoded[0]);
        $user = auth()->user();

        // Ensure the account class group belongs to the current user's company
        if ($accountClassGroup->company_id !== $user->company_id) {
            abort(403, 'Unauthorized access to this account class group.');
        }

        $accountClassGroup->delete();

        return redirect()->route('accounting.account-class-groups.index')
            ->with('success', 'Account Class Group deleted successfully.');
    }
}
