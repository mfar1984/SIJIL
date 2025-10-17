<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseParticipantsPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // This seeder is deprecated. Database Users permissions are legacy and not used.
        // Keeping it no-op to avoid re-introducing legacy permissions.
        $permissions = [];

        // Create permissions
        foreach ($permissions as $permission => $display_name) {/* no-op */}

        // Assign permissions to Administrator role
        $adminRole = Role::where('name', 'Administrator')->first();
        if ($adminRole) {/* no-op */}

        // Assign permissions to Organizer role
        $organizerRole = Role::where('name', 'Organizer')->first();
        if ($organizerRole) {/* no-op */}
    }
} 