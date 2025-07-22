<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'display_name',
        'group',
        'description',
        'guard_name',
    ];
    
    /**
     * Scope a query to only include permissions of a given group.
     */
    public function scopeByGroup($query, $group)
    {
        return $query->where('group', $group);
    }
    
    /**
     * Get permissions grouped by their group.
     */
    public static function getGroupedPermissions()
    {
        $permissions = self::all();
        $grouped = [];
        
        foreach ($permissions as $permission) {
            $group = $permission->group ?? 'other';
            
            if (!isset($grouped[$group])) {
                $grouped[$group] = [
                    'title' => ucwords(str_replace('_', ' ', $group)),
                    'items' => [],
                ];
            }
            
            $grouped[$group]['items'][$permission->name] = $permission->display_name ?? $permission->name;
        }
        
        return $grouped;
    }
}
