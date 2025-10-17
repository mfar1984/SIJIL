@props(['permissions' => [], 'checkedPermissionNames' => [], 'mode' => 'edit'])

@php
    // Helper to map action keywords to matrix columns
    $actionMap = [
        'create' => 'create',
        'view' => 'read',
        'read' => 'read',
        'edit' => 'update',
        'update' => 'update',
        'delete' => 'delete',
        'export' => 'export',
        'generate' => 'create', // treat generate_* as create
        'manage' => 'update', // treat manage_* as update
        'archive' => 'archive',
        'unarchive' => 'archive',
    ];
    
    // Define sidebar order for groups
    $groupOrder = [
        'dashboard' => 1,
        'event' => 2,
        'participants' => 3,
        'attendance' => 4,
        'certificate' => 5,
        'pwa' => 6, // PWA Management
        'reports' => 7,
        'campaign' => 8,
        'helpdesk' => 9,
        'settings' => 10,
        'legacy' => 99, // hide by default
        'other' => 100, // hide by default
    ];
    
    // Map various DB group keys to canonical keys for ordering/labeling
    $groupAlias = [
        'ecertificate_online' => 'pwa',
        'pwa_management' => 'pwa',
        'ecertificate' => 'pwa',
    ];
    
    // Filter out legacy/other groups and then sort
    $sortedPermissions = collect($permissions)
        ->filter(function($group, $key){ return !in_array(strtolower($key), ['legacy','other']); })
        ->sortBy(function($group, $key) use ($groupOrder, $groupAlias) {
            $k = strtolower($key);
            $k = $groupAlias[$k] ?? $k;
            return $groupOrder[$k] ?? 999;
        })->toArray();
@endphp

<div class="overflow-x-auto" x-data="permissionsMatrix()">
    <!-- Quick Actions Toolbar (top-right) -->
    @if($mode !== 'show')
    <div class="mb-2 w-full flex justify-end gap-2">
        <button type="button" @click="checkAll()" class="px-2 py-0.5 text-[10px] bg-green-600 text-white rounded">Check All</button>
        <button type="button" @click="uncheckAll()" class="px-2 py-0.5 text-[10px] bg-gray-500 text-white rounded">Uncheck All</button>
        <button type="button" @click="applyOrganizer()" class="px-2 py-0.5 text-[10px] bg-indigo-600 text-white rounded">Organizer</button>
    </div>
    @endif

    <table class="min-w-full bg-white border border-gray-200 rounded-lg">
        <thead class="bg-gradient-to-r from-blue-600 to-blue-500 text-white">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Module</th>
                <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider">Create</th>
                <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider">Read</th>
                <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider">Update</th>
                <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider">Delete</th>
                <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider">Archive</th>
                <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider">Export</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @foreach($sortedPermissions as $groupKey => $group)
                @php
                    $isLegacy = false;
                    $groupKeyLower = strtolower($groupKey);
                    $isPwaGroup = in_array($groupKeyLower, ['ecertificate_online','pwa','pwa_management','ecertificate']);
                    $groupDisplayName = $isPwaGroup ? 'PWA Management' : $group['title'];
                    
                    // Build matrix rows for this group
                    $rows = [];
                    foreach ($group['items'] as $permName => $label) {
                        $action = null; $resourceKey = null;
                        // Patterns: action_resource, resource.action, resource-action, resource:action
                        if (preg_match('/^(create|read|view|edit|update|delete|export|generate|manage|archive|unarchive)[_.:-](.+)$/', $permName, $m)) {
                            $action = $actionMap[$m[1]] ?? null;
                            $resourceKey = $m[2];
                        } elseif (preg_match('/^(.+)[_.:-](create|read|view|edit|update|delete|export|generate|manage|archive|unarchive)$/', $permName, $m)) {
                            $action = $actionMap[$m[2]] ?? null;
                            $resourceKey = $m[1];
                        } else {
                            // Fallback: detect from display label or names with spaces
                            if (preg_match('/^(create|read|view|edit|update|delete|export|archive)\s+(.+)$/i', $permName, $m)) {
                                $action = $actionMap[strtolower($m[1])] ?? null;
                                $resourceKey = strtolower($m[2]);
                            } elseif (preg_match('/^(.+)\s+(create|read|view|edit|update|delete|export|archive)$/i', $permName, $m)) {
                                $action = $actionMap[strtolower($m[2])] ?? null;
                                $resourceKey = strtolower($m[1]);
                            } elseif (preg_match('/^(create|read|view|edit|update|delete|export|archive)\s+(.+)$/i', $label, $m)) {
                                $action = $actionMap[strtolower($m[1])] ?? null;
                                $resourceKey = strtolower($m[2]);
                            } elseif (preg_match('/^(.+)\s+(create|read|view|edit|update|delete|export|archive)$/i', $label, $m)) {
                                $action = $actionMap[strtolower($m[2])] ?? null;
                                $resourceKey = strtolower($m[1]);
                            }
                        }

                        if ($action) {
                            // Canonicalize resource to merge legacy and modern names
                            $normalizedKey = preg_replace('/[\-\.]+/', '_', strtolower($resourceKey));
                            $resourceSynonymMap = [
                                'role_management' => 'roles',
                                'user_management' => 'users',
                                'event_management' => 'events',
                                'survey' => 'surveys',
                                'manage_attendance' => 'attendance',
                                'attendance_management' => 'attendance',
                                'archive' => 'attendance_archive',
                                'archives' => 'attendance_archive',
                                'certificates' => 'manage_certificates',
                                'templates' => 'template_designer',
                                'pwa_templates' => 'pwa_templates',
                                'campaign' => 'campaigns',
                                'global_config' => 'global_config',
                                'log_activity' => 'log_activity',
                                'security_audit' => 'security_audit',
                                'config_delivery' => 'delivery',
                            ];
                            $canonicalKey = $resourceSynonymMap[$normalizedKey] ?? $normalizedKey;
                            $canonicalName = $canonicalKey . '.' . $action;

                            // Prefer modern canonical permission id; fall back to original name
                            $pid = App\Models\Permission::where('name', $canonicalName)->value('id');
                            if (!$pid) {
                                $pid = App\Models\Permission::where('name', $permName)->value('id');
                            }

                            // Determine checked state against both names
                            $checkedByName = in_array($permName, $checkedPermissionNames ?? []);
                            $checkedByCanonical = in_array($canonicalName, $checkedPermissionNames ?? []);
                            $checkedByOld = is_array(old('permissions')) && in_array($pid, old('permissions'));
                            $isChecked = $checkedByName || $checkedByCanonical || $checkedByOld;

                            // Normalize display label (pretty title for canonical key)
                            $displayLabelMap = [
                                'roles' => 'Role Management',
                                'users' => 'User Management',
                                'events' => 'Event Management',
                                'surveys' => 'Surveys',
                                'attendance' => 'Attendance Management',
                                'attendance_archive' => 'Attendance Archive',
                                'manage_certificates' => 'Manage Certificates',
                                'template_designer' => 'Template Designer',
                                'campaigns' => 'Campaign',
                                'global_config' => 'Global Config',
                                'log_activity' => 'Log Activity',
                                'security_audit' => 'Security Audit',
                                'pwa_templates' => 'PWA Templates',
                                'attendance_reports' => 'Attendance Reports',
                                'event_statistics' => 'Event Statistics',
                                'certificate_reports' => 'Certificate Reports',
                            ];
                            $resourceLabel = $displayLabelMap[$canonicalKey] ?? ucwords(str_replace('_', ' ', $canonicalKey));

                            if (!isset($rows[$resourceLabel])) {
                                $rows[$resourceLabel] = [
                                    'ids' => [],
                                    'checked' => [],
                                ];
                            }
                            $rows[$resourceLabel]['ids'][$action] = $pid;
                            $rows[$resourceLabel]['checked'][$action] = $isChecked;
                        } else {
                            // Non-CRUD permission: show under Read column for its own label
                            $normalizedKey = preg_replace('/[\-\.]+/', '_', strtolower($permName));
                            $canonicalName = $normalizedKey; // for non-CRUD keep normalized name
                            $pid = App\Models\Permission::where('name', $canonicalName)->value('id');
                            if (!$pid) {
                                $pid = App\Models\Permission::where('name', $permName)->value('id');
                            }
                            $checkedByName = in_array($permName, $checkedPermissionNames ?? []);
                            $checkedByCanonical = in_array($canonicalName, $checkedPermissionNames ?? []);
                            $checkedByOld = is_array(old('permissions')) && in_array($pid, old('permissions'));
                            $isChecked = $checkedByName || $checkedByCanonical || $checkedByOld;
                            // Remap labels for archives to unified label
                            $resLabelKey = $resourceSynonymMap[$normalizedKey] ?? $normalizedKey;
                            $displayLabelMap = [
                                'attendance_archive' => 'Attendance Archive',
                            ];
                            $resLabel = $displayLabelMap[$resLabelKey] ?? $label;
                            if (!isset($rows[$resLabel])) {
                                $rows[$resLabel] = ['ids' => [], 'checked' => []];
                            }
                            $rows[$resLabel]['ids']['read'] = $pid;
                            $rows[$resLabel]['checked']['read'] = $isChecked;
                        }
                    }

                    // Special-case augmentations for Attendance rows
                    $groupKeyNormalized = $groupAlias[strtolower($groupKey)] ?? strtolower($groupKey);
                    if ($groupKeyNormalized === 'attendance') {
                        // Remove standalone rows named like "Attendance Create/Read/Update/Delete"
                        foreach (['Attendance Create','Attendance Read','Attendance Update','Attendance Delete'] as $dup) {
                            unset($rows[$dup]);
                        }

                        // Build ordered rows for Attendance module
                        $orderedRows = [];

                        // 1. Attendance Management (Create, Read, Update, Delete, Archive)
                        $attMgmtReadId = App\Models\Permission::where('name', 'attendance_management.read')->value('id');
                        $attendanceCreateId = App\Models\Permission::where('name', 'attendance.create')->value('id');
                        $attendanceUpdateId = App\Models\Permission::where('name', 'attendance.update')->value('id');
                        $attendanceDeleteId = App\Models\Permission::where('name', 'attendance.delete')->value('id');
                        $attendanceArchiveId = App\Models\Permission::where('name', 'attendance.archive')->value('id');
                        
                        $orderedRows['Attendance Management'] = ['ids' => [], 'checked' => []];
                        if ($attendanceCreateId) {
                            $orderedRows['Attendance Management']['ids']['create'] = $attendanceCreateId;
                            $orderedRows['Attendance Management']['checked']['create'] = in_array('attendance.create', $checkedPermissionNames ?? []);
                        }
                        if ($attMgmtReadId) {
                            $orderedRows['Attendance Management']['ids']['read'] = $attMgmtReadId;
                            $orderedRows['Attendance Management']['checked']['read'] = in_array('attendance_management.read', $checkedPermissionNames ?? []);
                        }
                        if ($attendanceUpdateId) {
                            $orderedRows['Attendance Management']['ids']['update'] = $attendanceUpdateId;
                            $orderedRows['Attendance Management']['checked']['update'] = in_array('attendance.update', $checkedPermissionNames ?? []);
                        }
                        if ($attendanceDeleteId) {
                            $orderedRows['Attendance Management']['ids']['delete'] = $attendanceDeleteId;
                            $orderedRows['Attendance Management']['checked']['delete'] = in_array('attendance.delete', $checkedPermissionNames ?? []);
                        }
                        if ($attendanceArchiveId) {
                            $orderedRows['Attendance Management']['ids']['archive'] = $attendanceArchiveId;
                            $orderedRows['Attendance Management']['checked']['archive'] = in_array('attendance.archive', $checkedPermissionNames ?? []);
                        }

                        // 2. Attendance List (Read only)
                        $attendanceReadId = App\Models\Permission::where('name', 'attendance.read')->value('id');
                        if ($attendanceReadId) {
                            $orderedRows['Attendance List'] = ['ids' => [], 'checked' => []];
                            $orderedRows['Attendance List']['ids']['read'] = $attendanceReadId;
                            $orderedRows['Attendance List']['checked']['read'] = in_array('attendance.read', $checkedPermissionNames ?? []);
                        }

                        // 3. Attendance Archive (Read, Delete, Archive)
                        $archivesReadId = App\Models\Permission::where('name', 'archives.read')->value('id');
                        $archivesDeleteId = App\Models\Permission::where('name', 'archives.delete')->value('id');
                        $archivesArchiveId = App\Models\Permission::where('name', 'archives.archive')->value('id');
                        
                        $orderedRows['Attendance Archive'] = ['ids' => [], 'checked' => []];
                        if ($archivesReadId) {
                            $orderedRows['Attendance Archive']['ids']['read'] = $archivesReadId;
                            $orderedRows['Attendance Archive']['checked']['read'] = in_array('archives.read', $checkedPermissionNames ?? []);
                        }
                        if ($archivesDeleteId) {
                            $orderedRows['Attendance Archive']['ids']['delete'] = $archivesDeleteId;
                            $orderedRows['Attendance Archive']['checked']['delete'] = in_array('archives.delete', $checkedPermissionNames ?? []);
                        }
                        if ($archivesArchiveId) {
                            $orderedRows['Attendance Archive']['ids']['archive'] = $archivesArchiveId;
                            $orderedRows['Attendance Archive']['checked']['archive'] = in_array('archives.archive', $checkedPermissionNames ?? []);
                        }

                        // Replace rows with ordered version
                        $rows = $orderedRows;
                    } elseif ($groupKeyNormalized === 'certificate') {
                        // Enforce row order: Manage Certificates, then Template Designer
                        $preferredOrder = ['Manage Certificates', 'Template Designer'];
                        $orderedRows = [];
                        foreach ($preferredOrder as $label) {
                            if (isset($rows[$label])) {
                                $orderedRows[$label] = $rows[$label];
                            }
                        }
                        // Append any remaining rows
                        foreach ($rows as $label => $data) {
                            if (!isset($orderedRows[$label])) {
                                $orderedRows[$label] = $data;
                            }
                        }
                        // Remove Update ability for Manage Certificates (no edit screen)
                        if (isset($orderedRows['Manage Certificates'])) {
                            unset($orderedRows['Manage Certificates']['ids']['update']);
                            unset($orderedRows['Manage Certificates']['checked']['update']);
                        }
                        $rows = $orderedRows;
                    }
                @endphp

                <tr class="bg-gray-100">
                    <td colspan="7" class="px-4 py-2 text-xs font-bold text-gray-700">{{ $groupDisplayName }}</td>
                </tr>

                @forelse($rows as $resourceLabel => $row)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-xs text-gray-900">{{ $resourceLabel }}</td>
                        @foreach(['create','read','update','delete','archive','export'] as $col)
                            <td class="px-4 py-3 text-center">
                                @php
                                    $pid = $row['ids'][$col] ?? null;
                                    $isChecked = $row['checked'][$col] ?? false;
                                @endphp
                                @if($pid)
                                    @if($mode === 'show')
                                        @if($isChecked)
                                            <span class="material-icons text-green-600 text-base">check_circle</span>
                                        @else
                                            <span class="material-icons text-red-600 text-base">cancel</span>
                                        @endif
                                    @else
                                        <input type="checkbox" name="permissions[]" value="{{ $pid }}" data-perm-group="{{ $groupKeyLower }}" class="perm-box rounded border-gray-300 text-primary-DEFAULT focus:ring-primary-light h-4 w-4" {{ $isChecked ? 'checked' : '' }}>
                                    @endif
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-3 text-xs text-gray-500">No permissions in this group.</td>
                    </tr>
                @endforelse
            @endforeach
        </tbody>
    </table>
    <script>
        function permissionsMatrix(){
            return {
                checkAll(){
                    document.querySelectorAll('.perm-box').forEach(cb=>{ cb.checked = true; });
                },
                uncheckAll(){
                    document.querySelectorAll('.perm-box').forEach(cb=>{ cb.checked = false; });
                },
                applyOrganizer(){
                    // Start from clean slate
                    this.uncheckAll();
                    // Tick all except Settings group
                    document.querySelectorAll('.perm-box').forEach(cb=>{
                        const g = cb.getAttribute('data-perm-group') || '';
                        if(g !== 'settings') cb.checked = true;
                    });
                }
            }
        }
    </script>
</div>


