<section class="space-y-6">
    <header>
        <h2 class="text-sm font-semibold text-gray-700">
            {{ __('Delete Account') }}
        </h2>
        <p class="mt-1 text-xs text-gray-500">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>
    <div class="flex justify-end space-x-3 pt-2">
        <x-danger-button
            x-data=""
            x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
            class="px-3 py-1 bg-gradient-to-r from-red-600 to-red-500 hover:from-red-700 hover:to-red-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center"
        >
            <span class="material-icons text-xs mr-1">delete</span>
            {{ __('Delete Account') }}
        </x-danger-button>
    </div>
    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')
            <h2 class="text-sm font-semibold text-gray-700">
                {{ __('Are you sure you want to delete your account?') }}
            </h2>
            <p class="mt-1 text-xs text-gray-500">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
            </p>
            <div class="mt-6">
                <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />
                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4"
                    placeholder="{{ __('Password') }}"
                />
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>
            <div class="mt-6 flex justify-end space-x-3">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>
                <x-danger-button class="px-3 py-1 bg-gradient-to-r from-red-600 to-red-500 hover:from-red-700 hover:to-red-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                    <span class="material-icons text-xs mr-1">delete</span>
                    {{ __('Delete Account') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
