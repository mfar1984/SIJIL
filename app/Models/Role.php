<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'status',
        'created_by',
        'modified_by',
        'guard_name',
    ];
    
    /**
     * Get all permission names as an array.
     */
    public function getPermissionNamesAttribute()
    {
        return $this->permissions->pluck('display_name')->toArray();
    }
    
    /**
     * Get the number of users with this role.
     */
    public function getUsersCountAttribute()
    {
        return $this->users()->count();
    }
}
