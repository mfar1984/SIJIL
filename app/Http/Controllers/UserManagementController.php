<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    /**
     * Display the user management page.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Get users with their roles from database
        $query = User::with('role');
        
        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('organization', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }
        
        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Apply role filter
        if ($request->filled('role')) {
            $query->whereHas('role', function($q) use ($request) {
                $q->where('id', $request->role);
            });
        }
        
        // Apply ownership scope for Organizer role
        if (auth()->user()->hasRole('Organizer')) {
            // Organizer can only see their own record
            $query->where('id', auth()->id());
        }
        
        // Get per_page parameter with default 10
        $perPage = $request->get('per_page', 10);
        
        $users = $query->orderBy('created_at', 'desc')->paginate($perPage);
        
        // Get roles for filter dropdown
        $roles = Role::all();
        
        return view('settings.user-management', [
            'users' => $users,
            'roles' => $roles
        ]);
    }
    
    /**
     * Show the form for creating a new user.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Get roles from database
        $roles = Role::all();
        
        return view('settings.user-create', [
            'roles' => $roles
        ]);
    }
    
    /**
     * Store a newly created user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            // Basic Information
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'phone' => 'nullable|string|max:15',
            'organization' => 'nullable|string|max:255',
            'role_id' => 'required|exists:roles,id',
            'status' => 'required|in:active,inactive,banned',
            
            // Address Information
            'address_line1' => 'nullable|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'postcode' => 'nullable|string|max:10',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            
            // Organization Information
            'org_type' => 'nullable|string|in:company,government,ngo,other',
            'org_name' => 'nullable|string|max:255',
            'org_address_line1' => 'nullable|string|max:255',
            'org_address_line2' => 'nullable|string|max:255',
            'org_state' => 'nullable|string|max:100',
            'org_city' => 'nullable|string|max:100',
            'org_postcode' => 'nullable|string|max:10',
            'org_country' => 'nullable|string|max:100',
            'org_telephone' => 'nullable|string|max:20',
            'org_fax' => 'nullable|string|max:20',
            'org_email' => 'nullable|email|max:255',
            'org_website' => 'nullable|url|max:255',
            
            // Account Settings
            'password' => 'required|min:8|confirmed',
        ]);
        
        // Create the user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'phone' => $request->phone,
            'organization' => $request->organization,
            'status' => $request->status,
            'address_line1' => $request->address_line1,
            'address_line2' => $request->address_line2,
            'city' => $request->city,
            'state' => $request->state,
            'postcode' => $request->postcode,
            'country' => $request->country,
            'org_type' => $request->org_type,
            'org_name' => $request->org_name,
            'org_address_line1' => $request->org_address_line1,
            'org_address_line2' => $request->org_address_line2,
            'org_city' => $request->org_city,
            'org_state' => $request->org_state,
            'org_postcode' => $request->org_postcode,
            'org_country' => $request->org_country,
            'org_telephone' => $request->org_telephone,
            'org_fax' => $request->org_fax,
            'org_email' => $request->org_email,
            'org_website' => $request->org_website,
            'email_verified_at' => now(), // Auto-verify email for now
        ]);
        
        // Assign role using Spatie Permission
        if ($request->filled('role_id')) {
            $role = Role::findById($request->role_id);
            if ($role) {
                $user->assignRole($role->name);
            }
        }
        
        // Log user creation
        activity('user')
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->withProperties([
                'attributes' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $role->name ?? 'N/A',
                    'status' => $user->status
                ]
            ])
            ->log('User created');
        
        return redirect()->route('user.management')
            ->with('success', 'User created successfully!');
    }
    
    /**
     * Display the specified user.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        // Find the user by ID with their role
        $user = User::with('role')->findOrFail($id);
        
        // Get all roles for displaying
        $roles = Role::all();
        
        return view('settings.user-show', [
            'user' => $user,
            'roles' => $roles
        ]);
    }
    
    /**
     * Show the form for editing the specified user.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        // Find the user by ID
        $user = User::findOrFail($id);
        
        // Get all roles for the dropdown
        $roles = Role::all();
        
        return view('settings.user-edit', [
            'user' => $user,
            'roles' => $roles
        ]);
    }
    
    /**
     * Update the specified user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        // Find the user
        $user = User::findOrFail($id);
        
        // Store old values for comparison
        $oldValues = [
            'name' => $user->name,
            'email' => $user->email,
            'role_id' => $user->role_id,
            'status' => $user->status
        ];
        
        // Validate the request
        $request->validate([
            // Basic Information
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($id)],
            'phone' => 'nullable|string|max:15',
            'organization' => 'nullable|string|max:255',
            'role_id' => 'required|exists:roles,id',
            'status' => 'required|in:active,inactive,banned',
            
            // Address Information
            'address_line1' => 'nullable|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'postcode' => 'nullable|string|max:10',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            
            // Organization Information
            'org_type' => 'nullable|string|in:company,government,ngo,other',
            'org_name' => 'nullable|string|max:255',
            'org_address_line1' => 'nullable|string|max:255',
            'org_address_line2' => 'nullable|string|max:255',
            'org_state' => 'nullable|string|max:100',
            'org_city' => 'nullable|string|max:100',
            'org_postcode' => 'nullable|string|max:10',
            'org_country' => 'nullable|string|max:100',
            'org_telephone' => 'nullable|string|max:20',
            'org_fax' => 'nullable|string|max:20',
            'org_email' => 'nullable|email|max:255',
            'org_website' => 'nullable|url|max:255',
            
            // Account Settings - password is optional on update
            'password' => 'nullable|min:8|confirmed',
        ]);
        
        // Update user data
        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $request->role_id,
            'phone' => $request->phone,
            'organization' => $request->organization,
            'status' => $request->status,
            'address_line1' => $request->address_line1,
            'address_line2' => $request->address_line2,
            'city' => $request->city,
            'state' => $request->state,
            'postcode' => $request->postcode,
            'country' => $request->country,
            'org_type' => $request->org_type,
            'org_name' => $request->org_name,
            'org_address_line1' => $request->org_address_line1,
            'org_address_line2' => $request->org_address_line2,
            'org_city' => $request->org_city,
            'org_state' => $request->org_state,
            'org_postcode' => $request->org_postcode,
            'org_country' => $request->org_country,
            'org_telephone' => $request->org_telephone,
            'org_fax' => $request->org_fax,
            'org_email' => $request->org_email,
            'org_website' => $request->org_website,
        ];
        
        // Update password if provided
        $passwordChanged = false;
        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
            $passwordChanged = true;
        }
        
        // Update the user
        $user->update($userData);
        
        // Sync role using Spatie Permission
        $roleChanged = false;
        if ($request->filled('role_id')) {
            $role = Role::findById($request->role_id);
            if ($role) {
                $user->syncRoles([$role->name]);
                $roleChanged = $oldValues['role_id'] != $request->role_id;
            }
        }
        
        // Log user update with changes
        $changes = [];
        if ($oldValues['name'] != $request->name) $changes[] = 'name';
        if ($oldValues['email'] != $request->email) $changes[] = 'email';
        if ($roleChanged) $changes[] = 'role';
        if ($oldValues['status'] != $request->status) $changes[] = 'status';
        if ($passwordChanged) $changes[] = 'password';
        
        if (!empty($changes)) {
            activity('user')
                ->causedBy(auth()->user())
                ->performedOn($user)
                ->withProperties([
                    'old' => $oldValues,
                    'attributes' => [
                        'name' => $user->name,
                        'email' => $user->email,
                        'role_id' => $user->role_id,
                        'status' => $user->status
                    ],
                    'changes' => $changes
                ])
                ->log('User updated: ' . implode(', ', $changes));
        }
        
        return redirect()->route('user.management')
            ->with('success', 'User updated successfully!');
    }
    
    /**
     * Remove the specified user from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        // Find the user
        $user = User::findOrFail($id);
        
        // Don't allow deleting the currently logged in user
        if ($user->id === Auth::id()) {
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }
        
        // Store user info before deletion
        $userName = $user->name;
        $userEmail = $user->email;
        
        // Log user deletion
        activity('user')
            ->causedBy(auth()->user())
            ->withProperties([
                'deleted_user' => [
                    'id' => $user->id,
                    'name' => $userName,
                    'email' => $userEmail,
                    'role_id' => $user->role_id
                ]
            ])
            ->log("User deleted: {$userName} ({$userEmail})");
        
        // Delete the user
        $user->delete();
        
        return redirect()->route('user.management')
            ->with('success', 'User deleted successfully!');
    }
    
    /**
     * Toggle the user status between active, inactive, and banned.
     *
     * @param  int  $id
     * @param  string  $status
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleStatus($id, $status)
    {
        // Valid status values
        $validStatus = ['active', 'inactive', 'banned'];
        
        // Check if status is valid
        if (!in_array($status, $validStatus)) {
            return redirect()->back()->with('error', 'Invalid status.');
        }
        
        // Find the user
        $user = User::findOrFail($id);
        
        // Don't allow changing status of the currently logged in user
        if ($user->id === Auth::id()) {
            return redirect()->back()->with('error', 'You cannot change your own status.');
        }
        
        // Store old status
        $oldStatus = $user->status;
        
        // Update status
        $user->update(['status' => $status]);
        
        // Log status change
        $logName = ($status === 'banned') ? 'security' : 'user';
        activity($logName)
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->withProperties([
                'old_status' => $oldStatus,
                'new_status' => $status,
                'user_name' => $user->name,
                'user_email' => $user->email
            ])
            ->log("User status changed from {$oldStatus} to {$status}");
        
        return redirect()->back()
            ->with('success', "User status changed to {$status} successfully.");
    }
}
