{{--
    General tab.

    Thirteen of the fifteen controls here were stored and read by nothing. Only the
    organisation name and email had any effect. Everything that remains is now
    honoured, and the controls that could not be honoured have been removed rather
    than left implying otherwise.

    Two were worse than inert. Default Event Status offered draft, published and
    archived, while events.status is enum('active','pending','completed'), so
    applying the stored value would have been rejected by MySQL. Date Format
    accepted free text that went straight into Carbon::format().
--}}
@php
    $formats = \App\Support\SystemSettings::dateFormatOptions();
    $currentFormat = \App\Support\SystemSettings::dateFormat();
    $statuses = \App\Support\SystemSettings::eventStatusOptions();
@endphp

<div x-show="activeTab === 'general'" class="space-y-4">

    {{-- ------------------------------------------------------------------
         Organisation
    ------------------------------------------------------------------- --}}
    <div class="bg-white border border-gray-200 rounded-md shadow-sm">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                <span class="material-icons-outlined text-primary-DEFAULT mr-2">business</span>
                Organisation
            </h2>
        </div>

        <div class="p-4 space-y-3">
            <div class="flex flex-col md:flex-row md:items-start gap-3">
                <label for="org_name" class="text-xs font-medium text-gray-700 md:w-44 md:pt-2">
                    Organisation Name
                </label>
                <div class="flex-1">
                    <input type="text" id="org_name" name="org_name" required
                           value="{{ old('org_name', $config->org_name ?? '') }}"
                           class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                    <p class="text-[11px] text-gray-500 mt-1">
                        Shown on the sign-in page, in the sidebar when no logo is set, in every outgoing email and on
                        the maintenance page.
                    </p>
                </div>
            </div>

            <div class="flex flex-col md:flex-row md:items-start gap-3">
                <label for="org_email" class="text-xs font-medium text-gray-700 md:w-44 md:pt-2">
                    Organisation Email
                </label>
                <div class="flex-1">
                    <input type="email" id="org_email" name="org_email" required
                           value="{{ old('org_email', $config->org_email ?? '') }}"
                           class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                    <p class="text-[11px] text-gray-500 mt-1">
                        Used as a fallback recipient for administrator and security alerts when the address on the
                        Notifications tab is not set.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- ------------------------------------------------------------------
         Regional and presentation
    ------------------------------------------------------------------- --}}
    <div class="bg-white border border-gray-200 rounded-md shadow-sm">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                <span class="material-icons-outlined text-primary-DEFAULT mr-2">public</span>
                Regional &amp; Display
            </h2>
        </div>

        <div class="p-4 space-y-3">
            <div class="flex flex-col md:flex-row md:items-start gap-3">
                <label for="timezone" class="text-xs font-medium text-gray-700 md:w-44 md:pt-2">Timezone</label>
                <div class="flex-1">
                    <select id="timezone" name="timezone" required
                            class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                        @php $currentZone = old('timezone', $config->timezone ?? 'Asia/Kuala_Lumpur'); @endphp
                        @foreach(timezone_identifiers_list() as $zone)
                            <option value="{{ $zone }}" {{ $currentZone === $zone ? 'selected' : '' }}>{{ $zone }}</option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-gray-500 mt-1">
                        Every date and time in the system is stored in UTC and rendered in this zone. Currently
                        <span class="font-medium">{{ now()->format('d M Y, H:i') }}</span>.
                    </p>
                </div>
            </div>

            <div class="flex flex-col md:flex-row md:items-start gap-3">
                <label for="date_format" class="text-xs font-medium text-gray-700 md:w-44 md:pt-2">Date Format</label>
                <div class="flex-1">
                    <select id="date_format" name="date_format"
                            class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                        @foreach($formats as $format => $example)
                            <option value="{{ $format }}" {{ $currentFormat === $format ? 'selected' : '' }}>
                                {{ $example }} &nbsp;({{ $format }})
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-gray-500 mt-1">
                        Applies to dates shown on their own in listings and reports. Columns that carry a time keep
                        their own format, because a date-only pattern would silently drop it.
                    </p>
                </div>
            </div>

            <div class="flex flex-col md:flex-row md:items-start gap-3">
                <label for="pagination" class="text-xs font-medium text-gray-700 md:w-44 md:pt-2">Rows Per Page</label>
                <div class="flex-1">
                    <input type="number" id="pagination" name="pagination" min="5" max="1000"
                           value="{{ old('pagination', $config->pagination ?? 10) }}"
                           class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                    <p class="text-[11px] text-gray-500 mt-1">
                        The starting page size for every listing table. A per-page choice made on a table still wins
                        for that table.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- ------------------------------------------------------------------
         Event defaults
    ------------------------------------------------------------------- --}}
    <div class="bg-white border border-gray-200 rounded-md shadow-sm">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                <span class="material-icons-outlined text-primary-DEFAULT mr-2">event</span>
                Event Defaults
            </h2>
        </div>

        <div class="p-4 space-y-3">
            <div class="flex flex-col md:flex-row md:items-start gap-3">
                <label for="default_event_status" class="text-xs font-medium text-gray-700 md:w-44 md:pt-2">
                    Default Status
                </label>
                <div class="flex-1">
                    <select id="default_event_status" name="default_event_status"
                            class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                        @php $currentStatus = old('default_event_status', \App\Support\SystemSettings::defaultEventStatus()); @endphp
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" {{ $currentStatus === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-gray-500 mt-1">
                        Pre-selected when creating an event. These are the only three values the events table
                        accepts; the tab previously offered draft, published and archived, which it does not.
                    </p>
                </div>
            </div>

            <div class="flex flex-col md:flex-row md:items-start gap-3">
                <label for="event_expiry" class="text-xs font-medium text-gray-700 md:w-44 md:pt-2">
                    Registration Window
                </label>
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <input type="number" id="event_expiry" name="event_expiry" min="1" max="720"
                               value="{{ old('event_expiry', $config->event_expiry ?? 48) }}"
                               class="w-32 h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                        <span class="text-xs text-gray-600">hours</span>
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1">
                        Suggested lifetime for a new event's public registration link. Each event can override it,
                        and switching off auto-expiry on the event keeps the link open indefinitely.
                    </p>
                </div>
            </div>

            <div class="flex flex-col md:flex-row md:items-start gap-3">
                <label for="registration_message" class="text-xs font-medium text-gray-700 md:w-44 md:pt-2">
                    Confirmation Message
                </label>
                <div class="flex-1">
                    <textarea id="registration_message" name="registration_message" rows="3" maxlength="1000"
                              class="w-full text-xs border-gray-300 rounded px-3 py-2 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">{{ old('registration_message', $config->registration_message ?? '') }}</textarea>
                    <p class="text-[11px] text-gray-500 mt-1">
                        Shown on the thank-you page after someone registers through a public link.
                    </p>
                </div>
            </div>

            <div class="flex flex-col md:flex-row md:items-start gap-3">
                <span class="text-xs font-medium text-gray-700 md:w-44 md:pt-1">Duplicates</span>
                <div class="flex-1">
                    <label class="flex items-start">
                        <input type="hidden" name="allow_multiple_registrations" value="0">
                        <input type="checkbox" name="allow_multiple_registrations" value="1"
                               class="mt-0.5 rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                               {{ (old('allow_multiple_registrations', $config->allow_multiple_registrations ?? false)) ? 'checked' : '' }}>
                        <span class="ml-2">
                            <span class="text-xs text-gray-700">Allow the same person to register more than once for one event</span>
                        </span>
                    </label>

                    <div class="mt-2 bg-amber-50 border border-amber-200 rounded px-3 py-2">
                        <p class="text-[11px] text-amber-800">
                            Leave this off unless you mean it. Off is the shipped rule: one IC or passport per event,
                            while an email may repeat as often as needed so a parent can register several children
                            or a company its staff. Ticking it
                            <span class="font-medium">removes the identity check altogether</span> rather than
                            loosening it, so the same person could be entered any number of times.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ------------------------------------------------------------------
         System
    ------------------------------------------------------------------- --}}
    <div class="bg-white border border-gray-200 rounded-md shadow-sm">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                <span class="material-icons-outlined text-primary-DEFAULT mr-2">settings_suggest</span>
                System
            </h2>
        </div>

        <div class="p-4 space-y-3">
            <div class="flex flex-col md:flex-row md:items-start gap-3">
                <span class="text-xs font-medium text-gray-700 md:w-44 md:pt-1">Maintenance Mode</span>
                <div class="flex-1">
                    <label class="flex items-center">
                        <input type="hidden" name="maintenance_mode" value="0">
                        <input type="checkbox" name="maintenance_mode" value="1"
                               class="rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                               {{ (old('maintenance_mode', $config->maintenance_mode ?? false)) ? 'checked' : '' }}>
                        <span class="ml-2 text-xs text-gray-700">Close the system to everyone except administrators</span>
                    </label>
                    <p class="text-[11px] text-gray-500 mt-1">
                        Organizers and participants see a holding page. Sign-in, the health check and public
                        certificate verification stay open, and an administrator is never blocked, so this cannot
                        lock out the person who has to switch it off.
                    </p>
                    @if(\App\Support\SystemSettings::maintenanceMode())
                        <div class="mt-2 bg-red-50 border border-red-200 rounded px-3 py-2">
                            <p class="text-[11px] text-red-800">
                                <span class="font-medium">Maintenance mode is on right now.</span>
                                Only administrators can reach the system.
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="flex flex-col md:flex-row md:items-start gap-3">
                <span class="text-xs font-medium text-gray-700 md:w-44 md:pt-1">Activity Logging</span>
                <div class="flex-1">
                    <label class="flex items-center">
                        <input type="hidden" name="activity_logging" value="0">
                        <input type="checkbox" name="activity_logging" value="1"
                               class="rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                               {{ (old('activity_logging', $config->activity_logging ?? true)) ? 'checked' : '' }}>
                        <span class="ml-2 text-xs text-gray-700">Record what users do</span>
                    </label>
                    <p class="text-[11px] text-gray-500 mt-1">
                        Feeds Log Activity and Security &amp; Audit. Switching it off stops new entries being
                        written, including the security entries the audit page relies on, so the retention setting on
                        the Security tab is usually the better control.
                    </p>
                </div>
            </div>

            <div class="flex flex-col md:flex-row md:items-start gap-3">
                <label for="cache_lifetime" class="text-xs font-medium text-gray-700 md:w-44 md:pt-2">
                    Settings Cache
                </label>
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <input type="number" id="cache_lifetime" name="cache_lifetime" min="1" max="1440"
                               value="{{ old('cache_lifetime', $config->cache_lifetime ?? 60) }}"
                               class="w-32 h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                        <span class="text-xs text-gray-600">minutes</span>
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1">
                        How long this settings row is held in the cache. Saving always refreshes it immediately, so
                        this only affects changes made directly in the database.
                    </p>
                </div>
            </div>

            <div class="flex flex-col md:flex-row md:items-start gap-3">
                <span class="text-xs font-medium text-gray-700 md:w-44 md:pt-1">Debug Output</span>
                <div class="flex-1">
                    <div class="{{ config('app.debug') ? 'bg-amber-50 border-amber-200' : 'bg-gray-50 border-gray-200' }} border rounded px-3 py-2">
                        <p class="text-[11px] {{ config('app.debug') ? 'text-amber-800' : 'text-gray-600' }}">
                            <span class="font-medium">
                                APP_DEBUG is {{ config('app.debug') ? 'on' : 'off' }}.
                            </span>
                            @if(config('app.debug'))
                                Stack traces, database queries and configuration values are shown to anyone who
                                triggers an error. This must be off in production.
                            @else
                                Errors do not expose internals.
                            @endif
                            This is set by the environment file, not from here. The tab used to carry Debug Mode and
                            Error Reporting checkboxes that were stored and read by nothing, and a switch that can
                            expose configuration to visitors does not belong behind a web form.
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex flex-col md:flex-row md:items-start gap-3">
                <span class="text-xs font-medium text-gray-700 md:w-44 md:pt-1">Moved</span>
                <div class="flex-1">
                    <div class="bg-gray-50 border border-gray-200 rounded px-3 py-2">
                        <p class="text-[11px] text-gray-600">
                            <span class="font-medium">Automatic confirmation emails</span> was a second switch for the
                            same behaviour as "Email the participant a confirmation" on the
                            <span class="font-medium">Notifications</span> tab, so it has been removed and that one
                            is now the only control.
                            <span class="font-medium">Organization Logo</span> moved to
                            <span class="font-medium">Appearance</span>, where it sits with the favicon and the
                            sign-in images.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
