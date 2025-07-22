<?php

namespace App\Http\Controllers;

use App\Helpers\RolePermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleCheckController extends Controller
{
    /**
     * Display the role check page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get all permissions for the current user
        $permissions = RolePermission::getUserPermissions();
        
        // Get all modules with access status
        $modules = [
            'Dashboard' => RolePermission::hasPermission('view_dashboard'),
            'User Management' => $user->role === 'Administrator',
            'Event Management' => RolePermission::hasPermission('view_events'),
            'Participants' => RolePermission::hasPermission('view_participants'),
            'Certificates' => RolePermission::hasPermission('view_certificates'),
            'Attendance' => RolePermission::hasPermission('view_attendance'),
            'Reports' => RolePermission::hasPermission('view_reports'),
            'Campaign' => RolePermission::hasPermission('view_campaigns'),
            'Helpdesk' => RolePermission::hasPermission('view_helpdesk'),
        ];
        
        return view('settings.role-check', [
            'user' => $user,
            'role' => $user->role,
            'permissions' => $permissions,
            'modules' => $modules
        ]);
    }
} 