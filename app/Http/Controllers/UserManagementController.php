<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    /**
     * Display the user management page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Sample data for users
        $users = [
            [
                'id' => 1,
                'name' => 'Administrator',
                'email' => 'admin@example.com',
                'role' => 'Administrator',
                'create_date' => '21 Jul 2025 - 09:15:00',
                'last_login' => '21 Jul 2025 - 19:48:00',
                'status' => 'active',
                'created_at' => '2023-01-10'
            ],
            [
                'id' => 2,
                'name' => 'John Organizer',
                'email' => 'john@example.com',
                'role' => 'Organizer',
                'create_date' => '21 Jul 2025 - 10:30:00',
                'last_login' => '21 Jul 2025 - 18:22:00',
                'status' => 'active',
                'created_at' => '2023-03-05'
            ],
            [
                'id' => 3,
                'name' => 'Sarah Smith',
                'email' => 'sarah@example.com',
                'role' => 'Organizer',
                'create_date' => '21 Jul 2025 - 11:45:00',
                'last_login' => '21 Jul 2025 - 15:10:45',
                'status' => 'inactive',
                'created_at' => '2023-04-18'
            ],
            [
                'id' => 4,
                'name' => 'Michael Brown',
                'email' => 'michael@example.com',
                'role' => 'Organizer',
                'create_date' => '21 Jul 2025 - 14:20:00',
                'last_login' => '21 Jul 2025 - 16:35:30',
                'status' => 'banned',
                'created_at' => '2023-05-12'
            ]
        ];

        return view('settings.user-management', [
            'users' => $users
        ]);
    }
}
