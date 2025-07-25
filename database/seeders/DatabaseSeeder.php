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
            RolesAndPermissionsSeeder::class,
            AdminUserSeeder::class,
            EventsSeeder::class,
            ParticipantsTableSeeder::class,
            RealisticAttendanceRecordSeeder::class,
            TemplateSeeder::class,
            CampaignSeeder::class, // Add the CampaignSeeder
        ]);
    }
}
