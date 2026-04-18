<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Settings</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Global Config</span>
    </x-slot>

    <x-slot name="title">Global Config</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300" x-data="{ isEditing: true }">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center">
                        <span class="material-icons-outlined mr-2 text-primary-DEFAULT">settings</span>
                        <h1 class="text-xl font-bold text-gray-800">Global Configuration</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-8">Manage system-wide configuration settings</p>
                </div>
            </div>
        </div>
        
        <div class="p-4" x-data="{ activeTab: 'general' }">
            <!-- Configuration Tabs -->
            <div class="border-b border-gray-200 mb-4">
                <div class="flex flex-wrap -mb-px">
                    <button 
                        @click="activeTab = 'general'" 
                        :class="{'border-primary-DEFAULT text-primary-DEFAULT': activeTab === 'general', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'general'}"
                        class="inline-flex items-center py-3 px-4 text-xs font-medium leading-5 border-b-2 focus:outline-none transition duration-150 ease-in-out"
                    >
                        <span class="material-icons-outlined text-xs mr-2">tune</span>
                        General
                    </button>
                    <button 
                        @click="activeTab = 'security'" 
                        :class="{'border-primary-DEFAULT text-primary-DEFAULT': activeTab === 'security', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'security'}"
                        class="inline-flex items-center py-3 px-4 text-xs font-medium leading-5 border-b-2 focus:outline-none transition duration-150 ease-in-out"
                    >
                        <span class="material-icons-outlined text-xs mr-2">security</span>
                        Security
                    </button>
                    <button 
                        @click="activeTab = 'appearance'" 
                        :class="{'border-primary-DEFAULT text-primary-DEFAULT': activeTab === 'appearance', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'appearance'}"
                        class="inline-flex items-center py-3 px-4 text-xs font-medium leading-5 border-b-2 focus:outline-none transition duration-150 ease-in-out"
                    >
                        <span class="material-icons-outlined text-xs mr-2">palette</span>
                        Appearance
                    </button>
                    <button 
                        @click="activeTab = 'notifications'" 
                        :class="{'border-primary-DEFAULT text-primary-DEFAULT': activeTab === 'notifications', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'notifications'}"
                        class="inline-flex items-center py-3 px-4 text-xs font-medium leading-5 border-b-2 focus:outline-none transition duration-150 ease-in-out"
                    >
                        <span class="material-icons-outlined text-xs mr-2">notifications</span>
                        Notifications
                    </button>
                    <button 
                        @click="activeTab = 'telegram'" 
                        :class="{'border-primary-DEFAULT text-primary-DEFAULT': activeTab === 'telegram', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'telegram'}"
                        class="inline-flex items-center py-3 px-4 text-xs font-medium leading-5 border-b-2 focus:outline-none transition duration-150 ease-in-out"
                    >
                        <span class="material-icons-outlined text-xs mr-2">telegram</span>
                        Telegram
                    </button>
                    <button 
                        @click="activeTab = 'api'" 
                        :class="{'border-primary-DEFAULT text-primary-DEFAULT': activeTab === 'api', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'api'}"
                        class="inline-flex items-center py-3 px-4 text-xs font-medium leading-5 border-b-2 focus:outline-none transition duration-150 ease-in-out"
                    >
                        <span class="material-icons-outlined text-xs mr-2">code</span>
                        API & Integrations
                    </button>
                </div>
            </div>
            
            @php $canUpdate = auth()->user()->can('global_config.update'); @endphp
            <form method="POST" action="{{ route('settings.global-config.update') }}" enctype="multipart/form-data" id="globalConfigForm">
                @csrf
                <fieldset {{ $canUpdate ? '' : 'disabled' }}>
                    <!-- Include Tab Content -->
                    @include('settings.global-config.general')
                    @include('settings.global-config.security')
                    @include('settings.global-config.appearance')
                    @include('settings.global-config.notifications')
                    @include('settings.global-config.telegram')
                    @include('settings.global-config.api')
                    
                    <!-- Form Submit Buttons -->
                    <div class="mt-6 flex justify-end space-x-3">
                        @can('global_config.update')
                        <button 
                            type="submit"
                            class="bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600"
                        >
                            <span class="text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                                <span class="material-icons-outlined text-xs mr-1">save</span>
                                <span>Save Changes</span>
                            </span>
                        </button>
                        @endcan
                    </div>
                </fieldset>
            </form>
        </div>
    </div>
    
    <script>
        document.addEventListener('alpine:init', () => {
            // Alpine.js configuration if needed
        })

        // Handle form submission
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('globalConfigForm');
            
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(form);
                    const submitButton = document.querySelector('button[type="submit"]');
                    
                    if (!submitButton) {
                        console.error('Submit button not found!');
                        return;
                    }
                    
                    const originalText = submitButton.innerHTML;
                    
                    // Show loading state
                    submitButton.innerHTML = '<span class="text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out"><span class="material-icons-outlined text-xs mr-1">hourglass_empty</span> Saving...</span>';
                    submitButton.disabled = true;
                    
                    fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Configuration saved successfully!');
                            window.location.reload();
                        } else if (data.errors) {
                            let errorMsg = 'Validation failed:\n';
                            for (const [field, messages] of Object.entries(data.errors)) {
                                errorMsg += `\n${field}: ${messages.join(', ')}`;
                            }
                            alert(errorMsg);
                            submitButton.innerHTML = originalText;
                            submitButton.disabled = false;
                        } else {
                            alert('Error: ' + (data.message || 'Unknown error occurred'));
                            submitButton.innerHTML = originalText;
                            submitButton.disabled = false;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while saving. Please try again.');
                        submitButton.innerHTML = originalText;
                        submitButton.disabled = false;
                    });
                });
            }
        });
    </script>
</x-app-layout>
