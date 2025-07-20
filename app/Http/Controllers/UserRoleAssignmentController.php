<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;

class UserRoleAssignmentController extends Controller
{
    protected $rolesModel;
    protected $permissionModel;
    protected $assignPermissions;

    public function __construct()
    {
        $this->rolesModel = Config::get('laratrust.models.role');
        $this->permissionModel = Config::get('laratrust.models.permission');
        $this->assignPermissions = Config::get('laratrust.panel.assign_permissions_to_user', true);

        $this->middleware('permission:view_role')->only(['index', 'show']);
        $this->middleware('permission:edit_role')->only(['edit', 'update']);
    }

    /**
     * Display a listing of users with their roles.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Get sort parameters from request
        $sortField = $request->get('sort', 'id');
        $sortDirection = $request->get('direction', 'desc'); // Default to desc

        // Get search parameter
        $search = $request->get('search');

        // Get role filter
        $roleFilter = $request->get('role');

        // Build query
        $query = User::with(['roles'])->withCount(['roles', 'permissions']);

        // Apply search if provided
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Apply role filter if provided
        if ($roleFilter) {
            $query->whereHas('roles', function($q) use ($roleFilter) {
                $q->where('roles.id', $roleFilter);
            });
        }

        // Apply sorting
        if (in_array($sortField, ['name', 'email', 'roles_count', 'id'])) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('id', $sortDirection);
        }

        // Get paginated results with request parameters appended to pagination links
        $users = $query->paginate(10)->appends($request->except('page'));

        // Log the query for debugging
        Log::info('User role assignments query', [
            'sort_field' => $sortField,
            'sort_direction' => $sortDirection,
            'search' => $search,
            'role_filter' => $roleFilter,
            'user_count' => $users->total()
        ]);

        return view('admin.role-assignments.index', compact('users'));
    }

    /**
     * Show the form for editing roles of the specified user.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $user = User::with(['roles:id,name', 'permissions:id,name'])
            ->findOrFail($id);

        $roles = $this->rolesModel::orderBy('name')
            ->get(['id', 'name', 'display_name', 'description'])
            ->map(function ($role) use ($user) {
                $role->assigned = $user->roles
                    ->pluck('id')
                    ->contains($role->id);

                return $role;
            });

        if ($this->assignPermissions) {
            $permissions = $this->permissionModel::orderBy('name')
                ->get(['id', 'name', 'display_name', 'description'])
                ->map(function ($permission) use ($user) {
                    $permission->assigned = $user->permissions
                        ->pluck('id')
                        ->contains($permission->id);

                    return $permission;
                });

            // Group permissions by their name prefix
            $groupedPermissions = $permissions->groupBy(function($permission) {
                $parts = explode('_', $permission->name);
                return $parts[0] ?? 'general';
            });
        } else {
            $groupedPermissions = null;
        }

        return view('admin.role-assignments.edit', compact('user', 'roles', 'groupedPermissions'));
    }

    /**
     * Update the roles and permissions of the specified user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        try {
            DB::beginTransaction();

            // Get the roles from the request
            $roles = $request->input('roles', []);

            // Get the permissions from the request if they're being submitted
            $permissions = $this->assignPermissions ? $request->input('permissions', []) : [];

            // Use syncRoles with the user_type parameter
            // The second parameter is an array of additional pivot data
            if (!empty($roles)) {
                // Detach all roles first
                $user->roles()->detach();

                // Attach each role with the user_type
                foreach ($roles as $roleId) {
                    $user->roles()->attach($roleId, [
                        'user_type' => get_class($user)
                    ]);
                }
            } else {
                // If no roles were selected, detach all
                $user->roles()->detach();
            }

            // Handle permissions (these are working fine already)
            if ($this->assignPermissions) {
                $user->syncPermissions($permissions);
            }

            DB::commit();

            return response()->json(['success' => true, 'message' => 'User roles and permissions updated successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update user roles: ' . $e->getMessage(), [
                'user_id' => $id,
                'exception' => $e
            ]);
            return response()->json(['success' => false, 'message' => 'Failed to update user roles: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display users with specific role
     *
     * @param  int  $roleId
     * @return \Illuminate\View\View
     */
    public function usersWithRole($roleId)
    {
        $role = $this->rolesModel::findOrFail($roleId);

        $users = User::whereHas('roles', function($query) use ($roleId) {
            $query->where('id', $roleId);
        })
            ->withCount(['roles', 'permissions'])
            ->paginate(10);

        return view('admin.role-assignments.users-with-role', compact('users', 'role'));
    }

    /**
     * Get default permissions for selected roles
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDefaultPermissions(Request $request)
    {
        try {
            // Validate input
            $roleIds = $request->input('roles', []);

            // Ensure roles exist and are valid
            $roles = Role::whereIn('id', $roleIds)->with('permissions')->get();

            // Log found roles for debugging
            Log::info('Roles found for default permissions', [
                'role_ids' => $roleIds,
                'roles_count' => $roles->count()
            ]);

            // Collect unique permissions from selected roles
            $defaultPermissions = $roles->flatMap(function($role) {
                return $role->permissions->pluck('id');
            })->unique()->values();

            return response()->json([
                'permissions' => $defaultPermissions,
                'message' => 'Default permissions retrieved successfully'
            ]);

        } catch (\Exception $e) {
            // Log the full error
            Log::error('Default permissions retrieval failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'role_ids' => $roleIds ?? 'No role IDs'
            ]);

            // Return a more informative error response
            return response()->json([
                'error' => 'Failed to retrieve default permissions',
                'details' => app()->environment('local') ? $e->getMessage() : null
            ], 500);
        }
    }
}
