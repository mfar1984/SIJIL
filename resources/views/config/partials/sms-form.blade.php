{{-- SMS delivery settings. Expects: $smsConfig, $smsEnabled --}}
@php
    $saved = $smsConfig->provider ?? null;
    $settings = $smsConfig->settings ?? [];

    // Providers share setting names - 'key', 'secret', 'from' all appear in more
    // than one - so a saved value is only shown in the block it belongs to.
    // Without this, an Infobip API key would appear in the Nexmo key field.
    $val = fn (string $provider, string $key) => $saved === $provider ? ($settings[$key] ?? null) : null;

    // Infobip is the default because it is the only provider with a service class
    // behind it. Offering Twilio first invited accounts to configure a dead channel.
    $provider = old('sms_provider', $saved ?? 'infobip');
@endphp

<form id="smsForm" method="POST" action="{{ route('config.deliver.sms') }}" class="space-y-3"
      x-data="{ enabled: {{ $smsEnabled ? 'true' : 'false' }}, provider: '{{ $provider }}' }">
    @csrf

    <x-delivery-switch label="Send SMS using this account's own gateway"
                       description="Certificate and attendance notifications are also sent by SMS through the gateway configured below."
                       :checked="$smsEnabled">
        SMS has no fallback. Leave this off and no SMS is sent for this account, by design: an SMS is
        billed to whoever owns the gateway and arrives from a sender the recipient cannot reply to.
    </x-delivery-switch>

    <div class="border border-gray-200 rounded">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700">Gateway</h2>
        </div>

        <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="sms_provider" class="block text-xs font-medium text-gray-700 mb-1">
                    Provider <span class="text-red-500">*</span>
                </label>
                <select name="sms_provider" id="sms_provider" x-model="provider"
                        class="w-full h-9 text-xs border-gray-300 rounded-[1px] px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50 leading-[1rem]">
                    <option value="infobip">Infobip</option>
                    <option value="twilio">Twilio</option>
                    <option value="nexmo">Nexmo (Vonage)</option>
                    <option value="aws_sns">AWS SNS</option>
                </select>
                @error('sms_provider')
                    <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>
                @enderror

                <p class="text-[11px] text-amber-700 mt-1" x-show="provider !== 'infobip'" x-cloak>
                    Only Infobip can currently send. Settings for the other gateways can be saved but
                    nothing will go out.
                </p>
            </div>
        </div>
    </div>

    <div class="border border-gray-200 rounded" x-show="provider === 'infobip'" x-cloak>
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700">Infobip</h2>
        </div>

        <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-delivery-field name="infobip_key" label="API key" type="password"
                              :value="$val('infobip', 'key')" :required="true" />

            <x-delivery-field name="infobip_base_url" label="Base URL"
                              :value="$val('infobip', 'base_url')"
                              placeholder="xxxxx.api.infobip.com" :required="true"
                              help="The personalised host shown on the Infobip dashboard." />

            <x-delivery-field name="infobip_from" label="Sender ID"
                              :value="$val('infobip', 'from')" placeholder="e-Certificate"
                              :required="true" />
        </div>
    </div>

    <div class="border border-gray-200 rounded" x-show="provider === 'twilio'" x-cloak>
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700">Twilio</h2>
        </div>

        <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-delivery-field name="twilio_sid" label="Account SID"
                              :value="$val('twilio', 'sid')" :required="true" />

            <x-delivery-field name="twilio_token" label="Auth token" type="password"
                              :value="$val('twilio', 'token')" :required="true" />

            <x-delivery-field name="twilio_from" label="From number"
                              :value="$val('twilio', 'from')" placeholder="+15551234567"
                              :required="true" />
        </div>
    </div>

    <div class="border border-gray-200 rounded" x-show="provider === 'nexmo'" x-cloak>
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700">Nexmo (Vonage)</h2>
        </div>

        <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-delivery-field name="nexmo_key" label="API key"
                              :value="$val('nexmo', 'key')" :required="true" />

            <x-delivery-field name="nexmo_secret" label="API secret" type="password"
                              :value="$val('nexmo', 'secret')" :required="true" />

            <x-delivery-field name="nexmo_from" label="From number or name"
                              :value="$val('nexmo', 'from')" :required="true" />
        </div>
    </div>

    <div class="border border-gray-200 rounded" x-show="provider === 'aws_sns'" x-cloak>
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700">AWS SNS</h2>
        </div>

        <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-delivery-field name="aws_key" label="Access key ID"
                              :value="$val('aws_sns', 'key')" :required="true" />

            <x-delivery-field name="aws_secret" label="Secret access key" type="password"
                              :value="$val('aws_sns', 'secret')" :required="true" />

            <x-delivery-field name="aws_region" label="Region"
                              :value="$val('aws_sns', 'region')" placeholder="ap-southeast-1"
                              :required="true" />
        </div>
    </div>

    <div class="border border-gray-200 rounded">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700">Default message</h2>
        </div>

        <div class="p-4">
            <label for="sms_template" class="block text-xs font-medium text-gray-700 mb-1">Template</label>
            <textarea name="sms_template" id="sms_template" rows="3"
                      placeholder="Hi {name}, your certificate for {event} is ready."
                      class="w-full text-xs border-gray-300 rounded-[1px] px-3 py-2 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">{{ old('sms_template', $smsConfig->default_template ?? '') }}</textarea>
            @error('sms_template')
                <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>
            @enderror
            <p class="text-[11px] text-gray-500 mt-1">
                Used when a notification does not carry its own wording. Keep it short: one SMS is 160
                characters.
            </p>
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

            <button type="button" onclick="sendTestSms()"
                    class="h-9 px-3 border border-gray-300 rounded text-xs font-medium text-gray-700 hover:bg-gray-50 inline-flex items-center shrink-0">
                <span class="material-icons-outlined text-sm mr-1">sms</span>
                Send test SMS
            </button>
        </div>
    </div>

    @can('delivery.update')
        <div class="flex justify-end pt-1">
            <button type="submit"
                    class="h-9 px-4 rounded text-xs font-medium text-white bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 inline-flex items-center shadow-sm">
                <span class="material-icons-outlined text-sm mr-1">save</span>
                Save SMS settings
            </button>
        </div>
    @endcan
</form>
