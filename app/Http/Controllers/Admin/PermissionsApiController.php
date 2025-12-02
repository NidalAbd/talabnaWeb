<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class PermissionsApiController extends Controller
{
    /**
     * Get permissions statistics
     */
    public function getStats(): JsonResponse
    {
        $totalPermissions = Permission::count();

        // Group permissions by prefix
        $permissions = Permission::all();
        $grouped = $permissions->groupBy(function ($permission) {
            $parts = explode('_', $permission->name);
            return $parts[0] ?? 'general';
        });

        $stats = [
            [
                'label' => 'Total Permissions',
                'value' => $totalPermissions,
                'icon' => 'fas fa-key',
                'color' => 'info'
            ],
            [
                'label' => 'Permission Categories',
                'value' => $grouped->count(),
                'icon' => 'fas fa-layer-group',
                'color' => 'success'
            ],
            [
                'label' => 'Role Assignments',
                'value' => DB::table(Config::get('laratrust.tables.permission_role'))->count(),
                'icon' => 'fas fa-link',
                'color' => 'warning'
            ],
            [
                'label' => 'Direct User Assignments',
                'value' => DB::table(Config::get('laratrust.tables.permission_user'))->count(),
                'icon' => 'fas fa-user-lock',
                'color' => 'danger'
            ]
        ];

        return response()->json(['stats' => $stats]);
    }

    /**
     * Get paginated permissions list
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search', '');
        $category = $request->input('category', '');
        $sortBy = $request->input('sort_by', 'id');
        $sortDirection = $request->input('sort_direction', 'desc');

        $query = Permission::query();

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('display_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Apply category filter
        if ($category) {
            $query->where('name', 'like', "{$category}_%");
        }

        // Apply sorting
        $query->orderBy($sortBy, $sortDirection);

        $permissions = $query->paginate($perPage);

        // Transform data
        $transformedPermissions = $permissions->getCollection()->map(function ($permission) {
            // Count role assignments
            $roleCount = DB::table(Config::get('laratrust.tables.permission_role'))
                ->where('permission_id', $permission->id)
                ->count();

            // Determine if permission is system-protected
            $systemPermissions = [
                'view_role', 'create_role', 'edit_role', 'delete_role',
                'view_permission', 'create_permission', 'edit_permission', 'delete_permission'
            ];
            $isSystem = in_array($permission->name, $systemPermissions);

            // Get category from permission name
            $parts = explode('_', $permission->name);
            $category = $parts[0] ?? 'general';

            return [
                'id' => $permission->id,
                'name' => $permission->name,
                'display_name' => $permission->display_name ?? $permission->name,
                'description' => $permission->description ?? '',
                'category' => $category,
                'role_count' => $roleCount ?? 0,
                'is_system' => $isSystem,
                'is_deletable' => !$isSystem && $roleCount == 0,
                'created_at' => $permission->created_at ? $permission->created_at->format('Y-m-d H:i:s') : null,
            ];
        });

        // Set the transformed collection back
        $permissions->setCollection($transformedPermissions);

        return response()->json([
            'permissions' => [
                'data' => $permissions->items(),
                'current_page' => $permissions->currentPage(),
                'last_page' => $permissions->lastPage(),
                'per_page' => $permissions->perPage(),
                'total' => $permissions->total(),
            ]
        ]);
    }

    /**
     * Get permission categories
     */
    public function getCategories(): JsonResponse
    {
        $permissions = Permission::all();
        $grouped = $permissions->groupBy(function ($permission) {
            $parts = explode('_', $permission->name);
            return $parts[0] ?? 'general';
        });

        $categories = $grouped->keys()->map(function ($category) {
            return [
                'value' => $category,
                'label' => ucfirst($category)
            ];
        })->values();

        return response()->json(['categories' => $categories]);
    }

    /**
     * Generate CRUD permissions for a module
     */
    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'module_name' => 'required|string|alpha_dash|lowercase'
        ]);

        $moduleName = $request->input('module_name');
        $actions = ['view', 'create', 'edit', 'delete'];
        $created = [];
        $existing = [];

        DB::beginTransaction();
        try {
            foreach ($actions as $action) {
                $permissionName = "{$action}_{$moduleName}";

                // Check if permission already exists
                $permission = Permission::where('name', $permissionName)->first();

                if ($permission) {
                    $existing[] = $permissionName;
                } else {
                    $permission = Permission::create([
                        'name' => $permissionName,
                        'display_name' => ucfirst($action) . ' ' . ucfirst(str_replace('_', ' ', $moduleName)),
                        'description' => 'Permission to ' . $action . ' ' . str_replace('_', ' ', $moduleName),
                    ]);
                    $created[] = $permissionName;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($created) . ' permission(s) created successfully',
                'created' => $created,
                'existing' => $existing
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error generating permissions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a permission
     */
    public function destroy($id): JsonResponse
    {
        $permission = Permission::findOrFail($id);

        // Check if permission is system-protected
        $systemPermissions = [
            'view_role', 'create_role', 'edit_role', 'delete_role',
            'view_permission', 'create_permission', 'edit_permission', 'delete_permission'
        ];

        if (in_array($permission->name, $systemPermissions)) {
            return response()->json([
                'error' => 'Cannot delete system permission'
            ], 403);
        }

        // Check if permission has role assignments
        $roleCount = DB::table(Config::get('laratrust.tables.permission_role'))
            ->where('permission_id', $permission->id)
            ->count();

        if ($roleCount > 0) {
            return response()->json([
                'error' => "Cannot delete permission. It is assigned to {$roleCount} role(s)."
            ], 403);
        }

        $permission->delete();

        return response()->json([
            'success' => true,
            'message' => 'Permission deleted successfully'
        ]);
    }
}
