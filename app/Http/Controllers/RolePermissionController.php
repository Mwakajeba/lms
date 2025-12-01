<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use App\Models\Menu;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionController extends Controller
{
    public function index()
    {
        $roles = Role::with(['permissions', 'users'])->get();
        $permissions = Permission::all();
        $activeUsers = User::where('status', 'active')->count();
        $systemRoles = Role::whereIn('name', ['super-admin', 'admin', 'manager', 'user', 'viewer'])->count();

        // Get permission groups from database
        $permissionGroups = $this->getPermissionGroupsFromDatabase();

        return view('roles.index', compact('roles', 'permissions', 'permissionGroups', 'activeUsers', 'systemRoles'));
    }

    public function create()
    {
        $permissions = Permission::all();
        $permissionGroups = $this->getPermissionGroupsFromDatabase();
        info('all permissions', ['permissions' => $permissions]);
        return view('roles.create', compact('permissions', 'permissionGroups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'description' => 'nullable|string|max:500',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        try {
            DB::beginTransaction();

            $role = Role::create([
                'name' => strtolower($request->name),
                'description' => $request->description,
            ]);

            if ($request->has('permissions')) {
                $permissions = Permission::whereIn('id', $request->permissions)->get();
                $role->syncPermissions($permissions);
            }

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Role created successfully!'
                ]);
            }

            return redirect()->route('roles.index')
                ->with('success', 'Role created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create role: ' . $e->getMessage()
                ], 422);
            }

            return back()->withInput()
                ->with('error', 'Failed to create role: ' . $e->getMessage());
        }
    }

    public function show(Role $role)
    {
        $role->load(['permissions', 'users']);
        $permissionGroups = $this->groupPermissions($role->permissions);

        return view('roles.show', compact('role', 'permissionGroups'));
    }

    public function edit(Role $role)
    {
        $permissions = Permission::all();
        $permissionGroups = $this->getPermissionGroupsFromDatabase();

        return view('roles.edit', compact('role', 'permissions', 'permissionGroups'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        try {
            DB::beginTransaction();

            // Update role basic information
            $role->update([
                'name' => strtolower($request->name),
                'description' => $request->description,
            ]);

            // Handle permissions - if no permissions are selected, sync with empty collection
            if ($request->has('permissions') && is_array($request->permissions)) {
                $permissions = Permission::whereIn('id', $request->permissions)->get();
            } else {
                $permissions = collect();
            }

            // Sync permissions (this will add new ones and remove old ones)
            $role->syncPermissions($permissions);

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Role updated successfully!',
                    'debug' => [
                        'role_id' => $role->id,
                        'permissions_count' => $permissions->count(),
                        'permissions' => $permissions->pluck('name')->toArray()
                    ]
                ]);
            }

            return redirect()->route('roles.index')
                ->with('success', 'Role updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update role: ' . $e->getMessage(),
                    'debug' => [
                        'error' => $e->getMessage(),
                        'line' => $e->getLine(),
                        'file' => $e->getFile()
                    ]
                ], 422);
            }

            return back()->withInput()
                ->with('error', 'Failed to update role: ' . $e->getMessage());
        }
    }

    public function destroy(Role $role)
    {
        // Prevent deletion of system roles
        if (in_array($role->name, ['super-admin', 'admin'])) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete system roles.'
                ], 422);
            }
            return back()->with('error', 'Cannot delete system roles.');
        }

        // Check if role has users
        if ($role->users()->count() > 0) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete role that has assigned users.'
                ], 422);
            }
            return back()->with('error', 'Cannot delete role that has assigned users.');
        }

        try {
            $role->delete();

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Role deleted successfully!'
                ]);
            }

            return redirect()->route('roles.index')
                ->with('success', 'Role deleted successfully!');

        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete role: ' . $e->getMessage()
                ], 422);
            }

            return back()->with('error', 'Failed to delete role: ' . $e->getMessage());
        }
    }

    public function assignToUser(Request $request, User $user)
    {
        $request->validate([
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id',
        ]);

        try {
            $roles = Role::whereIn('id', $request->roles)->get();
            $user->syncRoles($roles);

            return back()->with('success', 'Roles assigned to user successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to assign roles: ' . $e->getMessage());
        }
    }

    public function removeFromUser(Request $request, User $user)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        try {
            $role = Role::find($request->role_id);
            $user->removeRole($role);

            return back()->with('success', 'Role removed from user successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to remove role: ' . $e->getMessage());
        }
    }

    public function permissions()
    {
        $permissions = Permission::with('roles')->get();
        $permissionGroups = $this->groupPermissions($permissions);

        return view('roles.permissions', compact('permissions', 'permissionGroups'));
    }

    public function createPermission(Request $request)
    {
        // Normalize inputs to avoid false duplicates
        $normalizedName = strtolower(trim((string) $request->input('name')));
        $guardName = $request->input('guard_name', 'web');
        $request->merge([
            'name' => $normalizedName,
            'guard_name' => $guardName,
        ]);

        // If already exists, return idempotent success to avoid blocking UX with 422
        $existing = Permission::where('name', $normalizedName)
            ->where('guard_name', $guardName)
            ->first();
        if ($existing) {
            // Clear cached permissions so UI reflects latest list
            app(PermissionRegistrar::class)->forgetCachedPermissions();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Permission already exists.',
                    'permission' => $existing,
                ]);
            }
            return back()->with('success', 'Permission already exists.');
        }

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('permissions', 'name')->where(function ($query) use ($guardName) {
                    return $query->where('guard_name', $guardName);
                }),
            ],
            'permission_group_id' => 'nullable|exists:permission_groups,id',
            'description' => 'nullable|string|max:500',
            'guard_name' => 'required|string|max:255',
        ]);

        try {
            $permission = Permission::create([
                'name' => $normalizedName,
                'guard_name' => $guardName,
                'description' => $request->description,
                'permission_group_id' => $request->permission_group_id,
            ]);

            // Clear cached permissions so UI fetches the new one
            app(PermissionRegistrar::class)->forgetCachedPermissions();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Permission created successfully!',
                    'permission' => $permission
                ]);
            }

            return back()->with('success', 'Permission created successfully!');

        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create permission: ' . $e->getMessage()
                ], 422);
            }

            return back()->withInput()
                ->with('error', 'Failed to create permission: ' . $e->getMessage());
        }
    }

    public function deletePermission(Permission $permission)
    {
        try {
            $permission->delete();
            return back()->with('success', 'Permission deleted successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete permission: ' . $e->getMessage());
        }
    }

    /**
     * Get permission groups from database with their permissions
     */
    private function getPermissionGroupsFromDatabase()
    {
        // Get all permissions with their permission groups
        $permissions = Permission::with('permissionGroup')->get();
        
        $groups = [
            'dashboard' => [],
            'settings' => [],
            'customers' => [],
            'loan_management' => [],
            'cash_collaterals' => [],
            'accounting' => [],
            'reports' => [],
            'chat' => [],
        ];

        foreach ($permissions as $permission) {
            $groupName = $permission->permissionGroup ? $permission->permissionGroup->name : null;
            
            if ($groupName && isset($groups[$groupName])) {
                $groups[$groupName][] = $permission;
            } else {
                // Default to settings for any unmatched permissions
                $groups['settings'][] = $permission;
            }
        }

        // Remove empty groups
        return array_filter($groups);
    }

    /**
     * Group permissions by category based on their stored group or names (fallback method)
     */
    private function groupPermissions($permissions)
    {
        $groups = [
            'dashboard' => [],
            'settings' => [],
            'customers' => [],
            'loan_management' => [],
            'cash_collaterals' => [],
            'accounting' => [],
            'reports' => [],
            'chat' => [],
        ];

        foreach ($permissions as $permission) {
            // First, try to use the stored group field
            if ($permission->group && isset($groups[$permission->group])) {
                $groups[$permission->group][] = $permission;
                continue;
            }

            // Fallback to name-based grouping for existing permissions without group
            $name = strtolower($permission->name);

            if (str_contains($name, 'dashboard') || str_contains($name, 'statistic') || str_contains($name, 'kpi') || str_contains($name, 'analytics')) {
                $groups['dashboard'][] = $permission;
            } elseif (
                str_contains($name, 'setting') || str_contains($name, 'backup') ||
                str_contains($name, 'configuration') || str_contains($name, 'role') ||
                str_contains($name, 'permission') || str_contains($name, 'user') ||
                str_contains($name, 'staff') || str_contains($name, 'company') ||
                str_contains($name, 'branch')
            ) {
                $groups['settings'][] = $permission;
            } elseif (str_contains($name, 'customer')) {
                $groups['customers'][] = $permission;
            } elseif (
                str_contains($name, 'loan') || str_contains($name, 'group') ||
                str_contains($name, 'guarantor') || str_contains($name, 'disburse')
            ) {
                $groups['loan_management'][] = $permission;
            } elseif (str_contains($name, 'cash collateral')) {
                $groups['cash_collaterals'][] = $permission;
            } elseif (
                str_contains($name, 'accounting') || str_contains($name, 'journal') ||
                str_contains($name, 'bank') || str_contains($name, 'ledger') ||
                str_contains($name, 'financial') || str_contains($name, 'chart account') ||
                str_contains($name, 'supplier') || str_contains($name, 'voucher') ||
                str_contains($name, 'reconciliation') || str_contains($name, 'bill purchase') ||
                str_contains($name, 'budget') || str_contains($name, 'fee') ||
                str_contains($name, 'penalty') || str_contains($name, 'transaction')
            ) {
                $groups['accounting'][] = $permission;
            } elseif (
                str_contains($name, 'report') || str_contains($name, 'audit') ||
                str_contains($name, 'compliance') || str_contains($name, 'portfolio') ||
                str_contains($name, 'delinquency') || str_contains($name, 'statement')
            ) {
                $groups['reports'][] = $permission;
            } elseif (str_contains($name, 'chat') || str_contains($name, 'message')) {
                $groups['chat'][] = $permission;
            } elseif (str_contains($name, 'ai') || str_contains($name, 'assistant')) {
                $groups['settings'][] = $permission; // AI assistant is part of settings
            } elseif (str_contains($name, 'collection') || str_contains($name, 'payment') || str_contains($name, 'receipt')) {
                $groups['accounting'][] = $permission; // Collections are part of accounting
            } elseif (str_contains($name, 'menu')) {
                $groups['settings'][] = $permission; // Menu management is part of settings
            } else {
                // Default to settings for any unmatched permissions
                $groups['settings'][] = $permission;
            }
        }

        // Remove empty groups
        return array_filter($groups);
    }

    /**
     * Get role statistics for dashboard
     */
    public function getStats()
    {
        $stats = [
            'total_roles' => Role::count(),
            'total_permissions' => Permission::count(),
            'total_users' => User::count(),
            'system_roles' => Role::whereIn('name', ['super-admin', 'admin', 'manager', 'user', 'viewer'])->count(),
        ];

        return response()->json($stats);
    }

    // Menu Management Methods
    public function manageMenus(Role $role)
    {
        $role->load([
            'menus' => function ($query) {
                $query->with('children');
            }
        ]);

        $allMenus = Menu::with('children')
            ->whereNull('parent_id')
            ->get();

        return view('roles.manage-menus', compact('role', 'allMenus'));
    }

    public function assignMenus(Request $request, Role $role)
    {
        $request->validate([
            'menu_ids' => 'required|array',
            'menu_ids.*' => 'exists:menus,id'
        ]);

        try {
            DB::beginTransaction();

            $role->menus()->sync($request->menu_ids);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Menus assigned successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to assign menus: ' . $e->getMessage()
            ], 422);
        }
    }

    public function removeMenu(Request $request, Role $role)
    {
        $request->validate([
            'menu_id' => 'required|exists:menus,id'
        ]);

        try {
            $role->menus()->detach($request->menu_id);

            return response()->json([
                'success' => true,
                'message' => 'Menu removed successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove menu: ' . $e->getMessage()
            ], 422);
        }
    }
}

