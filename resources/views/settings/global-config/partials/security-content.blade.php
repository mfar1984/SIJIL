{{--
    Security tab.

    Every control here now enforces something. Previously all of them were stored
    and none were read: password rules were hard-coded, the lockout was a literal
    5 attempts releasing after 60 seconds, the session timeout came from the
    environment, and the four logging switches did nothing because the audit page
    read the log unconditionally.
--}}
@php
    $policy = \App\Support\SecurityPolicy::class;
    $sec = $securityPanel ?? \App\Support\SecuritySurface::payload();
@endphp

<div x-show="activeTab === 'security'" class="space-y-4">

    {{-- ------------------------------------------------------------------
         Posture summary
    ------------------------------------------------------------------- --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-blue-50 rounded-md p-4 border border-blue-100">
            <p class="text-xs text-blue-700 font-medium">Password Floor</p>
            <p class="text-2xl font-bold text-blue-800">{{ $sec['min_password_length'] }}</p>
            <p class="text-[11px] text-blue-600 mt-1">{{ $sec['complexity_count'] }} of 3 character rules on</p>
        </div>
        <div class="bg-green-50 rounded-md p-4 border border-green-100">
            <p class="text-xs text-green-700 font-medium">Lockout After</p>
            <p class="text-2xl font-bold text-green-800">{{ $sec['max_login_attempts'] }}</p>
            <p class="text-[11px] text-green-600 mt-1">held {{ $sec['lockout_minutes'] }} min</p>
        </div>
        <div class="{{ $sec['locked_accounts'] > 0 ? 'bg-red-50 border-red-100' : 'bg-gray-50 border-gray-200' }} rounded-md p-4 border">
            <p class="text-xs {{ $sec['locked_accounts'] > 0 ? 'text-red-700' : 'text-gray-600' }} font-medium">Locked Right Now</p>
            <p class="text-2xl font-bold {{ $sec['locked_accounts'] > 0 ? 'text-red-800' : 'text-gray-700' }}">{{ $sec['locked_accounts'] }}</p>
            <p class="text-[11px] {{ $sec['locked_accounts'] > 0 ? 'text-red-600' : 'text-gray-500' }} mt-1">participant account(s)</p>
        </div>
        <div class="bg-amber-50 rounded-md p-4 border border-amber-100">
            <p class="text-xs text-amber-700 font-medium">Failed Logins (24h)</p>
            <p class="text-2xl font-bold text-amber-800">{{ $sec['failed_logins_24h'] }}</p>
            <p class="text-[11px] text-amber-600 mt-1">{{ $sec['audit_rows'] }} audit entries kept</p>
        </div>
    </div>

    {{-- ------------------------------------------------------------------
         Password policy
    ------------------------------------------------------------------- --}}
    <div class="bg-white border border-gray-200 rounded-md shadow-sm">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                <span class="material-icons-outlined text-primary-DEFAULT mr-2">password</span>
                Password Policy
            </h2>
        </div>

        <div class="p-4 space-y-3">
            <div class="bg-blue-50 border border-blue-200 rounded px-3 py-2">
                <p class="text-[11px] text-blue-800">
                    Applies to administrator and organizer sign-in, to the user management form, to the profile
                    page, and to passwords chosen during public event registration. Current rule:
                    <span class="font-medium">{{ \App\Support\SecurityPolicy::describe() }}</span>
                </p>
            </div>

            <div class="flex flex-col md:flex-row md:items-start gap-3">
                <label for="min_password_length" class="text-xs font-medium text-gray-700 md:w-44 md:pt-2">
                    Minimum Length
                </label>
                <div class="flex-1">
                    <input type="number" id="min_password_length" name="min_password_length"
                           value="{{ old('min_password_length', $config->min_password_length ?? 8) }}"
                           min="6" max="32"
                           class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                           :disabled="!isEditing">
                    <p class="text-[11px] text-gray-500 mt-1">
                        Existing passwords are not invalidated. The rule applies the next time one is set.
                    </p>
                </div>
            </div>

            <div class="flex flex-col md:flex-row md:items-start gap-3">
                <span class="text-xs font-medium text-gray-700 md:w-44 md:pt-1">Character Requirements</span>
                <div class="flex-1 space-y-2">
                    <label class="flex items-center">
                        <input type="hidden" name="require_uppercase" value="0">
                        <input type="checkbox" name="require_uppercase" value="1"
                               class="rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                               {{ (old('require_uppercase', $config->require_uppercase ?? false)) ? 'checked' : '' }}
                               :disabled="!isEditing">
                        <span class="ml-2 text-xs text-gray-700">Upper and lower case letters</span>
                    </label>
                    <label class="flex items-center">
                        <input type="hidden" name="require_numbers" value="0">
                        <input type="checkbox" name="require_numbers" value="1"
                               class="rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                               {{ (old('require_numbers', $config->require_numbers ?? false)) ? 'checked' : '' }}
                               :disabled="!isEditing">
                        <span class="ml-2 text-xs text-gray-700">At least one number</span>
                    </label>
                    <label class="flex items-center">
                        <input type="hidden" name="require_special_chars" value="0">
                        <input type="checkbox" name="require_special_chars" value="1"
                               class="rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                               {{ (old('require_special_chars', $config->require_special_chars ?? false)) ? 'checked' : '' }}
                               :disabled="!isEditing">
                        <span class="ml-2 text-xs text-gray-700">At least one symbol</span>
                    </label>
                </div>
            </div>

            <div class="flex flex-col md:flex-row md:items-start gap-3">
                <label for="password_expiry" class="text-xs font-medium text-gray-700 md:w-44 md:pt-2">
                    Password Expiry
                </label>
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <input type="number" id="password_expiry" name="password_expiry"
                               value="{{ old('password_expiry', $config->password_expiry ?? 0) }}"
                               min="0" max="365"
                               class="w-32 h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                               :disabled="!isEditing">
                        <span class="text-xs text-gray-600">days</span>
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1">
                        <span class="font-medium">0</span> means never. When set, a backend user past the limit is
                        sent to their profile to set a new one before they can go anywhere else.
                    </p>
                    @if($sec['expiry_enabled'])
                        <p class="text-[11px] {{ $sec['expired_passwords'] > 0 ? 'text-amber-700' : 'text-gray-500' }} mt-1">
                            {{ $sec['expired_passwords'] }} of {{ $sec['user_count'] }} account(s) are currently past it.
                        </p>
                    @else
                        <div class="mt-2 bg-amber-50 border border-amber-200 rounded px-3 py-2">
                            <p class="text-[11px] text-amber-800">
                                Switching this on takes effect immediately and counts from when each password was
                                last set, not from today. On the current data
                                <span class="font-medium">{{ $sec['would_expire_at_90'] }} of {{ $sec['user_count'] }}</span>
                                account(s) would be asked to change their password on their next page load at 90 days.
                                They can still reach their profile and sign out, nothing else.
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="flex flex-col md:flex-row md:items-start gap-3">
                <span class="text-xs font-medium text-gray-700 md:w-44 md:pt-1">Generated Passwords</span>
                <div class="flex-1">
                    <p class="text-[11px] text-gray-600">
                        Participant app accounts get a generated password, whose length and character mix are set
                        separately under
                        <a href="{{ route('pwa.settings') }}" class="text-primary-DEFAULT underline">PWA &rsaquo; Settings</a>.
                        Currently <span class="font-medium">{{ $sec['generated_password_length'] }} characters</span>.
                        @if($sec['generated_password_length'] < $sec['min_password_length'])
                            <span class="block mt-1 text-amber-700">
                                That is shorter than the minimum above, so generated passwords would not pass the
                                policy a person has to meet. Worth aligning.
                            </span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- ------------------------------------------------------------------
         Sign-in protection
    ------------------------------------------------------------------- --}}
    <div class="bg-white border border-gray-200 rounded-md shadow-sm">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                <span class="material-icons-outlined text-primary-DEFAULT mr-2">login</span>
                Sign-in Protection
            </h2>
        </div>

        <div class="p-4 space-y-3">
            <div class="bg-blue-50 border border-blue-200 rounded px-3 py-2">
                <p class="text-[11px] text-blue-800">
                    Applies to both sign-in paths: the backend at
                    <span class="font-mono">/login</span>, keyed on email and IP, and the participant app, keyed on
                    the account itself. The participant app previously had no lockout of any kind.
                </p>
            </div>

            <div class="flex flex-col md:flex-row md:items-start gap-3">
                <label for="max_login_attempts" class="text-xs font-medium text-gray-700 md:w-44 md:pt-2">
                    Max Failed Attempts
                </label>
                <div class="flex-1">
                    <input type="number" id="max_login_attempts" name="max_login_attempts"
                           value="{{ old('max_login_attempts', $config->max_login_attempts ?? 5) }}"
                           min="1" max="20"
                           class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                           :disabled="!isEditing">
                </div>
            </div>

            <div class="flex flex-col md:flex-row md:items-start gap-3">
                <label for="lockout_duration" class="text-xs font-medium text-gray-700 md:w-44 md:pt-2">
                    Lockout Duration
                </label>
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <input type="number" id="lockout_duration" name="lockout_duration"
                               value="{{ old('lockout_duration', $config->lockout_duration ?? 15) }}"
                               min="1" max="1440"
                               class="w-32 h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                               :disabled="!isEditing">
                        <span class="text-xs text-gray-600">minutes</span>
                    </div>
                </div>
            </div>

            <div class="flex flex-col md:flex-row md:items-start gap-3">
                <label for="session_timeout" class="text-xs font-medium text-gray-700 md:w-44 md:pt-2">
                    Session Timeout
                </label>
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <input type="number" id="session_timeout" name="session_timeout"
                               value="{{ old('session_timeout', $config->session_timeout ?? 120) }}"
                               min="5" max="1440"
                               class="w-32 h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                               :disabled="!isEditing">
                        <span class="text-xs text-gray-600">minutes of inactivity</span>
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1">
                        Takes effect for sessions started after saving.
                    </p>
                </div>
            </div>

            <div class="flex flex-col md:flex-row md:items-start gap-3">
                <label for="api_token_lifetime_days" class="text-xs font-medium text-gray-700 md:w-44 md:pt-2">
                    App Token Lifetime
                </label>
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <input type="number" id="api_token_lifetime_days" name="api_token_lifetime_days"
                               value="{{ old('api_token_lifetime_days', $config->api_token_lifetime_days ?? 0) }}"
                               min="0" max="3650"
                               class="w-32 h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                               :disabled="!isEditing">
                        <span class="text-xs text-gray-600">days</span>
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1">
                        How long a participant app sign-in stays valid. <span class="font-medium">0</span> means
                        never expires, which is how the app has always behaved: all
                        <span class="font-medium">{{ $sec['participant_tokens'] }}</span> tokens ever issued are
                        still usable, including
                        <span class="font-medium">{{ $sec['stale_tokens'] }}</span> not used in the last 90 days.
                        Only newly issued tokens take the lifetime.
                    </p>
                </div>
            </div>

            <div class="flex flex-col md:flex-row md:items-start gap-3">
                <span class="text-xs font-medium text-gray-700 md:w-44 md:pt-1">Transport</span>
                <div class="flex-1">
                    <label class="flex items-center">
                        <input type="hidden" name="force_ssl" value="0">
                        <input type="checkbox" name="force_ssl" value="1"
                               class="rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                               {{ (old('force_ssl', $config->force_ssl ?? false)) ? 'checked' : '' }}
                               :disabled="!isEditing">
                        <span class="ml-2 text-xs text-gray-700">Force HTTPS</span>
                    </label>
                    <p class="text-[11px] text-gray-500 mt-1">
                        Generates every link as <span class="font-mono">https://</span>, marks the session cookie
                        secure, and sends HSTS. Currently serving over
                        <span class="font-medium">{{ $sec['current_scheme'] }}</span>.
                    </p>
                    @if(! $sec['is_https'])
                        <div class="mt-2 bg-amber-50 border border-amber-200 rounded px-3 py-2">
                            <p class="text-[11px] text-amber-800">
                                Do not switch this on until the site is actually served over HTTPS. Every link would
                                point at an address that does not answer, and the session cookie would stop being
                                sent, which locks everyone out.
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="flex flex-col md:flex-row md:items-start gap-3">
                <span class="text-xs font-medium text-gray-700 md:w-44 md:pt-1">Two-Factor</span>
                <div class="flex-1">
                    <div class="bg-gray-50 border border-gray-200 rounded px-3 py-2">
                        <p class="text-[11px] text-gray-600">
                            <span class="font-medium">Not implemented.</span> This tab carried an
                            "Enable two-factor authentication" checkbox that defaulted to on, so the page claimed
                            two-factor was active when no part of the system asked for a second factor. The
                            checkbox has been removed rather than left saying something untrue. Adding real TOTP
                            needs a secret store, an enrolment screen with a QR code, recovery codes and a
                            challenge step at sign-in.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ------------------------------------------------------------------
         Auditing
    ------------------------------------------------------------------- --}}
    <div class="bg-white border border-gray-200 rounded-md shadow-sm">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3 flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                <span class="material-icons-outlined text-primary-DEFAULT mr-2">gpp_maybe</span>
                Auditing &amp; Alerts
            </h2>
            @can('security_audit.read')
                <a href="{{ route('settings.security-audit') }}"
                   class="h-9 px-3 bg-white border border-gray-300 hover:bg-gray-50 text-xs text-gray-700 rounded flex items-center">
                    Open Security Audit
                </a>
            @endcan
        </div>

        <div class="p-4 space-y-3">
            <div class="flex flex-col md:flex-row md:items-start gap-3">
                <span class="text-xs font-medium text-gray-700 md:w-44 md:pt-1">Record</span>
                <div class="flex-1 space-y-2">
                    <label class="flex items-start">
                        <input type="hidden" name="log_failed_logins" value="0">
                        <input type="checkbox" name="log_failed_logins" value="1"
                               class="mt-0.5 rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                               {{ (old('log_failed_logins', $config->log_failed_logins ?? true)) ? 'checked' : '' }}
                               :disabled="!isEditing">
                        <span class="ml-2">
                            <span class="text-xs text-gray-700">Failed sign-in attempts</span>
                            <span class="block text-[11px] text-gray-500">
                                Backend and participant app. Lockouts and attempts on disabled accounts are always
                                recorded regardless of this setting.
                            </span>
                        </span>
                    </label>
                    <label class="flex items-start">
                        <input type="hidden" name="log_password_changes" value="0">
                        <input type="checkbox" name="log_password_changes" value="1"
                               class="mt-0.5 rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                               {{ (old('log_password_changes', $config->log_password_changes ?? true)) ? 'checked' : '' }}
                               :disabled="!isEditing">
                        <span class="ml-2">
                            <span class="text-xs text-gray-700">Password changes and resets</span>
                            <span class="block text-[11px] text-gray-500">
                                Including resets performed by an administrator on someone else's account.
                            </span>
                        </span>
                    </label>
                    <label class="flex items-start">
                        <input type="hidden" name="log_permission_changes" value="0">
                        <input type="checkbox" name="log_permission_changes" value="1"
                               class="mt-0.5 rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                               {{ (old('log_permission_changes', $config->log_permission_changes ?? true)) ? 'checked' : '' }}
                               :disabled="!isEditing">
                        <span class="ml-2">
                            <span class="text-xs text-gray-700">Role and permission changes</span>
                            <span class="block text-[11px] text-gray-500">
                                Role reassignments and edits to what a role may do.
                            </span>
                        </span>
                    </label>
                </div>
            </div>

            <div class="flex flex-col md:flex-row md:items-start gap-3">
                <span class="text-xs font-medium text-gray-700 md:w-44 md:pt-1">Alerts</span>
                <div class="flex-1">
                    <label class="flex items-center">
                        <input type="hidden" name="enable_security_alerts" value="0">
                        <input type="checkbox" name="enable_security_alerts" value="1"
                               class="rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                               {{ (old('enable_security_alerts', $config->enable_security_alerts ?? false)) ? 'checked' : '' }}
                               :disabled="!isEditing">
                        <span class="ml-2 text-xs text-gray-700">Email an administrator on security events</span>
                    </label>
                    <p class="text-[11px] text-gray-500 mt-1">
                        Sent to <span class="font-medium">{{ $sec['alert_recipient'] ?? 'no address configured' }}</span>
                        on lockouts, password changes, account locks, API key changes and edits to these settings.
                    </p>
                </div>
            </div>

            <div class="flex flex-col md:flex-row md:items-start gap-3">
                <label for="log_retention_days" class="text-xs font-medium text-gray-700 md:w-44 md:pt-2">
                    Retention
                </label>
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <input type="number" id="log_retention_days" name="log_retention_days"
                               value="{{ old('log_retention_days', $config->log_retention_days ?? 0) }}"
                               min="0" max="3650"
                               class="w-32 h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                               :disabled="!isEditing">
                        <span class="text-xs text-gray-600">days</span>
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1">
                        <span class="font-medium">0</span> keeps everything, which is what happened until now: the
                        log has {{ number_format($sec['audit_rows']) }} entries and no purge existed. Entries hold
                        email addresses, IP addresses and user agents, so a retention window is a privacy control
                        as much as a housekeeping one. Trimmed nightly by
                        <span class="font-mono">audit:purge</span>.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- ------------------------------------------------------------------
         Enforced elsewhere: read-only status of things that are not settings
    ------------------------------------------------------------------- --}}
    <div class="bg-white border border-gray-200 rounded-md shadow-sm" x-data="{ open: false }">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                <span class="material-icons-outlined text-primary-DEFAULT mr-2">verified_user</span>
                Hardening Status
            </h2>
            <button type="button" @click="open = !open"
                    class="h-9 px-3 bg-white border border-gray-300 hover:bg-gray-50 text-xs text-gray-700 rounded">
                <span x-text="open ? 'Hide' : 'Show'"></span>
            </button>
        </div>

        <div class="p-4" x-show="open" x-cloak>
            <p class="text-[11px] text-gray-500 mb-3">
                Protections that are not settings. Listed so their state is visible rather than assumed.
            </p>
            <div class="overflow-x-auto border border-gray-200 rounded">
                <table class="min-w-full border-collapse">
                    <thead>
                        <tr class="bg-primary-light text-white text-xs uppercase">
                            <th class="py-3 px-4 text-left rounded-tl">Control</th>
                            <th class="py-3 px-4 text-left">State</th>
                            <th class="py-3 px-4 text-left rounded-tr">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($sec['hardening'] as $item)
                            <tr class="text-xs hover:bg-gray-50">
                                <td class="py-3 px-4 font-medium text-gray-800">{{ $item['name'] }}</td>
                                <td class="py-3 px-4">
                                    <span class="px-1.5 py-0.5 rounded text-[10px]
                                        @if($item['state'] === 'On') bg-green-50 text-green-700
                                        @elseif($item['state'] === 'Off') bg-red-50 text-red-700
                                        @else bg-amber-50 text-amber-700 @endif">
                                        {{ $item['state'] }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-gray-500">{{ $item['note'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
