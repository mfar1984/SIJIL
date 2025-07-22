<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ParticipantsTableSeeder extends Seeder
{
    public function run()
    {
        $events = DB::table('events')->pluck('id')->toArray();
        $names = [
            'Ali Bin Ahmad', 'Siti Nurhaliza', 'John Doe', 'Jane Smith', 'Ahmad Faizal',
            'Nurul Izzah', 'Lim Wei Ming', 'Raj Kumar', 'Aisyah Humaira', 'Mohd Hafiz',
            'Emily Tan', 'Syafiqah Zainal', 'Faridah Omar', 'Zulkifli Hassan', 'Chong Lee',
            'Aminah Bakar', 'Suresh Kumar', 'Fatimah Zahra', 'Hafizah Rahman', 'Adam Lee',
            'Nadia Syamimi', 'Roslan Ismail', 'Kavitha Subramaniam', 'Yusof Abdullah', 'Liyana Azman'
        ];
        $participants = [];
        foreach ($names as $i => $name) {
            $ic = rand(100000,999999) . '-' . rand(10,99) . '-' . rand(1000,9999);
            $email = Str::slug($name, '.') . '@example.com';
            $phone = '01' . rand(10000000,99999999);
            $event_id = $events[array_rand($events)];
            $participants[] = [
                'name' => $name,
                'organization' => $ic,
                'email' => $email,
                'phone' => $phone,
                'event_id' => $event_id,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('participants')->insert($participants);
    }
} 