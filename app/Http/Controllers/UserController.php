<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Role;
use App\Rules\PasswordValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class UserController extends Controller
{
    public function __construct()
    {
        // Middleware is applied in routes/web.php
    }

    public function index(Request $request)
    {
        $query = User::with(['branch', 'roles']);

        // Optionally filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Optionally filter by role
        if ($request->has('role') && $request->role) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        $users = $query->latest()->paginate(20);
        $totalUsers = User::count();
        $activeUsers = User::where('status', 'active')->count();
        $inactiveUsers = User::where('status', 'inactive')->count();

        return view('users.index', compact('users', 'totalUsers', 'activeUsers', 'inactiveUsers'));
    }

    public function create()
    {
        // Get branches for current company
        $branches = Branch::forCompany()->active()->get();
        // Get all roles except super_admin
        $roles = Role::where('guard_name', 'web')
            ->where('name', '!=', 'super-admin')
            ->orderBy('name')
            ->get();

        return view('users.form', compact('branches', 'roles'));
    }

    public function store(Request $request)
    {
        // Log the incoming request for debugging
        \Log::info('User creation request started', [
            'request_data' => $request->except(['password', 'password_confirmation']),
            'user_id' => auth()->id(),
            'company_id' => current_company_id()
        ]);

        try {
            // Validate the request
            $validator = \Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:20|unique:users,phone,NULL,id,company_id,' . current_company_id(),
                'email' => 'nullable|email|unique:users,email,NULL,id,company_id,' . current_company_id(),
                'password' => ['required', 'confirmed', new PasswordValidation],
                'branch_id' => 'required|exists:branches,id',
                'role_id' => 'required|exists:roles,id',
                'status' => 'required|in:active,inactive,suspended',
            ]);

            if ($validator->fails()) {
                \Log::warning('User creation validation failed', [
                    'errors' => $validator->errors()->toArray(),
                    'request_data' => $request->except(['password', 'password_confirmation'])
                ]);

                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput($request->except(['password', 'password_confirmation']));
            }

            \Log::info('User creation validation passed');

            // Get company_id from the selected branch
            $branch = Branch::find($request->branch_id);
            if (!$branch) {
                \Log::error('Branch not found during user creation', [
                    'branch_id' => $request->branch_id,
                    'company_id' => current_company_id()
                ]);

                return redirect()->back()
                    ->withErrors(['branch_id' => 'Selected branch not found.'])
                    ->withInput($request->except(['password', 'password_confirmation']));
            }

            $companyId = $branch->company_id;

            // Verify the role exists
            $role = Role::find($request->role_id);
            if (!$role) {
                \Log::error('Role not found during user creation', [
                    'role_id' => $request->role_id
                ]);

                return redirect()->back()
                    ->withErrors(['role_id' => 'Selected role not found.'])
                    ->withInput($request->except(['password', 'password_confirmation']));
            }

            \Log::info('Creating user with validated data', [
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'branch_id' => $request->branch_id,
                'role_id' => $request->role_id,
                'status' => $request->status
            ]);

            // Create the user
            $user = User::create([
                'name' => $request->name,
                'phone' => $this->formatPhoneNumber($request->phone),
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'company_id' => current_company_id(),
                'branch_id' => $request->branch_id,
                'status' => $request->status ?? 'active',
                'is_active' => $request->status === 'active' ? 'yes' : 'no',
            ]);

            // Generate user_id like US00001
            $user->user_id = 'US' . str_pad($user->id, 5, '0', STR_PAD_LEFT);
            $user->save();

            \Log::info('User created successfully', [
                'user_id' => $user->id,
                'user_name' => $user->name
            ]);

            // Assign the role
            $user->assignRole($role);

            \Log::info('Role assigned successfully', [
                'user_id' => $user->id,
                'role_id' => $role->id,
                'role_name' => $role->name
            ]);

            return redirect()->route('users.index')
                ->with('success', 'User created successfully!');

        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Database error during user creation', [
                'error' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
                'request_data' => $request->except(['password', 'password_confirmation'])
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Database error occurred. Please try again.'])
                ->withInput($request->except(['password', 'password_confirmation']));

        } catch (\Exception $e) {
            \Log::error('Unexpected error during user creation', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['password', 'password_confirmation'])
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'An unexpected error occurred. Please try again.'])
                ->withInput($request->except(['password', 'password_confirmation']));
        }
    }

    public function show(User $user)
    {
        // Ensure user belongs to current company
        if ($user->company_id !== current_company_id()) {
            abort(403, 'Unauthorized access.');
        }

        // Load user relationships
        $user->load(['branch', 'company', 'roles', 'permissions']);

        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        // Ensure user belongs to current company
        if ($user->company_id !== current_company_id()) {
            abort(403, 'Unauthorized access.');
        }

        $branches = Branch::forCompany()->active()->get();
        $roles = Role::where('guard_name', 'web')->orderBy('name')->get();
        $user->load('roles');

        return view('users.form', compact('user', 'branches', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        // Ensure user belongs to current company
        if ($user->company_id !== current_company_id()) {
            abort(403, 'Unauthorized access.');
        }

        // Log the incoming request for debugging
        \Log::info('User update request started', [
            'user_id' => $user->id,
            'request_data' => $request->except(['password', 'password_confirmation']),
            'updated_by' => auth()->id(),
            'company_id' => current_company_id()
        ]);

        try {
            // Custom validation for email to handle existing email
            $emailRules = 'nullable|email';
            if ($request->email !== $user->email) {
                $emailRules .= '|unique:users,email,' . $user->id . ',id,company_id,' . current_company_id();
            }

            // Validate the request
            $validator = \Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:20|unique:users,phone,' . $user->id . ',id,company_id,' . current_company_id(),
                'email' => $emailRules,
                'password' => 'nullable|string|min:8|confirmed',
                'branch_id' => 'required|exists:branches,id',
                'role_id' => 'required|exists:roles,id',
                'status' => 'required|in:active,inactive,suspended',
            ]);

            if ($validator->fails()) {
                \Log::warning('User update validation failed', [
                    'user_id' => $user->id,
                    'errors' => $validator->errors()->toArray(),
                    'request_data' => $request->except(['password', 'password_confirmation'])
                ]);

                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput($request->except(['password', 'password_confirmation']));
            }

            \Log::info('User update validation passed', ['user_id' => $user->id]);

            // Get company_id from the selected branch
            $branch = Branch::find($request->branch_id);
            if (!$branch) {
                \Log::error('Branch not found during user update', [
                    'user_id' => $user->id,
                    'branch_id' => $request->branch_id,
                    'company_id' => current_company_id()
                ]);

                return redirect()->back()
                    ->withErrors(['branch_id' => 'Selected branch not found.'])
                    ->withInput($request->except(['password', 'password_confirmation']));
            }

            $companyId = $branch->company_id;

            // Verify the role exists
            $role = Role::find($request->role_id);
            if (!$role) {
                \Log::error('Role not found during user update', [
                    'user_id' => $user->id,
                    'role_id' => $request->role_id
                ]);

                return redirect()->back()
                    ->withErrors(['role_id' => 'Selected role not found.'])
                    ->withInput($request->except(['password', 'password_confirmation']));
            }

            \Log::info('Updating user with validated data', [
                'user_id' => $user->id,
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'branch_id' => $request->branch_id,
                'role_id' => $request->role_id,
                'status' => $request->status
            ]);

            $userData = [
                'name' => $request->name,
                'phone' => $this->formatPhoneNumber($request->phone),
                'email' => $request->email,
                'branch_id' => $request->branch_id,
                'company_id' => $companyId,
                'status' => $request->status,
                'is_active' => $request->status === 'active' ? 'yes' : 'no',
            ];

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
                \Log::info('Password will be updated for user', ['user_id' => $user->id]);
            }

            $user->update($userData);

            \Log::info('User updated successfully', [
                'user_id' => $user->id,
                'user_name' => $user->name
            ]);

            // Sync roles (remove all existing and assign the new one)
            $user->syncRoles([$role]);

            \Log::info('Role synced successfully', [
                'user_id' => $user->id,
                'role_id' => $role->id,
                'role_name' => $role->name
            ]);

            return redirect()->route('users.index')
                ->with('success', 'User updated successfully!');

        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Database error during user update', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
                'request_data' => $request->except(['password', 'password_confirmation'])
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Database error occurred. Please try again.'])
                ->withInput($request->except(['password', 'password_confirmation']));

        } catch (\Exception $e) {
            \Log::error('Unexpected error during user update', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['password', 'password_confirmation'])
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'An unexpected error occurred. Please try again.'])
                ->withInput($request->except(['password', 'password_confirmation']));
        }
    }

    public function destroy(User $user)
    {
        // Ensure user belongs to current company
        if ($user->company_id !== current_company_id()) {
            abort(403, 'Unauthorized access.');
        }

        // Prevent deletion of own account
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'You cannot delete your own account.');
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully!');
    }

    public function profile()
    {
        $user = auth()->user();
        $user->load(['branch', 'company', 'roles']);

        return view('users.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        // Custom validation for email to handle existing email
        $emailRules = 'nullable|email';
        if ($request->email !== $user->email) {
            $emailRules .= '|unique:users,email,' . $user->id . ',id,company_id,' . current_company_id();
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:users,phone,' . $user->id . ',id,company_id,' . current_company_id(),
            'email' => $emailRules,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $userData = [
            'name' => $request->name,
            'phone' => $this->formatPhoneNumber($request->phone),
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $user->update($userData);

        return redirect()->route('users.profile')->with('success', 'Profile updated successfully!');
    }

    public function changeStatus(User $user)
    {
        // Ensure user belongs to current company
        if ($user->company_id !== current_company_id()) {
            abort(403, 'Unauthorized access.');
        }

        $newStatus = $user->status === 'active' ? 'inactive' : 'active';
        $user->update([
            'status' => $newStatus,
            'is_active' => $newStatus === 'active' ? 'yes' : 'no'
        ]);

        return redirect()->route('users.index')->with('success', "User status changed to {$newStatus}!");
    }

    public function assignRoles(Request $request, User $user)
    {
        // Ensure user belongs to current company
        if ($user->company_id !== current_company_id()) {
            abort(403, 'Unauthorized access.');
        }

        $request->validate([
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id',
        ]);

        $roles = Role::whereIn('id', $request->roles)->get();
        $user->syncRoles($roles);

        return redirect()->route('users.edit', $user)->with('success', 'Roles assigned successfully!');
    }

    /**
     * Format phone number to 255 format
     *
     * @param string $phone
     * @return string
     */
    /**
     * Assign branches to a user (AJAX)
     */
    public function assignBranches(Request $request, User $user)
    {
        $request->validate([
            'branches' => 'array',
            'branches.*' => 'exists:branches,id',
        ]);

        $user->branches()->sync($request->branches ?? []);

        return response()->json([
            'success' => true,
            'message' => 'Branches assigned successfully.'
        ]);
    }

    private function formatPhoneNumber($phone)
    {
        // Remove any spaces, dashes, or other characters
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // If starts with +255, remove the +
        if (str_starts_with($phone, '+255')) {
            return substr($phone, 1);
        }

        // If starts with 0, remove 0 and add 255
        if (str_starts_with($phone, '0')) {
            return '255' . substr($phone, 1);
        }

        // If already starts with 255, return as is
        if (str_starts_with($phone, '255')) {
            return $phone;
        }

        // If it's a 9-digit number (Tanzania mobile), add 255
        if (strlen($phone) === 9) {
            return '255' . $phone;
        }

        // Return as is if no pattern matches
        return $phone;
    }
}