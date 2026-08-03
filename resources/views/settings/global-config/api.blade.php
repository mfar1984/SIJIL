{{--
    API & Integrations tab.

    Every figure here is read from the live router and the database through
    App\Support\ApiSurface. The previous version of this tab described an API
    that did not exist: API keys with no table, OAuth with no package, a rate
    limit no middleware read, and a CORS allow-list that config/cors.php ignored.
    Reading the router at render time is what keeps the page honest.
--}}
@php
    $api = $apiPanel ?? \App\Support\ApiSurface::payload();
    $summary = $api['summary'];
    $origins = \App\Support\ApiSurface::originList(old('cors_domains', $config->cors_domains ?? ''));
@endphp

{{--
    Key and webhook actions talk to the server with fetch rather than with a form
    per row. This tab is rendered inside the Global Config form, and a form
    nested in a form is invalid HTML that browsers silently discard.
--}}
<div x-show="activeTab === 'api'" class="space-y-4"
     x-data="apiIntegrations({
        keysStore: '{{ route('settings.api.keys.store') }}',
        webhooksStore: '{{ route('settings.api.webhooks.store') }}',
        base: '{{ url('settings/api-integrations') }}',
        apiBase: @js($api['base_url']),
        authHeader: @js($api['auth_header']),
     }, @js($api['keys']), @js($api['webhooks']), @js($api['deliveries']))">

    {{-- Result banner shared by every action on this tab --}}
    <template x-if="notice.message">
        <div :class="notice.ok
                ? 'bg-green-50 border-green-200 text-green-800'
                : 'bg-red-50 border-red-200 text-red-800'"
             class="border rounded px-4 py-2 text-xs flex items-start justify-between gap-3">
            <span x-text="notice.message"></span>
            <button type="button" @click="notice = {}" class="shrink-0 text-current opacity-60 hover:opacity-100">
                <span class="material-icons-outlined text-sm">close</span>
            </button>
        </div>
    </template>

    {{--
        The generated secret, shown once, as a modal.

        This began as an inline banner, which could be scrolled past and
        dismissed without noticing that the value was the only copy. A modal that
        has to be acknowledged is the only place this value ever exists outside
        the operator's clipboard.
    --}}
    <div x-show="revealed.secret" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60"
         @keydown.escape.window="/* deliberately inert: closing must be explicit */">
        <div class="bg-white rounded-md shadow-xl border border-gray-300 w-full max-w-2xl max-h-[90vh] overflow-y-auto"
             @click.outside="/* also inert, for the same reason */">
            <div class="bg-amber-50 border-b border-amber-200 px-4 py-3 flex items-center">
                <span class="material-icons-outlined text-amber-600 mr-2">key</span>
                <h3 class="text-sm font-semibold text-amber-900" x-text="revealed.title"></h3>
            </div>

            <div class="p-4 space-y-3">
                <div class="bg-red-50 border border-red-200 rounded px-3 py-2">
                    <p class="text-[11px] text-red-800">
                        <span class="font-medium">This is shown once and cannot be recovered.</span>
                        Only a SHA-256 hash is stored, so nobody &mdash; including an administrator reading the
                        database &mdash; can retrieve it later. Copy it into the receiving system now. If it is lost,
                        use <span class="font-medium">Regenerate</span> on the key to issue a replacement.
                    </p>
                </div>

                <div>
                    <label class="text-xs font-medium text-gray-700">Secret</label>
                    <div class="mt-1 flex items-center gap-2">
                        <input type="text" readonly :value="revealed.secret" x-ref="secretField"
                               @click="$refs.secretField.select()"
                               class="w-full h-9 text-xs font-mono border-gray-300 rounded px-3 bg-gray-50 text-gray-900">
                        <button type="button" @click="copy(revealed.secret, 'secret')"
                                class="h-9 px-3 text-xs rounded shrink-0 text-white"
                                :class="copied === 'secret' ? 'bg-green-600 hover:bg-green-700' : 'bg-primary-DEFAULT hover:bg-primary-dark'">
                            <span x-text="copied === 'secret' ? 'Copied' : 'Copy'"></span>
                        </button>
                    </div>
                    {{--
                        Feedback has to live inside the modal. The shared notice
                        banner sits at the top of the tab, behind the overlay, so a
                        failure reported there would never be seen.
                    --}}
                    <p x-show="copyFailed" class="text-[11px] text-red-700 mt-1">
                        The browser blocked the clipboard. Click the box to select the value, then press
                        <span class="font-medium">Ctrl+C</span>.
                    </p>
                </div>

                <template x-if="revealed.kind === 'key'">
                    <div class="border-t border-gray-200 pt-3 space-y-2">
                        <div>
                            <label class="text-xs font-medium text-gray-700">Ready-to-run request</label>
                            <div class="mt-1 flex items-center gap-2">
                                <input type="text" readonly :value="curlFor(revealed.secret)" x-ref="curlField"
                                       @click="$refs.curlField.select()"
                                       class="w-full h-9 text-[11px] font-mono border-gray-300 rounded px-3 bg-gray-50 text-gray-700">
                                <button type="button" @click="copy(curlFor(revealed.secret), 'curl')"
                                        class="h-9 px-3 text-xs rounded shrink-0"
                                        :class="copied === 'curl'
                                            ? 'bg-green-600 hover:bg-green-700 text-white'
                                            : 'bg-gray-100 hover:bg-gray-200 text-gray-700'">
                                    <span x-text="copied === 'curl' ? 'Copied' : 'Copy'"></span>
                                </button>
                            </div>
                        </div>

                        <div>
                            <button type="button" @click="verifyKey(revealed.secret)" :disabled="verifying"
                                    class="h-9 px-3 bg-gray-700 hover:bg-gray-800 text-white text-xs rounded disabled:opacity-50">
                                <span x-text="verifying ? 'Testing...' : 'Test this key now'"></span>
                            </button>
                            <template x-if="verifyResult">
                                <pre class="mt-2 bg-gray-50 border border-gray-200 rounded p-2 text-[11px] font-mono text-gray-700 overflow-x-auto"
                                     x-text="verifyResult"></pre>
                            </template>
                        </div>
                    </div>
                </template>

                <template x-if="revealed.kind === 'webhook'">
                    <div class="border-t border-gray-200 pt-3">
                        <p class="text-[11px] text-gray-600">
                            Give this to the receiving system. It needs it to verify the
                            <span class="font-mono">{{ $api['signature_header'] }}</span> header on every delivery.
                            The sample verification code is in
                            <span class="font-medium">Using the Integration API</span> below.
                        </p>
                    </div>
                </template>
            </div>

            <div class="bg-gray-50 border-t border-gray-200 px-4 py-3 flex items-center justify-between gap-3">
                <label class="flex items-center">
                    <input type="checkbox" x-model="acknowledged"
                           class="rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                    <span class="ml-2 text-xs text-gray-700">I have saved this value somewhere safe</span>
                </label>
                <button type="button" :disabled="! acknowledged"
                        @click="revealed = {}; verifyResult = ''; acknowledged = false; copied = false; copyFailed = false"
                        class="h-9 px-4 bg-green-600 hover:bg-green-700 text-white text-xs rounded disabled:bg-gray-200 disabled:text-gray-400">
                    Close
                </button>
            </div>
        </div>
    </div>

    {{-- ------------------------------------------------------------------
         Summary tiles
    ------------------------------------------------------------------- --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-blue-50 rounded-md p-4 border border-blue-100">
            <p class="text-xs text-blue-700 font-medium">Live Endpoints</p>
            <p class="text-2xl font-bold text-blue-800">{{ $summary['endpoints'] }}</p>
            <p class="text-[11px] text-blue-600 mt-1">{{ $summary['public'] }} public</p>
        </div>
        <div class="bg-green-50 rounded-md p-4 border border-green-100">
            <p class="text-xs text-green-700 font-medium">Active API Keys</p>
            <p class="text-2xl font-bold text-green-800">{{ $summary['active_keys'] }}</p>
            <p class="text-[11px] text-green-600 mt-1">
                {{ $summary['expiring_keys'] }} expiring within 14 days
            </p>
        </div>
        <div class="bg-amber-50 rounded-md p-4 border border-amber-100">
            <p class="text-xs text-amber-700 font-medium">Active Webhooks</p>
            <p class="text-2xl font-bold text-amber-800">{{ $summary['active_webhooks'] }}</p>
            <p class="text-[11px] text-amber-600 mt-1">{{ $summary['event_types'] }} event types available</p>
        </div>
        <div class="{{ $summary['failures_24h'] > 0 ? 'bg-red-50 border-red-100' : 'bg-gray-50 border-gray-200' }} rounded-md p-4 border">
            <p class="text-xs {{ $summary['failures_24h'] > 0 ? 'text-red-700' : 'text-gray-600' }} font-medium">
                Failed Deliveries (24h)
            </p>
            <p class="text-2xl font-bold {{ $summary['failures_24h'] > 0 ? 'text-red-800' : 'text-gray-700' }}">
                {{ $summary['failures_24h'] }}
            </p>
            <p class="text-[11px] {{ $summary['failures_24h'] > 0 ? 'text-red-600' : 'text-gray-500' }} mt-1">
                {{ $summary['disabled_webhooks'] }} endpoint(s) auto-disabled
            </p>
        </div>
    </div>

    {{-- ------------------------------------------------------------------
         Panel 1: API status
    ------------------------------------------------------------------- --}}
    <div class="bg-white border border-gray-200 rounded-md shadow-sm">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                <span class="material-icons-outlined text-primary-DEFAULT mr-2">api</span>
                API Status
            </h2>
        </div>

        <div class="p-4 space-y-3">
            @php $apiEnabled = (bool) old('api_enabled', $config->api_enabled ?? true); @endphp

            <div class="flex flex-col md:flex-row md:items-start gap-3">
                <label class="text-xs font-medium text-gray-700 md:w-40 md:pt-1">Availability</label>
                <div class="flex-1">
                    <div class="flex items-center">
                        <label class="inline-flex items-center mr-4">
                            <input type="radio" name="api_enabled" value="1"
                                   class="text-primary-DEFAULT focus:ring-primary-light"
                                   {{ $apiEnabled ? 'checked' : '' }}>
                            <span class="ml-2 text-xs text-gray-700">Enabled</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="api_enabled" value="0"
                                   class="text-primary-DEFAULT focus:ring-primary-light"
                                   {{ $apiEnabled ? '' : 'checked' }}>
                            <span class="ml-2 text-xs text-gray-700">Disabled</span>
                        </label>
                    </div>
                    <div class="mt-2 bg-amber-50 border border-amber-200 rounded px-3 py-2">
                        <p class="text-[11px] text-amber-800">
                            Disabling the API stops the participant app at
                            <span class="font-medium">user.e-certificate.com.my</span> from signing in, loading
                            certificates and scanning attendance. Public certificate verification stays available.
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex flex-col md:flex-row md:items-center gap-3">
                <label class="text-xs font-medium text-gray-700 md:w-40">Base URL</label>
                <div class="flex-1 flex items-center gap-2">
                    <input type="text" readonly value="{{ $api['base_url'] }}"
                           class="w-full h-9 text-xs border-gray-300 rounded px-3 bg-gray-50 text-gray-600">
                    {{-- Routed through copy() so it works on an insecure origin too. --}}
                    <button type="button"
                            class="h-9 px-3 text-xs rounded shrink-0"
                            :class="copied === 'base'
                                ? 'bg-green-600 hover:bg-green-700 text-white'
                                : 'bg-gray-100 hover:bg-gray-200 text-gray-700'"
                            @click="copy(@js($api['base_url']), 'base')">
                        <span x-text="copied === 'base' ? 'Copied' : 'Copy'"></span>
                    </button>
                </div>
            </div>

            <div class="flex flex-col md:flex-row md:items-start gap-3">
                <label for="api_rate_limit" class="text-xs font-medium text-gray-700 md:w-40 md:pt-2">
                    Rate Limit
                </label>
                <div class="flex-1">
                    <input type="number" id="api_rate_limit" name="api_rate_limit"
                           value="{{ old('api_rate_limit', $config->api_rate_limit ?? 60) }}"
                           min="10" max="1000"
                           class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                    <p class="text-[11px] text-gray-500 mt-1">
                        Requests per minute per client, applied to ordinary endpoints. Six sign-in and identity
                        endpoints keep their own tighter limits and are unaffected by this number; they are marked
                        in the endpoint list below.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- ------------------------------------------------------------------
         Panel 2: Endpoint inventory, read from the router
    ------------------------------------------------------------------- --}}
    <div class="bg-white border border-gray-200 rounded-md shadow-sm"
         x-data="{ search: '', riskyOnly: false }">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                <span class="material-icons-outlined text-primary-DEFAULT mr-2">lan</span>
                Endpoints
                <span class="ml-2 text-gray-400 font-normal">({{ $summary['endpoints'] }})</span>
            </h2>
            <div class="flex items-center gap-3">
                <label class="flex items-center text-xs text-gray-600">
                    <input type="checkbox" x-model="riskyOnly"
                           class="rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                    <span class="ml-2">Public and unlimited only</span>
                </label>
                <input type="text" x-model="search" placeholder="Filter path"
                       class="w-48 h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
            </div>
        </div>

        <div class="p-4">
            @if($summary['unlimited_public'] > 0)
                <div class="mb-3 bg-red-50 border border-red-200 rounded px-3 py-2">
                    <p class="text-[11px] text-red-800">
                        <span class="font-medium">{{ $summary['unlimited_public'] }} endpoint(s) are reachable by
                        anyone with no rate limit at all.</span>
                        Tick the filter above to list them. Nothing throttles them today because the API middleware
                        group was never wired up, so the limit setting above has no effect until it is.
                    </p>
                </div>
            @endif

            <div class="overflow-x-auto border border-gray-200 rounded">
                <table class="min-w-full border-collapse">
                    <thead>
                        <tr class="bg-primary-light text-white text-xs uppercase">
                            <th class="py-3 px-4 text-left rounded-tl">Method</th>
                            <th class="py-3 px-4 text-left">Path</th>
                            <th class="py-3 px-4 text-left">Group</th>
                            <th class="py-3 px-4 text-left">Auth</th>
                            <th class="py-3 px-4 text-left">Limit</th>
                            <th class="py-3 px-4 text-center rounded-tr">Risk</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($api['endpoints'] as $endpoint)
                            @php $risky = $endpoint['is_public'] && ! $endpoint['has_limit']; @endphp
                            <tr class="text-xs hover:bg-gray-50"
                                x-show="(!riskyOnly || {{ $risky ? 'true' : 'false' }})
                                        && ('{{ strtolower($endpoint['uri']) }}'.includes(search.toLowerCase()))">
                                <td class="py-3 px-4 font-medium text-gray-700 whitespace-nowrap">
                                    {{ $endpoint['method'] }}
                                </td>
                                <td class="py-3 px-4 font-mono text-[11px] text-gray-800">{{ $endpoint['uri'] }}</td>
                                <td class="py-3 px-4 text-gray-500">{{ $endpoint['group'] }}</td>
                                <td class="py-3 px-4">
                                    @if($endpoint['auth'] === 'Public')
                                        <span class="px-1.5 py-0.5 rounded bg-gray-100 text-gray-600 text-[10px]">Public</span>
                                    @else
                                        <span class="px-1.5 py-0.5 rounded bg-blue-50 text-blue-700 text-[10px]">{{ $endpoint['auth'] }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-gray-600 whitespace-nowrap">
                                    {{ $endpoint['limit'] }}
                                    @if($endpoint['hardened'])
                                        <span class="ml-1 text-[10px] text-gray-400">fixed</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($risky)
                                        <span class="material-icons-outlined text-red-500 text-sm" title="Public with no rate limit">warning</span>
                                    @else
                                        <span class="material-icons-outlined text-green-500 text-sm" title="Guarded">check_circle</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="text-[11px] text-gray-400 mt-2">
                Built from the route table when this page loaded, so it cannot fall out of step with the code.
                Endpoints marked <span class="text-gray-500">fixed</span> ignore the global limit on purpose.
            </p>
        </div>
    </div>

    {{-- ------------------------------------------------------------------
         Panel 3: API keys
    ------------------------------------------------------------------- --}}
    <div class="bg-white border border-gray-200 rounded-md shadow-sm" x-data="{ creating: false }">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                <span class="material-icons-outlined text-primary-DEFAULT mr-2">vpn_key</span>
                API Keys
                <span class="ml-2 text-gray-400 font-normal" x-text="'(' + keys.length + ')'"></span>
            </h2>
            <button type="button"
                    @click="creating = !creating"
                    @disabled(! $api['storage_ready'])
                    class="h-9 px-3 text-xs rounded flex items-center shrink-0 transition-colors duration-200 ease-in-out
                           {{ $api['storage_ready'] ? 'bg-primary-DEFAULT hover:bg-primary-dark text-white' : 'bg-gray-100 text-gray-400 cursor-not-allowed' }}">
                <span class="material-icons-outlined text-xs mr-1">add</span>
                Generate Key
            </button>
        </div>

        <div class="p-4">
            @unless($api['storage_ready'])
                <div class="mb-3 bg-gray-50 border border-gray-200 rounded px-3 py-2">
                    <p class="text-[11px] text-gray-600">
                        <span class="font-medium">Storage for API keys is not installed yet.</span>
                        The layout below is final; the table, the generate action and the revoke action start working
                        once the migration that creates <span class="font-mono">api_keys</span> has run.
                    </p>
                </div>
            @endunless

            {{-- Generate form, opened inline above the table --}}
            <div x-show="creating" x-cloak class="mb-4 border border-gray-200 rounded p-4 bg-gray-50">
                <div class="space-y-3">
                    <div class="flex flex-col md:flex-row md:items-center gap-3">
                        <label class="text-xs font-medium text-gray-700 md:w-40">Name</label>
                        <div class="flex-1">
                            <input type="text" x-model="keyForm.name" placeholder="e.g. HRMS portal"
                                   class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                        </div>
                    </div>
                    <div class="flex flex-col md:flex-row md:items-start gap-3">
                        <label class="text-xs font-medium text-gray-700 md:w-40 md:pt-1">Abilities</label>
                        <div class="flex-1">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                @foreach(\App\Models\ApiKey::availableAbilities() as $ability => $label)
                                    <label class="flex items-center">
                                        <input type="checkbox" value="{{ $ability }}" x-model="keyForm.abilities"
                                               class="rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                                        <span class="ml-2 text-xs text-gray-700">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <p class="text-[11px] text-gray-500 mt-1">
                                A key with no abilities can authenticate but read nothing, which is the safe default
                                if you are unsure.
                            </p>
                        </div>
                    </div>
                    <div class="flex flex-col md:flex-row md:items-center gap-3">
                        <label class="text-xs font-medium text-gray-700 md:w-40">Expires after</label>
                        <div class="flex-1 flex items-center gap-3">
                            <input type="number" x-model.number="keyForm.expires_in_days" min="1" max="3650"
                                   :disabled="keyForm.never_expires"
                                   class="w-32 h-9 text-xs border-gray-300 rounded px-3 disabled:bg-gray-100 disabled:text-gray-400 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                            <span class="text-xs text-gray-600">days</span>
                            <label class="flex items-center ml-2">
                                <input type="checkbox" x-model="keyForm.never_expires"
                                       class="rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                                <span class="ml-2 text-xs text-gray-700">Never expires</span>
                            </label>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-1">
                        <button type="button" @click="creating = false"
                                class="h-9 px-3 bg-white border border-gray-300 hover:bg-gray-50 text-xs text-gray-700 rounded">
                            Cancel
                        </button>
                        <button type="button" @click="createKey().then(ok => { if (ok) creating = false })"
                                :disabled="busy"
                                class="h-9 px-3 text-xs rounded bg-green-600 hover:bg-green-700 text-white disabled:opacity-50">
                            <span x-text="busy ? 'Generating...' : 'Generate'"></span>
                        </button>
                    </div>
                    <p class="text-[11px] text-gray-500 border-t border-gray-200 pt-2">
                        The secret is shown once, immediately after generation. Only a hash is stored, so it cannot
                        be retrieved again; a lost key has to be revoked and replaced.
                    </p>
                </div>
            </div>

            {{--
                Rendered from Alpine state seeded by the server, so generating or
                revoking a key updates the table immediately. It was previously
                rendered by Blade, which meant a new key was invisible until the
                page was reloaded by hand.
            --}}
            <div x-show="keys.length === 0" class="bg-gray-50 border border-gray-200 rounded p-8 text-center">
                <span class="material-icons-outlined text-gray-400" style="font-size: 40px;">vpn_key</span>
                <p class="text-xs text-gray-600 mt-2 font-medium">No API keys yet</p>
                <p class="text-[11px] text-gray-500 mt-1 max-w-md mx-auto">
                    API keys are for external systems that pull data from this system. The participant app does
                    not use them &mdash; it signs in with Sanctum tokens, listed further down this tab.
                </p>
            </div>

            <div x-show="keys.length > 0" class="overflow-x-auto border border-gray-200 rounded">
                <table class="min-w-full border-collapse">
                    <thead>
                        <tr class="bg-primary-light text-white text-xs uppercase">
                            <th class="py-3 px-4 text-left rounded-tl">Name</th>
                            <th class="py-3 px-4 text-left">Identifier</th>
                            <th class="py-3 px-4 text-left">Abilities</th>
                            <th class="py-3 px-4 text-left">Last Used</th>
                            <th class="py-3 px-4 text-left">Calls</th>
                            <th class="py-3 px-4 text-left">Expires</th>
                            <th class="py-3 px-4 text-left">Status</th>
                            <th class="py-3 px-4 text-center rounded-tr">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="key in keys" :key="key.id">
                            <tr class="text-xs hover:bg-gray-50">
                                <td class="py-3 px-4 font-medium text-gray-800" x-text="key.name"></td>
                                <td class="py-3 px-4 font-mono text-[11px] text-gray-600" x-text="key.prefix + '...'"></td>
                                <td class="py-3 px-4 text-gray-500"
                                    x-text="key.abilities.length ? key.abilities.join(', ') : 'None'"></td>
                                <td class="py-3 px-4 text-gray-500" x-text="key.last_used"></td>
                                <td class="py-3 px-4 text-gray-500" x-text="key.requests"></td>
                                <td class="py-3 px-4 text-gray-500" x-text="key.expires"></td>
                                <td class="py-3 px-4">
                                    <span class="px-1.5 py-0.5 rounded text-[10px]"
                                          :class="{
                                            'bg-green-50 text-green-700': key.status === 'Active',
                                            'bg-red-50 text-red-700': key.status === 'Revoked',
                                            'bg-amber-50 text-amber-700': key.status === 'Expired',
                                          }"
                                          x-text="key.status"></span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex justify-center space-x-2">
                                        <button type="button" title="Regenerate secret"
                                                x-show="key.status !== 'Expired'" :disabled="busy"
                                                @click="regenerateKey(key)"
                                                class="p-1 bg-amber-50 rounded hover:bg-amber-100 border border-amber-100 disabled:opacity-50">
                                            <span class="material-icons-outlined text-amber-600 text-xs">autorenew</span>
                                        </button>
                                        <button type="button" title="Revoke" x-show="key.active" :disabled="busy"
                                                @click="revokeKey(key)"
                                                class="p-1 bg-red-50 rounded hover:bg-red-100 border border-red-100 disabled:opacity-50">
                                            <span class="material-icons-outlined text-red-600 text-xs">block</span>
                                        </button>
                                        <span x-show="key.status === 'Expired'" class="text-[10px] text-gray-400">&mdash;</span>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div x-show="keys.length > 0" class="mt-2 flex items-start gap-2">
                <span class="material-icons-outlined text-gray-400 text-sm mt-0.5">info</span>
                <p class="text-[11px] text-gray-500">
                    <span class="font-medium">Identifier is not the key.</span> It is the first 12 characters, kept so
                    a key can be recognised here and matched quickly on each request. The key itself is 43 characters
                    and is shown only at the moment it is created, because only its hash is stored. Lost one?
                    <span class="material-icons-outlined text-amber-600" style="font-size:13px;vertical-align:-2px">autorenew</span>
                    <span class="font-medium">Regenerate</span> issues a replacement and keeps the name, abilities and
                    expiry; the old secret stops working straight away.
                </p>
            </div>
        </div>
    </div>

    {{-- ------------------------------------------------------------------
         Panel 3b: What an API key is for, and how to use it.

         Added because generating a credential answered the wrong question. The
         page produced a secret and then said nothing about what to call with it
         or how to tell whether it worked.
    ------------------------------------------------------------------- --}}
    <div class="bg-white border border-gray-200 rounded-md shadow-sm" x-data="{ open: false }">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                <span class="material-icons-outlined text-primary-DEFAULT mr-2">menu_book</span>
                Using the Integration API
            </h2>
            <button type="button" @click="open = !open"
                    class="h-9 px-3 bg-white border border-gray-300 hover:bg-gray-50 text-xs text-gray-700 rounded">
                <span x-text="open ? 'Hide' : 'Show'"></span>
            </button>
        </div>

        <div class="p-4" x-show="open" x-cloak>
            <div class="bg-blue-50 border border-blue-200 rounded px-3 py-2 mb-4">
                <p class="text-[11px] text-blue-800">
                    An API key lets another system read data from here. It is read-only, it cannot create or change
                    anything, and it is not how the participant app signs in. Send it in the
                    <span class="font-mono">{{ $api['auth_header'] }}</span> header on every request.
                </p>
            </div>

            <h3 class="text-xs font-medium text-gray-700 mb-2">Available endpoints</h3>
            <div class="overflow-x-auto border border-gray-200 rounded mb-4">
                <table class="min-w-full border-collapse">
                    <thead>
                        <tr class="bg-primary-light text-white text-xs uppercase">
                            <th class="py-3 px-4 text-left rounded-tl">Method</th>
                            <th class="py-3 px-4 text-left">Path</th>
                            <th class="py-3 px-4 text-left">Ability Required</th>
                            <th class="py-3 px-4 text-left rounded-tr">Returns</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($api['integration_endpoints'] as $endpoint)
                            <tr class="text-xs hover:bg-gray-50">
                                <td class="py-3 px-4 font-medium text-gray-700">{{ $endpoint['method'] }}</td>
                                <td class="py-3 px-4 font-mono text-[11px] text-gray-800">{{ $endpoint['path'] }}</td>
                                <td class="py-3 px-4">
                                    @if($endpoint['ability'] === '—')
                                        <span class="text-gray-400">Any key</span>
                                    @else
                                        <span class="font-mono text-[10px] px-1.5 py-0.5 rounded bg-gray-100 text-gray-700">{{ $endpoint['ability'] }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-gray-500">{{ $endpoint['description'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <h3 class="text-xs font-medium text-gray-700 mb-2">Example request</h3>
            <pre class="bg-gray-900 text-gray-100 rounded p-3 text-[11px] font-mono overflow-x-auto mb-2">curl -H "{{ $api['auth_header'] }}: sk_your_key_here" \
     -H "Accept: application/json" \
     {{ $api['base_url'] }}/v1/whoami</pre>
            <p class="text-[11px] text-gray-500 mb-4">
                A working key answers <span class="font-mono">200</span> with its name and abilities. A wrong,
                revoked or expired key answers <span class="font-mono">401</span>, and a key missing the ability for
                that endpoint answers <span class="font-mono">403</span>.
            </p>

            <h3 class="text-xs font-medium text-gray-700 mb-2">Verify a key</h3>
            <div class="flex items-center gap-2">
                <input type="text" x-model="probeSecret" placeholder="Paste a key to check it"
                       class="w-full h-9 text-xs font-mono border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                <button type="button" @click="verifyKey(probeSecret)" :disabled="verifying || ! probeSecret"
                        class="h-9 px-3 bg-gray-700 hover:bg-gray-800 text-white text-xs rounded shrink-0 disabled:opacity-50">
                    <span x-text="verifying ? 'Checking...' : 'Check'"></span>
                </button>
            </div>
            <template x-if="verifyResult">
                <pre class="mt-2 bg-gray-50 border border-gray-200 rounded p-2 text-[11px] font-mono text-gray-700 overflow-x-auto"
                     x-text="verifyResult"></pre>
            </template>
            <p class="text-[11px] text-gray-500 mt-1">
                Checked from your browser against this server, so it exercises the same path an external caller
                takes. Nothing is stored.
            </p>

            <h3 class="text-xs font-medium text-gray-700 mt-4 mb-2">Verifying a webhook signature</h3>
            <p class="text-[11px] text-gray-500 mb-2">
                Every delivery carries <span class="font-mono">{{ $api['signature_header'] }}</span> in the form
                <span class="font-mono">t=&lt;unix&gt;,v1=&lt;hex&gt;</span>. Recompute it over
                <span class="font-mono">t + "." + rawBody</span> with that endpoint's secret:
            </p>
            <pre class="bg-gray-900 text-gray-100 rounded p-3 text-[11px] font-mono overflow-x-auto">$parts = [];
foreach (explode(',', $header) as $segment) {
    [$k, $v] = explode('=', trim($segment), 2);
    $parts[$k] = $v;
}

$expected = hash_hmac('sha256', $parts['t'] . '.' . $rawBody, $secret);

if (! hash_equals($expected, $parts['v1'])) {
    abort(400, 'Bad signature');
}</pre>
        </div>
    </div>

    {{-- ------------------------------------------------------------------
         Panel 4: Webhooks
    ------------------------------------------------------------------- --}}
    <div class="bg-white border border-gray-200 rounded-md shadow-sm" x-data="{ adding: false }">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                <span class="material-icons-outlined text-primary-DEFAULT mr-2">webhook</span>
                Webhooks
                <span class="ml-2 text-gray-400 font-normal" x-text="'(' + hooks.length + ')'"></span>
            </h2>
            <div class="flex items-center gap-3">
                <label class="flex items-center">
                    <input type="hidden" name="enable_webhooks" value="0">
                    <input type="checkbox" name="enable_webhooks" value="1"
                           class="rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                           {{ old('enable_webhooks', $config->enable_webhooks ?? false) ? 'checked' : '' }}>
                    <span class="ml-2 text-xs text-gray-700">Deliveries enabled</span>
                </label>
                <button type="button"
                        @click="adding = !adding"
                        @disabled(! $api['storage_ready'])
                        class="h-9 px-3 text-xs rounded flex items-center shrink-0 transition-colors duration-200 ease-in-out
                               {{ $api['storage_ready'] ? 'bg-primary-DEFAULT hover:bg-primary-dark text-white' : 'bg-gray-100 text-gray-400 cursor-not-allowed' }}">
                    <span class="material-icons-outlined text-xs mr-1">add</span>
                    Add Endpoint
                </button>
            </div>
        </div>

        <div class="p-4">
            @unless($api['storage_ready'])
                <div class="mb-3 bg-gray-50 border border-gray-200 rounded px-3 py-2">
                    <p class="text-[11px] text-gray-600">
                        <span class="font-medium">Storage for webhooks is not installed yet.</span>
                        Nothing has ever been delivered: the old tab had an enable switch, a shared secret and a
                        comma separated event list, but no endpoint to call and no sender behind it.
                    </p>
                </div>
            @endunless

            @if($api['queue_connection'] !== 'sync')
                <div class="mb-3 bg-blue-50 border border-blue-200 rounded px-3 py-2">
                    <p class="text-[11px] text-blue-800">
                        Deliveries are queued on the <span class="font-mono">{{ $api['queue_connection'] }}</span>
                        connection, so a queue worker has to be running or nothing is sent.
                        <span class="font-medium">Send test</span> bypasses the queue and reports the response
                        straight away, which is the quickest way to tell the two problems apart.
                    </p>
                </div>
            @endif

            {{-- Add / edit endpoint form --}}
            <div x-show="adding" x-cloak class="mb-4 border border-gray-200 rounded p-4 bg-gray-50">
                <div class="space-y-3">
                    <div class="flex flex-col md:flex-row md:items-center gap-3">
                        <label class="text-xs font-medium text-gray-700 md:w-40">Name</label>
                        <div class="flex-1">
                            <input type="text" x-model="hookForm.name" placeholder="e.g. Company CRM"
                                   class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                        </div>
                    </div>
                    <div class="flex flex-col md:flex-row md:items-center gap-3">
                        <label class="text-xs font-medium text-gray-700 md:w-40">Payload URL</label>
                        <div class="flex-1">
                            <input type="url" x-model="hookForm.url" placeholder="https://example.com/hooks/sijil"
                                   class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                        </div>
                    </div>
                    <div class="flex flex-col md:flex-row md:items-start gap-3">
                        <label class="text-xs font-medium text-gray-700 md:w-40 md:pt-1">Events</label>
                        <div class="flex-1 space-y-2">
                            @foreach($api['events'] as $name => $meta)
                                <label class="flex items-start">
                                    <input type="checkbox" value="{{ $name }}" x-model="hookForm.events"
                                           class="mt-0.5 rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                                    <span class="ml-2">
                                        <span class="text-xs text-gray-700 font-medium">{{ $meta['label'] }}</span>
                                        <span class="ml-1 font-mono text-[10px] text-gray-400">{{ $name }}</span>
                                        <span class="block text-[11px] text-gray-500">{{ $meta['description'] }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="flex flex-col md:flex-row md:items-start gap-3">
                        <label class="text-xs font-medium text-gray-700 md:w-40 md:pt-2">Signing Secret</label>
                        <div class="flex-1">
                            <input type="text" readonly
                                   :value="hookForm.id ? 'Held on the server. Use Rotate to replace it.' : 'Generated when the endpoint is saved'"
                                   class="w-full h-9 text-xs border-gray-300 rounded px-3 bg-gray-100 text-gray-500">
                            <p class="text-[11px] text-gray-500 mt-1">
                                Each endpoint gets its own secret, so rotating one subscriber's secret does not
                                invalidate the signatures every other subscriber is checking. Payloads are signed
                                HMAC-SHA256 over <span class="font-mono">timestamp.body</span> and sent in
                                <span class="font-mono">X-Sijil-Signature</span>.
                            </p>
                        </div>
                    </div>
                    <div class="flex flex-col md:flex-row md:items-center gap-3">
                        <label class="text-xs font-medium text-gray-700 md:w-40">Active</label>
                        <div class="flex-1">
                            <label class="flex items-center">
                                <input type="checkbox" x-model="hookForm.is_active"
                                       class="rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                                <span class="ml-2 text-xs text-gray-700">Receive deliveries</span>
                            </label>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-1">
                        <button type="button" @click="adding = false; resetHookForm()"
                                class="h-9 px-3 bg-white border border-gray-300 hover:bg-gray-50 text-xs text-gray-700 rounded">
                            Cancel
                        </button>
                        <button type="button" x-show="hookForm.id" :disabled="busy"
                                @click="rotateSecret(hookForm.id)"
                                class="h-9 px-3 text-xs rounded bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 disabled:opacity-50">
                            Rotate Secret
                        </button>
                        <button type="button" @click="saveHook()" :disabled="busy"
                                class="h-9 px-3 text-xs rounded bg-green-600 hover:bg-green-700 text-white disabled:opacity-50">
                            <span x-text="busy ? 'Saving...' : (hookForm.id ? 'Update Endpoint' : 'Save Endpoint')"></span>
                        </button>
                    </div>
                </div>
            </div>

            <div x-show="hooks.length === 0" class="bg-gray-50 border border-gray-200 rounded p-8 text-center">
                <span class="material-icons-outlined text-gray-400" style="font-size: 40px;">webhook</span>
                <p class="text-xs text-gray-600 mt-2 font-medium">No webhook endpoints</p>
                <p class="text-[11px] text-gray-500 mt-1 max-w-md mx-auto">
                    Add an endpoint to have this system POST to an external URL when an event is created, a
                    registration completes, a certificate is issued or attendance is recorded.
                </p>
            </div>

            <div x-show="hooks.length > 0" class="overflow-x-auto border border-gray-200 rounded">
                <table class="min-w-full border-collapse">
                    <thead>
                        <tr class="bg-primary-light text-white text-xs uppercase">
                            <th class="py-3 px-4 text-left rounded-tl">Name</th>
                            <th class="py-3 px-4 text-left">Payload URL</th>
                            <th class="py-3 px-4 text-left">Events</th>
                            <th class="py-3 px-4 text-left">Last Result</th>
                            <th class="py-3 px-4 text-left">Failures</th>
                            <th class="py-3 px-4 text-center rounded-tr">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="hook in hooks" :key="hook.id">
                            <tr class="text-xs hover:bg-gray-50">
                                <td class="py-3 px-4 font-medium text-gray-800">
                                    <span x-text="hook.name"></span>
                                    <span x-show="hook.disabled"
                                          class="ml-1 px-1.5 py-0.5 rounded bg-red-50 text-red-700 text-[10px]">Auto-disabled</span>
                                    <span x-show="! hook.disabled && ! hook.is_active"
                                          class="ml-1 px-1.5 py-0.5 rounded bg-gray-100 text-gray-600 text-[10px]">Paused</span>
                                </td>
                                <td class="py-3 px-4 font-mono text-[11px] text-gray-600 max-w-xs truncate" x-text="hook.url"></td>
                                <td class="py-3 px-4 text-gray-500" x-text="hook.event_count"></td>
                                <td class="py-3 px-4 text-gray-600">
                                    <template x-if="hook.last_status">
                                        <span>
                                            <span :class="hook.last_status < 300 ? 'text-green-700' : 'text-red-700'"
                                                  x-text="hook.last_status"></span>
                                            <span class="text-gray-400" x-text="' · ' + hook.last_delivery"></span>
                                        </span>
                                    </template>
                                    <span x-show="! hook.last_status" class="text-gray-400">Never delivered</span>
                                </td>
                                <td class="py-3 px-4" :class="hook.failures > 0 ? 'text-red-700 font-medium' : 'text-gray-500'"
                                    x-text="hook.failures"></td>
                                <td class="py-3 px-4">
                                    <div class="flex justify-center space-x-2">
                                        <button type="button" title="Send test" :disabled="busy"
                                                @click="testHook(hook)"
                                                class="p-1 bg-blue-50 rounded hover:bg-blue-100 border border-blue-100 disabled:opacity-50">
                                            <span class="material-icons-outlined text-blue-600 text-xs">send</span>
                                        </button>
                                        <button type="button" title="Edit"
                                                @click="editHook(hook); adding = true"
                                                class="p-1 bg-gray-50 rounded hover:bg-gray-100 border border-gray-200">
                                            <span class="material-icons-outlined text-gray-600 text-xs">edit</span>
                                        </button>
                                        <button type="button" title="Delete" :disabled="busy"
                                                @click="deleteHook(hook)"
                                                class="p-1 bg-red-50 rounded hover:bg-red-100 border border-red-100 disabled:opacity-50">
                                            <span class="material-icons-outlined text-red-600 text-xs">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            {{-- Delivery log --}}
            <div class="mt-4">
                <h3 class="text-xs font-medium text-gray-700 mb-2 flex items-center">
                    <span class="material-icons-outlined text-gray-400 text-sm mr-1">history</span>
                    Recent Deliveries
                </h3>
                <div x-show="deliveries.length === 0" class="bg-gray-50 border border-gray-200 rounded px-3 py-4 text-center">
                    <p class="text-[11px] text-gray-500">
                        No deliveries recorded. Each attempt will be logged here with its status code, duration
                        and the first part of the response body.
                    </p>
                </div>

                <div x-show="deliveries.length > 0" class="overflow-x-auto border border-gray-200 rounded">
                    <table class="min-w-full border-collapse">
                        <thead>
                            <tr class="bg-primary-light text-white text-xs uppercase">
                                <th class="py-3 px-4 text-left rounded-tl">Time</th>
                                <th class="py-3 px-4 text-left">Event</th>
                                <th class="py-3 px-4 text-left">Endpoint</th>
                                <th class="py-3 px-4 text-left">Attempt</th>
                                <th class="py-3 px-4 text-left">Code</th>
                                <th class="py-3 px-4 text-left rounded-tr">Duration</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            {{--
                                One row per delivery. The failure reason sits under
                                the event name rather than in a second row, because
                                x-for needs a single root element and a second <tr>
                                would have required nesting a <tbody>.
                            --}}
                            <template x-for="delivery in deliveries" :key="delivery.id">
                                <tr class="text-xs hover:bg-gray-50">
                                    <td class="py-3 px-4 text-gray-500 whitespace-nowrap align-top" x-text="delivery.time"></td>
                                    <td class="py-3 px-4 align-top">
                                        <span class="font-mono text-[11px] text-gray-700" x-text="delivery.event"></span>
                                        <span x-show="! delivery.succeeded && delivery.error"
                                              class="block text-[11px] text-red-700 mt-1" x-text="delivery.error"></span>
                                    </td>
                                    <td class="py-3 px-4 text-gray-600 align-top" x-text="delivery.endpoint"></td>
                                    <td class="py-3 px-4 text-gray-500 align-top" x-text="delivery.attempt"></td>
                                    <td class="py-3 px-4 align-top">
                                        <span class="font-medium"
                                              :class="delivery.succeeded ? 'text-green-700' : 'text-red-700'"
                                              x-text="delivery.status_code || 'error'"></span>
                                    </td>
                                    <td class="py-3 px-4 text-gray-500 align-top" x-text="delivery.duration_ms + 'ms'"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ------------------------------------------------------------------
         Panel 5: CORS
    ------------------------------------------------------------------- --}}
    <div class="bg-white border border-gray-200 rounded-md shadow-sm">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                <span class="material-icons-outlined text-primary-DEFAULT mr-2">public</span>
                Cross-Origin Access
            </h2>
        </div>

        <div class="p-4 space-y-3">
            @if($api['cors_effective']['wide_open'])
                <div class="bg-red-50 border border-red-200 rounded px-3 py-2">
                    <p class="text-[11px] text-red-800">
                        <span class="font-medium">Every origin is currently accepted.</span>
                        <span class="font-mono">config/cors.php</span> is hard-coded to
                        <span class="font-mono">*</span> and never reads the list below, so the restriction this tab
                        appears to describe is not in force.
                    </p>
                </div>
            @endif

            <div class="flex flex-col md:flex-row md:items-start gap-3">
                <label class="text-xs font-medium text-gray-700 md:w-40 md:pt-1">Restrict Origins</label>
                <div class="flex-1">
                    <label class="flex items-center">
                        <input type="hidden" name="api_cors_enabled" value="0">
                        <input type="checkbox" name="api_cors_enabled" value="1"
                               class="rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                               {{ old('api_cors_enabled', $config->api_cors_enabled ?? false) ? 'checked' : '' }}>
                        <span class="ml-2 text-xs text-gray-700">Accept browser requests only from the origins listed below</span>
                    </label>
                    <p class="text-[11px] text-gray-500 mt-1">
                        Leave unticked to accept any origin. This affects browsers only; server-to-server callers
                        are not subject to CORS.
                    </p>
                </div>
            </div>

            <div class="flex flex-col md:flex-row md:items-start gap-3">
                <label for="cors_domains" class="text-xs font-medium text-gray-700 md:w-40 md:pt-2">
                    Allowed Origins
                </label>
                <div class="flex-1">
                    <textarea id="cors_domains" name="cors_domains" rows="4"
                              class="w-full text-xs border-gray-300 rounded px-3 py-2 font-mono focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                              placeholder="https://user.e-certificate.com.my">{{ implode("\n", $origins) }}</textarea>
                    <p class="text-[11px] text-gray-500 mt-1">
                        One origin per line. A leading <span class="font-mono">*.</span> matches any subdomain.
                    </p>
                    <div class="mt-2 bg-amber-50 border border-amber-200 rounded px-3 py-2">
                        <p class="text-[11px] text-amber-800">
                            Removing <span class="font-mono">user.e-certificate.com.my</span> cuts off the
                            participant app, which is served from a different host to this one.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ------------------------------------------------------------------
         Panel 6: Participant app tokens
    ------------------------------------------------------------------- --}}
    <div class="bg-white border border-gray-200 rounded-md shadow-sm">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                <span class="material-icons-outlined text-primary-DEFAULT mr-2">smartphone</span>
                Participant App Tokens
            </h2>
        </div>

        <div class="p-4">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-3">
                <div>
                    <p class="text-xs text-gray-500">Issued</p>
                    <p class="text-lg font-bold text-gray-800">{{ $api['tokens']['total'] }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Used in last 30 days</p>
                    <p class="text-lg font-bold text-gray-800">{{ $api['tokens']['recent'] }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Never used</p>
                    <p class="text-lg font-bold text-gray-800">{{ $api['tokens']['never_used'] }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">With an expiry</p>
                    <p class="text-lg font-bold {{ $api['tokens']['expiry_configured'] ? 'text-gray-800' : 'text-amber-700' }}">
                        {{ $api['tokens']['expiring'] }}
                    </p>
                </div>
            </div>

            @unless($api['tokens']['expiry_configured'])
                <div class="bg-amber-50 border border-amber-200 rounded px-3 py-2">
                    <p class="text-[11px] text-amber-800">
                        No token lifetime is configured, so every token ever issued stays valid indefinitely. A
                        participant who signed in once on a shared or lost device still has a working credential.
                    </p>
                </div>
            @endunless

            <p class="text-[11px] text-gray-500 mt-2">
                These are issued when a participant signs in to the app. They are separate from API keys: revoking
                a key never signs a participant out, and signing a participant out never affects an integration.
            </p>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('apiIntegrations', (routes, seedKeys, seedHooks, seedDeliveries) => ({
            routes,
            busy: false,
            notice: {},
            revealed: {},
            verifying: false,
            verifyResult: '',
            probeSecret: '',

            // The reveal modal cannot be closed until this is ticked, because
            // closing it discards the only copy of the secret.
            acknowledged: false,
            copied: false,
            copyFailed: false,

            // Tables render from these, so an action updates the screen without a
            // reload. They previously came straight from Blade, which is why a
            // newly generated key only appeared after refreshing by hand.
            keys: seedKeys || [],
            hooks: seedHooks || [],
            deliveries: seedDeliveries || [],

            keyForm: { name: '', abilities: [], expires_in_days: 90, never_expires: false },
            hookForm: { id: null, name: '', url: '', events: [], is_active: true },

            resetKeyForm() {
                this.keyForm = { name: '', abilities: [], expires_in_days: 90, never_expires: false };
            },

            resetHookForm() {
                this.hookForm = { id: null, name: '', url: '', events: [], is_active: true };
            },

            editHook(hook) {
                this.hookForm = {
                    id: hook.id,
                    name: hook.name,
                    url: hook.url,
                    events: hook.events || [],
                    is_active: hook.is_active,
                };
            },

            /**
             * Copy to the clipboard, with a fallback for insecure origins.
             *
             * navigator.clipboard only exists in a secure context. This app is
             * served over plain HTTP on a custom hostname in development, where
             * the property is undefined and calling it throws, so the button
             * appeared to do nothing at all. execCommand is deprecated but is the
             * only thing that works there.
             *
             * @param {string} field Which control was copied, for the feedback label.
             */
            async copy(value, field = '') {
                let ok = false;

                if (navigator.clipboard && window.isSecureContext) {
                    try {
                        await navigator.clipboard.writeText(value);
                        ok = true;
                    } catch (error) {
                        ok = false;
                    }
                }

                if (!ok) {
                    ok = this.copyViaSelection(value);
                }

                this.copied = ok ? (field || true) : false;
                this.copyFailed = !ok;

                this.notice = ok
                    ? { ok: true, message: 'Copied to clipboard.' }
                    : { ok: false, message: 'Could not copy automatically. Select the text and press Ctrl+C.' };

                if (ok) {
                    // Let the button settle back so a second copy still reads as an action.
                    setTimeout(() => {
                        if (this.copied === (field || true)) {
                            this.copied = false;
                        }
                    }, 2500);
                }

                return ok;
            },

            copyViaSelection(value) {
                const area = document.createElement('textarea');

                area.value = value;
                area.setAttribute('readonly', '');
                // Off-screen rather than hidden: a display:none element cannot be
                // selected, so the copy would fail.
                area.style.position = 'fixed';
                area.style.top = '-1000px';
                area.style.opacity = '0';

                document.body.appendChild(area);

                let ok = false;

                try {
                    area.select();
                    area.setSelectionRange(0, value.length);
                    ok = document.execCommand('copy');
                } catch (error) {
                    ok = false;
                } finally {
                    document.body.removeChild(area);
                }

                return ok;
            },

            curlFor(secret) {
                return 'curl -H "' + this.routes.authHeader + ': ' + secret
                    + '" -H "Accept: application/json" ' + this.routes.apiBase + '/v1/whoami';
            },

            /**
             * Call the integration API with a key and show exactly what it
             * answers, so "does this work?" has an answer on the page.
             */
            async verifyKey(secret) {
                if (!secret) {
                    return;
                }

                this.verifying = true;
                this.verifyResult = '';

                try {
                    const response = await fetch(this.routes.apiBase + '/v1/whoami', {
                        headers: {
                            [this.routes.authHeader]: secret,
                            'Accept': 'application/json',
                        },
                    });

                    const data = await response.json().catch(() => null);

                    const explanation = {
                        200: 'Working. This key is accepted.',
                        401: 'Rejected: unknown, revoked or expired key.',
                        403: 'Accepted, but this key lacks the ability for that endpoint.',
                        429: 'Accepted, but the rate limit has been reached. Try again in a minute.',
                        503: 'The API is switched off on this tab.',
                    }[response.status] || 'Unexpected response.';

                    this.verifyResult = 'HTTP ' + response.status + '  ' + explanation + '\n\n'
                        + (data ? JSON.stringify(data, null, 2) : '(no JSON body)');
                } catch (error) {
                    this.verifyResult = 'Could not reach the API: ' + error.message;
                } finally {
                    this.verifying = false;
                }
            },

            /**
             * One place that talks to the server, so the CSRF token, the JSON
             * headers and the error handling cannot differ between actions.
             */
            async send(url, method, body) {
                this.busy = true;
                this.notice = {};

                try {
                    const response = await fetch(url, {
                        method,
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                                || document.querySelector('input[name="_token"]')?.value || '',
                        },
                        body: body ? JSON.stringify(body) : undefined,
                    });

                    const data = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        // Laravel returns 422 with a field keyed error bag; show the
                        // first message rather than a bare "request failed".
                        const first = data.errors ? Object.values(data.errors)[0][0] : null;
                        this.notice = { ok: false, message: first || data.message || ('Request failed (' + response.status + ')') };
                        return null;
                    }

                    return data;
                } catch (error) {
                    this.notice = { ok: false, message: 'Could not reach the server: ' + error.message };
                    return null;
                } finally {
                    this.busy = false;
                }
            },

            async createKey() {
                const payload = {
                    name: this.keyForm.name,
                    abilities: this.keyForm.abilities,
                };

                if (!this.keyForm.never_expires) {
                    payload.expires_in_days = this.keyForm.expires_in_days;
                }

                const data = await this.send(this.routes.keysStore, 'POST', payload);

                if (!data) {
                    return false;
                }

                // The row appears immediately; the secret panel stays open until
                // dismissed, because closing it destroys the only copy.
                this.keys.unshift(data.key);
                this.revealed = { secret: data.secret, title: 'API key for "' + payload.name + '"', kind: 'key' };
                this.notice = { ok: true, message: data.message };
                this.resetKeyForm();

                return true;
            },

            async revokeKey(key) {
                if (!confirm('Revoke "' + key.name + '"? Any system using it will immediately start receiving 401 responses.')) {
                    return;
                }

                const data = await this.send(this.routes.base + '/keys/' + key.id + '/revoke', 'POST');

                if (data) {
                    this.notice = { ok: true, message: data.message };
                    this.replaceIn(this.keys, data.key);
                }
            },

            async regenerateKey(key) {
                const warning = key.status === 'Revoked'
                    ? 'Issue a new secret for "' + key.name + '"? This also makes the key active again.'
                    : 'Issue a new secret for "' + key.name + '"? The current secret stops working immediately, so '
                        + 'any system still using it will break until it is updated.';

                if (!confirm(warning)) {
                    return;
                }

                const data = await this.send(this.routes.base + '/keys/' + key.id + '/regenerate', 'POST');

                if (data) {
                    this.replaceIn(this.keys, data.key);
                    this.revealed = {
                        secret: data.secret,
                        title: 'New secret for "' + key.name + '"',
                        kind: 'key',
                    };
                    this.notice = { ok: true, message: data.message };
                }
            },

            /**
             * Swap a row for its updated version, keeping its position.
             */
            replaceIn(list, row) {
                if (!row) {
                    return;
                }

                const index = list.findIndex(item => item.id === row.id);

                if (index === -1) {
                    list.unshift(row);
                } else {
                    list.splice(index, 1, row);
                }
            },

            async saveHook() {
                const payload = {
                    name: this.hookForm.name,
                    url: this.hookForm.url,
                    events: this.hookForm.events,
                    is_active: this.hookForm.is_active,
                };

                const editing = !!this.hookForm.id;

                const data = editing
                    ? await this.send(this.routes.base + '/webhooks/' + this.hookForm.id, 'PUT', payload)
                    : await this.send(this.routes.webhooksStore, 'POST', payload);

                if (!data) {
                    return;
                }

                this.notice = { ok: true, message: data.message };
                this.replaceIn(this.hooks, data.endpoint);

                if (data.secret) {
                    this.revealed = {
                        secret: data.secret,
                        title: 'Signing secret for "' + payload.name + '"',
                        kind: 'webhook',
                    };
                }

                this.adding = false;
                this.resetHookForm();
            },

            async rotateSecret(id) {
                if (!confirm('Rotate this signing secret? Deliveries will fail the subscriber\'s signature check until they are given the new value.')) {
                    return;
                }

                const data = await this.send(this.routes.base + '/webhooks/' + id + '/rotate-secret', 'POST');

                if (data) {
                    this.revealed = { secret: data.secret, title: 'New signing secret', kind: 'webhook' };
                    this.notice = { ok: true, message: data.message };
                }
            },

            async testHook(hook) {
                const data = await this.send(this.routes.base + '/webhooks/' + hook.id + '/test', 'POST');

                if (data) {
                    this.notice = { ok: data.success, message: data.message };
                    this.replaceIn(this.hooks, data.endpoint);

                    if (data.delivery) {
                        this.deliveries.unshift(data.delivery);
                    }
                }
            },

            async deleteHook(hook) {
                if (!confirm('Delete "' + hook.name + '"? Its delivery history is removed with it.')) {
                    return;
                }

                const data = await this.send(this.routes.base + '/webhooks/' + hook.id, 'DELETE');

                if (data) {
                    this.notice = { ok: true, message: data.message };
                    this.hooks = this.hooks.filter(item => item.id !== hook.id);
                    this.deliveries = this.deliveries.filter(item => item.endpoint !== hook.name);

                    if (this.hookForm.id === hook.id) {
                        this.adding = false;
                        this.resetHookForm();
                    }
                }
            },
        }));
    });
</script>
