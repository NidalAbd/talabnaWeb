<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RolesApiController extends Controller
{
    /**
     * Get role statistics for dashboard widgets
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = [
                [
                    'label' => 'Total Roles',
                    'value' => Role::count(),
                    'icon' => 'fas fa-user-tag',
                    'color' => 'info'
                ],
                [
                    'label' => 'Permission Assignments',
                    'value' => DB::table(config('laratrust.tables.permission_role'))->count(),
                    'icon' => 'fas fa-link',
                    'color' => 'success'
                ],
                [
                    'label' => 'Administrative Roles',
                    'value' => Role::whereNotIn('name', ['user'])->count(),
                    'icon' => 'fas fa-user-shield',
                    'color' => 'warning'
                ],
                [
                    'label' => 'Admin Users',
                    'value' => DB::table(config('laratrust.tables.role_user'))
                        ->whereIn('role_id', Role::whereIn('name', ['admin', 'superadmin'])->pluck('id'))
                        ->distinct('user_id')
                        ->count(),
                    'icon' => 'fas fa-users-cog',
                    'color' => 'danger'
                ]
            ];

            return response()->json(['stats' => $stats]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load statistics',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get paginated roles list with filters
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $search = $request->input('search');
            $sortBy = $request->input('sort_by', 'id');
            $sortDirection = $request->input('sort_direction', 'desc');
            $perPage = $request->input('per_page', 10);

            // Base query
            $query = Role::withCount('permissions');

            // Apply search
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('display_name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Apply sorting
            $allowedSortFields = ['id', 'name', 'created_at', 'permissions_count'];
            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortDirection);
            }

            // Get paginated roles
            $roles = $query->paginate($perPage);

            // Transform data for Vue
            $transformedRoles = $roles->getCollection()->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'display_name' => $role->display_name ?? $role->name,
                    'description' => $role->description,
                    'permissions_count' => $role->permissions_count ?? 0,
                    'is_editable' => !in_array($role->name, ['superadmin', 'admin']),
                    'is_deletable' => !in_array($role->name, ['superadmin', 'admin', 'user']),
                    'created_at' => $role->created_at ? $role->created_at->toISOString() : null,
                ];
            });

            // Set the transformed collection back
            $roles->setCollection($transformedRoles);

            return response()->json([
                'roles' => [
                    'data' => $roles->items(),
                    'current_page' => $roles->currentPage(),
                    'last_page' => $roles->lastPage(),
                    'per_page' => $roles->perPage(),
                    'total' => $roles->total(),
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Roles API Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'error' => 'Failed to load roles',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get role details with permissions
     */
    public function show($id): JsonResponse
    {
        try {
            $role = Role::with('permissions')->findOrFail($id);

            // Group permissions by category
            $groupedPermissions = $role->permissions->groupBy(function ($permission) {
                $parts = explode('_', $permission->name);
                return count($parts) > 1 ? $parts[0] : 'general';
            });

            return response()->json([
                'role' => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'display_name' => $role->display_name ?? $role->name,
                    'description' => $role->description,
                    'permissions_count' => $role->permissions->count(),
                    'is_editable' => !in_array($role->name, ['superadmin', 'admin']),
                    'is_deletable' => !in_array($role->name, ['superadmin', 'admin', 'user']),
                    'created_at' => $role->created_at->toISOString(),
                    'updated_at' => $role->updated_at->toISOString(),
                ],
                'permissions' => $role->permissions,
                'grouped_permissions' => $groupedPermissions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Role not found',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Create a new role
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|max:100|unique:roles,name',
                'display_name' => 'nullable|string|max:100',
                'description' => 'nullable|string|max:255',
                'permissions' => 'array',
                'permissions.*' => 'exists:permissions,id',
            ]);

            DB::beginTransaction();

            $role = Role::create([
                'name' => strtolower(str_replace(' ', '_', $request->name)),
                'display_name' => $request->display_name ?? $request->name,
                'description' => $request->description,
            ]);

            // Attach permissions if provided
            if ($request->has('permissions') && count($request->permissions) > 0) {
                $role->syncPermissions($request->permissions);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Role created successfully',
                'role' => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'display_name' => $role->display_name,
                    'description' => $role->description,
                    'permissions_count' => $role->permissions()->count(),
                ]
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to create role',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a role
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $role = Role::findOrFail($id);

            // Prevent updating protected roles
            if (in_array($role->name, ['superadmin', 'admin'])) {
                return response()->json([
                    'error' => 'Cannot modify system roles'
                ], 403);
            }

            $request->validate([
                'display_name' => 'nullable|string|max:100',
                'description' => 'nullable|string|max:255',
                'permissions' => 'array',
                'permissions.*' => 'exists:permissions,id',
            ]);

            DB::beginTransaction();

            $role->update([
                'display_name' => $request->display_name ?? $role->display_name,
                'description' => $request->description ?? $role->description,
            ]);

            // Sync permissions if provided
            if ($request->has('permissions')) {
                $role->syncPermissions($request->permissions);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Role updated successfully',
                'role' => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'display_name' => $role->display_name,
                    'description' => $role->description,
                    'permissions_count' => $role->permissions()->count(),
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to update role',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all permissions for role form
     */
    public function getPermissions(): JsonResponse
    {
        try {
            $permissions = Permission::orderBy('name')->get();

            // Group permissions by category
            $grouped = $permissions->groupBy(function ($permission) {
                $parts = explode('_', $permission->name);
                return count($parts) > 1 ? $parts[0] : 'general';
            });

            return response()->json([
                'permissions' => $permissions,
                'grouped_permissions' => $grouped
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load permissions',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete role
     */
    public function destroy($id): JsonResponse
    {
        try {
            $role = Role::findOrFail($id);

            // Prevent deleting protected roles
            if (in_array($role->name, ['superadmin', 'admin', 'user'])) {
                return response()->json([
                    'error' => 'Cannot delete system roles'
                ], 403);
            }

            // Check if role has users
            $userCount = DB::table(config('laratrust.tables.role_user'))
                ->where('role_id', $role->id)
                ->count();

            if ($userCount > 0) {
                return response()->json([
                    'error' => "Cannot delete role. It is assigned to {$userCount} user(s)."
                ], 403);
            }

            $role->delete();

            return response()->json([
                'success' => true,
                'message' => 'Role deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete role',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get users with specific role
     */
    public function getUsersWithRole($roleId): JsonResponse
    {
        try {
            $role = Role::findOrFail($roleId);

            $users = $role->users()
                ->with(['photos', 'roles'])
                ->paginate(20);

            $transformedUsers = $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'user_name' => $user->user_name,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_active' => $user->is_active,
                    'avatar' => $user->photos && $user->photos->count() > 0
                        ? ($user->photos->first()->is_external
                            ? $user->photos->first()->src
                            : asset($user->photos->first()->src))
                        : asset('vendor/adminlte/dist/img/user-default.jpg'),
                    'roles' => $user->roles->map(function ($role) {
                        return [
                            'id' => $role->id,
                            'name' => $role->name,
                            'display_name' => $role->display_name ?? $role->name
                        ];
                    }),
                ];
            });

            return response()->json([
                'role' => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'display_name' => $role->display_name ?? $role->name,
                    'description' => $role->description,
                ],
                'users' => [
                    'data' => $transformedUsers,
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load users',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
