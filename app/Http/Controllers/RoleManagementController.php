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
        // Get all permissions grouped by module (DB-backed)
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
        
        // Log role creation
        activity('role')
            ->causedBy(auth()->user())
            ->performedOn($role)
            ->withProperties([
                'role_name' => $role->name,
                'permissions_count' => count($request->permissions ?? []),
                'status' => $role->status
            ])
            ->log('Role created');
        
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
        
        // Use DB-backed permissions
        $permissions = Permission::getGroupedPermissions();
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        
        return view('settings.role-show', [
            'role' => $role,
            'permissions' => $permissions,
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
        
        // DB-backed permissions and role selections
        $permissions = Permission::getGroupedPermissions();
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        
        return view('settings.role-edit', [
            'role' => $role,
            'permissions' => $permissions,
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
        
        // Store old values
        $oldPermissions = $role->permissions->pluck('id')->toArray();
        $oldName = $role->name;
        $oldStatus = $role->status;
        
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
        
        // Sync permissions with role (detach all existing and attach new)
        $newPermissions = $request->permissions ?? [];
        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        } else {
            $role->permissions()->detach();
        }
        
        // Log role update
        $changes = [];
        if ($oldName != $request->role_name) $changes[] = 'name';
        if ($oldStatus != $request->status) $changes[] = 'status';
        if ($oldPermissions != $newPermissions) $changes[] = 'permissions';
        
        if (!empty($changes)) {
            activity('role')
                ->causedBy(auth()->user())
                ->performedOn($role)
                ->withProperties([
                    'old' => [
                        'name' => $oldName,
                        'status' => $oldStatus,
                        'permissions_count' => count($oldPermissions)
                    ],
                    'new' => [
                        'name' => $role->name,
                        'status' => $role->status,
                        'permissions_count' => count($newPermissions)
                    ],
                    'changes' => $changes
                ])
                ->log('Role updated: ' . implode(', ', $changes));
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
        
        // Store role info before deletion
        $roleName = $role->name;
        $roleId = $role->id;
        
        // Log role deletion
        activity('role')
            ->causedBy(auth()->user())
            ->withProperties([
                'deleted_role' => [
                    'id' => $roleId,
                    'name' => $roleName,
                    'description' => $role->description
                ]
            ])
            ->log("Role deleted: {$roleName}");
        
        // Delete the role
        $role->delete();
        
        return redirect()->route('role.management')
            ->with('success', 'Role deleted successfully!');
    }
}


