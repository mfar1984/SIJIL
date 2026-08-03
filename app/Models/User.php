<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, LogsActivity, SoftDeletes;
    
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'role_id', 'status', 'phone', 'organization'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "User {$eventName}");
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        // The profile page has always offered a picture upload; until now there was
        // no column to keep it in and nothing read the field.
        'profile_image',
        'password',
        // Written when a password is set, so password expiry has something to
        // measure against. Without it here, mass assignment would drop the value
        // silently and every account would keep counting from its creation date.
        'password_changed_at',
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
            // Without this the column comes back as a string and any date
            // arithmetic on it fails. Password expiry compares against it.
            'password_changed_at' => 'datetime',
        ];
    }
    
    /**
     * Get the role that owns the user (old relationship, kept for backward compatibility).
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the events created by the user.
     */
    public function events()
    {
        return $this->hasMany(Event::class);
    }
    
    /**
     * Get the campaigns created by the user.
     */
    public function campaigns()
    {
        return $this->hasMany(Campaign::class);
    }
    
    // Note: The hasRole and hasPermission methods are now provided by the HasRoles trait
    // So we've removed the custom implementations here
}
