{{-- Email delivery settings. Expects: $emailConfig, $emailEnabled --}}
@php
    $saved = $emailConfig->provider ?? null;
    $s = $emailConfig->settings ?? [];

    // 'secret' means the Mailgun API key under Mailgun and the AWS secret access
    // key under SES, so a saved value is only shown in the block it belongs to.
    $val = fn (string $provider, string $key) => $saved === $provider ? ($s[$key] ?? null) : null;

    $driver = old('mail_driver', $saved ?? 'smtp');
@endphp

<form id="emailForm" method="POST" action="{{ route('config.deliver.email') }}" class="space-y-3"
      x-data="{ enabled: {{ $emailEnabled ? 'true' : 'false' }}, driver: '{{ $driver }}' }">
    @csrf

    <x-delivery-switch label="Send email using this account's own settings"
                       description="Registration confirmations, certificates and app passwords go out through the provider configured below."
                       :checked="$emailEnabled">
        Leave this off to send through the Administrator configuration instead. Email is never dropped.
    </x-delivery-switch>

    <div class="border border-gray-200 rounded">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700">Provider</h2>
        </div>

        <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="mail_driver" class="block text-xs font-medium text-gray-700 mb-1">
                    Mail driver <span class="text-red-500">*</span>
                </label>
                <select name="mail_driver" id="mail_driver" x-model="driver"
                        class="w-full h-9 text-xs border-gray-300 rounded-[1px] px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50 leading-[1rem]">
                    <option value="smtp">SMTP</option>
                    <option value="mailgun">Mailgun</option>
                    <option value="ses">Amazon SES</option>
                    <option value="sendmail">Sendmail</option>
                </select>
                @error('mail_driver')
                    <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <div class="border border-gray-200 rounded" x-show="driver === 'smtp'" x-cloak>
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700">SMTP</h2>
        </div>

        <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-delivery-field name="mail_host" label="Host" :value="$val('smtp', 'host')"
                              placeholder="smtp.example.com" :required="true" />

            <x-delivery-field name="mail_port" label="Port" :value="$val('smtp', 'port')"
                              placeholder="587" :required="true" />

            <x-delivery-field name="mail_username" label="Username" :value="$val('smtp', 'username')"
                              :required="true" />

            <x-delivery-field name="mail_password" label="Password" type="password"
                              :value="$val('smtp', 'password')" :required="true" />

            <div>
                <label for="mail_encryption" class="block text-xs font-medium text-gray-700 mb-1">
                    Encryption <span class="text-red-500">*</span>
                </label>
                @php $encryption = old('mail_encryption', $val('smtp', 'encryption') ?? 'tls'); @endphp
                <select name="mail_encryption" id="mail_encryption"
                        class="w-full h-9 text-xs border-gray-300 rounded-[1px] px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50 leading-[1rem]">
                    <option value="tls" {{ $encryption === 'tls' ? 'selected' : '' }}>TLS</option>
                    <option value="ssl" {{ $encryption === 'ssl' ? 'selected' : '' }}>SSL</option>
                    <option value="none" {{ $encryption === 'none' ? 'selected' : '' }}>None</option>
                </select>
                @error('mail_encryption')
                    <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <div class="border border-gray-200 rounded" x-show="driver === 'mailgun'" x-cloak>
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700">Mailgun</h2>
        </div>

        <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-delivery-field name="mailgun_domain" label="Domain" :value="$val('mailgun', 'domain')"
                              placeholder="mg.example.com" :required="true" />

            <x-delivery-field name="mailgun_secret" label="API key" type="password"
                              :value="$val('mailgun', 'secret')" :required="true" />

            <x-delivery-field name="mailgun_endpoint" label="Endpoint" :value="$val('mailgun', 'endpoint')"
                              placeholder="api.mailgun.net" :required="true"
                              help="Use api.eu.mailgun.net for EU accounts." />
        </div>
    </div>

    <div class="border border-gray-200 rounded" x-show="driver === 'ses'" x-cloak>
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700">Amazon SES</h2>
        </div>

        <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-delivery-field name="ses_key" label="Access key ID" :value="$val('ses', 'key')"
                              :required="true" />

            <x-delivery-field name="ses_secret" label="Secret access key" type="password"
                              :value="$val('ses', 'secret')" :required="true" />

            <x-delivery-field name="ses_region" label="Region" :value="$val('ses', 'region')"
                              placeholder="ap-southeast-1" :required="true" />
        </div>
    </div>

    <div class="border border-gray-200 rounded" x-show="driver === 'sendmail'" x-cloak>
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700">Sendmail</h2>
        </div>

        <div class="p-4">
            <p class="text-xs text-gray-500">
                Uses the sendmail binary on the server. There is nothing to configure here beyond the
                sender identity below.
            </p>
        </div>
    </div>

    <div class="border border-gray-200 rounded">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700">Sender identity</h2>
        </div>

        <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-delivery-field name="mail_from_address" label="From address" type="email"
                              :value="$s['from_address'] ?? null" placeholder="no-reply@example.com"
                              :required="true"
                              help="Recipients see this as the sender. It has to be an address the provider is allowed to send from." />

            <x-delivery-field name="mail_from_name" label="From name" :value="$s['from_name'] ?? null"
                              placeholder="e-Certificate" :required="true" />
        </div>
    </div>

    <div class="border border-gray-200 rounded">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700">Send a test</h2>
        </div>

        <div class="p-4">
            <p class="text-xs text-gray-500 mb-3">
                Uses the settings already saved, not what is currently on screen. Save first.
            </p>

            <div class="flex flex-wrap items-center gap-2">
                <input type="email" id="test_email"
                       placeholder="you@example.com"
                       class="h-9 text-xs border-gray-300 rounded-[1px] px-3 w-full sm:w-72 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">

                <button type="button" onclick="sendTestEmailToAddress()"
                        class="h-9 px-3 border border-gray-300 rounded text-xs font-medium text-gray-700 hover:bg-gray-50 inline-flex items-center shrink-0">
                    <span class="material-icons-outlined text-sm mr-1">send</span>
                    Send test email
                </button>
            </div>
        </div>
    </div>

    @can('delivery.update')
        <div class="flex justify-end pt-1">
            <button type="submit"
                    class="h-9 px-4 rounded text-xs font-medium text-white bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 inline-flex items-center shadow-sm">
                <span class="material-icons-outlined text-sm mr-1">save</span>
                Save email settings
            </button>
        </div>
    @endcan
</form>
