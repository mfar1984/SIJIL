<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <x-login-text-input
            type="email"
            name="email"
            id="email"
            label="Email"
            icon="mail"
            required
            autocomplete="username"
            :value="old('email')"
        />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />

        <!-- Password -->
        <x-login-text-input
            type="password"
            name="password"
            id="password"
            label="Password"
            icon="lock"
            required
            autocomplete="current-password"
        />
        <x-input-error :messages="$errors->get('password')" class="mt-2" />

        <div class="mt-4">
            <x-primary-button class="w-full text-center">
                ACCESS
            </x-primary-button>
        </div>
    </form>

    <div class="mt-2 text-[10px] text-gray-400 text-center" style="letter-spacing:0.17rem;">
        Disclaimer / Privacy Policy / Terms &amp; Condition
    </div>
</x-guest-layout>
