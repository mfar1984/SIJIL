<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission; // use app model to support extra columns (display_name, group)
use Spatie\Permission\Models\Role;

class AddPwaParticipantPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create missing PWA participant permissions
        $permissions = [
            'create_ecertificate_participants' => 'Create E-Certificate Participants',
            'edit_ecertificate_participants' => 'Edit E-Certificate Participants', 
            'delete_ecertificate_participants' => 'Delete E-Certificate Participants',
            'export_ecertificate_analytics' => 'Export E-Certificate Analytics',
        ];

        foreach ($permissions as $permission => $display) {
            $perm = Permission::where('name', $permission)->first();
            if (!$perm) {
                Permission::create([
                    'name' => $permission,
                    'display_name' => $display,
                    'guard_name' => 'web',
                    'group' => 'ecertificate_online',
                    'description' => 'Permission to ' . strtolower($display),
                ]);
                $this->command->info("Created permission: {$permission}");
            } else {
                // ensure proper grouping and labels
                $perm->update([
                    'display_name' => $display,
                    'group' => 'ecertificate_online',
                ]);
                $this->command->info("Updated permission: {$permission}");
            }
        }

        // Assign permissions to roles
        $administratorRole = Role::where('name', 'Administrator')->first();
        $organizerRole = Role::where('name', 'Organizer')->first();

        if ($administratorRole) {
            $administratorRole->givePermissionTo($permissions);
            $this->command->info("Assigned PWA participant permissions to Administrator role");
        }

        if ($organizerRole) {
            $organizerRole->givePermissionTo($permissions);
            $this->command->info("Assigned PWA participant permissions to Organizer role");
        }

        $this->command->info('PWA participant permissions setup completed!');
    }
} 