<x-guest-layout>
    <h1 class="text-lg font-semibold text-gray-800 mb-1">Set your password</h1>
    <p class="text-sm text-gray-600 mb-4">Choose a password for your e-Certificate account. This link works once.</p>

    <form method="POST" action="{{ route('account.set-password.store') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full bg-gray-100" type="email" name="email" :value="old('email', $email)" required readonly autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autofocus autocomplete="new-password" aria-describedby="password-policy" />
            <p id="password-policy" class="mt-1 text-xs text-gray-500">{{ $policy }}</p>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Set Password') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
