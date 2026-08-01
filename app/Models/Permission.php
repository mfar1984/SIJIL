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
        'sort_order',
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
     *
     * Both the groups and the permissions inside each group are ordered by
     * sort_order, which the seeder assigns to match the sidebar sequence.
     * Anything without a sort_order falls to the end of its group.
     */
    public static function getGroupedPermissions()
    {
        $permissions = self::orderBy('sort_order')->orderBy('name')->get();
        $grouped = [];

        foreach ($permissions as $permission) {
            $group = $permission->group ?? 'other';

            if (!isset($grouped[$group])) {
                $grouped[$group] = [
                    'title' => ucwords(str_replace('_', ' ', $group)),
                    'sort_order' => $permission->sort_order ?? 9999,
                    'items' => [],
                ];
            }

            $grouped[$group]['items'][$permission->name] = $permission->display_name ?? $permission->name;
        }

        // Order the groups themselves by the first permission they contain.
        uasort($grouped, fn($a, $b) => $a['sort_order'] <=> $b['sort_order']);

        return $grouped;
    }
}
