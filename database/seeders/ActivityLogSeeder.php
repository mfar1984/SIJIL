<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Activitylog\Models\Activity;
use App\Models\User;
use Carbon\Carbon;

class ActivityLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        
        if ($users->isEmpty()) {
            $this->command->info('No users found. Please run UserSeeder first.');
            return;
        }

        $admin = $users->first();
        
        // Sample activity logs
        $activities = [
            // Login activities
            [
                'log_name' => 'auth',
                'description' => 'User logged in successfully',
                'event' => 'login',
                'causer_id' => $admin->id,
                'causer_type' => User::class,
                'properties' => ['ip' => '192.168.1.100', 'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'],
                'created_at' => now()->subHours(2),
            ],
            [
                'log_name' => 'auth',
                'description' => 'User logged out',
                'event' => 'logout',
                'causer_id' => $admin->id,
                'causer_type' => User::class,
                'properties' => ['ip' => '192.168.1.100'],
                'created_at' => now()->subHours(1),
            ],
            [
                'log_name' => 'auth',
                'description' => 'Failed login attempt',
                'event' => 'failed_login',
                'causer_id' => null,
                'causer_type' => null,
                'properties' => ['ip' => '192.168.1.105', 'email' => 'unknown@example.com'],
                'created_at' => now()->subHours(3),
            ],
            
            // User management activities
            [
                'log_name' => 'user',
                'description' => 'User created: John Doe',
                'event' => 'created',
                'causer_id' => $admin->id,
                'causer_type' => User::class,
                'properties' => ['user_id' => 2, 'email' => 'john@example.com'],
                'created_at' => now()->subDays(1),
            ],
            [
                'log_name' => 'user',
                'description' => 'User updated: Jane Smith',
                'event' => 'updated',
                'causer_id' => $admin->id,
                'causer_type' => User::class,
                'properties' => ['user_id' => 3, 'changes' => ['name', 'email']],
                'created_at' => now()->subDays(2),
            ],
            [
                'log_name' => 'user',
                'description' => 'Password changed for user',
                'event' => 'password_changed',
                'causer_id' => $admin->id,
                'causer_type' => User::class,
                'properties' => ['user_id' => $admin->id],
                'created_at' => now()->subDays(3),
            ],
            
            // Role management activities
            [
                'log_name' => 'role',
                'description' => 'Role created: Moderator',
                'event' => 'created',
                'causer_id' => $admin->id,
                'causer_type' => User::class,
                'properties' => ['role_name' => 'Moderator', 'permissions' => ['read', 'write']],
                'created_at' => now()->subDays(4),
            ],
            [
                'log_name' => 'role',
                'description' => 'Role updated: Administrator',
                'event' => 'updated',
                'causer_id' => $admin->id,
                'causer_type' => User::class,
                'properties' => ['role_name' => 'Administrator', 'changes' => ['permissions']],
                'created_at' => now()->subDays(5),
            ],
            
            // Security activities
            [
                'log_name' => 'security',
                'description' => 'Suspicious activity detected',
                'event' => 'suspicious',
                'causer_id' => null,
                'causer_type' => null,
                'properties' => ['ip' => '192.168.1.200', 'reason' => 'Multiple failed login attempts'],
                'created_at' => now()->subHours(4),
            ],
            [
                'log_name' => 'security',
                'description' => 'Unauthorized access attempt',
                'event' => 'unauthorized',
                'causer_id' => null,
                'causer_type' => null,
                'properties' => ['ip' => '192.168.1.150', 'resource' => '/admin/settings'],
                'created_at' => now()->subHours(5),
            ],
            
            // System activities
            [
                'log_name' => 'system',
                'description' => 'Database backup completed',
                'event' => 'backup',
                'causer_id' => null,
                'causer_type' => null,
                'properties' => ['size' => '256MB', 'duration' => '2m 30s'],
                'created_at' => now()->subDays(6),
            ],
            [
                'log_name' => 'system',
                'description' => 'System maintenance started',
                'event' => 'maintenance',
                'causer_id' => $admin->id,
                'causer_type' => User::class,
                'properties' => ['duration' => '30m', 'type' => 'scheduled'],
                'created_at' => now()->subDays(7),
            ],
            
            // Event management activities
            [
                'log_name' => 'event',
                'description' => 'Event created: Tech Conference 2024',
                'event' => 'created',
                'causer_id' => $admin->id,
                'causer_type' => User::class,
                'properties' => ['event_id' => 1, 'name' => 'Tech Conference 2024'],
                'created_at' => now()->subDays(8),
            ],
            [
                'log_name' => 'event',
                'description' => 'Event updated: Workshop Series',
                'event' => 'updated',
                'causer_id' => $admin->id,
                'causer_type' => User::class,
                'properties' => ['event_id' => 2, 'changes' => ['date', 'location']],
                'created_at' => now()->subDays(9),
            ],
            
            // Certificate activities
            [
                'log_name' => 'certificate',
                'description' => 'Certificate generated for participant',
                'event' => 'generated',
                'causer_id' => $admin->id,
                'causer_type' => User::class,
                'properties' => ['participant_id' => 1, 'event_id' => 1],
                'created_at' => now()->subDays(10),
            ],
            [
                'log_name' => 'certificate',
                'description' => 'Certificate template created',
                'event' => 'template_created',
                'causer_id' => $admin->id,
                'causer_type' => User::class,
                'properties' => ['template_id' => 1, 'name' => 'Default Template'],
                'created_at' => now()->subDays(11),
            ],
        ];

        foreach ($activities as $activity) {
            Activity::create($activity);
        }

        $this->command->info('Activity logs seeded successfully!');
    }
}
