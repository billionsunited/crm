<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    public function index()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $roles = Role::with('permissions')->get();
        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $permissions = Permission::all();
        return view('roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'required|array'
        ]);

        $role = Role::create(['name' => $request->name]);
        $role->syncPermissions($request->permissions);

        return redirect()->route('roles.index')->with('success', 'Role created successfully.');
    }

    public function edit(Role $role)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        if ($role->name === 'admin') {
            return redirect()->route('roles.index')->with('error', 'The admin role cannot be edited.');
        }

        $permissions = Permission::all();
        $rolePermissions = $role->getPermissionNames()->toArray();
        
        return view('roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        if ($role->name === 'admin') {
            return redirect()->route('roles.index')->with('error', 'The admin role cannot be modified.');
        }

        $request->validate([
            'name' => 'required|unique:roles,name,' . $role->id,
            'permissions' => 'required|array'
        ]);

        $role->update(['name' => $request->name]);
        
        // Ensure permissions are synced correctly
        $role->syncPermissions($request->permissions);
        
        // Clear cache for the role
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        if ($role->name === 'admin') {
            return redirect()->back()->with('error', 'Cannot delete Admin role.');
        }

        $role->delete();
        return redirect()->route('roles.index')->with('success', 'Role deleted successfully.');
    }
}
