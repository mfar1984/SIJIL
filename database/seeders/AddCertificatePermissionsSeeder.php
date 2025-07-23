<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AddCertificatePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get roles
        $adminRole = Role::findByName('Administrator');
        $organizerRole = Role::findByName('Organizer');
        
        // Certificate permissions
        $permissions = [
            'view_certificates',
            'generate_certificates',
            'edit_templates',
            'manage_certificates',
        ];
        
        // Create permissions if they don't exist
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
        
        // Assign certificate permissions to roles
        $adminRole->givePermissionTo($permissions);
        
        $organizerRole->givePermissionTo([
            'view_certificates',
            'generate_certificates',
            'edit_templates',
        ]);
    }
}
