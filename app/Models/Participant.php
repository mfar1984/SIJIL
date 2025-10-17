<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'organization',
        'identity_card',
        'passport_no',
        'gender',
        'date_of_birth',
        'race',
        'job_title',
        'address1',
        'address2',
        'city',
        'state',
        'postcode',
        'country',
        'event_id',
        'status',
        'registration_date',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'registration_date' => 'datetime',
        'attendance_date' => 'datetime',
        'date_of_birth' => 'date',
    ];

    /**
     * Get the formatted phone number with + prefix.
     */
    public function getFormattedPhoneAttribute()
    {
        if (!$this->phone) {
            return null;
        }
        
        $phone = $this->phone;
        
        // Check if the number already has Malaysia country code
        if (preg_match('/^60/', $phone)) {
            // Already has Malaysia country code, just add + prefix
            return '+' . $phone;
        }
        
        // If number doesn't match pattern for a valid number, return null
        if (!preg_match('/^[1-9][0-9]+/', $phone)) {
            return null;
        }
        
        // If number starts with 0, remove the 0 and add 60
        if (substr($phone, 0, 1) === '0') {
            $phone = '60' . substr($phone, 1);
        } else {
            // If number doesn't start with 0 or 60, add 60
            $phone = '60' . $phone;
        }
        
        return '+' . $phone;
    }
    
    /**
     * Set the phone number by removing + prefix if present.
     */
    public function setPhoneAttribute($value)
    {
        $this->attributes['phone'] = $value ? ltrim($value, '+') : null;
    }

    /**
     * Parse the address string into components.
     */
    public function getParsedAddressAttribute()
    {
        if (!$this->address) {
            return [
                'address_line1' => '',
                'address_line2' => '',
                'state' => '',
                'city' => '',
                'postcode' => '',
                'country' => 'Malaysia'
            ];
        }

        $parts = explode("\n", $this->address);
        $result = [
            'address_line1' => $parts[0] ?? '',
            'address_line2' => $parts[1] ?? '',
            'state' => '',
            'city' => '',
            'postcode' => '',
            'country' => 'Malaysia'
        ];

        // Try to parse location data from the last line
        if (count($parts) >= 3) {
            $lastLine = $parts[count($parts) - 1];
            
            // Try to extract postcode (5 digits)
            if (preg_match('/\b\d{5}\b/', $lastLine, $matches)) {
                $result['postcode'] = $matches[0];
            }

            // Try to extract state and city
            $statesInMalaysia = [
                'Johor', 'Kedah', 'Kelantan', 'Melaka', 'Negeri Sembilan', 
                'Pahang', 'Perak', 'Perlis', 'Pulau Pinang', 'Sabah', 
                'Sarawak', 'Selangor', 'Terengganu', 'Kuala Lumpur', 
                'Labuan', 'Putrajaya'
            ];

            foreach ($statesInMalaysia as $state) {
                if (stripos($lastLine, $state) !== false) {
                    $result['state'] = $state;
                    // Extract city (assume it's before the state)
                    $beforeState = stristr($lastLine, $state, true);
                    if ($beforeState) {
                        // Clean up and get the last word as city
                        $city = trim(preg_replace('/[0-9,\.]/', '', $beforeState));
                        $cityParts = explode(' ', $city);
                        if (!empty($cityParts)) {
                            $result['city'] = end($cityParts);
                        }
                    }
                    break;
                }
            }

            // Look for country in the last line
            $countries = ['Malaysia', 'Singapore', 'Indonesia', 'Thailand', 'Philippines'];
            foreach ($countries as $country) {
                if (stripos($lastLine, $country) !== false) {
                    $result['country'] = $country;
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Relationship with the event this participant registered for.
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Relationship with the original participant if this is a registration from existing participant
     */
    public function relatedParticipant()
    {
        return $this->belongsTo(Participant::class, 'related_participant_id');
    }

    /**
     * Get registrations that were created from this participant
     */
    public function registrations()
    {
        return $this->hasMany(Participant::class, 'related_participant_id');
    }
} 