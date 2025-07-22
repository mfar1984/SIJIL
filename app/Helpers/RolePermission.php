<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class RolePermission
{
    /**
     * Check if the user has a specific permission
     *
     * @param string $permission
     * @return bool
     */
    public static function hasPermission($permission)
    {
        // For demonstration purposes only - in a real app this would check the database
        
        if (!Auth::check()) {
            return false;
        }
        
        // Get the current user's role
        $role = Auth::user()->role;
        
        // Administrator has all permissions
        if ($role === 'Administrator') {
            return true;
        }
        
        // Check if the Organizer has this specific permission
        if ($role === 'Organizer') {
            // Organizer permissions for demo - this would come from the database in a real app
            $organizerPermissions = [
                'view_dashboard',
                'view_participants', 'add_participants', 'edit_participants', 'delete_participants',
                'view_certificates', 'generate_certificates', 'email_certificates',
                'view_attendance', 'manage_attendance', 'view_archive',
                'view_reports', 'export_reports',
                'view_campaigns', 'manage_campaigns', 'view_database_users', 'manage_delivery',
                'view_helpdesk', 'manage_helpdesk'
            ];
            
            return in_array($permission, $organizerPermissions);
        }
        
        return false;
    }
    
    /**
     * Check if the current user owns a specific resource
     *
     * @param mixed $resource The resource to check ownership of
     * @return bool
     */
    public static function ownsResource($resource)
    {
        if (!Auth::check() || !$resource) {
            return false;
        }
        
        // Administrator owns all resources
        if (Auth::user()->role === 'Administrator') {
            return true;
        }
        
        // For Organizer, check if the resource belongs to them
        // This is just a demo implementation - in a real app, you'd check the actual relationship
        return isset($resource['user_id']) && $resource['user_id'] === Auth::id();
    }
    
    /**
     * Apply scope to a query to filter by user ownership
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function applyOwnershipScope($query)
    {
        // If user is Administrator, don't apply any filter
        if (Auth::check() && Auth::user()->role === 'Administrator') {
            return $query;
        }
        
        // For Organizer, only show their own resources
        return $query->where('user_id', Auth::id());
    }
    
    /**
     * Get permissions list for current user
     *
     * @return array
     */
    public static function getUserPermissions()
    {
        if (!Auth::check()) {
            return [];
        }
        
        $role = Auth::user()->role;
        
        // For Administrator, return all permissions
        if ($role === 'Administrator') {
            // This would be all permissions in the system in a real app
            return ['all_permissions'];
        }
        
        // For Organizer, return their specific permissions
        if ($role === 'Organizer') {
            return [
                'view_dashboard',
                'view_participants', 'add_participants', 'edit_participants', 'delete_participants',
                'view_certificates', 'generate_certificates', 'email_certificates',
                'view_attendance', 'manage_attendance', 'view_archive',
                'view_reports', 'export_reports',
                'view_campaigns', 'manage_campaigns', 'view_database_users', 'manage_delivery',
                'view_helpdesk', 'manage_helpdesk'
            ];
        }
        
        return [];
    }
} 