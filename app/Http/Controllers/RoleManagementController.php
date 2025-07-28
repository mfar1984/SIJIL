<?php

namespace App\Http\Controllers;

use App\Helpers\RolePermission;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RoleManagementController extends Controller
{
    /**
     * Display the role management page.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Get roles from database
        $query = Role::with('permissions');
        
        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }
        
        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Get per_page parameter with default 10
        $perPage = $request->get('per_page', 10);
        
        $roles = $query->orderBy('created_at', 'desc')->paginate($perPage);

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
     * Get permission matrix structure
     */
    private function getPermissionMatrix()
    {
        return [
            'Dashboard' => ['read'],
            'Event' => [
                'Event Management' => ['create', 'read', 'update', 'delete'],
                'Survey' => ['create', 'read', 'update', 'delete'],
            ],
            'Participants' => ['create', 'read', 'update', 'delete'],
            'Certificate' => [
                'All Certificate' => ['read'],
                'Generate Certificate' => ['create', 'read'],
                'Template Designer' => ['create', 'read', 'update', 'delete'],
            ],
            'Attendance' => [
                'Manage Attendance' => ['create', 'read', 'update', 'delete'],
                'Archive' => ['read'],
            ],
            'Reports' => [
                'Attendance Reports' => ['read'],
                'Event Statistics' => ['read'],
                'Certificate Reports' => ['read'],
            ],
            'Campaign' => [
                'Campaign' => ['create', 'read', 'update', 'delete'],
                'Config Delivery' => ['read', 'update'],
            ],
            'Helpdesk' => ['read', 'update'],
            'Settings' => [
                'Global Config' => ['read', 'update'],
                'Role Management' => ['create', 'read', 'update', 'delete'],
                'User Management' => ['create', 'read', 'update', 'delete'],
                'Log Activity' => ['read'],
                'Security & Audit' => ['read'],
            ],
        ];
    }

    /**
     * Convert permission matrix to flat array of permission names
     */
    private function getPermissionNames()
    {
        $matrix = $this->getPermissionMatrix();
        $permissions = [];
        
        foreach ($matrix as $main => $sub) {
            if (is_array($sub) && isset($sub[0]) && is_string($sub[0])) {
                // Direct permissions (like Dashboard)
                foreach ($sub as $action) {
                    $permissions[] = Str::slug($main) . '.' . $action;
                }
            } else {
                // Sub-menu permissions
                foreach ($sub as $subName => $actions) {
                    foreach ($actions as $action) {
                        $permissions[] = Str::slug($subName) . '.' . $action;
                    }
                }
            }
        }
        
        return $permissions;
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
        $role = Role::with('permissions')->withCount('users')->findOrFail($id);
        
        // Get permission matrix
        $permissionMatrix = $this->getPermissionMatrix();
        
        // Get role's current permissions
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        
        return view('settings.role-show', [
            'role' => $role,
            'permissionMatrix' => $permissionMatrix,
            'rolePermissions' => $rolePermissions
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
        
        // Get permission matrix
        $permissionMatrix = $this->getPermissionMatrix();
        
        // Get role's current permissions
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        
        return view('settings.role-edit', [
            'role' => $role,
            'permissionMatrix' => $permissionMatrix,
            'rolePermissions' => $rolePermissions
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
        
        return redirect()->route('role.show', $role->id)
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


