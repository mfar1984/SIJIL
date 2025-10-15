<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FixParticipantsNullSeeder extends Seeder
{
    public function run()
    {
        $fields = [
            'phone',
            'identity_card',
            'organization',
            'job_title',
            'address1',
            'address2',
            'city',
            'state',
            'postcode',
            'country',
            'gender',
            'date_of_birth',
            'registration_date',
        ];

        $participants = DB::table('participants')
            ->whereNotIn('id', [26, 28])
            ->get();

        foreach ($participants as $participant) {
            $update = [];
            foreach ($fields as $field) {
                $value = $participant->$field ?? null;
                // For date fields, also check for '0000-00-00'
                if (in_array($field, ['date_of_birth', 'registration_date'])) {
                    if (empty($value) || $value == '0000-00-00') {
                        $update[$field] = null;
                    }
                } else {
                    if (is_null($value) || trim($value) === '') {
                        $update[$field] = null;
                    }
                }
            }
            if (!empty($update)) {
                DB::table('participants')
                    ->where('id', $participant->id)
                    ->update($update);
            }
        }
    }
} 