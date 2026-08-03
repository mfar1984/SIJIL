@extends('layouts.event-registration')

@section('content')
<div class="reg-ui min-h-screen bg-gray-50 py-6 px-3 sm:px-4" x-data="{
    step: 1,
    form: {
        name: '',
        identity_card: '',
        passport_no: '',
        address1: '',
        address2: '',
        state: '',
        city: '',
        postcode: '',
        country: 'Malaysia',
        organization: '',
        job_title: '',
        email: '',
        phone: '',
        gender: '',
        date_of_birth: '',
        race: '',
        notes: '',
        id_type: '', // Added for IC/Passport dropdown
        manual_state: '', // Added for manual state input
        manual_city: '', // Added for manual city input
        manual_postcode: '', // Added for manual postcode input
    },
    locked: {
        email: '',
        id_type: '', // 'ic' or 'passport'
        identity: '', // normalized ic digits or passport string
    },
    // Sent with every gate call so the endpoints only work as part of this
    // registration, not as a general lookup service.
    eventToken: '{{ $event->registration_link }}',
    auth: {
        open: false,
        step: 'lookup', // lookup | login | register
        ic: '',
        passport: '',
        idType: 'ic',
        loading: false,
        // Masked addresses only. The full address is never sent to the browser
        // before the password has been checked, so sign-in refers to an account
        // by id.
        accounts: [],
        accountId: null,
        message: '',
        login: { password: '' },
        register: { name: '', email: '', password: '' },
    },
    next() {
        // Ensure form values are synced from DOM controls before moving forward
        if (this.step === 3) {
            // When using dynamic loaders for MY postcodes, read from selects as a fallback
            const stateEl = document.getElementById('state');
            const cityEl = document.getElementById('city');
            const postcodeEl = document.getElementById('postcode');
            const countryEl = document.getElementById('country');
            if (stateEl && !this.form.state) this.form.state = stateEl.value;
            if (cityEl && !this.form.city) this.form.city = cityEl.value;
            if (postcodeEl && !this.form.postcode) this.form.postcode = postcodeEl.value;
            if (countryEl && !this.form.country) this.form.country = countryEl.value;
        }
        if (this.step < 5) this.step++
    },
    openAuthGate() {
        this.auth.open = true;
        this.auth.step = 'lookup';
        this.auth.ic = this.form.identity_card?.trim() || '';
        this.auth.message = '';
        // Cleared so that reopening the modal cannot sign in against an account
        // found by an earlier lookup, or leave a password sitting in memory.
        this.auth.accounts = [];
        this.auth.accountId = null;
        this.auth.login.password = '';
    },
    // Shared request helper. Every gate call is a POST carrying the event token,
    // and every one of them reports its own failure the same way, so this keeps
    // the three steps below down to the part that differs.
    async gateRequest(path, body) {
        const res = await fetch(path, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                // Selector deliberately written without quotes around the
                // attribute value. This whole object is the value of the x-data
                // attribute, which is itself delimited by double quotes, so one
                // double quote here ends the attribute and spills the rest of the
                // script onto the page as visible text.
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '',
            },
            body: JSON.stringify({ event_token: this.eventToken, ...body }),
        });

        let data = {};
        try { data = await res.json(); } catch (e) { data = {}; }

        // 429 comes from the rate limiter, which returns no message of its own.
        if (res.status === 429) {
            return { ok: false, status: res.status, data, message: 'Too many attempts. Please wait a minute and try again.' };
        }

        return {
            ok: res.ok && data.success === true,
            status: res.status,
            data: data.data || {},
            message: data.message || '',
        };
    },
    async submitLookup() {
        if (this.auth.idType === 'ic' && !this.auth.ic) { this.auth.message = 'Please enter your IC number.'; return; }
        if (this.auth.idType === 'passport' && !this.auth.passport) { this.auth.message = 'Please enter your passport number.'; return; }
        this.auth.loading = true; this.auth.message = '';
        try {
            const result = await this.gateRequest('/api/participant/lookup', {
                id_type: this.auth.idType,
                ic: this.auth.idType === 'ic' ? (this.auth.ic || '').replace(/\D/g, '') : null,
                passport: this.auth.idType === 'passport' ? (this.auth.passport || '').trim() : null,
            });

            if (!result.ok) { this.auth.message = result.message || 'The check could not be completed.'; return; }

            this.auth.accounts = result.data.accounts || [];

            if (result.data.exists && this.auth.accounts.length) {
                // Sign in to prove the account is theirs. Only after that is any
                // of their information handed back.
                this.auth.accountId = this.auth.accounts[0].id;
                this.auth.step = 'login';
            } else {
                this.auth.register.name = '';
                this.auth.register.email = '';
                this.auth.register.password = '';
                this.auth.step = 'register';
            }
        } catch (e) {
            this.auth.message = 'Could not reach the server. Please check your connection and try again.';
        } finally {
            this.auth.loading = false;
        }
    },
    // Fill the form from what the server returned once ownership was proven.
    //
    // The IC is deliberately left unlocked. One account covers several people -
    // a parent registering children, an office registering staff - so the person
    // being registered is often not the account holder, and their document has to
    // be editable. The email is locked, because that is what ties the
    // registration back to the account.
    applyPrefill(prefill, lockedEmail) {
        if (!prefill) return;

        const take = (field, value) => { if (value) { this.form[field] = value; } };

        take('name', prefill.name);
        take('phone', prefill.phone);
        take('organization', prefill.organization);
        take('job_title', prefill.job_title);
        take('address1', prefill.address1);
        take('address2', prefill.address2);
        take('state', prefill.state);
        take('city', prefill.city);
        take('postcode', prefill.postcode);
        take('gender', prefill.gender);
        take('race', prefill.race);
        this.form.country = prefill.country || this.form.country || 'Malaysia';

        if (prefill.date_of_birth) {
            this.form.date_of_birth = this.normalizeDateToYmd(prefill.date_of_birth);
        }

        if (prefill.identity_card) {
            this.form.id_type = 'ic';
            const digits = (prefill.identity_card || '').replace(/\D/g, '');
            this.form.identity_card = digits.length === 12
                ? digits.substring(0, 6) + '-' + digits.substring(6, 8) + '-' + digits.substring(8, 12)
                : digits;
            this.form.passport_no = '';
        } else if (prefill.passport_no) {
            this.form.id_type = 'passport';
            this.form.passport_no = prefill.passport_no;
            this.form.identity_card = '';
        }

        this.locked.email = lockedEmail || prefill.email || '';
        this.form.email = this.locked.email || this.form.email;

        // The state, city and postcode selects are populated asynchronously, so a
        // value restored before the options exist would not display.
        this.ensureSelectOption('state', this.form.state);
        this.ensureSelectOption('city', this.form.city);
        this.ensureSelectOption('postcode', this.form.postcode);
        this.ensureSelectOption('country', this.form.country);
    },
    enterForm() {
        this.auth.open = false;
        this.step = 3;
        setTimeout(() => { window.scrollTo({ top: 0, behavior: 'smooth' }); }, 50);
    },
    ensureSelectOption(id, value) {
        if (!value) return;
        const el = document.getElementById(id);
        if (!el) return;
        const exists = Array.from(el.options).some(o => o.value == value);
        if (!exists) {
            const opt = document.createElement('option');
            opt.value = value;
            opt.textContent = value;
            el.appendChild(opt);
        }
        el.value = value;
    },
    async doLogin() {
        if (!this.auth.accountId) { this.auth.message = 'Please choose which account to sign in to.'; return; }
        if (!this.auth.login.password) { this.auth.message = 'Please enter your password.'; return; }
        this.auth.loading = true; this.auth.message = '';
        try {
            // Sent as an account id rather than an address, so a correct IC never
            // reveals a usable email address to whoever typed it.
            const result = await this.gateRequest('/api/participant/verify', {
                account_id: this.auth.accountId,
                password: this.auth.login.password,
            });

            if (!result.ok) { this.auth.message = result.message || 'Sign-in failed.'; return; }

            this.applyPrefill(result.data.prefill, result.data.email);
            this.enterForm();
        } catch (e) {
            this.auth.message = 'Could not reach the server. Please check your connection and try again.';
        } finally { this.auth.loading = false; }
    },
    async doRegister() {
        if (!this.auth.register.name || !this.auth.register.email || !this.auth.register.password) {
            this.auth.message = 'Name, email and password are all required.'; return;
        }
        if (this.auth.register.password.length < 8) {
            this.auth.message = 'Please choose a password of at least 8 characters.'; return;
        }
        this.auth.loading = true; this.auth.message = '';
        try {
            const result = await this.gateRequest('/api/participant/register', {
                name: this.auth.register.name,
                email: this.auth.register.email,
                password: this.auth.register.password,
                id_type: this.auth.idType,
                ic: this.auth.idType === 'ic' ? (this.auth.ic || '').replace(/\D/g, '') : null,
                passport: this.auth.idType === 'passport' ? (this.auth.passport || '').trim() : null,
            });

            // The address already has an account. Send them to sign in rather than
            // leaving them to work out why creating one was refused.
            if (!result.ok && result.status === 409 && result.data.account) {
                this.auth.accounts = [result.data.account];
                this.auth.accountId = result.data.account.id;
                this.auth.login.password = '';
                this.auth.step = 'login';
                this.auth.message = result.message || 'This email already has an account. Please sign in.';
                return;
            }

            if (!result.ok) { this.auth.message = result.message || 'The account could not be created.'; return; }

            this.applyPrefill(result.data.prefill, result.data.email);

            // A new account has nothing on file, so carry across what was just
            // typed into the modal.
            this.form.name = this.auth.register.name;

            if (this.auth.idType === 'ic') {
                const digits = (this.auth.ic || '').replace(/\D/g, '');
                this.form.id_type = 'ic';
                this.form.identity_card = digits.length === 12
                    ? digits.substring(0, 6) + '-' + digits.substring(6, 8) + '-' + digits.substring(8, 12)
                    : digits;
                this.form.passport_no = '';
            } else if (this.auth.idType === 'passport') {
                this.form.id_type = 'passport';
                this.form.passport_no = (this.auth.passport || '').trim();
                this.form.identity_card = '';
            }

            if (!this.form.country) { this.form.country = 'Malaysia'; this.ensureSelectOption('country', 'Malaysia'); }

            this.enterForm();
        } catch (e) {
            this.auth.message = 'Could not reach the server. Please check your connection and try again.';
        } finally { this.auth.loading = false; }
    },
    async doResetPassword() {
        // Reset is keyed on the address, which the browser never sees in full, so
        // the account chosen in the previous step is resolved server side.
        if (!this.auth.accountId) { this.auth.message = 'Please choose an account first.'; return; }
        this.auth.loading = true; this.auth.message = '';
        try {
            const result = await this.gateRequest('/api/participant/reset-password-for-account', {
                account_id: this.auth.accountId,
            });
            this.auth.message = result.message
                || 'If that account exists, a new password has been emailed to it.';
        } catch (e) {
            this.auth.message = 'Could not reach the server. Please check your connection and try again.';
        } finally { this.auth.loading = false; }
    },
    prev() { if (this.step > 1) this.step-- },
    setField(field, value) { this.form[field] = value },
    fillOld() {
        // Fill from old() if available (for validation error)
        this.form.name = '{{ old('name') }}';
        this.form.identity_card = '{{ old('identity_card') }}';
        this.form.passport_no = '{{ old('passport_no') }}';
        this.form.address1 = '{{ old('address1') }}';
        this.form.address2 = '{{ old('address2') }}';
        this.form.state = '{{ old('state') }}';
        this.form.city = '{{ old('city') }}';
        this.form.postcode = '{{ old('postcode') }}';
        this.form.country = '{{ old('country', 'Malaysia') }}';
        this.form.organization = '{{ old('organization') }}';
        this.form.job_title = '{{ old('job_title') }}';
        this.form.email = '{{ old('email') }}';
        this.form.phone = '{{ old('phone') }}';
        this.form.gender = '{{ old('gender') }}';
        this.form.date_of_birth = '{{ old('date_of_birth') }}';
        this.form.race = '{{ old('race') }}';
        this.form.notes = '{{ old('notes') }}';
        this.form.id_type = '{{ old('id_type') }}'; // Fill id_type
        this.form.manual_state = '{{ old('manual_state') }}'; // Fill manual_state
        this.form.manual_city = '{{ old('manual_city') }}'; // Fill manual_city
        this.form.manual_postcode = '{{ old('manual_postcode') }}'; // Fill manual_postcode
    },
    // New methods for IC/Passport formatting and state/city/postcode/country population
    formatIC(e) {
        let input = e.target;
        let value = input.value.replace(/\D/g, '');
        let formatted = '';
        if (value.length > 6) {
            formatted = value.substring(0, 6) + '-';
            if (value.length > 8) {
                formatted += value.substring(6, 8) + '-';
                formatted += value.substring(8, 12);
            } else {
                formatted += value.substring(6, 8);
            }
        } else {
            formatted = value;
        }
        input.value = formatted;
    },
    // Populate state/city/postcode/country (fallback without dynamic imports)
    loadStates() {
        // States will be populated by malaysia-postcodes.js for Malaysia
        // For non-Malaysia countries, state field will be text input
        // So this function is no longer needed
    },
    loadCountries() {
        const countries = [
            '-- Select Country --',
            'Malaysia',
            'Singapore',
            'Thailand',
            'Indonesia',
            'Brunei',
            'Philippines',
            'Vietnam',
            'Myanmar',
            'Cambodia',
            'Laos',
            'China',
            'Japan',
            'South Korea',
            'Taiwan',
            'Hong Kong',
            'India',
            'Pakistan',
            'Bangladesh',
            'United States',
            'United Kingdom',
            'Australia',
            'New Zealand',
            'Canada',
            'Others'
        ];
        const countryEl = document.getElementById('country');
        if (!countryEl) return;
        countryEl.innerHTML = '';
        countries.forEach(c => { 
            const o = document.createElement('option'); 
            o.value = c === '-- Select Country --' ? '' : c; 
            o.textContent = c; 
            countryEl.appendChild(o); 
        });
        // default to Malaysia if empty
        if (!this.form.country) { this.form.country = 'Malaysia'; }
        countryEl.value = this.form.country;
    },

    // Helpers for preview formatting
    formatGender(g) {
        if (!g) return '';
        return g.charAt(0).toUpperCase() + g.slice(1);
    },
    formatPhoneForPreview(p) {
        if (!p) return '';
        let digits = ('' + p).replace(/\D/g, '');
        if (!digits) return '';
        if (digits.startsWith('60')) {
            return '+' + digits;
        }
        if (digits.startsWith('0')) {
            digits = '60' + digits.slice(1);
        } else if (!digits.startsWith('60')) {
            digits = '60' + digits;
        }
        return '+' + digits;
    },
    formatDateDmy(d) {
        if (!d) return '';
        const dt = new Date(d);
        if (isNaN(dt.getTime())) return d;
        const dd = String(dt.getDate()).padStart(2, '0');
        const mm = String(dt.getMonth() + 1).padStart(2, '0');
        const yyyy = dt.getFullYear();
        return `${dd}-${mm}-${yyyy}`;
    },
    normalizeDateToYmd(val) {
        if (!val) return '';
        const s = String(val);
        // Already YYYY-MM-DD
        if (/^\d{4}-\d{2}-\d{2}$/.test(s)) return s;
        const dt = new Date(s.includes('T') ? s : s.replace(' ', 'T'));
        if (isNaN(dt.getTime())) return '';
        const y = dt.getFullYear();
        const m = String(dt.getMonth() + 1).padStart(2, '0');
        const d2 = String(dt.getDate()).padStart(2, '0');
        return `${y}-${m}-${d2}`;
    },
    calculateAge(d) {
        if (!d) return '';
        const birth = new Date(d);
        if (isNaN(birth.getTime())) return '';
        const today = new Date();
        let age = today.getFullYear() - birth.getFullYear();
        const m = today.getMonth() - birth.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) {
            age--;
        }
        return age;
    },
}" x-init="fillOld(); loadStates(); loadCountries()">
    <div class="max-w-6xl mx-auto">
        <style>
            /* ===== Registration UI polish (scoped) ===== */
            .reg-ui { font-family: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
            .reg-ui label { font-size: 12.5px; font-weight: 600; color: #374151; }
            .reg-ui input[type="text"],
            .reg-ui input[type="email"],
            .reg-ui input[type="password"],
            .reg-ui input[type="tel"],
            .reg-ui input[type="date"],
            .reg-ui select,
            .reg-ui textarea { font-size: 13px; height: 36px; border-radius: 2px; }
            .reg-ui textarea { min-height: 92px; height: auto; }
            .reg-ui .btn { height: 32px; border-radius: 20px; font-size: 12.5px; font-weight: 700; padding: 0 12px; }
            .reg-ui .btn-primary { background: #2563eb; color: #fff; }
            .reg-ui .btn-primary:hover { background: #1d4ed8; }
            .reg-ui .btn-secondary { background: #e5e7eb; color: #374151; }
            .reg-ui .btn-secondary:hover { background: #d1d5db; }
            .reg-ui .btn-success { background: #16a34a; color: #fff; }
            .reg-ui .btn-success:hover { background: #15803d; }
            .reg-ui .btn-danger { background: #dc2626; color: #fff; }
            .reg-ui .btn-danger:hover { background: #b91c1c; }
            .reg-ui .modal-card { border-radius: 8px; }
            .reg-ui .hint { font-size: 11px; color: #6b7280; }
            /* Existing rich content formatting */
            .rich-content ol { list-style: decimal; padding-left: 1.25rem; }
            .rich-content ul { list-style: disc; padding-left: 1.25rem; }
            .rich-content p { margin: 0.5rem 0; }
            .rich-content img { max-width: 100%; height: auto; border-radius: 0.25rem; }
            .rich-content table { width: 100%; border-collapse: collapse; margin: 0.75rem 0; }
            .rich-content table, .rich-content th, .rich-content td { border: 1px solid #e5e7eb; }
            .rich-content th, .rich-content td { padding: 0.5rem; }
        </style>
        {{--
            Every rejection in registerSubmit() returns redirect()->back(), and
            failed validation does the same. Neither this view nor its layout
            rendered those messages, so a refused registration looked identical
            to a successful one: the page reloaded, the wizard reset to its first
            step, and nothing said why. Kept outside the x-show blocks so it is
            visible wherever the wizard lands.
        --}}
        @if (session('error'))
            <div class="bg-white shadow rounded-lg overflow-hidden mb-6 border-l-4 border-red-500" role="alert">
                <div class="p-4 flex items-start">
                    <span class="material-icons-outlined text-red-600 text-base mr-2 shrink-0">error_outline</span>
                    <div class="text-xs">
                        <p class="font-semibold text-red-700 mb-0.5">Your registration was not submitted</p>
                        <p class="text-gray-700">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-white shadow rounded-lg overflow-hidden mb-6 border-l-4 border-red-500" role="alert">
                <div class="p-4 flex items-start">
                    <span class="material-icons-outlined text-red-600 text-base mr-2 shrink-0">error_outline</span>
                    <div class="text-xs">
                        <p class="font-semibold text-red-700 mb-1">
                            Please correct {{ $errors->count() === 1 ? 'this detail' : 'these details' }} and submit again
                        </p>
                        <ul class="text-gray-700 list-disc pl-4 space-y-0.5">
                            @foreach ($errors->all() as $message)
                                <li>{{ $message }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <!-- Section 1: Banner & Event Info (Selalu di atas) -->
        <div class="bg-white shadow rounded-lg overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-4 py-3">
                <div class="flex items-center">
                    <div class="flex-shrink-0 mr-3">
                        <span class="material-icons-outlined text-white text-xl">event</span>
                    </div>
                    <h1 class="text-white text-lg font-semibold leading-tight">
                        {{ $event->name }}
                    </h1>
                </div>
            </div>
            <div class="p-4 text-xs">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Left column: fixed label width for aligned colons -->
                    <div>
                        <div class="mb-1 flex items-start">
                            <span class="font-semibold inline-block w-28">Date</span>
                            <span class="mx-1">:</span>
                            <span>{{ \Carbon\Carbon::parse($event->start_date)->format('d M Y') }} - {{ \Carbon\Carbon::parse($event->end_date)->format('d M Y') }}</span>
                        </div>
                        <div class="mb-1 flex items-start">
                            <span class="font-semibold inline-block w-28">Time</span>
                            <span class="mx-1">:</span>
                            <span>{{ $event->start_time ? substr($event->start_time, 0, 5) : '' }} - {{ $event->end_time ? substr($event->end_time, 0, 5) : '' }}</span>
                        </div>
                        <div class="mb-1 flex items-start">
                            <span class="font-semibold inline-block w-28">Location</span>
                            <span class="mx-1">:</span>
                            <span>{{ $event->location }}</span>
                        </div>
                        @if ($event->address)
                        <div class="mb-1 flex items-start">
                            <span class="font-semibold inline-block w-28">Address</span>
                            <span class="mx-1">:</span>
                            <span>{{ $event->address }}</span>
                        </div>
                        @endif
                    </div>
                    <!-- Right column: wider label width for longer texts -->
                    <div>
                        <div class="mb-1 flex items-start">
                            <span class="font-semibold inline-block w-36">Organizer</span>
                            <span class="mx-1">:</span>
                            <span>{{ $event->organizer }}</span>
                        </div>
                        @if ($event->contact_person)
                        <div class="mb-1 flex items-start">
                            <span class="font-semibold inline-block w-36">Contact Person</span>
                            <span class="mx-1">:</span>
                            <span>{{ $event->contact_person }}</span>
                        </div>
                        @endif
                        @if ($event->contact_email)
                        <div class="mb-1 flex items-start">
                            <span class="font-semibold inline-block w-36">Contact Email</span>
                            <span class="mx-1">:</span>
                            <span>{{ $event->contact_email }}</span>
                        </div>
                        @endif
                        @if ($event->contact_phone)
                        <div class="mb-1 flex items-start">
                            <span class="font-semibold inline-block w-36">Contact Phone</span>
                            <span class="mx-1">:</span>
                            <span>{{ $event->contact_phone }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Stepper Navigation -->
        <div class="flex justify-center mb-4">
            <template x-for="n in 5" :key="n">
                <div :class="{'bg-blue-600 text-white': step === n, 'bg-gray-200 text-gray-500': step !== n, 'cursor-not-allowed opacity-50': step !== n, 'cursor-default': step === n}" class="w-7 h-7 flex items-center justify-center rounded-full mx-1 text-xs font-bold">
                    <span x-text="n"></span>
                </div>
            </template>
        </div>

        <!-- Section 2: Syarat Event -->
        <div x-show="step === 2" class="bg-white shadow rounded-lg overflow-hidden mb-6 text-xs">
            <div class="bg-gradient-to-r from-green-500 to-green-600 px-4 py-3">
                <h2 class="text-white text-base font-semibold flex items-center">
                    <span class="material-icons-outlined text-white text-sm mr-2">rule</span>
                    Syarat-syarat Program/Event
                </h2>
            </div>
            <div class="p-4">
                <div class="rich-content text-xs leading-5">{!! $event->condition ?? '-' !!}</div>
            </div>
            <div class="p-4 flex justify-end">
                <button type="button" @click="next()" class="btn btn-primary flex items-center gap-2">
                    <span>Next</span>
                    <span class="material-icons-outlined text-[16px]">arrow_forward</span>
                </button>
            </div>
        </div>

        <!-- Section 3: Particulars 1 -->
        <form x-show="step === 3" @submit.prevent="next()" class="bg-white shadow rounded-lg overflow-hidden mb-6 text-xs">
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-4 py-3">
                <h2 class="text-white text-base font-semibold flex items-center">
                    <span class="material-icons-outlined text-white text-sm mr-2">person</span>
                    Personal Information
                </h2>
            </div>
            <div class="p-4 space-y-3">
                <div>
                    <label class="block mb-1">Full Name</label>
                    <input type="text" x-model="form.name" name="name" class="w-full border border-gray-300 rounded px-2 py-1 text-xs" required>
                </div>
                @if(!$event->skip_identity_verification)
                <!-- IC/Passport Dropdown -->
                <div>
                    <label class="block mb-1">Identity Card / Passport No.</label>
                    <select x-model="form.id_type" name="id_type" id="id_type" class="w-full border border-gray-300 rounded px-2 py-1 text-xs" @change="form.identity_card='';form.passport_no='';" :disabled="locked.identity !== ''">
                        <option value="">-- Select IC / Passport --</option>
                        <option value="ic">Identity Card</option>
                        <option value="passport">Passport</option>
                    </select>
                </div>
                <div x-show="form.id_type === 'ic'">
                    <label class="block mb-1">Identity Card (IC)</label>
                    <input type="text" x-model="form.identity_card" name="identity_card" id="identity_card" maxlength="14" class="w-full border border-gray-300 rounded px-2 py-1 text-xs" placeholder="000000-00-0000" @input="formatIC($event)" @blur="formatIC($event)" :readonly="locked.id_type==='ic' && locked.identity !== ''">
                </div>
                <div x-show="form.id_type === 'passport'">
                    <label class="block mb-1">Passport No.</label>
                    <input type="text" x-model="form.passport_no" name="passport_no" class="w-full border border-gray-300 rounded px-2 py-1 text-xs" placeholder="A00000000" :readonly="locked.id_type==='passport' && locked.identity !== ''">
                </div>
                @endif
                <!-- Address Section (copy from participants/create) -->
                <div class="grid grid-cols-2 gap-2 mb-2">
                    <div>
                        <label class="block mb-1">Address Line 1</label>
                        <input type="text" x-model="form.address1" name="address1" id="address1" class="w-full border border-gray-300 rounded px-2 py-1 text-xs">
                    </div>
                    <div>
                        <label class="block mb-1">Address Line 2</label>
                        <input type="text" x-model="form.address2" name="address2" id="address2" class="w-full border border-gray-300 rounded px-2 py-1 text-xs">
                    </div>
                </div>
                <div class="grid grid-cols-4 gap-2">
                    <div>
                        <label class="block mb-1">Country</label>
                        <select x-model="form.country" name="country" id="country" class="w-full border border-gray-300 rounded px-2 py-1 text-xs" @change="form.state=''; form.city=''; form.postcode=''; form.manual_state=''; form.manual_city=''; form.manual_postcode=''"></select>
                    </div>
                    <!-- Malaysia: Show dropdowns -->
                    <div x-show="form.country === 'Malaysia'">
                        <label class="block mb-1">State</label>
                        <select x-model="form.state" name="state" id="state" class="w-full border border-gray-300 rounded px-2 py-1 text-xs">
                            <!-- options populated by JS -->
                        </select>
                    </div>
                    <div x-show="form.country === 'Malaysia'">
                        <label class="block mb-1">City</label>
                        <select x-model="form.city" name="city" id="city" class="w-full border border-gray-300 rounded px-2 py-1 text-xs" :disabled="!form.state || form.state === 'others'" x-show="form.state !== 'others'"></select>
                    </div>
                    <div x-show="form.country === 'Malaysia'">
                        <label class="block mb-1">Postcode</label>
                        <select x-show="form.state !== 'others'" x-model="form.postcode" name="postcode" id="postcode" class="w-full border border-gray-300 rounded px-2 py-1 text-xs" :disabled="!form.city"></select>
                        <input x-show="form.state === 'others'" type="text" x-model="form.manual_postcode" name="manual_postcode" id="manual_postcode_alt" class="w-full border border-gray-300 rounded px-2 py-1 text-xs" placeholder="Enter postcode">
                    </div>
                    <!-- Other Countries: Show text inputs -->
                    <div x-show="form.country !== 'Malaysia'">
                        <label class="block mb-1">State/Province</label>
                        <input type="text" x-model="form.manual_state" name="manual_state" id="manual_state" class="w-full border border-gray-300 rounded px-2 py-1 text-xs" placeholder="Enter state/province">
                    </div>
                    <div x-show="form.country !== 'Malaysia'">
                        <label class="block mb-1">City</label>
                        <input type="text" x-model="form.manual_city" name="manual_city" id="manual_city" class="w-full border border-gray-300 rounded px-2 py-1 text-xs" placeholder="Enter city">
                    </div>
                    <div x-show="form.country !== 'Malaysia'">
                        <label class="block mb-1">Postcode/ZIP</label>
                        <input type="text" x-model="form.manual_postcode" name="manual_postcode" id="manual_postcode" class="w-full border border-gray-300 rounded px-2 py-1 text-xs" placeholder="Enter postcode/ZIP">
                    </div>
                </div>
            </div>
            <div class="p-4 flex justify-between">
                <button type="button" @click="prev()" class="btn btn-secondary">Back</button>
                <button type="submit" class="btn btn-primary flex items-center gap-2">
                    <span>Next</span>
                    <span class="material-icons-outlined text-[16px]">arrow_forward</span>
                </button>
            </div>
        </form>

        <!-- Section 4: Particulars 2 -->
        <form x-show="step === 4" @submit.prevent="next()" class="bg-white shadow rounded-lg overflow-hidden mb-6 text-xs">
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-4 py-3">
                <h2 class="text-white text-base font-semibold flex items-center">
                    <span class="material-icons-outlined text-white text-sm mr-2">work</span>
                    Organization & Contact
                </h2>
            </div>
            <div class="p-4 space-y-3">
                @if(!$event->skip_identity_verification)
                <div>
                    <label class="block mb-1">Company / Government</label>
                    <input type="text" x-model="form.organization" name="organization" class="w-full border border-gray-300 rounded px-2 py-1 text-xs">
                </div>
                <div>
                    <label class="block mb-1">Job Title</label>
                    <input type="text" x-model="form.job_title" name="job_title" class="w-full border border-gray-300 rounded px-2 py-1 text-xs">
                </div>
                @endif
                <div>
                    <label class="block mb-1">Email</label>
                    <input type="email" x-model="form.email" name="email" class="w-full border border-gray-300 rounded px-2 py-1 text-xs" required :readonly="locked.email !== ''" :value="locked.email || form.email">
                </div>
                <div>
                    <label class="block mb-1">Phone Number</label>
                    <input type="tel" name="phone" id="phone" class="phone-input w-full border border-gray-300 rounded px-2 py-1 text-xs" x-model="form.phone">
                </div>
                <div>
                    <label class="block mb-1">Gender</label>
                    <select x-model="form.gender" name="gender" class="w-full border border-gray-300 rounded px-2 py-1 text-xs">
                        <option value="">-- Select Gender --</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block mb-1">Date of Birth</label>
                    <input type="date" x-model="form.date_of_birth" name="date_of_birth" class="w-full border border-gray-300 rounded px-2 py-1 text-xs">
                </div>
                <div>
                    <label class="block mb-1">Race</label>
                    <select x-model="form.race" name="race" class="w-full border border-gray-300 rounded px-2 py-1 text-xs">
                        <option value="">-- Select Race --</option>
                        <option value="Malay (Peninsular)">Malay (Peninsular)</option>
                        <option value="Malay (Sarawak)">Malay (Sarawak)</option>
                        <option value="Malay (Sabah)">Malay (Sabah)</option>
                        <option value="Chinese Hokkien">Chinese Hokkien</option>
                        <option value="Chinese Cantonese">Chinese Cantonese</option>
                        <option value="Chinese Hakka">Chinese Hakka</option>
                        <option value="Chinese Teochew">Chinese Teochew</option>
                        <option value="Chinese Foochow">Chinese Foochow</option>
                        <option value="Chinese Hainan">Chinese Hainan</option>
                        <option value="Chinese Kwongsai">Chinese Kwongsai</option>
                        <option value="Chinese Henghua">Chinese Henghua</option>
                        <option value="Chinese Others">Chinese Others</option>
                        <option value="Indian Tamil">Indian Tamil</option>
                        <option value="Indian Punjabi">Indian Punjabi</option>
                        <option value="Indian Malayalee">Indian Malayalee</option>
                        <option value="Indian Telugu">Indian Telugu</option>
                        <option value="Indian Gujerati">Indian Gujerati</option>
                        <option value="Indian Bengali">Indian Bengali</option>
                        <option value="Indian Others">Indian Others</option>
                        <option value="Iban">Iban</option>
                        <option value="Kadazan">Kadazan</option>
                        <option value="Dusun">Dusun</option>
                        <option value="Bajau">Bajau</option>
                        <option value="Sama">Sama</option>
                        <option value="Bidayuh">Bidayuh</option>
                        <option value="Melanau">Melanau</option>
                        <option value="Murut">Murut</option>
                        <option value="Orang Ulu Kayan">Orang Ulu Kayan</option>
                        <option value="Orang Ulu Kenyah">Orang Ulu Kenyah</option>
                        <option value="Orang Ulu Kelabit">Orang Ulu Kelabit</option>
                        <option value="Orang Ulu Penan">Orang Ulu Penan</option>
                        <option value="Orang Ulu Lun Bawang">Orang Ulu Lun Bawang</option>
                        <option value="Orang Ulu Others">Orang Ulu Others</option>
                        <option value="Orang Asli Temuan">Orang Asli Temuan</option>
                        <option value="Orang Asli Semai">Orang Asli Semai</option>
                        <option value="Orang Asli Jakun">Orang Asli Jakun</option>
                        <option value="Orang Asli Mah Meri">Orang Asli Mah Meri</option>
                        <option value="Orang Asli Negrito (Kensiu)">Orang Asli Negrito (Kensiu)</option>
                        <option value="Orang Asli Negrito (Kintaq)">Orang Asli Negrito (Kintaq)</option>
                        <option value="Orang Asli Negrito (Jahai)">Orang Asli Negrito (Jahai)</option>
                        <option value="Orang Asli Negrito (Lanoh)">Orang Asli Negrito (Lanoh)</option>
                        <option value="Orang Asli Negrito (Mendriq)">Orang Asli Negrito (Mendriq)</option>
                        <option value="Orang Asli Negrito (Batek)">Orang Asli Negrito (Batek)</option>
                        <option value="Orang Asli Senoi (Temiar)">Orang Asli Senoi (Temiar)</option>
                        <option value="Orang Asli Senoi (Semaq Beri)">Orang Asli Senoi (Semaq Beri)</option>
                        <option value="Orang Asli Senoi (Jah Hut)">Orang Asli Senoi (Jah Hut)</option>
                        <option value="Orang Asli Senoi (Che Wong)">Orang Asli Senoi (Che Wong)</option>
                        <option value="Orang Asli Proto-Malay (Temuan)">Orang Asli Proto-Malay (Temuan)</option>
                        <option value="Orang Asli Proto-Malay (Semelai)">Orang Asli Proto-Malay (Semelai)</option>
                        <option value="Orang Asli Proto-Malay (Jakun)">Orang Asli Proto-Malay (Jakun)</option>
                        <option value="Orang Asli Proto-Malay (Kanaq)">Orang Asli Proto-Malay (Kanaq)</option>
                        <option value="Orang Asli Proto-Malay (Seletar)">Orang Asli Proto-Malay (Seletar)</option>
                        <option value="Orang Asli Others">Orang Asli Others</option>
                        <option value="Sungai">Sungai</option>
                        <option value="Rungus">Rungus</option>
                        <option value="Lundayeh">Lundayeh</option>
                        <option value="Kedayan">Kedayan</option>
                        <option value="Bisaya">Bisaya</option>
                        <option value="Brunei">Brunei</option>
                        <option value="Bugis">Bugis</option>
                        <option value="Jawa">Jawa</option>
                        <option value="Banjar">Banjar</option>
                        <option value="Kristang/Serani">Kristang/Serani</option>
                        <option value="Sikh">Sikh</option>
                        <option value="Thai">Thai</option>
                        <option value="Peranakan/Baba Nyonya">Peranakan/Baba Nyonya</option>
                        <option value="Chitty">Chitty</option>
                        <option value="Others">Others</option>
                    </select>
                </div>
            </div>
            <div class="p-4 flex justify-between">
                <button type="button" @click="prev()" class="btn btn-secondary">Back</button>
                <button type="submit" class="btn btn-primary flex items-center gap-2">
                    <span>Next</span>
                    <span class="material-icons-outlined text-[16px]">arrow_forward</span>
                </button>
            </div>
        </form>

        <!-- Section 5: Preview & Submit -->
        <form x-show="step === 5" method="POST" action="{{ route('event.register.submit', $event->registration_link) }}" class="bg-white shadow rounded-lg overflow-hidden mb-6 text-xs">
            @csrf
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-4 py-3">
                <h2 class="text-white text-base font-semibold flex items-center">
                    <span class="material-icons-outlined text-white text-sm mr-2">preview</span>
                    Preview & Submit
                </h2>
            </div>
            <div class="p-4 space-y-2">
                <div class="font-semibold mb-2">Please review your information before submitting:</div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="mb-1 flex items-start"><span class="font-semibold inline-block w-36">Full Name</span><span class="mx-1">:</span><span x-text="form.name"></span></div>
                    <div class="mb-1 flex items-start" x-show="form.id_type === 'ic'"><span class="font-semibold inline-block w-36">IC</span><span class="mx-1">:</span><span x-text="form.identity_card"></span></div>
                    <div class="mb-1 flex items-start" x-show="form.id_type === 'passport'"><span class="font-semibold inline-block w-36">Passport</span><span class="mx-1">:</span><span x-text="form.passport_no"></span></div>
                    <div class="mb-1 flex items-start"><span class="font-semibold inline-block w-36">Address 1</span><span class="mx-1">:</span><span x-text="form.address1"></span></div>
                    <div class="mb-1 flex items-start"><span class="font-semibold inline-block w-36">Address 2</span><span class="mx-1">:</span><span x-text="form.address2"></span></div>
                    <div class="mb-1 flex items-start"><span class="font-semibold inline-block w-36">State</span><span class="mx-1">:</span><span x-text="form.country === 'Malaysia' ? (form.state === 'others' ? form.manual_state : form.state) : form.manual_state"></span></div>
                    <div class="mb-1 flex items-start"><span class="font-semibold inline-block w-36">City</span><span class="mx-1">:</span><span x-text="form.country === 'Malaysia' ? (form.state === 'others' ? form.manual_city : form.city) : form.manual_city"></span></div>
                    <div class="mb-1 flex items-start"><span class="font-semibold inline-block w-36">Postcode</span><span class="mx-1">:</span><span x-text="form.country === 'Malaysia' ? (form.state === 'others' ? form.manual_postcode : form.postcode) : form.manual_postcode"></span></div>
                    <div class="mb-1 flex items-start"><span class="font-semibold inline-block w-36">Country</span><span class="mx-1">:</span><span x-text="form.country"></span></div>
                    <div class="mb-1 flex items-start"><span class="font-semibold inline-block w-36">Organization</span><span class="mx-1">:</span><span x-text="form.organization"></span></div>
                    <div class="mb-1 flex items-start"><span class="font-semibold inline-block w-36">Job Title</span><span class="mx-1">:</span><span x-text="form.job_title"></span></div>
                    <div class="mb-1 flex items-start"><span class="font-semibold inline-block w-36">Email</span><span class="mx-1">:</span><span x-text="form.email"></span></div>
                    <div class="mb-1 flex items-start"><span class="font-semibold inline-block w-36">Phone</span><span class="mx-1">:</span><span x-text="formatPhoneForPreview(form.phone)"></span></div>
                    <div class="mb-1 flex items-start"><span class="font-semibold inline-block w-36">Gender</span><span class="mx-1">:</span><span x-text="formatGender(form.gender)"></span></div>
                    <div class="mb-1 flex items-start"><span class="font-semibold inline-block w-36">Date of Birth</span><span class="mx-1">:</span><span x-text="formatDateDmy(form.date_of_birth)"></span></div>
                    <div class="mb-1 flex items-start"><span class="font-semibold inline-block w-36">Age</span><span class="mx-1">:</span><span x-text="calculateAge(form.date_of_birth)"></span></div>
                    <div class="mb-1 flex items-start"><span class="font-semibold inline-block w-36">Race</span><span class="mx-1">:</span><span x-text="form.race"></span></div>
                </div>
            </div>
            <!-- Hidden fields for submit -->
            <template x-for="(value, key) in form" :key="key">
                <input type="hidden" :name="key" :value="value">
            </template>
            <!-- Locked identity/email for server guard -->
            <input type="hidden" name="locked_email" :value="locked.email">
            <input type="hidden" name="locked_id_type" :value="locked.id_type">
            <input type="hidden" name="locked_identity" :value="locked.identity">
            <div class="p-4 flex justify-between">
                <button type="button" @click="prev()" class="btn btn-secondary">Back</button>
                <button type="submit" class="btn btn-success flex items-center gap-2">
                    <span>Submit</span>
                    <span class="material-icons-outlined text-[16px]">check_circle</span>
                </button>
            </div>
        </form>

        <!-- Section 1: Welcome & Poster -->
        <div x-show="step === 1" class="bg-white shadow rounded-lg overflow-hidden mb-6 text-xs">
            <div class="bg-gradient-to-r from-green-500 to-green-600 px-4 py-3">
                <h2 class="text-white text-base font-semibold flex items-center">
                    <span class="material-icons-outlined text-white text-sm mr-2">info</span>
                    Welcome! Please review the event information and click Next to proceed.
                </h2>
            </div>
            <div class="p-4">
                @if($event->poster)
                <div class="mb-4">
                    <div class="flex justify-center">
                        <img src="{{ asset('storage/'.$event->poster) }}" alt="Event Poster" class="mx-auto rounded border border-gray-200 shadow max-w-full w-full sm:max-w-md md:max-w-lg lg:max-w-xl">
                    </div>
                </div>
                @endif
                <div class="flex justify-end">
                    @if($event->skip_identity_verification)
                    <button type="button" @click="next()" class="px-4 py-1 bg-blue-600 text-white rounded text-xs">Next</button>
                    @else
                    <button type="button" @click="openAuthGate()" class="px-4 py-1 bg-blue-600 text-white rounded text-xs">Next</button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Auth Gate Modal (IC Lookup + Login/Register) -->
        <div x-show="auth.open" style="display:none" class="fixed inset-0 z-50">
            <div class="absolute inset-0 modal-backdrop-glass" @click="auth.open=false"></div>
            <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 bg-white modal-card shadow w-full max-w-md text-xs">
                <div class="px-4 py-3 border-b flex items-center justify-between">
                    <h3 class="font-semibold">Account Verification (IC/Passport)</h3>
                    <button type="button" class="text-gray-500 hover:text-gray-700" @click="auth.open=false">
                        <span class="material-icons-outlined text-sm">close</span>
                    </button>
                </div>
                <div class="p-4 space-y-3">
                    <!-- Step: Lookup -->
                    <template x-if="auth.step==='lookup'">
                        <div>
                            <label class="block mb-1">Enter IC/Passport for verification</label>
                            <div class="flex gap-2">
                                <select x-model="auth.idType" class="border border-gray-300 rounded-sm px-2 py-1" style="min-width: 110px;">
                                    <option value="ic">IC</option>
                                    <option value="passport">Passport</option>
                                </select>
                                <input type="text" x-model="auth.ic" x-show="auth.idType==='ic'" class="w-full border border-gray-300 rounded-sm px-2 py-1" placeholder="000000-00-0000" @input="formatIC($event)">
                                <input type="text" x-model="auth.passport" x-show="auth.idType==='passport'" class="w-full border border-gray-300 rounded-sm px-2 py-1" placeholder="A12345678">
                            </div>
                            <div class="flex items-center justify-between mt-3">
                                <div class="hint">Format: 000000-00-0000</div>
                                <button type="button" @click="submitLookup()" class="btn btn-primary flex items-center gap-1" :disabled="auth.loading">
                                    <span class="material-icons-outlined text-[16px]" x-show="!auth.loading">search</span>
                                    <span x-show="auth.loading" class="inline-block w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                    <span>Search</span>
                                </button>
                            </div>
                        </div>
                    </template>

                    <!-- Step: Sign in to the account this document belongs to -->
                    <template x-if="auth.step==='login'">
                        <div class="space-y-2">
                            <div class="bg-gray-50 border rounded p-2">
                                <p class="text-[11px] text-gray-600">
                                    This IC or passport is already linked to an account. Sign in to confirm
                                    it is yours, and we will fill in what we already have.
                                </p>
                            </div>

                            <div>
                                <label class="block mb-1" x-text="auth.accounts.length > 1 ? 'Choose an account' : 'Account'"></label>
                                <template x-if="auth.accounts.length > 1">
                                    <div class="space-y-1">
                                        <template x-for="acc in auth.accounts" :key="acc.id">
                                            <label class="flex items-center gap-2">
                                                <input type="radio" name="gate_account" x-model.number="auth.accountId" :value="acc.id">
                                                <span x-text="acc.email_masked"></span>
                                            </label>
                                        </template>
                                    </div>
                                </template>
                                <template x-if="auth.accounts.length === 1">
                                    <div class="px-2 py-1 bg-gray-50 border border-gray-200 rounded-sm text-gray-700"
                                         x-text="auth.accounts[0].email_masked"></div>
                                </template>
                                <p class="hint mt-1">Part of the address is hidden. Sign in to confirm it is yours.</p>
                            </div>

                            <div>
                                <label class="block mb-1">Password</label>
                                <input type="password" x-model="auth.login.password" class="w-full border border-gray-300 rounded-sm px-2 py-1"
                                       @keydown.enter.prevent="doLogin()">
                            </div>
                            <div class="flex items-center justify-between mt-2">
                                <button type="button" @click="doLogin()" class="btn btn-primary flex items-center gap-1" :disabled="auth.loading">
                                    <span class="material-icons-outlined text-[16px]" x-show="!auth.loading">login</span>
                                    <span x-show="auth.loading" class="inline-block w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                    <span>Sign in</span>
                                </button>
                                <button type="button" @click="doResetPassword()" class="btn btn-secondary flex items-center gap-1" :disabled="auth.loading">
                                    <span class="material-icons-outlined text-[16px]">lock_reset</span>
                                    <span>Email me a new password</span>
                                </button>
                            </div>
                            <div class="flex justify-end">
                                <button type="button" @click="auth.step='register'; auth.message=''" class="px-3 py-1 text-blue-600">
                                    Use a different email instead
                                </button>
                            </div>
                        </div>
                    </template>

                    <!-- Step: No account yet, so create one -->
                    <template x-if="auth.step==='register'">
                        <div class="space-y-2">
                            <div class="bg-gray-50 border rounded p-2">
                                <p class="text-[11px] text-gray-600">
                                    Enter the details of the person who will hold this account. One account
                                    can register several people, so if you are signing up a child or someone
                                    you are acting for, use your own name and email here.
                                </p>
                            </div>
                            <div>
                                <label class="block mb-1">Account holder's full name</label>
                                <input type="text" x-model="auth.register.name" class="w-full border border-gray-300 rounded-sm px-2 py-1">
                            </div>
                            <div>
                                <label class="block mb-1">Email</label>
                                <input type="email" x-model="auth.register.email" class="w-full border border-gray-300 rounded-sm px-2 py-1">
                                <p class="hint mt-1">Certificates and event notices are sent here.</p>
                            </div>
                            <div>
                                <label class="block mb-1">Password</label>
                                <input type="password" x-model="auth.register.password" class="w-full border border-gray-300 rounded-sm px-2 py-1"
                                       @keydown.enter.prevent="doRegister()">
                                <p class="hint mt-1">At least 8 characters.</p>
                            </div>
                            <div class="flex items-center justify-between">
                                <button type="button" x-show="auth.accounts.length" @click="auth.step='login'; auth.message=''"
                                        class="px-3 py-1 text-blue-600">Back to sign in</button>
                                <button type="button" @click="doRegister()" class="btn btn-success flex items-center gap-1 ml-auto" :disabled="auth.loading">
                                    <span class="material-icons-outlined text-[16px]" x-show="!auth.loading">person_add</span>
                                    <span x-show="auth.loading" class="inline-block w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                    <span>Create account</span>
                                </button>
                            </div>
                        </div>
                    </template>

                    <template x-if="auth.message">
                        <div class="text-[11px] text-red-600" x-text="auth.message"></div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Malaysia Postcodes Script -->
<script src="/js/malaysia-postcodes.js"></script>
<script>
    // Initialize Malaysia postcodes when country is Malaysia
    document.addEventListener('DOMContentLoaded', async function() {
        // Wait for Alpine.js to be ready
        await new Promise(resolve => {
            if (window.Alpine) {
                resolve();
            } else {
                document.addEventListener('alpine:init', resolve);
            }
        });
        
        // Load Malaysia data
        await loadMalaysiaData();
        
        // Get country select element
        const countrySelect = document.getElementById('country');
        const stateSelect = document.getElementById('state');
        const citySelect = document.getElementById('city');
        const postcodeSelect = document.getElementById('postcode');
        
        // Function to initialize Malaysia dropdowns
        function initMalaysiaDropdowns() {
            if (!stateSelect || !citySelect || !postcodeSelect) return;
            
            // Populate states
            populateStates(stateSelect);
            
            // Add event listeners
            stateSelect.addEventListener('change', function() {
                const selectedState = this.value;
                populateCities(citySelect, selectedState);
                postcodeSelect.innerHTML = '<option value="">-- Select Postcode --</option>';
                postcodeSelect.disabled = true;
                citySelect.value = '';
                postcodeSelect.value = '';
            });
            
            citySelect.addEventListener('change', function() {
                const selectedState = stateSelect.value;
                const selectedCity = this.value;
                populatePostcodes(postcodeSelect, selectedState, selectedCity);
                postcodeSelect.value = '';
            });
        }
        
        // Initialize if Malaysia is selected
        if (countrySelect && countrySelect.value === 'Malaysia') {
            initMalaysiaDropdowns();
        }
        
        // Re-initialize when country changes to Malaysia
        if (countrySelect) {
            countrySelect.addEventListener('change', function() {
                if (this.value === 'Malaysia') {
                    initMalaysiaDropdowns();
                }
            });
        }
    });
</script>

@endsection 