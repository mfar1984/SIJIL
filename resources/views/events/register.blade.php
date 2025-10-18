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
    auth: {
        open: false,
        step: 'lookup', // lookup | login | register | reset | done
        ic: '',
        passport: '',
        idType: 'ic',
        loading: false,
        result: null,
        emailChoice: '',
        message: '',
        login: { email: '', password: '' },
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
    },
    async submitLookup() {
        if (this.auth.idType === 'ic' && !this.auth.ic) { this.auth.message = 'Sila masukkan IC untuk semakan.'; return; }
        if (this.auth.idType === 'passport' && !this.auth.passport) { this.auth.message = 'Sila masukkan Passport untuk semakan.'; return; }
        this.auth.loading = true; this.auth.message = '';
        try {
            const params = new URLSearchParams();
            params.append('id_type', this.auth.idType);
            if (this.auth.idType === 'ic') {
                const normalized = (this.auth.ic || '').replace(/\D/g,'');
                params.append('ic', normalized);
            } else {
                params.append('passport', (this.auth.passport || '').trim());
            }
            const res = await fetch(`/api/participant/lookup?${params.toString()}`);
            const data = await res.json();
            if (!data.success) { this.auth.message = data.message || 'Semakan gagal.'; return; }
            this.auth.result = data.data;
            if (this.auth.result.exists) {
                // Prefill nama dari rekod terakhir, pilih emel pertama sebagai default
                this.auth.emailChoice = (this.auth.result.emails?.[0]) || '';
                this.auth.login.email = this.auth.emailChoice;
                this.auth.step = 'login';
            } else {
                // Daftar baru
                this.auth.register.name = '';
                this.auth.register.email = '';
                this.auth.step = 'register';
            }
        } catch (e) {
            this.auth.message = 'Ralat rangkaian semasa semakan.';
        } finally {
            this.auth.loading = false;
        }
    },
    prefillFromLastParticipant() {
        const lp = this.auth.result?.last_participant; if (!lp) return;
        this.form.name = lp.name || this.form.name;
        this.form.email = this.auth.emailChoice || lp.email || this.form.email;
        this.form.phone = lp.phone || this.form.phone;
        // IC / Passport
        if (lp.identity_card) {
            this.form.id_type = 'ic';
            // normalise then format with dashes
            const digits = (lp.identity_card || '').replace(/\D/g,'');
            let formatted = digits;
            if (digits.length === 12) {
                formatted = digits.substring(0,6) + '-' + digits.substring(6,8) + '-' + digits.substring(8,12);
            }
            this.form.identity_card = formatted;
            this.form.passport_no = '';
            this.locked.id_type = 'ic';
            this.locked.identity = digits;
        } else if (lp.passport_no) {
            this.form.id_type = 'passport';
            this.form.passport_no = lp.passport_no;
            this.form.identity_card = '';
            this.locked.id_type = 'passport';
            this.locked.identity = (lp.passport_no || '').trim();
        }
        this.form.address1 = lp.address1 || this.form.address1;
        this.form.address2 = lp.address2 || this.form.address2;
        this.form.state = lp.state || this.form.state;
        this.form.city = lp.city || this.form.city;
        this.form.postcode = lp.postcode || this.form.postcode;
        this.form.country = lp.country || this.form.country || 'Malaysia';
        this.form.gender = lp.gender || this.form.gender;
        this.form.date_of_birth = this.normalizeDateToYmd(lp.date_of_birth) || this.form.date_of_birth;
        this.form.race = lp.race || this.form.race;
        this.form.job_title = lp.job_title || this.form.job_title;
        this.form.organization = lp.organization || this.form.organization;
        // lock email to chosen
        this.locked.email = this.auth.emailChoice || lp.email || this.form.email;

        // Prefill DOB and Race if available
        if (lp.date_of_birth) { this.form.date_of_birth = this.normalizeDateToYmd(lp.date_of_birth); }
        if (lp.race) { this.form.race = lp.race; }

        // Ensure selects display values even if options belum dimuat
        this.ensureSelectOption('state', this.form.state);
        this.ensureSelectOption('city', this.form.city);
        this.ensureSelectOption('postcode', this.form.postcode);
        this.ensureSelectOption('country', this.form.country);
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
        if (!this.auth.login.email || !this.auth.login.password) { this.auth.message = 'Sila isi emel dan kata laluan.'; return; }
        this.auth.loading = true; this.auth.message = '';
        try {
            const res = await fetch('/api/participant/login', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ email: this.auth.login.email, password: this.auth.login.password })});
            const data = await res.json();
            if (!data.success) { this.auth.message = data.message || 'Log masuk gagal.'; return; }
            this.prefillFromLastParticipant();
            this.auth.open = false; this.step = 3;
            // Scroll to top of Tab 3 for clarity
            setTimeout(() => { window.scrollTo({ top: 0, behavior: 'smooth' }); }, 50);
            // Ensure locked email set for login path
            this.locked.email = this.auth.login.email || this.locked.email;
        } catch (e) {
            this.auth.message = 'Ralat rangkaian semasa log masuk.';
        } finally { this.auth.loading = false; }
    },
    async doRegister() {
        if (!this.auth.register.name || !this.auth.register.email || !this.auth.register.password) { this.auth.message = 'Nama, emel dan kata laluan diperlukan.'; return; }
        this.auth.loading = true; this.auth.message = '';
        try {
            const res = await fetch('/api/participant/register', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ name: this.auth.register.name, email: this.auth.register.email, password: this.auth.register.password })});
            const data = await res.json();
            if (!data.success) {
                // Map validation errors nicely
                const firstError = (errs) => {
                    if (!errs) return '';
                    for (const k in errs) { if (errs[k] && errs[k][0]) return errs[k][0]; }
                    return '';
                };
                const msg = firstError(data.errors) || data.message || 'Registration failed.';
                // If email already registered, guide user to Login step
                if ((data.errors && data.errors.email) || /already been taken/i.test(msg)) {
                    this.auth.message = 'This email is already registered. Please login.';
                    this.auth.login.email = this.auth.register.email;
                    this.auth.step = 'login';
                } else {
                    this.auth.message = msg;
                }
                return;
            }
            // Prefill daripada input daftar
            this.form.name = this.auth.register.name;
            this.form.email = this.auth.register.email;
            // Also carry over ID type and value from lookup input
            if (this.auth.idType === 'ic') {
                this.form.id_type = 'ic';
                const digits = (this.auth.ic || '').replace(/\D/g,'');
                if (digits.length === 12) {
                    this.form.identity_card = digits.substring(0,6) + '-' + digits.substring(6,8) + '-' + digits.substring(8,12);
                } else {
                    this.form.identity_card = digits;
                }
                this.form.passport_no = '';
            } else if (this.auth.idType === 'passport') {
                this.form.id_type = 'passport';
                const pass = (this.auth.passport || '').trim();
                this.form.passport_no = pass;
                this.form.identity_card = '';
            }
            this.auth.open = false; this.step = 3;
            setTimeout(() => { window.scrollTo({ top: 0, behavior: 'smooth' }); }, 50);
            // Lock based on registration inputs and current lookup idType
            this.locked.email = this.auth.register.email; // keep email read-only after register
            // Do NOT lock identity for new registration so user can edit the IC/Passport
            this.locked.id_type = '';
            this.locked.identity = '';
            // Ensure default country is set and options exist
            if (!this.form.country) { this.form.country = 'Malaysia'; this.ensureSelectOption('country', 'Malaysia'); }
        } catch (e) {
            this.auth.message = 'Network error during registration.';
        } finally { this.auth.loading = false; }
    },
    async doResetPassword() {
        if (!this.auth.emailChoice) { this.auth.message = 'Sila pilih emel untuk reset kata laluan.'; return; }
        this.auth.loading = true; this.auth.message = '';
        try {
            const eventToken = '{{ $event->registration_link }}';
            const res = await fetch('/api/participant/reset-password', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ email: this.auth.emailChoice, event_token: eventToken })});
            const data = await res.json();
            this.auth.message = data.message || 'Permintaan reset dihantar (jika emel wujud).';
        } catch (e) {
            this.auth.message = 'Ralat rangkaian semasa reset.';
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
        const states = [
            'Johor','Kedah','Kelantan','Melaka','Negeri Sembilan','Pahang','Perak','Perlis','Pulau Pinang','Sabah','Sarawak','Selangor','Terengganu','Wilayah Persekutuan Kuala Lumpur','Wilayah Persekutuan Labuan','Wilayah Persekutuan Putrajaya','others'
        ];
        const stateEl = document.getElementById('state');
        if (!stateEl) return;
        // clear existing
        stateEl.innerHTML = '';
        // placeholder
        const ph = document.createElement('option'); ph.value = ''; ph.textContent = '-- Select State --'; stateEl.appendChild(ph);
        states.forEach(s => { const o = document.createElement('option'); o.value = s; o.textContent = s; stateEl.appendChild(o); });
        // keep value if already set
        if (this.form.state) stateEl.value = this.form.state;
    },
    loadCountries() {
        const countries = ['Malaysia','Singapore','Thailand','Indonesia','Brunei','Others'];
        const countryEl = document.getElementById('country');
        if (!countryEl) return;
        countryEl.innerHTML = '';
        countries.forEach(c => { const o = document.createElement('option'); o.value = c; o.textContent = c; countryEl.appendChild(o); });
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
        <!-- Section 1: Banner & Event Info (Selalu di atas) -->
        <div class="bg-white shadow rounded-lg overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-4 py-3">
                <div class="flex items-center">
                    <div class="flex-shrink-0 mr-3">
                        <span class="material-icons text-white text-xl">event</span>
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
                <div :class="{'bg-blue-600 text-white': step === n, 'bg-gray-200 text-gray-500': step !== n}" class="w-7 h-7 flex items-center justify-center rounded-full mx-1 text-xs font-bold cursor-pointer" @click="step = n">
                    <span x-text="n"></span>
                </div>
            </template>
        </div>

        <!-- Section 2: Syarat Event -->
        <div x-show="step === 2" class="bg-white shadow rounded-lg overflow-hidden mb-6 text-xs">
            <div class="bg-gradient-to-r from-green-500 to-green-600 px-4 py-3">
                <h2 class="text-white text-base font-semibold flex items-center">
                    <span class="material-icons text-white text-sm mr-2">rule</span>
                    Syarat-syarat Program/Event
                </h2>
            </div>
            <div class="p-4">
                <div class="rich-content text-xs leading-5">{!! $event->condition ?? '-' !!}</div>
            </div>
            <div class="p-4 flex justify-end">
                <button type="button" @click="next()" class="btn btn-primary flex items-center gap-2">
                    <span class="material-icons text-[16px]">arrow_forward</span>
                    <span>Next</span>
                </button>
            </div>
        </div>

        <!-- Section 3: Particulars 1 -->
        <form x-show="step === 3" @submit.prevent="next()" class="bg-white shadow rounded-lg overflow-hidden mb-6 text-xs">
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-4 py-3">
                <h2 class="text-white text-base font-semibold flex items-center">
                    <span class="material-icons text-white text-sm mr-2">person</span>
                    Personal Information
                </h2>
            </div>
            <div class="p-4 space-y-3">
                <div>
                    <label class="block mb-1">Full Name</label>
                    <input type="text" x-model="form.name" name="name" class="w-full border border-gray-300 rounded px-2 py-1 text-xs" required>
                </div>
                <!-- IC/Passport Dropdown -->
                <div>
                    <label class="block mb-1">Identity Card / Passport No.</label>
                    <select x-model="form.id_type" name="id_type" id="id_type" class="w-full border border-gray-300 rounded px-2 py-1 text-xs" @change="form.identity_card='';form.passport_no='';" :disabled="locked.identity">
                        <option value="">-- Select IC / Passport --</option>
                        <option value="ic">Identity Card</option>
                        <option value="passport">Passport</option>
                    </select>
                </div>
                <div x-show="form.id_type === 'ic'">
                    <label class="block mb-1">Identity Card (IC)</label>
                    <input type="text" x-model="form.identity_card" name="identity_card" id="identity_card" maxlength="14" class="w-full border border-gray-300 rounded px-2 py-1 text-xs" placeholder="000000-00-0000" @input="formatIC($event)" @blur="formatIC($event)" :readonly="locked.id_type==='ic' && locked.identity">
                </div>
                <div x-show="form.id_type === 'passport'">
                    <label class="block mb-1">Passport No.</label>
                    <input type="text" x-model="form.passport_no" name="passport_no" class="w-full border border-gray-300 rounded px-2 py-1 text-xs" placeholder="A00000000" :readonly="locked.id_type==='passport' && locked.identity">
                </div>
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
                        <label class="block mb-1">State</label>
                        <select x-model="form.state" name="state" id="state" class="w-full border border-gray-300 rounded px-2 py-1 text-xs">
                            <!-- options populated by JS -->
                        </select>
                    </div>
                    <div>
                        <label class="block mb-1">City</label>
                        <select x-model="form.city" name="city" id="city" class="w-full border border-gray-300 rounded px-2 py-1 text-xs" :disabled="!form.state || form.state === 'others'" x-show="form.state !== 'others'"></select>
                    </div>
                    <div>
                        <label class="block mb-1">Postcode</label>
                        <template x-if="form.state !== 'others'">
                            <select x-model="form.postcode" name="postcode" id="postcode" class="w-full border border-gray-300 rounded px-2 py-1 text-xs" :disabled="!form.city"></select>
                        </template>
                        <template x-if="form.state === 'others'">
                            <input type="text" x-model="form.manual_postcode" name="manual_postcode" id="manual_postcode_alt" class="w-full border border-gray-300 rounded px-2 py-1 text-xs" placeholder="Enter postcode manually">
                        </template>
                    </div>
                    <div>
                        <label class="block mb-1">Country</label>
                        <select x-model="form.country" name="country" id="country" class="w-full border border-gray-300 rounded px-2 py-1 text-xs"></select>
                    </div>
                </div>
                <!-- Manual address fields if state == others -->
                <div x-show="form.state === 'others'" class="mt-2">
                    <div class="grid grid-cols-4 gap-2">
                        <div>
                            <label class="block mb-1">State (Manual)</label>
                            <input type="text" x-model="form.manual_state" name="manual_state" id="manual_state" class="w-full border border-gray-300 rounded px-2 py-1 text-xs" placeholder="Enter state manually">
                        </div>
                        <div>
                            <label class="block mb-1">City (Manual)</label>
                            <input type="text" x-model="form.manual_city" name="manual_city" id="manual_city" class="w-full border border-gray-300 rounded px-2 py-1 text-xs" placeholder="Enter city manually">
                        </div>
                        <div>
                            <label class="block mb-1">Postcode (Manual)</label>
                            <input type="text" x-model="form.manual_postcode" name="manual_postcode" id="manual_postcode" class="w-full border border-gray-300 rounded px-2 py-1 text-xs" placeholder="Enter postcode manually">
                        </div>
                        <div></div>
                    </div>
                </div>
            </div>
            <div class="p-4 flex justify-between">
                <button type="button" @click="prev()" class="btn btn-secondary">Back</button>
                <button type="submit" class="btn btn-primary flex items-center gap-2">
                    <span class="material-icons text-[16px]">arrow_forward</span>
                    <span>Next</span>
                </button>
            </div>
        </form>

        <!-- Section 4: Particulars 2 -->
        <form x-show="step === 4" @submit.prevent="next()" class="bg-white shadow rounded-lg overflow-hidden mb-6 text-xs">
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-4 py-3">
                <h2 class="text-white text-base font-semibold flex items-center">
                    <span class="material-icons text-white text-sm mr-2">work</span>
                    Organization & Contact
                </h2>
            </div>
            <div class="p-4 space-y-3">
                <div>
                    <label class="block mb-1">Company / Government</label>
                    <input type="text" x-model="form.organization" name="organization" class="w-full border border-gray-300 rounded px-2 py-1 text-xs">
                </div>
                <div>
                    <label class="block mb-1">Job Title</label>
                    <input type="text" x-model="form.job_title" name="job_title" class="w-full border border-gray-300 rounded px-2 py-1 text-xs">
                </div>
                <div>
                    <label class="block mb-1">Email</label>
                    <input type="email" x-model="form.email" name="email" class="w-full border border-gray-300 rounded px-2 py-1 text-xs" required :readonly="locked.email">
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
                    <label class="block mb-1">Race (Bangsa)</label>
                    <select x-model="form.race" name="race" class="w-full border border-gray-300 rounded px-2 py-1 text-xs">
                        <option value="">-- Select Race --</option>
                        <option value="Melayu (Semenanjung)">Melayu (Semenanjung)</option>
                        <option value="Melayu (Sarawak)">Melayu (Sarawak)</option>
                        <option value="Melayu (Sabah)">Melayu (Sabah)</option>
                        <option value="Cina Hokkien">Cina Hokkien</option>
                        <option value="Cina Kantonis">Cina Kantonis</option>
                        <option value="Cina Hakka">Cina Hakka</option>
                        <option value="Cina Teochew">Cina Teochew</option>
                        <option value="Cina Foochow">Cina Foochow</option>
                        <option value="Cina Hainan">Cina Hainan</option>
                        <option value="Cina Kwongsai">Cina Kwongsai</option>
                        <option value="Cina Henghua">Cina Henghua</option>
                        <option value="Cina lain-lain">Cina lain-lain</option>
                        <option value="India Tamil">India Tamil</option>
                        <option value="India Punjabi">India Punjabi</option>
                        <option value="India Malayalee">India Malayalee</option>
                        <option value="India Telugu">India Telugu</option>
                        <option value="India Gujerati">India Gujerati</option>
                        <option value="India Bengali">India Bengali</option>
                        <option value="India lain-lain">India lain-lain</option>
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
                        <option value="Orang Ulu (lain-lain)">Orang Ulu (lain-lain)</option>
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
                        <option value="Orang Asli (lain-lain)">Orang Asli (lain-lain)</option>
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
                        <option value="Lain-lain Warganegara">Lain-lain Warganegara</option>
                    </select>
                </div>
            </div>
            <div class="p-4 flex justify-between">
                <button type="button" @click="prev()" class="btn btn-secondary">Back</button>
                <button type="submit" class="btn btn-primary flex items-center gap-2">
                    <span class="material-icons text-[16px]">arrow_forward</span>
                    <span>Next</span>
                </button>
            </div>
        </form>

        <!-- Section 5: Preview & Submit -->
        <form x-show="step === 5" method="POST" action="{{ route('event.register.submit', $event->registration_link) }}" class="bg-white shadow rounded-lg overflow-hidden mb-6 text-xs">
            @csrf
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-4 py-3">
                <h2 class="text-white text-base font-semibold flex items-center">
                    <span class="material-icons text-white text-sm mr-2">preview</span>
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
                    <div class="mb-1 flex items-start"><span class="font-semibold inline-block w-36">State</span><span class="mx-1">:</span><span x-text="form.state === 'others' ? form.manual_state : form.state"></span></div>
                    <div class="mb-1 flex items-start"><span class="font-semibold inline-block w-36">City</span><span class="mx-1">:</span><span x-text="form.state === 'others' ? form.manual_city : form.city"></span></div>
                    <div class="mb-1 flex items-start"><span class="font-semibold inline-block w-36">Postcode</span><span class="mx-1">:</span><span x-text="form.state === 'others' ? form.manual_postcode : form.postcode"></span></div>
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
                    <span class="material-icons text-[16px]">check_circle</span>
                    <span>Submit</span>
                </button>
            </div>
        </form>

        <!-- Section 1: Welcome & Poster -->
        <div x-show="step === 1" class="bg-white shadow rounded-lg overflow-hidden mb-6 text-xs">
            <div class="bg-gradient-to-r from-green-500 to-green-600 px-4 py-3">
                <h2 class="text-white text-base font-semibold flex items-center">
                    <span class="material-icons text-white text-sm mr-2">info</span>
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
                    <button type="button" @click="openAuthGate()" class="px-4 py-1 bg-blue-600 text-white rounded text-xs">Next</button>
                </div>
            </div>
        </div>

        <!-- Auth Gate Modal (IC Lookup + Login/Register) -->
        <div x-show="auth.open" style="display:none" class="fixed inset-0 z-50">
            <div class="absolute inset-0 bg-black bg-opacity-40" @click="auth.open=false"></div>
            <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 bg-white modal-card shadow w-full max-w-md text-xs">
                <div class="px-4 py-3 border-b flex items-center justify-between">
                    <h3 class="font-semibold">Account Verification (IC/Passport)</h3>
                    <button class="text-gray-500" @click="auth.open=false"><span class="material-icons text-sm">close</span></button>
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
                                    <span class="material-icons text-[16px]" x-show="!auth.loading">search</span>
                                    <span x-show="auth.loading" class="inline-block w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                    <span>Check</span>
                                </button>
                            </div>
                        </div>
                    </template>

                    <!-- Step: Login (existing) -->
                    <template x-if="auth.step==='login'">
                        <div class="space-y-2">
                            <div class="bg-gray-50 border rounded p-2" x-show="auth.result?.last_participant">
                                <div><span class="font-semibold">Name:</span> <span x-text="auth.result.last_participant?.name"></span></div>
                                <div class="mt-1">
                                    <span class="font-semibold">Email:</span>
                                    <template x-for="em in auth.result.emails" :key="em">
                                        <label class="ml-2 inline-flex items-center gap-1"><input type="radio" x-model="auth.emailChoice" :value="em"> <span x-text="em"></span></label>
                                    </template>
                                </div>
                            </div>
                            <div x-show="auth.result?.emails?.length > 1">
                                <label class="block mb-1">Email</label>
                                <input type="email" x-model="auth.login.email" class="w-full border border-gray-300 rounded-sm px-2 py-1">
                            </div>
                            <div x-show="!auth.result?.emails || auth.result?.emails?.length <= 1">
                                <label class="block mb-1">Email</label>
                                <input type="email" x-model="auth.login.email" class="w-full border border-gray-300 rounded-sm px-2 py-1" :readonly="auth.result?.emails?.length === 1">
                            </div>
                            <div>
                                <label class="block mb-1">Password</label>
                                <input type="password" x-model="auth.login.password" class="w-full border border-gray-300 rounded-sm px-2 py-1">
                            </div>
                            <div class="flex items-center justify-between mt-2">
                                <button type="button" @click="doLogin()" class="btn btn-primary flex items-center gap-1" :disabled="auth.loading">
                                    <span class="material-icons text-[16px]">login</span>
                                    <span>Login</span>
                                </button>
                                <button type="button" @click="doResetPassword()" class="btn btn-danger flex items-center gap-1" :disabled="auth.loading">
                                    <span class="material-icons text-[16px]">lock_reset</span>
                                    <span>Reset Password</span>
                                </button>
                            </div>
                            <div class="text-[10px] text-gray-500">If this is not your account, you can register a new account.</div>
                            <div class="flex justify-end">
                                <button type="button" @click="auth.step='register'" class="px-3 py-1 text-blue-600">Register new account</button>
                            </div>
                        </div>
                    </template>

                    <!-- Step: Register (new) -->
                    <template x-if="auth.step==='register'">
                        <div class="space-y-2">
                            <div>
                                <label class="block mb-1">Nama Penuh</label>
                                <input type="text" x-model="auth.register.name" class="w-full border border-gray-300 rounded-sm px-2 py-1">
                            </div>
                            <div>
                                <label class="block mb-1">Emel</label>
                                <input type="email" x-model="auth.register.email" class="w-full border border-gray-300 rounded-sm px-2 py-1">
                            </div>
                            <div>
                                <label class="block mb-1">Kata Laluan</label>
                                <input type="password" x-model="auth.register.password" class="w-full border border-gray-300 rounded-sm px-2 py-1">
                            </div>
                            <div class="flex justify-end">
                                <button type="button" @click="doRegister()" class="btn btn-success flex items-center gap-1" :disabled="auth.loading">
                                    <span class="material-icons text-[16px]">person_add</span>
                                    <span>Daftar & Teruskan</span>
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
@endsection 