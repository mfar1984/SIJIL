<?php

namespace App\Models\Database;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'database_participants';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'id_passport',
        'date_of_birth',
        'gender',
        'organization',
        'job_title',
        'tags',
        'source',
        'notes'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
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
        
        return '+' . $this->phone;
    }
    
    /**
     * Set the phone number by removing + prefix if present.
     */
    public function setPhoneAttribute($value)
    {
        $this->attributes['phone'] = $value ? ltrim($value, '+') : null;
    }
}
