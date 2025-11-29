<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use App\Models\Role;
use App\Models\Permission;

class CustomRolesController extends Controller
{
    protected $rolesModel;
    protected $permissionModel;

    public function __construct()
    {
        $this->rolesModel = Config::get('laratrust.models.role');
        $this->permissionModel = Config::get('laratrust.models.permission');

        $this->middleware('permission:view_role')->only(['index', 'show']);
        $this->middleware('permission:create_role')->only(['create', 'store']);
        $this->middleware('permission:edit_role')->only(['edit', 'update']);
        $this->middleware('permission:delete_role')->only(['destroy']);
    }

    /**
     * Display a listing of roles.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Return SPA shell - Vue Router will handle the component
        return view('admin.spa');
    }

    /**
     * Show the form for creating a new role.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $permissions = $this->permissionModel::orderBy('name')->get(['id', 'name', 'display_name', 'description']);

        // Group permissions by their name prefix
        $groupedPermissions = $permissions->groupBy(function($permission) {
            $parts = explode('_', $permission->name);
            return $parts[0] ?? 'general';
        });

        return view('admin.roles.create', compact('groupedPermissions'));
    }

    /**
     * Store a newly created role in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:roles,name',
            'display_name' => 'nullable|string',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        try {
            DB::beginTransaction();

            $role = $this->rolesModel::create([
                'name' => $data['name'],
                'display_name' => $data['display_name'],
                'description' => $data['description'],
            ]);

            $role->syncPermissions($request->get('permissions') ?? []);

            DB::commit();

            return redirect()
                ->route('roles.index')
                ->with('success', 'Role created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create role: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified role.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $role = $this->rolesModel::with('permissions:id,name,display_name,description')
            ->findOrFail($id);

        // Count users with this role
        $usersCount = DB::table(Config::get('laratrust.tables.role_user'))
            ->where(Config::get('laratrust.foreign_keys.role'), $id)
            ->count();

        return view('admin.roles.show', compact('role', 'usersCount'));
    }

    /**
     * Show the form for editing the specified role.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $role = $this->rolesModel::with('permissions:id')
            ->findOrFail($id);

        $rolePermissionIds = $role->permissions->pluck('id')->toArray();

        $permissions = $this->permissionModel::orderBy('name')
            ->get(['id', 'name', 'display_name', 'description']);

        // Group permissions by their name prefix
        $groupedPermissions = $permissions->groupBy(function($permission) {
            $parts = explode('_', $permission->name);
            return $parts[0] ?? 'general';
        });

        $isEditable = $role->name !== 'superadmin' && $role->name !== 'admin';

        return view('admin.roles.edit', compact('role', 'groupedPermissions', 'rolePermissionIds', 'isEditable'));
    }

    /**
     * Update the specified role in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $role = $this->rolesModel::findOrFail($id);

        // Prevent editing of system roles
        if ($role->name === 'superadmin' || $role->name === 'admin') {
            return redirect()
                ->back()
                ->with('error', 'System roles cannot be modified');
        }

        $data = $request->validate([
            'display_name' => 'nullable|string',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        try {
            DB::beginTransaction();

            $role->update([
                'display_name' => $data['display_name'],
                'description' => $data['description'],
            ]);

            $role->syncPermissions($request->get('permissions') ?? []);

            DB::commit();

            return redirect()
                ->route('roles.index')
                ->with('success', 'Role updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update role: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified role from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $role = $this->rolesModel::findOrFail($id);

        // Prevent deletion of system roles
        if ($role->name === 'superadmin' || $role->name === 'admin' || $role->name === 'user') {
            return redirect()
                ->back()
                ->with('error', 'System roles cannot be deleted');
        }

        $usersAssignedToRole = DB::table(Config::get('laratrust.tables.role_user'))
            ->where(Config::get('laratrust.foreign_keys.role'), $id)
            ->count();

        if ($usersAssignedToRole > 0) {
            return redirect()
                ->back()
                ->with('warning', 'This role is assigned to ' . $usersAssignedToRole . ' users and cannot be deleted');
        }

        try {
            $role->delete();
            return redirect()
                ->route('roles.index')
                ->with('success', 'Role deleted successfully');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to delete role: ' . $e->getMessage());
        }
    }
}
