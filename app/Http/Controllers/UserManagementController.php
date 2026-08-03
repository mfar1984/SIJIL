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
        $perPage = \App\Support\SystemSettings::perPage($request, 10);
        
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
        // users.email carries a database-level unique index, so an address held
        // by a soft-deleted account cannot be reused. Say so plainly instead of
        // showing a bare "already taken" error for an invisible record.
        if ($request->filled('email')) {
            $trashed = User::onlyTrashed()->where('email', $request->email)->first();

            if ($trashed) {
                return back()->withInput()->withErrors([
                    'email' => "This email belongs to \"{$trashed->name}\", a user sitting in the Recycle Bin. Restore that user, or delete it permanently from Settings > Global Config > Recycle Bin to free up the email.",
                ]);
            }
        }

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
            
            // Account Settings. The rule comes from Settings > Global Config >
            // Security; this was a hard-coded min:8 that ignored the configured
            // length and every character requirement.
            'password' => ['required', 'confirmed', \App\Support\SecurityPolicy::passwordRule()],
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
        
        // A newly set password starts the expiry clock from now, not from the
        // account's creation date.
        $user->forceFill(['password_changed_at' => now()])->save();

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

        // Honours "Email new users a welcome message" on the Notifications tab,
        // which was stored and read by nothing, so the switch did nothing.
        $notice = '';

        if (\App\Models\GlobalConfig::getConfig()->email_new_user_registration ?? false) {
            try {
                \Illuminate\Support\Facades\Mail::to($user->email)->send(
                    new \App\Mail\UserWelcome($user, $role->name ?? null, auth()->user()?->name)
                );
                $notice = ' A welcome email was sent to ' . $user->email . '.';
            } catch (\Throwable $e) {
                // The account exists and is usable; only the email failed.
                \Illuminate\Support\Facades\Log::error('Welcome email failed', [
                    'user' => $user->id,
                    'error' => $e->getMessage(),
                ]);
                $notice = ' The account was created, but the welcome email could not be sent.';
            }
        }

        return redirect()->route('user.management')
            ->with('success', 'User created successfully!' . $notice);
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
            'password' => ['nullable', 'confirmed', \App\Support\SecurityPolicy::passwordRule()],
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
            // Recorded so password expiry has something to measure against.
            // Without it an administrator resetting a password left the account
            // still counting from whenever it was created.
            $userData['password_changed_at'] = now();
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

        // A password reset by an administrator and a change of role are security
        // events, and the Security Audit tab only reads the 'security' log. Both
        // were recorded under 'user', so neither appeared there.
        if ($passwordChanged) {
            \App\Support\SecurityPolicy::audit('password', 'Password reset by administrator', [
                'target' => $user->email,
                'ip_address' => $request->ip(),
            ], auth()->user(), $user);

            \App\Support\SecurityAlert::send('Password reset by administrator', [
                'Account' => (string) $user->email,
                'Changed by' => (string) auth()->user()?->email,
                'IP address' => (string) $request->ip(),
            ]);
        }

        if ($roleChanged) {
            \App\Support\SecurityPolicy::audit('permission', 'User role changed', [
                'target' => $user->email,
                'old_role_id' => $oldValues['role_id'],
                'new_role_id' => $user->role_id,
                'ip_address' => $request->ip(),
            ], auth()->user(), $user);
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
        
        // Soft delete: the account can no longer sign in, but the record and
        // everything it owns is kept and can be restored from the Recycle Bin.
        $user->delete();
        
        return redirect()->route('user.management')->with(
            'success',
            "{$userName} moved to Recycle Bin. The email {$userEmail} stays reserved until the record is permanently deleted from Settings → Global Config → Recycle Bin."
        );
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
