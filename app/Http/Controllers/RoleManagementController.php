<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RoleManagementController extends Controller
{
    /**
     * Display the role management page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Sample data for roles - updated to only include Administrator and Organizer
        $roles = [
            [
                'id' => 1,
                'name' => 'Administrator',
                'permissions' => ['All permissions'],
                'users_count' => 2,
                'status' => 'active',
                'created_at' => '21 Jul 2025 - 09:15:00',
                'modified_at' => '21 Jul 2025 - 19:48:00'
            ],
            [
                'id' => 2,
                'name' => 'Organizer',
                'permissions' => ['View reports', 'Manage events', 'Manage certificates', 'Manage attendance', 'Generate certificates'],
                'users_count' => 8,
                'status' => 'active',
                'created_at' => '21 Jul 2025 - 10:30:00',
                'modified_at' => '21 Jul 2025 - 15:22:00'
            ]
        ];

        return view('settings.role-management', [
            'roles' => $roles
        ]);
    }
}
