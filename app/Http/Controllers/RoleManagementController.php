<?php

namespace App\Http\Controllers;

use App\Helpers\RolePermission;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleManagementController extends Controller
{
    /**
     * Display the role management page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get roles from database
        $roles = Role::all();

        return view('settings.role-management', [
            'roles' => $roles
        ]);
    }

    /**
     * Show the form for creating a new role.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Get all permissions grouped by module
        $permissions = Permission::getGroupedPermissions();
        
        return view('settings.role-create', [
            'permissions' => $permissions
        ]);
    }
    
    /**
     * Store a newly created role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'role_name' => 'required|string|max:255',
            'role_description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
            'permissions' => 'array',
        ]);
        
        // Create new role
        $role = Role::create([
            'name' => $request->role_name,
            'description' => $request->role_description,
            'status' => $request->status,
            'created_by' => Auth::user()->name,
        ]);
        
        // Attach permissions to role
        if ($request->has('permissions')) {
            $role->permissions()->attach($request->permissions);
        }
        
        return redirect()->route('role.management')
            ->with('success', 'Role created successfully!');
    }
    
    /**
     * Display the specified role.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        // Find the role by ID
        $role = Role::with('permissions')->findOrFail($id);
        
        // Get all permissions grouped by module for display
        $permissionGroups = Permission::getGroupedPermissions();
        
        // Format permissions for view
        $permissions = [];
        foreach ($permissionGroups as $group => $groupData) {
            $permissions[$group] = [
                'title' => $groupData['title'],
                'items' => [],
            ];
            
            foreach ($groupData['items'] as $name => $displayName) {
                $permissions[$group]['items'][$name] = [
                    'name' => $displayName,
                    'granted' => $role->hasPermissionTo($name),
                ];
            }
        }
        
        return view('settings.role-show', [
            'role' => $role,
            'permissions' => $permissions
        ]);
    }
    
    /**
     * Show the form for editing the specified role.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        // Find the role by ID
        $role = Role::with('permissions')->findOrFail($id);
        
        // Get all permissions grouped by module
        $permissions = Permission::getGroupedPermissions();
        
        // Get role's permission IDs
        $rolePermissionIds = $role->permissions->pluck('id')->toArray();
        
        return view('settings.role-edit', [
            'role' => $role,
            'permissions' => $permissions,
            'rolePermissionIds' => $rolePermissionIds
        ]);
    }
    
    /**
     * Update the specified role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        // Validate the request
        $request->validate([
            'role_name' => 'required|string|max:255',
            'role_description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
            'permissions' => 'array',
        ]);
        
        // Find the role
        $role = Role::findOrFail($id);
        
        // Prevent modifying system roles (Administrator and Organizer)
        if (in_array($role->name, ['Administrator', 'Organizer']) && $role->name !== $request->role_name) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'System roles cannot be renamed.');
        }
        
        // Update role details
        $role->update([
            'name' => $request->role_name,
            'description' => $request->role_description,
            'status' => $request->status,
            'modified_by' => Auth::user()->name,
        ]);
        
        // Update permissions if not a system role
        if (!in_array($role->name, ['Administrator'])) {
            // Sync permissions with role (detach all existing and attach new)
            if ($request->has('permissions')) {
                $role->permissions()->sync($request->permissions);
            } else {
                $role->permissions()->detach();
            }
        }
        
        return redirect()->route('role.management')
            ->with('success', 'Role updated successfully!');
    }
    
    /**
     * Remove the specified role.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        // Find the role
        $role = Role::findOrFail($id);
        
        // Prevent deleting system roles
        if (in_array($role->name, ['Administrator', 'Organizer'])) {
            return redirect()->back()
                ->with('error', 'System roles cannot be deleted.');
        }
        
        // Check if role has users
        if ($role->users()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Role cannot be deleted because it has assigned users.');
        }
        
        // Delete the role
        $role->delete();
        
        return redirect()->route('role.management')
            ->with('success', 'Role deleted successfully!');
    }
}


