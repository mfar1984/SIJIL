{{--
    Payment tab (CHIP Collect). Administrator only.

    This tab sits outside the shared Global Config form and saves through its own
    endpoint, like the Recycle Bin: the CHIP secret key must never travel with,
    be cached with or be logged with the global_configs row. The form uses
    method="post" so that even if Alpine fails to load, pressing Enter can never
    put the secret into a GET query string.

    The secret key is never sent to the browser. The page only knows whether one
    is stored and its last four characters.
--}}
<div x-show="activeTab === 'payment'" x-cloak class="space-y-4">
    @if(! $paymentPanel)
        <div class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded text-xs">
            Payment settings are available to Administrators only.
        </div>
    @else
        @php $canUpdatePayment = \App\Support\PaymentPermissions::canUpdateSettings(auth()->user()); @endphp
        <div x-data="paymentSettings(@js($paymentPanel), {
                update: @js(route('settings.payment.update')),
                test: @js(route('settings.payment.test')),
             })">
            {{-- Result banner --}}
            <template x-if="notice.message">
                <div :class="notice.ok ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800'"
                     class="border rounded px-4 py-2 text-xs flex items-start justify-between gap-3 mb-4" role="status">
                    <span x-text="notice.message"></span>
                    <button type="button" @click="notice = {}" class="shrink-0 text-current opacity-60 hover:opacity-100" aria-label="Dismiss">
                        <span class="material-icons-outlined text-sm">close</span>
                    </button>
                </div>
            </template>

            <template x-for="warning in panel.url_warnings" :key="warning">
                <div class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-2 rounded text-xs mb-2" x-text="warning"></div>
            </template>

            <form id="paymentSettingsForm" method="post" action="{{ route('settings.payment.update') }}" @submit.prevent="save()" x-ref="form" class="space-y-4">
                @csrf
                <fieldset {{ $canUpdatePayment ? '' : 'disabled' }} class="space-y-4">

                    {{-- Status --}}
                    <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3 flex items-center justify-between">
                            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                                <span class="material-icons-outlined text-primary-DEFAULT mr-2">payments</span>
                                CHIP Collect
                            </h2>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold"
                                      :class="panel.mode === 'live' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800'"
                                      x-text="panel.mode === 'live' ? 'LIVE' : 'TEST'"></span>
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold"
                                      :class="panel.checkout_enabled ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'"
                                      x-text="panel.checkout_enabled ? 'Enabled' : 'Disabled'"></span>
                            </div>
                        </div>
                        <div class="p-4 space-y-3">
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="payment_enabled" class="text-xs font-medium text-gray-700 md:w-48">Online payment</label>
                                <div class="flex-1">
                                    <label class="inline-flex items-center gap-2 text-xs text-gray-700">
                                        <input type="checkbox" id="payment_enabled" name="enabled" value="1" x-model="form.enabled"
                                               class="rounded border-gray-300 text-primary-DEFAULT focus:ring-primary-light h-4 w-4">
                                        <span>Enable checkout on the pricing page</span>
                                    </label>
                                    <p class="text-[11px] text-gray-500 mt-1" x-show="!panel.can_enable">
                                        Save a secret key and a Brand ID before enabling. While disabled, the pricing page keeps its Contact Us buttons.
                                    </p>
                                    <p class="text-[11px] text-red-600 mt-1" x-show="errors.enabled" x-text="errors.enabled?.[0]"></p>
                                </div>
                            </div>
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="payment_mode" class="text-xs font-medium text-gray-700 md:w-48">Mode</label>
                                <div class="flex-1">
                                    <select id="payment_mode" name="mode" x-model="form.mode"
                                            class="w-full md:w-64 h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                                        <option value="test">Test (CHIP test keys, no real money)</option>
                                        <option value="live">Live</option>
                                    </select>
                                    <p class="text-[11px] text-gray-500 mt-1">Must match the key below. A purchase whose test flag differs from this mode is held for review instead of being fulfilled.</p>
                                    <p class="text-[11px] text-red-600 mt-1" x-show="errors.mode" x-text="errors.mode?.[0]"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Credentials --}}
                    <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                                <span class="material-icons-outlined text-primary-DEFAULT mr-2">key</span>
                                Credentials
                            </h2>
                        </div>
                        <div class="p-4 space-y-3">
                            <div class="flex flex-col md:flex-row md:items-start gap-3">
                                <label for="payment_secret_key" class="text-xs font-medium text-gray-700 md:w-48 md:pt-2">Secret key (API key)</label>
                                <div class="flex-1">
                                    <input type="password" id="payment_secret_key" name="secret_key" x-model="form.secret_key"
                                           autocomplete="new-password" spellcheck="false" maxlength="512"
                                           :placeholder="panel.has_secret ? 'Leave blank to keep the stored key (' + panel.secret_hint + ')' : 'Paste the secret key from CHIP portal > Developers > Keys'"
                                           class="w-full h-9 text-xs font-mono border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                                    <p class="text-[11px] text-gray-500 mt-1">
                                        Stored encrypted and never shown again.
                                        <span x-show="panel.has_secret">Stored key: <span class="font-mono" x-text="panel.secret_hint"></span>.</span>
                                        <span x-show="!panel.has_secret">No key stored.</span>
                                    </p>
                                    <label class="inline-flex items-center gap-2 text-[11px] text-gray-700 mt-1" x-show="panel.has_secret">
                                        <input type="checkbox" name="remove_secret_key" value="1" x-model="form.remove_secret_key"
                                               class="rounded border-gray-300 text-red-600 focus:ring-red-300 h-3.5 w-3.5">
                                        Remove stored key
                                    </label>
                                    <p class="text-[11px] text-red-600 mt-1" x-show="errors.secret_key" x-text="errors.secret_key?.[0]"></p>
                                </div>
                            </div>
                            <div class="flex flex-col md:flex-row md:items-start gap-3">
                                <label for="payment_brand_id" class="text-xs font-medium text-gray-700 md:w-48 md:pt-2">Brand ID</label>
                                <div class="flex-1">
                                    <input type="text" id="payment_brand_id" name="brand_id" x-model="form.brand_id"
                                           spellcheck="false" maxlength="36" placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
                                           class="w-full md:w-96 h-9 text-xs font-mono border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                                    <p class="text-[11px] text-gray-500 mt-1">CHIP portal &gt; Developers &gt; Brands.</p>
                                    <p class="text-[11px] text-red-600 mt-1" x-show="errors.brand_id" x-text="errors.brand_id?.[0]"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Signatures --}}
                    <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                                <span class="material-icons-outlined text-primary-DEFAULT mr-2">verified_user</span>
                                Webhooks and signatures
                            </h2>
                        </div>
                        <div class="p-4 space-y-3">
                            <div class="flex flex-col md:flex-row md:items-start gap-3">
                                <label for="payment_webhook_public_key" class="text-xs font-medium text-gray-700 md:w-48 md:pt-2">Webhook public key</label>
                                <div class="flex-1">
                                    <textarea id="payment_webhook_public_key" name="webhook_public_key" x-model="form.webhook_public_key" rows="6"
                                              spellcheck="false" maxlength="5000"
                                              placeholder="-----BEGIN PUBLIC KEY-----"
                                              class="w-full text-[11px] font-mono border-gray-300 rounded px-3 py-2 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"></textarea>
                                    <p class="text-[11px] text-gray-500 mt-1">
                                        Shown by CHIP after you create the webhook below. It verifies the signature of every webhook delivery.
                                        <span x-show="panel.webhook_key_fingerprint">Fingerprint: <span class="font-mono" x-text="panel.webhook_key_fingerprint"></span></span>
                                    </p>
                                    <p class="text-[11px] text-red-600 mt-1" x-show="errors.webhook_public_key" x-text="errors.webhook_public_key?.[0]"></p>
                                </div>
                            </div>
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <span class="text-xs font-medium text-gray-700 md:w-48">Callback signing key</span>
                                <div class="flex-1 text-xs text-gray-700">
                                    <span x-show="panel.company_key_fetched_at">Fetched from CHIP on <span x-text="panel.company_key_fetched_at"></span>.</span>
                                    <span x-show="!panel.company_key_fetched_at" class="text-gray-500">Not fetched yet. It is fetched automatically by Test connection or by the first callback.</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- URLs --}}
                    <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                                <span class="material-icons-outlined text-primary-DEFAULT mr-2">link</span>
                                URLs for the CHIP portal
                            </h2>
                        </div>
                        <div class="p-4 space-y-3">
                            @foreach([
                                ['webhook_url', 'Webhook URL', 'CHIP portal > Developers > Webhooks: create a webhook with this URL and the events purchase.paid, purchase.payment_failure and purchase.cancelled. Test and live webhooks are separate.'],
                                ['callback_url', 'Success callback URL', 'Sent with every purchase automatically. Nothing to paste.'],
                                ['return_url', 'Buyer return URL', 'Where CHIP sends the buyer after paying, failing or cancelling.'],
                            ] as [$key, $label, $help])
                                <div class="flex flex-col md:flex-row md:items-start gap-3">
                                    <label for="payment_{{ $key }}" class="text-xs font-medium text-gray-700 md:w-48 md:pt-2">{{ $label }}</label>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <input type="text" id="payment_{{ $key }}" readonly :value="panel.{{ $key }}"
                                                   class="w-full h-9 text-xs font-mono border-gray-300 rounded px-3 bg-gray-50 text-gray-800">
                                            <button type="button" @click="copy(panel.{{ $key }}, '{{ $key }}')"
                                                    class="h-9 px-3 text-xs rounded shrink-0"
                                                    :class="copied === '{{ $key }}' ? 'bg-green-600 text-white' : 'bg-gray-100 hover:bg-gray-200 text-gray-700'">
                                                <span x-text="copied === '{{ $key }}' ? 'Copied' : 'Copy'"></span>
                                            </button>
                                        </div>
                                        <p class="text-[11px] text-gray-500 mt-1">{{ $help }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Eligibility --}}
                    <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                                <span class="material-icons-outlined text-primary-DEFAULT mr-2">account_balance</span>
                                Government packages
                            </h2>
                        </div>
                        <div class="p-4">
                            <div class="flex flex-col md:flex-row md:items-start gap-3">
                                <label for="payment_government_email_domains" class="text-xs font-medium text-gray-700 md:w-48 md:pt-2">Government email domains</label>
                                <div class="flex-1">
                                    <textarea id="payment_government_email_domains" name="government_email_domains" x-model="form.government_email_domains" rows="3"
                                              spellcheck="false" maxlength="1000"
                                              class="w-full md:w-96 text-xs font-mono border-gray-300 rounded px-3 py-2 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"></textarea>
                                    <p class="text-[11px] text-gray-500 mt-1">One per line. A buyer's email must be at this domain or a subdomain of it (gov.my accepts jpn.gov.my) to buy a Government package.</p>
                                    <p class="text-[11px] text-red-600 mt-1" x-show="errors.government_email_domains" x-text="errors.government_email_domains?.[0]"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="text-[11px] text-gray-500">
                        <template x-if="panel.last_test.at">
                            <span>
                                Last test <span x-text="panel.last_test.at"></span>:
                                <span :class="panel.last_test.ok ? 'text-green-700' : 'text-red-700'" x-text="panel.last_test.message"></span>
                            </span>
                        </template>
                        <span x-show="!panel.base_url_is_default" class="ml-2 text-amber-700">CHIP base URL is overridden (local mock).</span>
                    </div>
                    @if($canUpdatePayment)
                        <div class="flex items-center gap-3">
                            <button type="button" @click="testConnection()" :disabled="busy || !panel.can_enable"
                                    class="px-3 h-[36px] border border-gray-300 rounded text-xs font-medium text-gray-700 hover:bg-gray-50 inline-flex items-center disabled:opacity-50">
                                <span class="material-icons-outlined text-xs mr-1">network_check</span>
                                <span x-text="testing ? 'Testing...' : 'Test connection'"></span>
                            </button>
                            <button type="submit" :disabled="busy"
                                    class="px-3 h-[36px] bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 text-white rounded shadow-sm text-xs font-medium inline-flex items-center disabled:opacity-50">
                                <span class="material-icons-outlined text-xs mr-1">save</span>
                                <span x-text="saving ? 'Saving...' : 'Save Payment Settings'"></span>
                            </button>
                        </div>
                    @endif
                </div>
            </form>
        </div>

        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('paymentSettings', (panel, routes) => ({
                    panel,
                    routes,
                    form: {},
                    errors: {},
                    notice: {},
                    busy: false,
                    saving: false,
                    testing: false,
                    copied: null,

                    init() {
                        this.resetForm();
                    },

                    resetForm() {
                        this.form = {
                            enabled: this.panel.enabled,
                            mode: this.panel.mode,
                            secret_key: '',
                            remove_secret_key: false,
                            brand_id: this.panel.brand_id || '',
                            webhook_public_key: this.panel.webhook_public_key || '',
                            government_email_domains: (this.panel.government_email_domains || []).join('\n'),
                        };
                    },

                    token() {
                        return document.querySelector('meta[name="csrf-token"]')?.content
                            || this.$refs.form.querySelector('input[name="_token"]')?.value || '';
                    },

                    async post(url, body) {
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': this.token(),
                            },
                            body,
                            credentials: 'same-origin',
                        });

                        const data = await response.json().catch(() => ({}));

                        return { response, data };
                    },

                    async save() {
                        this.busy = this.saving = true;
                        this.errors = {};
                        this.notice = {};

                        const body = new FormData();
                        body.append('mode', this.form.mode);
                        body.append('enabled', this.form.enabled ? '1' : '0');
                        body.append('secret_key', this.form.secret_key);
                        body.append('remove_secret_key', this.form.remove_secret_key ? '1' : '0');
                        body.append('brand_id', this.form.brand_id);
                        body.append('webhook_public_key', this.form.webhook_public_key);
                        body.append('government_email_domains', this.form.government_email_domains);

                        try {
                            const { response, data } = await this.post(this.routes.update, body);

                            if (response.ok && data.success) {
                                this.panel = data.panel;
                                this.resetForm();
                                this.notice = { ok: true, message: data.message };
                            } else {
                                this.errors = data.errors || {};
                                const first = data.errors ? Object.values(data.errors)[0]?.[0] : null;
                                this.notice = { ok: false, message: first || data.message || ('Save failed (HTTP ' + response.status + ')') };
                            }
                        } catch (error) {
                            this.notice = { ok: false, message: 'Could not reach the server. Try again.' };
                        } finally {
                            // The secret never stays in the page after a save attempt.
                            this.form.secret_key = '';
                            this.busy = this.saving = false;
                        }
                    },

                    async testConnection() {
                        this.busy = this.testing = true;
                        this.notice = {};

                        try {
                            const { response, data } = await this.post(this.routes.test, new FormData());

                            if (data.panel) {
                                this.panel = data.panel;
                            }

                            this.notice = { ok: !!data.success, message: data.message || ('Test failed (HTTP ' + response.status + ')') };
                        } catch (error) {
                            this.notice = { ok: false, message: 'Could not reach the server. Try again.' };
                        } finally {
                            this.busy = this.testing = false;
                        }
                    },

                    async copy(value, key) {
                        try {
                            await navigator.clipboard.writeText(value);
                            this.copied = key;
                            setTimeout(() => { if (this.copied === key) this.copied = null; }, 2000);
                        } catch (error) {
                            this.notice = { ok: false, message: 'The browser blocked the clipboard. Select the text and press Ctrl+C.' };
                        }
                    },
                }));
            });
        </script>
    @endif
</div>
