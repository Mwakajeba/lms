<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Customer;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Vinkla\Hashids\Facades\Hashids;

class GroupMemberController extends Controller
{
    /**
     * Show the form to add members to a group.
     */
    public function create($encodedId)
    {
        // Decode the ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('groups.index')->withErrors(['Group not found.']);
        }

        $group = Group::findOrFail($decoded[0]);

        // Get customers who are not already members of this group
        $existingMemberIds = $group->members()->pluck('customer_id')->toArray();
        $branchId = auth()->user()->branch_id;

        // Get all customers who are borrowers and not in this group
        $allCustomers = Customer::with(['region', 'district'])
            ->where('category', 'Borrower')
            ->where('branch_id', $branchId)
            ->whereNotIn('id', $existingMemberIds) // Not already in this group
            ->orderBy('name')
            ->get();

        // Filter to only include customers who are:
        // 1. Not in any group, OR
        // 2. In the Individual group
        $availableCustomers = collect();

        foreach ($allCustomers as $customer) {
            $currentGroup = \DB::table('group_members')
                ->join('groups', 'group_members.group_id', '=', 'groups.id')
                ->where('group_members.customer_id', $customer->id)
                ->select('groups.name as group_name', 'groups.id as group_id')
                ->first();

            // Include customer if not in any group OR in Individual group
            if (!$currentGroup || $currentGroup->group_name === 'Individual') {
                $customer->current_group = $currentGroup;
                $availableCustomers->push($customer);
            }
        }

        // Check if this is the first member and if there's a group leader
        $isFirstMember = $group->members()->count() === 0;
        $groupLeader = null;
        if ($isFirstMember && $group->group_leader) {
            $groupLeader = Customer::find($group->group_leader);
        }

        return view('group-members.create', compact('group', 'availableCustomers', 'isFirstMember', 'groupLeader'));
    }

    /**
     * Add members to the group.
     */
    public function store(Request $request, $encodedId)
    {
        // Decode the ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('groups.index')->withErrors(['Group not found.']);
        }

        $group = Group::findOrFail($decoded[0]);

        $validator = Validator::make($request->all(), [
            'customer_ids' => 'required|array|min:1',
            'customer_ids.*' => 'exists:customers,id',
            'notes' => 'nullable|string|max:500',
        ], [
            'customer_ids.required' => 'Please select at least one customer to add.',
            'customer_ids.array' => 'Please select valid customers.',
            'customer_ids.min' => 'Please select at least one customer.',
            'customer_ids.*.exists' => 'One or more selected customers are invalid.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $customerIds = $request->customer_ids;
        $addedCount = 0;
        $errors = [];

        // Check if this is the first member and ensure group leader is included
        if ($group->members()->count() === 0 && $group->group_leader) {
            // If no members yet and there's a group leader, ensure group leader is the first member
            if (!in_array($group->group_leader, $customerIds)) {
                $groupLeader = Customer::find($group->group_leader);
                $errors[] = "Group leader '{$groupLeader->name}' must be the first member of the group.";
            }
        }

        foreach ($customerIds as $customerId) {
            // Check if customer is already a member of this group
            if ($group->members()->where('customer_id', $customerId)->exists()) {
                $customer = Customer::find($customerId);
                $errors[] = "Customer '{$customer->name}' is already a member of this group.";
                continue;
            }

            // Check if customer is in another group
            $currentGroupMember = \DB::table('group_members')
                ->join('groups', 'group_members.group_id', '=', 'groups.id')
                ->where('group_members.customer_id', $customerId)
                ->select('groups.name as group_name', 'groups.id as group_id', 'group_members.id as member_id')
                ->first();

            if ($currentGroupMember) {
                // Check if the customer is in an individual group
                if ($currentGroupMember->group_name === 'Individual') {
                    // Allow moving from individual group - remove from individual group first
                    try {
                        \DB::table('group_members')->where('id', $currentGroupMember->member_id)->delete();
                    } catch (\Exception $e) {
                        $customer = Customer::find($customerId);
                        $errors[] = "Failed to remove customer '{$customer->name}' from individual group.";
                        continue;
                    }
                } else {
                    // Customer is in a regular group - not allowed to move
                    $customer = Customer::find($customerId);
                    $errors[] = "Customer '{$customer->name}' is already a member of group '{$currentGroupMember->group_name}'. Cannot move from regular groups.";
                    continue;
                }
            }

            // Check if group can accept more members
            if (!$group->canAcceptMoreMembers()) {
                $errors[] = "This group has reached its maximum member limit ({$group->maximum_members}).";
                break;
            }

            try {
                GroupMember::create([
                    'group_id' => $group->id,
                    'customer_id' => $customerId,
                    'status' => 'active',
                    'joined_date' => now(),
                    'notes' => $request->notes,
                ]);
                $addedCount++;
            } catch (\Exception $e) {
                $customer = Customer::find($customerId);
                $errors[] = "Failed to add customer '{$customer->name}'. Please try again.";
            }
        }

        if ($addedCount > 0) {
            $message = $addedCount . ' customer(s) added successfully!';
            if (!empty($errors)) {
                $message .= ' Some customers could not be added: ' . implode(', ', $errors);
            }
            return redirect()->route('groups.show', Hashids::encode($group->id))->with('success', $message);
        } else {
            return redirect()->back()->with('error', implode(' ', $errors))->withInput();
        }
    }

    /**
     * Remove a member from the group.
     */
    public function destroy($encodedId, GroupMember $member)
    {
        // Decode the ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('groups.index')->withErrors(['Group not found.']);
        }

        $group = Group::findOrFail($decoded[0]);

        // Ensure the member belongs to this group
        if ($member->group_id !== $group->id) {
            return redirect()->back()->with('error', 'Invalid member.');
        }

        // ✅ Check kama ana mkopo
        $hasLoan = Loan::where('customer_id', $member->customer_id)
            ->where('status', 'active')
            ->exists();

        if ($hasLoan) {
            return redirect()->back()->with('error', 'Cannot remove member with active loan.');
        }

        try {
            $member->delete();
            return redirect()->route('groups.show', Hashids::encode($group->id))
                ->with('success', 'Member removed successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to remove member. Please try again.');
        }
    }

}
