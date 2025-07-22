<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id', // This will be kept for backward compatibility
        'phone',
        'organization',
        'status',
        'last_login_at',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'postcode',
        'country',
        'org_type',
        'org_name',
        'org_address_line1',
        'org_address_line2',
        'org_city',
        'org_state',
        'org_postcode',
        'org_country',
        'org_telephone',
        'org_fax',
        'org_email',
        'org_website',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
        ];
    }
    
    /**
     * Get the role that owns the user (old relationship, kept for backward compatibility).
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }
    
    // Note: The hasRole and hasPermission methods are now provided by the HasRoles trait
    // So we've removed the custom implementations here
}
