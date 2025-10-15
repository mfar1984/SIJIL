<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Faker\Factory as Faker;

class FixParticipantsDummySeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('ms_MY');
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

        $usedDob = [];
        foreach ($participants as $participant) {
            $update = [];
            foreach ($fields as $field) {
                // Always update date_of_birth with a unique value
                if ($field === 'date_of_birth') {
                    $dob = null;
                    $attempts = 0;
                    do {
                        $dob = $faker->date('Y-m-d', '-'.rand(18,60).' years');
                        $attempts++;
                    } while (in_array($dob, $usedDob) && $attempts < 10);
                    $usedDob[] = $dob;
                    $update[$field] = $dob;
                } else {
                    $value = $participant->$field;
                    if (is_null($value) || trim($value) === '' || trim($value) === '-') {
                        switch ($field) {
                            case 'phone':
                                $update[$field] = '01' . $faker->numberBetween(1, 9) . $faker->numberBetween(1000000, 9999999);
                                break;
                            case 'identity_card':
                                $update[$field] = $faker->numerify('############');
                                break;
                            case 'organization':
                                $update[$field] = $faker->company;
                                break;
                            case 'job_title':
                                $update[$field] = $faker->jobTitle;
                                break;
                            case 'address1':
                                $update[$field] = $faker->streetAddress;
                                break;
                            case 'address2':
                                $update[$field] = $faker->streetAddress;
                                break;
                            case 'city':
                                $update[$field] = $faker->city;
                                break;
                            case 'state':
                                $update[$field] = $faker->state;
                                break;
                            case 'postcode':
                                $update[$field] = $faker->postcode;
                                break;
                            case 'country':
                                $update[$field] = 'Malaysia';
                                break;
                            case 'gender':
                                $update[$field] = $faker->randomElement(['male', 'female', 'other']);
                                break;
                            case 'registration_date':
                                $update[$field] = $faker->date('Y-m-d');
                                break;
                        }
                    }
                }
            }
            if (!empty($update)) {
                DB::table('participants')->where('id', $participant->id)->update($update);
            }
        }
    }
} 