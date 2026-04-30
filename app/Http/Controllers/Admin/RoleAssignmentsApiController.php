<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class RoleAssignmentsApiController extends Controller
{
    /**
     * Get role assignments list with filtering and pagination
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 15);
            $search = $request->input('search');
            $roleFilter = $request->input('role_id');
            $sortField = $request->input('sort_field', 'id');
            $sortDirection = $request->input('sort_direction', 'desc');

            $query = User::with(['roles:id,name,display_name'])
                ->withCount(['roles', 'permissions']);

            // Apply search
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('user_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Apply role filter
            if ($roleFilter) {
                $query->whereHas('roles', function($q) use ($roleFilter) {
                    $q->where('roles.id', $roleFilter);
                });
            }

            // Apply sorting
            if (in_array($sortField, ['user_name', 'email', 'roles_count', 'id'])) {
                $query->orderBy($sortField, $sortDirection);
            }

            $users = $query->paginate($perPage);

            return response()->json([
                'data' => $users->items(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total()
            ]);
        } catch (\Exception $e) {
            Log::error('Role Assignments Index Error: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to load role assignments',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get statistics for role assignments
     */
    public function getStats(): JsonResponse
    {
        try {
            $totalUsers = User::count();
            $usersWithRoles = User::has('roles')->count();
            $usersWithoutRoles = $totalUsers - $usersWithRoles;
            $usersWithPermissions = User::has('permissions')->count();

            return response()->json([
                'total_users' => $totalUsers,
                'users_with_roles' => $usersWithRoles,
                'users_without_roles' => $usersWithoutRoles,
                'users_with_permissions' => $usersWithPermissions
            ]);
        } catch (\Exception $e) {
            Log::error('Role Assignments Stats Error: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to load stats',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific user's role assignment details
     */
    public function show($id): JsonResponse
    {
        try {
            $user = User::with([
                'roles:id,name,display_name,description',
                'permissions:id,name,display_name,description'
            ])->findOrFail($id);

            return response()->json($user);
        } catch (\Exception $e) {
            Log::error('Role Assignment Show Error: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to load user',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update user's roles and permissions
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $request->validate([
                'roles' => 'array',
                'roles.*' => 'exists:roles,id',
                'permissions' => 'array',
                'permissions.*' => 'exists:permissions,id'
            ]);

            $user = User::findOrFail($id);

            DB::beginTransaction();

            try {
                // The role_user / permission_user pivots have a NOT NULL `user_type`
                // column (polymorphic relation). Plain sync(ids) only writes the
                // foreign key — must pass pivot data so user_type is populated.
                $userType = get_class($user);

                if ($request->has('roles')) {
                    $rolesSync = [];
                    foreach ((array) $request->input('roles', []) as $rid) {
                        $rolesSync[$rid] = ['user_type' => $userType];
                    }
                    $user->roles()->sync($rolesSync);
                }

                if ($request->has('permissions')) {
                    $permissionsSync = [];
                    foreach ((array) $request->input('permissions', []) as $pid) {
                        $permissionsSync[$pid] = ['user_type' => $userType];
                    }
                    $user->permissions()->sync($permissionsSync);
                }

                DB::commit();

                // Reload user with relationships
                $user->load(['roles:id,name,display_name', 'permissions:id,name,display_name']);

                return response()->json([
                    'message' => 'User roles and permissions updated successfully',
                    'user' => $user
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Role Assignment Update Error: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to update user roles',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all available roles for assignment
     */
    public function getRoles(): JsonResponse
    {
        try {
            $roles = Role::select('id', 'name', 'display_name', 'description')
                ->orderBy('name')
                ->get();

            return response()->json($roles);
        } catch (\Exception $e) {
            Log::error('Get Roles Error: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to load roles',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign default 'user' role to all users who have no roles
     */
    public function fixUsersWithoutRoles(): JsonResponse
    {
        try {
            $role = Role::where('name', 'user')->first();

            if (!$role) {
                return response()->json([
                    'error' => 'Default "user" role not found'
                ], 404);
            }

            $usersWithoutRoles = User::whereDoesntHave('roles')->get();
            $fixedCount = 0;

            $permissionIds = $role->permissions->pluck('id')->toArray();

            foreach ($usersWithoutRoles as $user) {
                $user->roles()->attach($role->id, ['user_type' => get_class($user)]);

                $permSync = [];
                foreach ($permissionIds as $pid) {
                    $permSync[$pid] = ['user_type' => get_class($user)];
                }
                $user->permissions()->sync($permSync);

                $fixedCount++;
            }

            Log::info('Fixed users without roles', [
                'fixed_count' => $fixedCount,
                'role' => $role->name,
                'admin_id' => auth()->id(),
            ]);

            return response()->json([
                'message' => "Assigned 'user' role to {$fixedCount} users",
                'fixed_count' => $fixedCount,
            ]);
        } catch (\Exception $e) {
            Log::error('Fix Users Without Roles Error: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to fix users',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all available permissions for assignment
     */
    public function getPermissions(): JsonResponse
    {
        try {
            $permissions = Permission::select('id', 'name', 'display_name', 'description')
                ->orderBy('name')
                ->get();

            return response()->json($permissions);
        } catch (\Exception $e) {
            Log::error('Get Permissions Error: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to load permissions',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
