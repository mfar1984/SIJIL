<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
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
            'create_ecertificate_participants',
            'edit_ecertificate_participants', 
            'delete_ecertificate_participants',
            'export_ecertificate_analytics'
        ];

        foreach ($permissions as $permission) {
            if (!Permission::where('name', $permission)->exists()) {
                Permission::create(['name' => $permission, 'guard_name' => 'web']);
                $this->command->info("Created permission: {$permission}");
            } else {
                $this->command->info("Permission already exists: {$permission}");
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