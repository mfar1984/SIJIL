@php
    // Effective values: this row, falling back to the shared defaults.
    $value = fn(string $key, $fallback = null) => data_get($settings->settings, $key, $fallback ?? (\App\Models\PwaSetting::DEFAULTS[$key] ?? null));
@endphp

<x-app-layout>
    <x-slot name="breadcrumb">
        <span>PWA Management</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Event Settings</span>
    </x-slot>

    <x-slot name="title">PWA Settings</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center">
                        <span class="material-icons-outlined mr-2 text-primary-DEFAULT">settings</span>
                        <h1 class="text-xl font-bold text-gray-800">PWA Settings</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-8">
                        How mobile app accounts are created, what their passwords look like, and what they receive by email
                    </p>
                </div>
                @can('pwa_settings.update')
                <div class="flex gap-2">
                    <form method="POST" action="{{ route('pwa.settings.reset') }}" onsubmit="return confirm('Reset every PWA setting back to its default value?')">
                        @csrf
                        <button type="submit" class="h-9 px-3 border border-gray-300 rounded text-xs text-gray-700 hover:bg-gray-50 flex items-center shrink-0">
                            <span class="material-icons-outlined text-xs mr-1">restart_alt</span>
                            Reset to defaults
                        </button>
                    </form>
                    <button form="pwa-settings-form" type="submit" class="px-3 h-[36px] bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 text-white rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        <span class="material-icons-outlined text-xs mr-1">save</span>
                        Save settings
                    </button>
                </div>
                @endcan
            </div>
        </div>

        @php $canUpdate = auth()->user()->can('pwa_settings.update'); @endphp

        <div class="p-4" x-data="{ tab: 'accounts' }">
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-2 rounded mb-4 text-xs">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-2 rounded mb-4 text-xs">
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="border-b border-gray-200 mb-4">
                <div class="flex flex-wrap -mb-px">
                    <button type="button" @click="tab='accounts'"
                            :class="tab==='accounts' ? 'border-primary-DEFAULT text-primary-DEFAULT' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="inline-flex items-center whitespace-nowrap py-3 px-4 text-xs font-medium leading-5 border-b-2 focus:outline-none transition duration-150 ease-in-out">
                        <span class="material-icons-outlined text-xs mr-2">person_add</span>
                        Accounts
                    </button>
                    <button type="button" @click="tab='passwords'"
                            :class="tab==='passwords' ? 'border-primary-DEFAULT text-primary-DEFAULT' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="inline-flex items-center whitespace-nowrap py-3 px-4 text-xs font-medium leading-5 border-b-2 focus:outline-none transition duration-150 ease-in-out">
                        <span class="material-icons-outlined text-xs mr-2">password</span>
                        Passwords
                    </button>
                    <button type="button" @click="tab='emails'"
                            :class="tab==='emails' ? 'border-primary-DEFAULT text-primary-DEFAULT' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="inline-flex items-center whitespace-nowrap py-3 px-4 text-xs font-medium leading-5 border-b-2 focus:outline-none transition duration-150 ease-in-out">
                        <span class="material-icons-outlined text-xs mr-2">mail</span>
                        Emails
                    </button>
                </div>
            </div>

            <form id="pwa-settings-form" method="POST" action="{{ route('pwa.settings.update') }}">
                @csrf
                <fieldset {{ $canUpdate ? '' : 'disabled' }}>

                    {{-- Accounts --}}
                    <div x-show="tab==='accounts'" class="border border-gray-200 rounded">
                        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                                <span class="material-icons-outlined text-primary-DEFAULT mr-2">person_add</span>
                                Creating mobile app accounts
                            </h2>
                        </div>

                        <div class="p-4 space-y-4">
                            <div class="flex flex-col md:flex-row md:items-start gap-3">
                                <label class="text-xs font-medium text-gray-700 md:w-48 shrink-0 md:pt-1">Automatic accounts</label>
                                <div class="flex-1">
                                    <label class="flex items-start gap-2 cursor-pointer">
                                        <input type="checkbox" name="auto_create_accounts" value="1" @checked($value('auto_create_accounts')) class="mt-0.5 shrink-0">
                                        <span>
                                            <span class="block text-xs text-gray-800">Create an app account when a participant is registered</span>
                                            <span class="block text-xs text-gray-500 mt-1">Turn this off to create accounts by hand only.</span>
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <div class="flex flex-col md:flex-row md:items-start gap-3">
                                <label class="text-xs font-medium text-gray-700 md:w-48 shrink-0 md:pt-1">First sign-in</label>
                                <div class="flex-1">
                                    <label class="flex items-start gap-2 cursor-pointer">
                                        <input type="checkbox" name="force_password_change" value="1" @checked($value('force_password_change')) class="mt-0.5 shrink-0">
                                        <span>
                                            <span class="block text-xs text-gray-800">Ask for a new password on first sign-in</span>
                                            <span class="block text-xs text-gray-500 mt-1">Recommended, since the first password is generated by the system.</span>
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <div class="flex flex-col md:flex-row md:items-start gap-3">
                                <label for="checkbox_label" class="text-xs font-medium text-gray-700 md:w-48 shrink-0 md:pt-2">
                                    Consent wording <span class="text-red-500">*</span>
                                </label>
                                <div class="flex-1">
                                    <input type="text" name="checkbox_label" id="checkbox_label"
                                           value="{{ old('checkbox_label', $value('checkbox_label')) }}"
                                           class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" required>
                                    <p class="text-xs text-gray-500 mt-1">Shown next to the opt-in checkbox on the participant form.</p>
                                </div>
                            </div>

                            <div class="flex flex-col md:flex-row md:items-start gap-3">
                                <label for="checkbox_default_state" class="text-xs font-medium text-gray-700 md:w-48 shrink-0 md:pt-2">
                                    Consent default <span class="text-red-500">*</span>
                                </label>
                                <div class="flex-1">
                                    <select name="checkbox_default_state" id="checkbox_default_state"
                                            class="w-full md:max-w-xs h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" required>
                                        <option value="checked" @selected($value('checkbox_default_state') === 'checked')>Ticked by default</option>
                                        <option value="unchecked" @selected($value('checkbox_default_state') === 'unchecked')>Unticked by default</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Passwords --}}
                    <div x-show="tab==='passwords'" class="border border-gray-200 rounded" x-cloak>
                        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                                <span class="material-icons-outlined text-primary-DEFAULT mr-2">password</span>
                                Generated passwords
                            </h2>
                        </div>

                        <div class="p-4 space-y-4">
                            <p class="text-xs text-gray-500">
                                Applies whenever the system creates a password: new accounts, bulk assignment,
                                CSV import and password resets.
                            </p>

                            <div class="flex flex-col md:flex-row md:items-start gap-3">
                                <label for="password_length" class="text-xs font-medium text-gray-700 md:w-48 shrink-0 md:pt-2">
                                    Length <span class="text-red-500">*</span>
                                </label>
                                <div class="flex-1">
                                    <input type="number" name="password_length" id="password_length" min="6" max="16"
                                           value="{{ old('password_length', $value('password_length')) }}"
                                           class="w-full md:max-w-[8rem] h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" required>
                                    <p class="text-xs text-gray-500 mt-1">Between 6 and 16 characters.</p>
                                </div>
                            </div>

                            <div class="flex flex-col md:flex-row md:items-start gap-3">
                                <label class="text-xs font-medium text-gray-700 md:w-48 shrink-0 md:pt-1">Characters to include</label>
                                <div class="flex-1 space-y-2">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="include_uppercase" value="1" @checked($value('include_uppercase')) class="shrink-0">
                                        <span class="text-xs text-gray-800">Uppercase letters <span class="text-gray-400">A-Z</span></span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="include_lowercase" value="1" @checked($value('include_lowercase')) class="shrink-0">
                                        <span class="text-xs text-gray-800">Lowercase letters <span class="text-gray-400">a-z</span></span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="include_numbers" value="1" @checked($value('include_numbers')) class="shrink-0">
                                        <span class="text-xs text-gray-800">Numbers <span class="text-gray-400">2-9</span></span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="include_special_chars" value="1" @checked($value('include_special_chars')) class="shrink-0">
                                        <span class="text-xs text-gray-800">Symbols <span class="text-gray-400">! @ # $ % &amp; * ?</span></span>
                                    </label>
                                    <p class="text-xs text-gray-500 pt-1">
                                        Every ticked group is guaranteed to appear at least once. Easily confused
                                        characters such as 0, O, 1, l and I are left out.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Emails --}}
                    <div x-show="tab==='emails'" class="border border-gray-200 rounded" x-cloak>
                        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                                <span class="material-icons-outlined text-primary-DEFAULT mr-2">mail</span>
                                Emails to participants
                            </h2>
                        </div>

                        <div class="p-4 space-y-4">
                            <div class="flex flex-col md:flex-row md:items-start gap-3">
                                <label class="text-xs font-medium text-gray-700 md:w-48 shrink-0 md:pt-1">Welcome email</label>
                                <div class="flex-1">
                                    <label class="flex items-start gap-2 cursor-pointer">
                                        <input type="checkbox" name="send_welcome_email" value="1" @checked($value('send_welcome_email')) class="mt-0.5 shrink-0">
                                        <span>
                                            <span class="block text-xs text-gray-800">Email new accounts their sign-in details</span>
                                            <span class="block text-xs text-gray-500 mt-1">
                                                Without this the person is never told their generated password and cannot sign in.
                                            </span>
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <div class="flex flex-col md:flex-row md:items-start gap-3">
                                <label class="text-xs font-medium text-gray-700 md:w-48 shrink-0 md:pt-1">App link</label>
                                <div class="flex-1">
                                    <label class="flex items-start gap-2 cursor-pointer">
                                        <input type="checkbox" name="include_app_link" value="1" @checked($value('include_app_link')) class="mt-0.5 shrink-0">
                                        <span class="block text-xs text-gray-800">Include a link to the app in emails</span>
                                    </label>
                                </div>
                            </div>

                            <div class="flex flex-col md:flex-row md:items-start gap-3">
                                <label for="pwa_app_link" class="text-xs font-medium text-gray-700 md:w-48 shrink-0 md:pt-2">
                                    App address <span class="text-red-500">*</span>
                                </label>
                                <div class="flex-1">
                                    <input type="url" name="pwa_app_link" id="pwa_app_link"
                                           value="{{ old('pwa_app_link', $value('pwa_app_link')) }}"
                                           class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" required>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Fills the <code class="bg-gray-100 px-1 rounded">@{{pwa_link}}</code> and
                                        <code class="bg-gray-100 px-1 rounded">@{{login_url}}</code> placeholders.
                                    </p>
                                </div>
                            </div>

                            <div class="flex flex-col md:flex-row md:items-start gap-3">
                                <label for="support_email" class="text-xs font-medium text-gray-700 md:w-48 shrink-0 md:pt-2">
                                    Support address <span class="text-red-500">*</span>
                                </label>
                                <div class="flex-1">
                                    <input type="email" name="support_email" id="support_email"
                                           value="{{ old('support_email', $value('support_email')) }}"
                                           class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" required>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Fills the <code class="bg-gray-100 px-1 rounded">@{{support_email}}</code> placeholder.
                                    </p>
                                </div>
                            </div>

                            <div class="flex flex-col md:flex-row md:items-start gap-3">
                                <span class="text-xs font-medium text-gray-700 md:w-48 shrink-0">Wording</span>
                                <p class="flex-1 text-xs text-gray-600 bg-gray-50 border border-gray-200 rounded p-3">
                                    The subject and body of these emails live in
                                    <a href="{{ route('pwa.templates') }}" class="text-primary-DEFAULT underline">Email Templates</a>.
                                    Which mail server they go through is set in
                                    <a href="{{ route('config.deliver') }}" class="text-primary-DEFAULT underline">Config Delivery</a>.
                                </p>
                            </div>
                        </div>
                    </div>
                </fieldset>
            </form>
        </div>
    </div>
</x-app-layout>
