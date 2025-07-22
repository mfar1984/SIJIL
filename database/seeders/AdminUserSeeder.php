<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get Administrator role ID
        $adminRoleId = Role::where('name', 'Administrator')->value('id');
        
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@e-certificate.com.my',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role_id' => $adminRoleId,
            'phone' => '+60123456789',
            'organization' => 'E-Certificate System',
            'status' => 'active',
            'last_login_at' => now(),
            'address_line1' => '123 Main Street',
            'address_line2' => 'Suite 100',
            'city' => 'Kuala Lumpur',
            'state' => 'Federal Territory',
            'postcode' => '50000',
            'country' => 'Malaysia',
            'org_type' => 'government',
            'org_name' => 'Ministry of Technology',
            'org_address_line1' => '456 Govt Street',
            'org_address_line2' => '8th Floor',
            'org_city' => 'Kuala Lumpur',
            'org_state' => 'Federal Territory',
            'org_postcode' => '50100',
            'org_country' => 'Malaysia',
            'org_telephone' => '+60323456789',
            'org_fax' => '+60323456780',
            'org_email' => 'info@mtech.gov.my',
            'org_website' => 'https://mtech.gov.my',
        ]);
        
        // Get Organizer role ID
        $organizerRoleId = Role::where('name', 'Organizer')->value('id');
        
        // Create organizer user
        User::create([
            'name' => 'Organizer User',
            'email' => 'organizer@e-certificate.com.my',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role_id' => $organizerRoleId,
            'phone' => '+60129876543',
            'organization' => 'Event Solutions',
            'status' => 'active',
            'last_login_at' => now()->subHours(2),
            'address_line1' => '789 Oak Avenue',
            'address_line2' => 'Apt 45',
            'city' => 'Petaling Jaya',
            'state' => 'Selangor',
            'postcode' => '46000',
            'country' => 'Malaysia',
            'org_type' => 'company',
            'org_name' => 'Event Solutions Sdn Bhd',
            'org_address_line1' => '101 Business Park',
            'org_address_line2' => 'Unit 302',
            'org_city' => 'Petaling Jaya',
            'org_state' => 'Selangor',
            'org_postcode' => '47100',
            'org_country' => 'Malaysia',
            'org_telephone' => '+60378901234',
            'org_fax' => '+60378901235',
            'org_email' => 'info@eventsolutions.com.my',
            'org_website' => 'https://eventsolutions.com.my',
        ]);
        
        // Create inactive user
        User::create([
            'name' => 'Inactive User',
            'email' => 'inactive@e-certificate.com.my',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role_id' => $organizerRoleId,
            'phone' => '+60132345678',
            'organization' => 'Creative Events',
            'status' => 'inactive',
            'last_login_at' => now()->subDays(30),
            'address_line1' => '234 Pine Road',
            'address_line2' => '',
            'city' => 'Johor Bahru',
            'state' => 'Johor',
            'postcode' => '80000',
            'country' => 'Malaysia',
            'org_type' => 'company',
            'org_name' => 'Creative Events Company',
            'org_address_line1' => '567 Creative Hub',
            'org_address_line2' => '2nd Floor',
            'org_city' => 'Johor Bahru',
            'org_state' => 'Johor',
            'org_postcode' => '80100',
            'org_country' => 'Malaysia',
            'org_telephone' => '+6073456789',
            'org_fax' => '+6073456780',
            'org_email' => 'info@creativeevents.com.my',
            'org_website' => 'https://creativeevents.com.my',
        ]);
        
        // Create banned user
        User::create([
            'name' => 'Banned User',
            'email' => 'banned@e-certificate.com.my',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role_id' => $organizerRoleId,
            'phone' => '+60145678901',
            'organization' => 'Northern Events',
            'status' => 'banned',
            'last_login_at' => now()->subDays(60),
            'address_line1' => '890 Cedar Lane',
            'address_line2' => 'Block B',
            'city' => 'Penang',
            'state' => 'Pulau Pinang',
            'postcode' => '10000',
            'country' => 'Malaysia',
            'org_type' => 'company',
            'org_name' => 'Northern Events Organizer',
            'org_address_line1' => '456 Beach Street',
            'org_address_line2' => '',
            'org_city' => 'Georgetown',
            'org_state' => 'Pulau Pinang',
            'org_postcode' => '10200',
            'org_country' => 'Malaysia',
            'org_telephone' => '+6042345678',
            'org_fax' => '+6042345679',
            'org_email' => 'info@northernevents.com.my',
            'org_website' => 'https://northernevents.com.my',
        ]);
    }
}
