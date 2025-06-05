<?php

namespace App\Http\Controllers;
use App\Models\Role;
use Illuminate\Support\Facades\Log;
use Laratrust\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

class RolesAssignmentController
{
    protected $rolesModel;
    protected $permissionModel;
    protected $assignPermissions;

    public function __construct()
    {
        $this->rolesModel = Config::get('laratrust.models.role');
        $this->permissionModel = Config::get('laratrust.models.permission');
        $this->assignPermissions = Config::get('laratrust.panel.assign_permissions_to_user');
    }
    public function index(Request $request)
    {
        $modelsKeys = array_keys(Config::get('laratrust.user_models'));
        $modelKey = $request->get('model') ?? $modelsKeys[0] ?? null;
        $userModel = Config::get('laratrust.user_models')[$modelKey] ?? null;

        if (!$userModel) {
            abort(404);
        }

        // Get sort parameters from request
        $sortField = $request->get('sort', 'id');
        $sortDirection = $request->get('direction', 'desc'); // Default to desc

        // Add debug logging to verify parameters are being received
        Log::info('Filter parameters received', [
            'sort' => $sortField,
            'direction' => $sortDirection,
            'search' => $request->get('search'),
            'role' => $request->get('role')
        ]);

        // Get search parameter
        $search = $request->get('search');

        // Get role filter
        $roleFilter = $request->get('role');

        // Build query
        $query = $userModel::query()->withCount(['roles', 'permissions']);

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

        // Get paginated results
        $users = $query->paginate(10)->appends($request->except('page'));

        // Use your custom view instead of the package view
        return view('admin.role-assignments.index', [
            'models' => $modelsKeys,
            'modelKey' => $modelKey,
            'users' => $users,
        ]);
    }
    public function edit(Request $request, $modelId)
    {
        $modelKey = $request->get('model');
        $userModel = Config::get('laratrust.user_models')[$modelKey] ?? null;

        if (!$userModel) {
            Session::flash('laratrust-error', 'Model was not specified in the request');
            return redirect(route('laratrust.roles-assignment.index'));
        }

        $user = $userModel::query()
            ->with(['roles:id,name', 'permissions:id,name'])
            ->findOrFail($modelId);

        $roles = $this->rolesModel::orderBy('name')->get(['id', 'name', 'display_name'])
            ->map(function ($role) use ($user) {
                $role->assigned = $user->roles
                ->pluck('id')
                    ->contains($role->id);
                $role->isRemovable = Helper::roleIsRemovable($role);

                return $role;
            });
        if ($this->assignPermissions) {
            $permissions = $this->permissionModel::orderBy('name')
                ->get(['id', 'name', 'display_name'])
                ->map(function ($permission) use ($user) {
                    $permission->assigned = $user->permissions
                        ->pluck('id')
                        ->contains($permission->id);

                    return $permission;
                });
        }


        return View::make('laratrust::panel.roles-assignment.edit', [
            'modelKey' => $modelKey,
            'roles' => $roles,
            'permissions' => $this->assignPermissions ? $permissions : null,
            'userController' => $user,
        ]);
    }

    public function getDefaultPermissions(Request $request)
    {
        try {
            // Validate input
            $roleIds = $request->input('roles', []);

            // Ensure roles exist and are valid
            $roles = Role::whereIn('id', $roleIds)->with('permissions')->get();

            // Log found roles for debugging
            Log::info('Roles found', [
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

    public function update(Request $request, $modelId)
    {
        $modelKey = $request->get('model');
        $userModel = Config::get('laratrust.user_models')[$modelKey] ?? null;

        if (!$userModel) {
            Session::flash('laratrust-error', 'Model was not specified in the request');
            return redirect()->back();
        }

        $user = $userModel::findOrFail($modelId);
        $user->syncRoles($request->get('roles') ?? []);
        if ($this->assignPermissions) {
            $user->syncPermissions($request->get('permissions') ?? []);
        }

        Session::flash('laratrust-success', 'Roles and permissions assigned successfully');
        return redirect(route('laratrust.roles-assignment.index', ['model' => $modelKey]));
    }
}
