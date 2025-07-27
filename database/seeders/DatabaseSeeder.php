<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionMatrixSeeder::class, // Run this first to create new permission structure
            RolesAndPermissionsSeeder::class,
            AdminUserSeeder::class,
            EventsSeeder::class,
            ParticipantsTableSeeder::class,
            TemplateSeeder::class,
            AddCertificatePermissionsSeeder::class,
            CampaignSeeder::class,
            HelpdeskSeeder::class,
            PermissionSeeder::class,
        ]);
    }
}
