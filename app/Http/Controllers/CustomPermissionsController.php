<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use App\Models\Role;
use App\Models\Permission;

class CustomPermissionsController extends Controller
{
    protected $permissionModel;

    public function __construct()
    {
        $this->permissionModel = Config::get('laratrust.models.permission');

        $this->middleware('permission:view_permission')->only(['index', 'show']);
        $this->middleware('permission:create_permission')->only(['create', 'store']);
        $this->middleware('permission:edit_permission')->only(['edit', 'update']);
        $this->middleware('permission:delete_permission')->only(['destroy']);
    }

    /**
     * Display a listing of permissions.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $permissions = $this->permissionModel::orderBy('name')->paginate(15);

        // Group all permissions for display purposes
        $allPermissions = $this->permissionModel::orderBy('name')->get();
        $groupedPermissions = $allPermissions->groupBy(function($permission) {
            $parts = explode('_', $permission->name);
            return $parts[0] ?? 'general';
        });

        return view('admin.permissions.index', compact('permissions', 'groupedPermissions'));
    }

    /**
     * Show the form for creating a new permission.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.permissions.create');
    }

    /**
     * Store a newly created permission in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:permissions,name',
            'display_name' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        try {
            $permission = $this->permissionModel::create($data);

            return redirect()
                ->route('permissions.index')
                ->with('success', 'Permission created successfully');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create permission: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified permission.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $permission = $this->permissionModel::findOrFail($id);

        // Find roles using this permission
        $rolesWithPermission = DB::table(Config::get('laratrust.tables.permission_role'))
            ->where('permission_id', $id)
            ->pluck('role_id')
            ->toArray();

        $roles = Role::whereIn('id', $rolesWithPermission)->get();

        return view('admin.permissions.show', compact('permission', 'roles'));
    }

    /**
     * Show the form for editing the specified permission.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $permission = $this->permissionModel::findOrFail($id);

        // Check if this is a system permission
        $isSystemPermission = in_array($permission->name, [
            'view_role', 'create_role', 'edit_role', 'delete_role',
            'view_permission', 'create_permission', 'edit_permission', 'delete_permission'
        ]);

        return view('admin.permissions.edit', compact('permission', 'isSystemPermission'));
    }

    /**
     * Update the specified permission in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $permission = $this->permissionModel::findOrFail($id);

        // Check if this is a system permission
        $isSystemPermission = in_array($permission->name, [
            'view_role', 'create_role', 'edit_role', 'delete_role',
            'view_permission', 'create_permission', 'edit_permission', 'delete_permission'
        ]);

        if ($isSystemPermission) {
            $data = $request->validate([
                'display_name' => 'nullable|string',
                'description' => 'nullable|string',
            ]);

            // Only allow updating display_name and description for system permissions
            $permission->update([
                'display_name' => $data['display_name'],
                'description' => $data['description'],
            ]);
        } else {
            $data = $request->validate([
                'name' => 'required|string|unique:permissions,name,' . $id,
                'display_name' => 'nullable|string',
                'description' => 'nullable|string',
            ]);

            $permission->update($data);
        }

        return redirect()
            ->route('permissions.index')
            ->with('success', 'Permission updated successfully');
    }

    /**
     * Remove the specified permission from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $permission = $this->permissionModel::findOrFail($id);

        // Check if this is a system permission
        $isSystemPermission = in_array($permission->name, [
            'view_role', 'create_role', 'edit_role', 'delete_role',
            'view_permission', 'create_permission', 'edit_permission', 'delete_permission'
        ]);

        if ($isSystemPermission) {
            return redirect()
                ->back()
                ->with('error', 'System permissions cannot be deleted');
        }

        // Check if permission is used by any roles
        $rolesUsingPermission = DB::table(Config::get('laratrust.tables.permission_role'))
            ->where('permission_id', $id)
            ->count();

        if ($rolesUsingPermission > 0) {
            return redirect()
                ->back()
                ->with('warning', 'This permission is used by ' . $rolesUsingPermission . ' roles and cannot be deleted');
        }

        try {
            $permission->delete();
            return redirect()
                ->route('permissions.index')
                ->with('success', 'Permission deleted successfully');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to delete permission: ' . $e->getMessage());
        }
    }

    /**
     * Generate standard CRUD permissions for a module.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function generateForModule(Request $request)
    {
        $request->validate([
            'module_name' => 'required|string|alpha_dash',
        ]);

        $moduleName = strtolower($request->module_name);
        $displayName = ucfirst(str_replace('_', ' ', $moduleName));

        $permissions = [
            'view_' . $moduleName => 'View ' . $displayName,
            'create_' . $moduleName => 'Create ' . $displayName,
            'edit_' . $moduleName => 'Edit ' . $displayName,
            'delete_' . $moduleName => 'Delete ' . $displayName,
        ];

        $created = 0;
        $skipped = 0;

        foreach ($permissions as $name => $displayName) {
            $exists = $this->permissionModel::where('name', $name)->exists();

            if (!$exists) {
                $this->permissionModel::create([
                    'name' => $name,
                    'display_name' => $displayName,
                    'description' => $displayName,
                ]);
                $created++;
            } else {
                $skipped++;
            }
        }

        $message = $created . ' permissions created';
        if ($skipped > 0) {
            $message .= ', ' . $skipped . ' already existed and were skipped';
        }

        return redirect()
            ->route('permissions.index')
            ->with('success', $message);
    }
}
