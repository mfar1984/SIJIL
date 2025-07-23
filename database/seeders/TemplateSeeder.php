<?php

namespace Database\Seeders;

use App\Models\CertificateTemplate;
use Illuminate\Database\Seeder;

class TemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample templates
        CertificateTemplate::create([
            'name' => 'Classic Certificate',
            'description' => 'A classic certificate design with elegant borders and typography.',
            'orientation' => 'landscape',
            'created_by' => 1, // Admin user ID
        ]);

        CertificateTemplate::create([
            'name' => 'Modern Award',
            'description' => 'A modern, minimalist certificate design for professional events.',
            'orientation' => 'portrait',
            'created_by' => 1, // Admin user ID
        ]);

        CertificateTemplate::create([
            'name' => 'Academic Achievement',
            'description' => 'Formal certificate design for academic achievements and graduations.',
            'orientation' => 'landscape',
            'created_by' => 1, // Admin user ID
        ]);

        CertificateTemplate::create([
            'name' => 'Event Participation',
            'description' => 'Certificate of participation for events and workshops.',
            'orientation' => 'portrait',
            'created_by' => 1, // Admin user ID
        ]);
    }
} 