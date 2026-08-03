{{--
    Notifications tab.

    Grouped by the moment that triggers the message rather than by channel, so it
    is possible to see what a participant receives at each point instead of
    reading three separate lists.

    Six of the fifteen switches here did nothing at all: the event reminders and
    their "hours before" value had no command, job or schedule behind them, the
    welcome email for new backend users was never sent, system error reporting was
    never wired to the exception handler, and password reset emails always went
    out regardless. All are now honoured.
--}}
@php
    $notif = $notificationPanel ?? \App\Support\NotificationSurface::payload();
@endphp

<div x-show="activeTab === 'notifications'" class="space-y-4">

    {{-- ------------------------------------------------------------------
         Channel readiness
    ------------------------------------------------------------------- --}}
    <div class="bg-white border border-gray-200 rounded-md shadow-sm">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                <span class="material-icons-outlined text-primary-DEFAULT mr-2">sensors</span>
                Channels
            </h2>
        </div>

        <div class="p-4">
            <p class="text-[11px] text-gray-500 mb-3">
                A switch below can only take effect if its channel can send. This is read from the delivery
                configuration, not from the switches.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                @foreach([
                    'email' => ['label' => 'Email', 'icon' => 'mail'],
                    'sms' => ['label' => 'SMS', 'icon' => 'sms'],
                    'telegram' => ['label' => 'Telegram', 'icon' => 'send'],
                ] as $key => $meta)
                    @php $channel = $notif[$key]; @endphp
                    <div class="border rounded p-3 {{ $channel['ready'] ? 'border-green-200 bg-green-50' : 'border-amber-200 bg-amber-50' }}">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-medium {{ $channel['ready'] ? 'text-green-800' : 'text-amber-800' }} flex items-center">
                                <span class="material-icons-outlined text-sm mr-1">{{ $meta['icon'] }}</span>
                                {{ $meta['label'] }}
                            </span>
                            <span class="px-1.5 py-0.5 rounded text-[10px] {{ $channel['ready'] ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                                {{ $channel['ready'] ? 'Ready' : 'Not ready' }}
                            </span>
                        </div>
                        <p class="text-[11px] {{ $channel['ready'] ? 'text-green-700' : 'text-amber-700' }} leading-snug">
                            {{ $channel['detail'] }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ------------------------------------------------------------------
         Per-event triggers
    ------------------------------------------------------------------- --}}
    <div class="bg-white border border-gray-200 rounded-md shadow-sm">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                <span class="material-icons-outlined text-primary-DEFAULT mr-2">event_available</span>
                When Someone Registers
            </h2>
        </div>

        <div class="p-4 space-y-2">
            <label class="flex items-start">
                <input type="hidden" name="email_event_registration" value="0">
                <input type="checkbox" name="email_event_registration" value="1"
                       class="mt-0.5 rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                       {{ (old('email_event_registration', $config->email_event_registration ?? false)) ? 'checked' : '' }}>
                <span class="ml-2">
                    <span class="text-xs text-gray-700">Email the participant a confirmation</span>
                    <span class="block text-[11px] text-gray-500">
                        Uses the simplified template for events with identity verification switched off, and the full
                        template otherwise.
                    </span>
                </span>
            </label>

            <label class="flex items-start">
                <input type="hidden" name="sms_event_registration" value="0">
                <input type="checkbox" name="sms_event_registration" value="1"
                       class="mt-0.5 rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                       {{ (old('sms_event_registration', $config->sms_event_registration ?? false)) ? 'checked' : '' }}>
                <span class="ml-2">
                    <span class="text-xs text-gray-700">Text the participant a confirmation</span>
                    <span class="block text-[11px] text-gray-500">Only when a phone number was given.</span>
                </span>
            </label>

            <label class="flex items-start">
                <input type="hidden" name="telegram_event_registration" value="0">
                <input type="checkbox" name="telegram_event_registration" value="1"
                       class="mt-0.5 rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                       {{ (old('telegram_event_registration', $config->telegram_event_registration ?? false)) ? 'checked' : '' }}>
                <span class="ml-2">
                    <span class="text-xs text-gray-700">Post to the Telegram channel</span>
                    <span class="block text-[11px] text-gray-500">
                        Goes to the configured channel, so everyone in it sees each registration.
                    </span>
                </span>
            </label>

            <label class="flex items-start">
                <input type="hidden" name="admin_new_registrations" value="0">
                <input type="checkbox" name="admin_new_registrations" value="1"
                       class="mt-0.5 rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                       {{ (old('admin_new_registrations', $config->admin_new_registrations ?? false)) ? 'checked' : '' }}>
                <span class="ml-2">
                    <span class="text-xs text-gray-700">Email the event organizer</span>
                    <span class="block text-[11px] text-gray-500">
                        Sent to whoever owns the event, not to the address at the bottom of this tab.
                    </span>
                </span>
            </label>
        </div>
    </div>

    {{-- ------------------------------------------------------------------
         Reminders
    ------------------------------------------------------------------- --}}
    <div class="bg-white border border-gray-200 rounded-md shadow-sm">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                <span class="material-icons-outlined text-primary-DEFAULT mr-2">alarm</span>
                Before an Event Starts
            </h2>
        </div>

        <div class="p-4 space-y-3">
            <div class="space-y-2">
                <label class="flex items-start">
                    <input type="hidden" name="email_event_reminder" value="0">
                    <input type="checkbox" name="email_event_reminder" value="1"
                           class="mt-0.5 rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                           {{ (old('email_event_reminder', $config->email_event_reminder ?? false)) ? 'checked' : '' }}>
                    <span class="ml-2">
                        <span class="text-xs text-gray-700">Email a reminder</span>
                        <span class="block text-[11px] text-gray-500">Every active participant on the event.</span>
                    </span>
                </label>

                <label class="flex items-start">
                    <input type="hidden" name="sms_event_reminder" value="0">
                    <input type="checkbox" name="sms_event_reminder" value="1"
                           class="mt-0.5 rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                           {{ (old('sms_event_reminder', $config->sms_event_reminder ?? false)) ? 'checked' : '' }}>
                    <span class="ml-2">
                        <span class="text-xs text-gray-700">Text a reminder</span>
                        <span class="block text-[11px] text-gray-500">Participants with a phone number.</span>
                    </span>
                </label>
            </div>

            <div class="flex flex-col md:flex-row md:items-start gap-3 pt-1">
                <label for="sms_reminder_hours" class="text-xs font-medium text-gray-700 md:w-40 md:pt-2">
                    Send This Far Ahead
                </label>
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <input type="number" id="sms_reminder_hours" name="sms_reminder_hours"
                               value="{{ old('sms_reminder_hours', $config->sms_reminder_hours ?? 24) }}"
                               min="1" max="72"
                               class="w-32 h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                        <span class="text-xs text-gray-600">hours before the start</span>
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1">
                        Applies to both the email and the SMS. Each participant is reminded once per event; the
                        record of having been reminded is kept in the activity log.
                    </p>
                </div>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded px-3 py-2">
                <p class="text-[11px] text-blue-800">
                    Reminders are sent by the hourly <span class="font-mono">events:remind</span> task, so the host
                    must be running <span class="font-mono">php artisan schedule:run</span> every minute. Run
                    <span class="font-mono">php artisan events:remind --dry-run</span> to see who would be contacted
                    without sending anything.
                </p>
            </div>
        </div>
    </div>

    {{-- ------------------------------------------------------------------
         Certificates
    ------------------------------------------------------------------- --}}
    <div class="bg-white border border-gray-200 rounded-md shadow-sm">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                <span class="material-icons-outlined text-primary-DEFAULT mr-2">workspace_premium</span>
                When a Certificate Is Issued
            </h2>
        </div>

        <div class="p-4 space-y-2">
            <label class="flex items-start">
                <input type="hidden" name="email_certificate_generated" value="0">
                <input type="checkbox" name="email_certificate_generated" value="1"
                       class="mt-0.5 rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                       {{ (old('email_certificate_generated', $config->email_certificate_generated ?? false)) ? 'checked' : '' }}>
                <span class="ml-2">
                    <span class="text-xs text-gray-700">Email the certificate to the participant</span>
                    <span class="block text-[11px] text-gray-500">
                        Simplified participants receive a signed download link valid for 30 days; verified
                        participants are pointed at the app.
                    </span>
                </span>
            </label>

            <label class="flex items-start">
                <input type="hidden" name="sms_certificate_generated" value="0">
                <input type="checkbox" name="sms_certificate_generated" value="1"
                       class="mt-0.5 rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                       {{ (old('sms_certificate_generated', $config->sms_certificate_generated ?? false)) ? 'checked' : '' }}>
                <span class="ml-2">
                    <span class="text-xs text-gray-700">Text the participant</span>
                </span>
            </label>

            <label class="flex items-start">
                <input type="hidden" name="telegram_certificate_generated" value="0">
                <input type="checkbox" name="telegram_certificate_generated" value="1"
                       class="mt-0.5 rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                       {{ (old('telegram_certificate_generated', $config->telegram_certificate_generated ?? false)) ? 'checked' : '' }}>
                <span class="ml-2">
                    <span class="text-xs text-gray-700">Post to the Telegram channel</span>
                </span>
            </label>
        </div>
    </div>

    {{-- ------------------------------------------------------------------
         Accounts and system
    ------------------------------------------------------------------- --}}
    <div class="bg-white border border-gray-200 rounded-md shadow-sm">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                <span class="material-icons-outlined text-primary-DEFAULT mr-2">manage_accounts</span>
                Accounts &amp; System
            </h2>
        </div>

        <div class="p-4 space-y-3">
            <div class="space-y-2">
                <label class="flex items-start">
                    <input type="hidden" name="email_new_user_registration" value="0">
                    <input type="checkbox" name="email_new_user_registration" value="1"
                           class="mt-0.5 rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                           {{ (old('email_new_user_registration', $config->email_new_user_registration ?? false)) ? 'checked' : '' }}>
                    <span class="ml-2">
                        <span class="text-xs text-gray-700">Welcome new backend users</span>
                        <span class="block text-[11px] text-gray-500">
                            Sent when an administrator creates an account under User Management. Carries the sign-in
                            address, the email address and the role, but deliberately not the password: that would
                            leave a working credential sitting in a mailbox.
                        </span>
                    </span>
                </label>

                <label class="flex items-start">
                    <input type="hidden" name="email_password_reset" value="0">
                    <input type="checkbox" name="email_password_reset" value="1"
                           class="mt-0.5 rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                           {{ (old('email_password_reset', $config->email_password_reset ?? true)) ? 'checked' : '' }}>
                    <span class="ml-2">
                        <span class="text-xs text-gray-700">Send password reset emails</span>
                        <span class="block text-[11px] text-gray-500">
                            Covers participant app resets, whether requested from the app or performed by an
                            organizer. A reset generates a new password, so unticking this locks the holder out
                            until someone reads it to them.
                        </span>
                    </span>
                </label>

                <label class="flex items-start">
                    <input type="hidden" name="admin_system_errors" value="0">
                    <input type="checkbox" name="admin_system_errors" value="1"
                           class="mt-0.5 rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                           {{ (old('admin_system_errors', $config->admin_system_errors ?? false)) ? 'checked' : '' }}>
                    <span class="ml-2">
                        <span class="text-xs text-gray-700">Report system errors</span>
                        <span class="block text-[11px] text-gray-500">
                            Emails unhandled exceptions with the URL, the signed-in user and the location in the
                            code. Repeats of the same error are suppressed for 30 minutes, because a broken page
                            raises it on every request.
                        </span>
                    </span>
                </label>
            </div>

            <div class="bg-gray-50 border border-gray-200 rounded px-3 py-2">
                <p class="text-[11px] text-gray-600">
                    <span class="font-medium">Security alerts moved.</span> This tab also carried "Send security
                    alerts to administrators", a second switch for the same thing as the one on the
                    <span class="font-medium">Security</span> tab. Two controls for one behaviour is a trap, so the
                    duplicate has been removed; lockouts, password changes and API key events are configured there.
                </p>
            </div>
        </div>
    </div>

    {{-- ------------------------------------------------------------------
         Recipient
    ------------------------------------------------------------------- --}}
    <div class="bg-white border border-gray-200 rounded-md shadow-sm">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                <span class="material-icons-outlined text-primary-DEFAULT mr-2">alternate_email</span>
                Administrator Address
            </h2>
        </div>

        <div class="p-4">
            <div class="flex flex-col md:flex-row md:items-start gap-3">
                <label for="admin_notification_email" class="text-xs font-medium text-gray-700 md:w-40 md:pt-2">
                    Send Alerts To
                </label>
                <div class="flex-1">
                    <input type="email" id="admin_notification_email" name="admin_notification_email"
                           value="{{ old('admin_notification_email', $config->admin_notification_email ?? '') }}"
                           required
                           class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                    <p class="text-[11px] text-gray-500 mt-1">
                        Where system errors and security alerts are sent. Organizer notifications go to the event
                        owner instead.
                    </p>

                    @unless($notif['recipient_looks_real'])
                        <div class="mt-2 bg-amber-50 border border-amber-200 rounded px-3 py-2">
                            <p class="text-[11px] text-amber-800">
                                <span class="font-medium">This looks like the seeded placeholder.</span>
                                The default is <span class="font-mono">admin@sijilevents.com</span>, a domain that
                                does not exist, so anything sent to it is discarded. Change it to a mailbox someone
                                reads.
                            </p>
                        </div>
                    @endunless
                </div>
            </div>
        </div>
    </div>
</div>
