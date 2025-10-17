<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Config</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Deliver</span>
    </x-slot>

    <x-slot name="title">Config Deliver</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300" x-data="{ isEditing: false }">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center">
                        <span class="material-icons mr-2 text-primary-DEFAULT">settings_applications</span>
                        <h1 class="text-xl font-bold text-gray-800">Config Deliver</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-8">Configure email and SMS delivery settings</p>
                </div>
                <div>
                    @can('delivery.update')
                    <button 
                        type="button" 
                        @click="if(isEditing){ if(document.getElementById('emailTab').style.display !== 'none'){ window.enableAndSubmit('emailForm'); } else { window.enableAndSubmit('smsForm'); } } else { isEditing = true }" 
                        class="bg-gradient-to-r" 
                        :class="isEditing ? 'from-green-600 to-green-500 hover:from-green-700 hover:to-green-600' : 'from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600'"
                        x-transition
                    >
                        <span class="text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                            <span class="material-icons text-xs mr-1" x-text="isEditing ? 'save' : 'edit'"></span>
                            <span x-text="isEditing ? 'Save Changes' : 'Edit Settings'"></span>
                        </span>
                    </button>
                    @endcan
                </div>
            </div>
        </div>
        
        <div class="p-4">
            <!-- Tab Navigation -->
            <div class="border-b border-gray-200 mb-4">
                <div class="flex flex-wrap -mb-px">
                    <button 
                        type="button"
                        onclick="switchTab('email')"
                        id="emailTabButton"
                        class="inline-flex items-center py-3 px-4 text-xs font-medium leading-5 border-b-2 border-primary-DEFAULT text-primary-DEFAULT focus:outline-none transition duration-150 ease-in-out"
                    >
                        <span class="material-icons text-xs mr-2">email</span>
                        Email Configuration
                    </button>
                    <button 
                        type="button"
                        onclick="switchTab('sms')"
                        id="smsTabButton"
                        class="inline-flex items-center py-3 px-4 text-xs font-medium leading-5 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none transition duration-150 ease-in-out"
                    >
                        <span class="material-icons text-xs mr-2">sms</span>
                        SMS Configuration
                    </button>
                </div>
            </div>
                <!-- Email Configuration Tab -->
            <div id="emailTab" class="space-y-4">
                    <div class="bg-blue-50 border border-blue-100 rounded-md p-3 mb-4">
                        <div class="flex">
                            <span class="material-icons text-blue-600 mr-2">info</span>
                            <div class="text-xs text-blue-700">
                                <p class="font-medium">Email Configuration</p>
                                <p class="mt-1">Configure your email sending settings. Make sure to test your configuration after saving.</p>
                            </div>
                        </div>
                    </div>
                
                <form id="emailForm" method="POST" action="{{ route('config.deliver.email') }}">
                    @csrf
                    
                    <div class="border-b border-gray-200 pb-5">
                        <h2 class="text-sm font-semibold text-gray-700 mb-4">Mail Driver</h2>
                            <div>
                                <label for="mail_driver" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">settings</span>
                                    Mail Driver
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons text-[#004aad] text-base">dns</span>
                                    </div>
                                    <select 
                                        id="mail_driver" 
                                        name="mail_driver" 
                                        class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                        :class="{'bg-gray-50': !isEditing}"
                                        :disabled="!isEditing"
                                        onchange="toggleMailDriverSettings()"
                                    >
                                        <option value="smtp" {{ isset($emailConfig) && $emailConfig->provider == 'smtp' ? 'selected' : '' }}>SMTP</option>
                                        <option value="mailgun" {{ isset($emailConfig) && $emailConfig->provider == 'mailgun' ? 'selected' : '' }}>Mailgun</option>
                                        <option value="ses" {{ isset($emailConfig) && $emailConfig->provider == 'ses' ? 'selected' : '' }}>Amazon SES</option>
                                        <option value="sendmail" {{ isset($emailConfig) && $emailConfig->provider == 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                                    </select>
                                </div>
                                <p class="mt-1 text-[10px] text-gray-500">Select the mail service provider to use</p>
                            </div>
                                    </div>
                    <!-- SMTP Settings -->
                    <div id="smtp_settings" class="border-b border-gray-200 pb-5">
                        <h2 class="text-sm font-semibold text-gray-700 mb-4">SMTP Settings</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <!-- Mail Host -->
                            <div>
                                <label for="mail_host" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">dns</span>
                                    Mail Host
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons text-[#004aad] text-base">language</span>
                                    </div>
                                    <input 
                                        type="text" 
                                        id="mail_host" 
                                        name="mail_host" 
                                        value="{{ isset($emailConfig) && isset($emailConfig->settings['host']) ? $emailConfig->settings['host'] : 'smtp.mailtrap.io' }}" 
                                        class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                        :disabled="!isEditing"
                                    >
                                </div>
                                <p class="mt-1 text-[10px] text-gray-500">SMTP server hostname (e.g., smtp.gmail.com)</p>
                            </div>
                            
                            <!-- Mail Port -->
                            <div>
                                <label for="mail_port" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">settings_ethernet</span>
                                    Mail Port
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons text-[#004aad] text-base">pin</span>
                                    </div>
                                    <input 
                                        type="text" 
                                        id="mail_port" 
                                        name="mail_port" 
                                        value="{{ isset($emailConfig) && isset($emailConfig->settings['port']) ? $emailConfig->settings['port'] : '2525' }}" 
                                        class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                        :disabled="!isEditing"
                                    >
                                </div>
                                <p class="mt-1 text-[10px] text-gray-500">SMTP server port (e.g., 587 for TLS, 465 for SSL)</p>
                            </div>
                            
                            <!-- Encryption -->
                            <div>
                                <label for="mail_encryption" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">security</span>
                                    Encryption
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons text-[#004aad] text-base">enhanced_encryption</span>
                                    </div>
                                    <select 
                                        id="mail_encryption" 
                                        name="mail_encryption" 
                                        class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                        :disabled="!isEditing"
                                    >
                                        <option value="tls" {{ isset($emailConfig) && isset($emailConfig->settings['encryption']) && $emailConfig->settings['encryption'] == 'tls' ? 'selected' : '' }}>TLS</option>
                                        <option value="ssl" {{ isset($emailConfig) && isset($emailConfig->settings['encryption']) && $emailConfig->settings['encryption'] == 'ssl' ? 'selected' : '' }}>SSL</option>
                                        <option value="none" {{ isset($emailConfig) && isset($emailConfig->settings['encryption']) && $emailConfig->settings['encryption'] == 'none' ? 'selected' : '' }}>None</option>
                                    </select>
                                </div>
                                <p class="mt-1 text-[10px] text-gray-500">Connection encryption protocol</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Mail Username -->
                            <div>
                                <label for="mail_username" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">person</span>
                                    Mail Username
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons text-[#004aad] text-base">account_circle</span>
                                    </div>
                                    <input 
                                        type="text" 
                                        id="mail_username" 
                                        name="mail_username" 
                                        value="{{ isset($emailConfig) && isset($emailConfig->settings['username']) ? $emailConfig->settings['username'] : '' }}"
                                        class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                        :disabled="!isEditing"
                                    >
                                </div>
                                <p class="mt-1 text-[10px] text-gray-500">SMTP authentication username</p>
                            </div>
                            
                            <!-- Mail Password -->
                            <div>
                                <label for="mail_password" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">vpn_key</span>
                                    Mail Password
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons text-[#004aad] text-base">lock</span>
                                    </div>
                                    <input 
                                        type="password" 
                                        id="mail_password" 
                                        name="mail_password" 
                                        value="{{ isset($emailConfig) && isset($emailConfig->settings['password']) ? $emailConfig->settings['password'] : '' }}"
                                        class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                        :disabled="!isEditing"
                                    >
                                </div>
                                <p class="mt-1 text-[10px] text-gray-500">SMTP authentication password</p>
                            </div>
                        </div>
                    </div>
                    <!-- Mailgun Settings -->
                    <div id="mailgun_settings" class="border-b border-gray-200 pb-5" style="display: none;">
                        <h2 class="text-sm font-semibold text-gray-700 mb-4">Mailgun Settings</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <!-- Mailgun Domain -->
                            <div>
                                <label for="mailgun_domain" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">dns</span>
                                    Mailgun Domain
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons text-[#004aad] text-base">language</span>
                                    </div>
                                    <input 
                                        type="text" 
                                        id="mailgun_domain" 
                                        name="mailgun_domain" 
                                        value="{{ isset($emailConfig) && isset($emailConfig->settings['domain']) ? $emailConfig->settings['domain'] : '' }}" 
                                        class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                        :disabled="!isEditing"
                                    >
                                </div>
                                <p class="mt-1 text-[10px] text-gray-500">Your Mailgun domain</p>
                            </div>
                            
                            <!-- Mailgun Secret -->
                            <div>
                                <label for="mailgun_secret" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">vpn_key</span>
                                    Mailgun Secret
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons text-[#004aad] text-base">lock</span>
                                    </div>
                                    <input 
                                        type="password" 
                                        id="mailgun_secret" 
                                        name="mailgun_secret" 
                                        value="{{ isset($emailConfig) && isset($emailConfig->settings['secret']) ? $emailConfig->settings['secret'] : '' }}" 
                                        class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                        :disabled="!isEditing"
                                    >
                                </div>
                                <p class="mt-1 text-[10px] text-gray-500">Your Mailgun API key</p>
                            </div>
                            
                            <!-- Mailgun Endpoint -->
                            <div>
                                <label for="mailgun_endpoint" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">link</span>
                                    Mailgun Endpoint
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons text-[#004aad] text-base">public</span>
                                    </div>
                                    <input 
                                        type="text" 
                                        id="mailgun_endpoint" 
                                        name="mailgun_endpoint" 
                                        value="{{ isset($emailConfig) && isset($emailConfig->settings['endpoint']) ? $emailConfig->settings['endpoint'] : 'api.mailgun.net' }}" 
                                        class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                        :disabled="!isEditing"
                                    >
                                </div>
                                <p class="mt-1 text-[10px] text-gray-500">Mailgun API endpoint (api.mailgun.net or api.eu.mailgun.net)</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Amazon SES Settings -->
                    <div id="ses_settings" class="border-b border-gray-200 pb-5" style="display: none;">
                        <h2 class="text-sm font-semibold text-gray-700 mb-4">Amazon SES Settings</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <!-- SES Key -->
                            <div>
                                <label for="ses_key" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">vpn_key</span>
                                    AWS Access Key ID
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons text-[#004aad] text-base">account_circle</span>
                                    </div>
                                    <input 
                                        type="text" 
                                        id="ses_key" 
                                        name="ses_key" 
                                        value="{{ isset($emailConfig) && isset($emailConfig->settings['key']) ? $emailConfig->settings['key'] : '' }}" 
                                        class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                        :disabled="!isEditing"
                                    >
                                </div>
                                <p class="mt-1 text-[10px] text-gray-500">Your AWS access key ID</p>
                            </div>
                            
                            <!-- SES Secret -->
                            <div>
                                <label for="ses_secret" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">vpn_key</span>
                                    AWS Secret Access Key
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons text-[#004aad] text-base">lock</span>
                                    </div>
                                    <input 
                                        type="password" 
                                        id="ses_secret" 
                                        name="ses_secret" 
                                        value="{{ isset($emailConfig) && isset($emailConfig->settings['secret']) ? $emailConfig->settings['secret'] : '' }}" 
                                        class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                        :disabled="!isEditing"
                                    >
                                </div>
                                <p class="mt-1 text-[10px] text-gray-500">Your AWS secret access key</p>
                            </div>
                            
                            <!-- SES Region -->
                            <div>
                                <label for="ses_region" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">public</span>
                                    AWS Region
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons text-[#004aad] text-base">language</span>
                                    </div>
                                    <select 
                                        id="ses_region" 
                                        name="ses_region" 
                                        class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                        :disabled="!isEditing"
                                    >
                                        <option value="us-east-1" {{ isset($emailConfig) && isset($emailConfig->settings['region']) && $emailConfig->settings['region'] == 'us-east-1' ? 'selected' : '' }}>US East (N. Virginia)</option>
                                        <option value="us-east-2" {{ isset($emailConfig) && isset($emailConfig->settings['region']) && $emailConfig->settings['region'] == 'us-east-2' ? 'selected' : '' }}>US East (Ohio)</option>
                                        <option value="us-west-1" {{ isset($emailConfig) && isset($emailConfig->settings['region']) && $emailConfig->settings['region'] == 'us-west-1' ? 'selected' : '' }}>US West (N. California)</option>
                                        <option value="us-west-2" {{ isset($emailConfig) && isset($emailConfig->settings['region']) && $emailConfig->settings['region'] == 'us-west-2' ? 'selected' : '' }}>US West (Oregon)</option>
                                        <option value="ap-south-1" {{ isset($emailConfig) && isset($emailConfig->settings['region']) && $emailConfig->settings['region'] == 'ap-south-1' ? 'selected' : '' }}>Asia Pacific (Mumbai)</option>
                                        <option value="ap-northeast-2" {{ isset($emailConfig) && isset($emailConfig->settings['region']) && $emailConfig->settings['region'] == 'ap-northeast-2' ? 'selected' : '' }}>Asia Pacific (Seoul)</option>
                                        <option value="ap-southeast-1" {{ isset($emailConfig) && isset($emailConfig->settings['region']) && $emailConfig->settings['region'] == 'ap-southeast-1' ? 'selected' : '' }}>Asia Pacific (Singapore)</option>
                                        <option value="ap-southeast-2" {{ isset($emailConfig) && isset($emailConfig->settings['region']) && $emailConfig->settings['region'] == 'ap-southeast-2' ? 'selected' : '' }}>Asia Pacific (Sydney)</option>
                                        <option value="ap-northeast-1" {{ isset($emailConfig) && isset($emailConfig->settings['region']) && $emailConfig->settings['region'] == 'ap-northeast-1' ? 'selected' : '' }}>Asia Pacific (Tokyo)</option>
                                        <option value="eu-central-1" {{ isset($emailConfig) && isset($emailConfig->settings['region']) && $emailConfig->settings['region'] == 'eu-central-1' ? 'selected' : '' }}>Europe (Frankfurt)</option>
                                        <option value="eu-west-1" {{ isset($emailConfig) && isset($emailConfig->settings['region']) && $emailConfig->settings['region'] == 'eu-west-1' ? 'selected' : '' }}>Europe (Ireland)</option>
                                        <option value="eu-west-2" {{ isset($emailConfig) && isset($emailConfig->settings['region']) && $emailConfig->settings['region'] == 'eu-west-2' ? 'selected' : '' }}>Europe (London)</option>
                                    </select>
                                </div>
                                <p class="mt-1 text-[10px] text-gray-500">AWS region where SES is configured</p>
                            </div>
                        </div>
                    </div>
                    <!-- Email Sender Settings -->
                    <div class="border-b border-gray-200 pb-5">
                        <h2 class="text-sm font-semibold text-gray-700 mb-4">Email Sender Settings</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- From Address -->
                            <div>
                                <label for="mail_from_address" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">alternate_email</span>
                                    From Address
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons text-[#004aad] text-base">email</span>
                                    </div>
                                    <input 
                                        type="email" 
                                        id="mail_from_address" 
                                        name="mail_from_address" 
                                        value="{{ isset($emailConfig) && isset($emailConfig->settings['from_address']) ? $emailConfig->settings['from_address'] : 'no-reply@example.com' }}" 
                                        class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                        :disabled="!isEditing"
                                    >
                                </div>
                                <p class="mt-1 text-[10px] text-gray-500">Email address that will appear in the From field</p>
                            </div>
                            
                            <!-- From Name -->
                            <div>
                                <label for="mail_from_name" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">badge</span>
                                    From Name
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons text-[#004aad] text-base">person</span>
                                    </div>
                                    <input 
                                        type="text" 
                                        id="mail_from_name" 
                                        name="mail_from_name" 
                                        value="{{ isset($emailConfig) && isset($emailConfig->settings['from_name']) ? $emailConfig->settings['from_name'] : 'SIJIL System' }}" 
                                        class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                        :disabled="!isEditing"
                                    >
                                </div>
                                <p class="mt-1 text-[10px] text-gray-500">Name that will appear in the From field</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <h2 class="text-sm font-semibold text-gray-700 mb-2 flex items-center">
                            <span class="material-icons text-primary-DEFAULT mr-2">send</span>
                            Test Email Configuration
                        </h2>
                        <div class="flex items-center p-4 bg-gray-50 border border-gray-200 rounded-md">
                        <button 
                            type="button" 
                                id="testEmailBtn"
                                onclick="showTestEmailModal()"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-xs flex items-center shadow-sm border border-blue-700"
                                :class="{'bg-gray-400 hover:bg-gray-500 cursor-not-allowed border-gray-500': !isEditing, 'bg-blue-600 hover:bg-blue-700 border-blue-700': isEditing}"
                            :disabled="!isEditing"
                        >
                            <span class="material-icons text-xs mr-1">send</span>
                            Send Test Email
                        </button>
                        <span class="text-xs text-gray-500 ml-3">Test your email configuration by sending a test email.</span>
                    </div>
                </div>

                <!-- Test Email Modal -->
                <div id="testEmailModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
                    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md mx-4">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                                <span class="material-icons text-primary-DEFAULT mr-2">email</span>
                                Send Test Email
                            </h3>
                            <button type="button" onclick="hideTestEmailModal()" class="text-gray-400 hover:text-gray-600">
                                <span class="material-icons">close</span>
                            </button>
                        </div>
                        
                        <div class="mt-4">
                            <label for="test_email" class="block text-sm font-medium text-gray-700 mb-1">
                                Recipient Email Address
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">email</span>
                                </div>
                                <input type="email" id="test_email" name="test_email" class="w-full text-sm border-gray-300 rounded-md pl-10 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" placeholder="Enter email address">
                            </div>
                            <p class="mt-1 text-xs text-gray-500">The test email will be sent to this address</p>
                        </div>
                        
                        <div class="mt-6 flex justify-end space-x-3">
                            <button type="button" onclick="hideTestEmailModal()" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-light">
                                Cancel
                            </button>
                            <button type="button" onclick="sendTestEmailToAddress()" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Send Test Email
                            </button>
                        </div>
                    </div>
                </div>
                
                    
                </form>
                </div>
                <!-- SMS Configuration Tab -->
            <div id="smsTab" class="space-y-4" style="display: none;">
                    <div class="bg-blue-50 border border-blue-100 rounded-md p-3 mb-4">
                        <div class="flex">
                            <span class="material-icons text-blue-600 mr-2">info</span>
                            <div class="text-xs text-blue-700">
                                <p class="font-medium">SMS Configuration</p>
                            <p class="mt-1">Configure your SMS gateway settings. Supported providers: Twilio, Nexmo, AWS SNS, and Infobip.</p>
                        </div>
                    </div>
                </div>
                
                <form id="smsForm" method="POST" action="{{ route('config.deliver.sms') }}">
                    @csrf
                    
                    <div class="border-b border-gray-200 pb-5">
                        <h2 class="text-sm font-semibold text-gray-700 mb-4">SMS Provider Settings</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- SMS Provider -->
                            <div>
                                <label for="sms_provider" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">sms</span>
                                    SMS Provider
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons text-[#004aad] text-base">business</span>
                                    </div>
                                    <select 
                                        id="sms_provider" 
                                        name="sms_provider" 
                                        class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                        :disabled="!isEditing"
                                        onchange="toggleSmsProviderSettings()"
                                    >
                                        <option value="twilio" {{ isset($smsConfig) && $smsConfig->provider == 'twilio' ? 'selected' : '' }}>Twilio</option>
                                        <option value="nexmo" {{ isset($smsConfig) && $smsConfig->provider == 'nexmo' ? 'selected' : '' }}>Nexmo (Vonage)</option>
                                        <option value="aws_sns" {{ isset($smsConfig) && $smsConfig->provider == 'aws_sns' ? 'selected' : '' }}>AWS SNS</option>
                                        <option value="infobip" {{ isset($smsConfig) && $smsConfig->provider == 'infobip' ? 'selected' : '' }}>Infobip</option>
                                    </select>
                                </div>
                                <p class="mt-1 text-[10px] text-gray-500">Select your SMS gateway provider</p>
                            </div>
                        </div>
                    </div>
                    <!-- Twilio Settings -->
                    <div id="twilio_settings" class="border-b border-gray-200 pb-5">
                        <h2 class="text-sm font-semibold text-gray-700 mb-4">Twilio Settings</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Twilio Account SID -->
                            <div>
                                <label for="twilio_sid" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">fingerprint</span>
                                    Twilio Account SID
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons text-[#004aad] text-base">vpn_key</span>
                                    </div>
                                    <input 
                                        type="text" 
                                        id="twilio_sid" 
                                        name="twilio_sid" 
                                        value="{{ isset($smsConfig) && $smsConfig->provider == 'twilio' && isset($smsConfig->settings['sid']) ? $smsConfig->settings['sid'] : '' }}"
                                        class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                        :disabled="!isEditing"
                                    >
                                </div>
                                <p class="mt-1 text-[10px] text-gray-500">Your Twilio account SID from the Twilio dashboard</p>
                            </div>
                            
                            <!-- Twilio Auth Token -->
                            <div>
                                <label for="twilio_token" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">vpn_key</span>
                                    Twilio Auth Token
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons text-[#004aad] text-base">lock</span>
                                    </div>
                                    <input 
                                        type="password" 
                                        id="twilio_token" 
                                        name="twilio_token" 
                                        value="{{ isset($smsConfig) && $smsConfig->provider == 'twilio' && isset($smsConfig->settings['token']) ? $smsConfig->settings['token'] : '' }}"
                                        class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                        :disabled="!isEditing"
                                    >
                                </div>
                                <p class="mt-1 text-[10px] text-gray-500">Your Twilio authentication token</p>
                            </div>
                            
                            <!-- From Number -->
                            <div>
                                <label for="twilio_from" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">phone</span>
                                    From Number
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons text-[#004aad] text-base">call</span>
                                    </div>
                                    <input 
                                        type="text" 
                                        id="twilio_from" 
                                        name="twilio_from" 
                                        placeholder="+12345678901" 
                                        value="{{ isset($smsConfig) && $smsConfig->provider == 'twilio' && isset($smsConfig->settings['from']) ? $smsConfig->settings['from'] : '' }}"
                                        class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                        :disabled="!isEditing"
                                    >
                                </div>
                                <p class="mt-1 text-[10px] text-gray-500">Twilio phone number to send SMS from (with country code)</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Nexmo Settings -->
                    <div id="nexmo_settings" class="border-b border-gray-200 pb-5" style="display: none;">
                        <h2 class="text-sm font-semibold text-gray-700 mb-4">Nexmo (Vonage) Settings</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Nexmo Key -->
                            <div>
                                <label for="nexmo_key" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">vpn_key</span>
                                    Nexmo API Key
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons text-[#004aad] text-base">key</span>
                                    </div>
                                    <input 
                                        type="text" 
                                        id="nexmo_key" 
                                        name="nexmo_key" 
                                        value="{{ isset($smsConfig) && $smsConfig->provider == 'nexmo' && isset($smsConfig->settings['key']) ? $smsConfig->settings['key'] : '' }}"
                                        class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                        :disabled="!isEditing"
                                    >
                                </div>
                                <p class="mt-1 text-[10px] text-gray-500">Your Nexmo API key</p>
                            </div>
                            
                            <!-- Nexmo Secret -->
                            <div>
                                <label for="nexmo_secret" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">vpn_key</span>
                                    Nexmo API Secret
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons text-[#004aad] text-base">lock</span>
                                    </div>
                                    <input 
                                        type="password" 
                                        id="nexmo_secret" 
                                        name="nexmo_secret" 
                                        value="{{ isset($smsConfig) && $smsConfig->provider == 'nexmo' && isset($smsConfig->settings['secret']) ? $smsConfig->settings['secret'] : '' }}"
                                        class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                        :disabled="!isEditing"
                                    >
                                </div>
                                <p class="mt-1 text-[10px] text-gray-500">Your Nexmo API secret</p>
                            </div>
                            
                            <!-- From Number/Name -->
                            <div>
                                <label for="nexmo_from" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">phone</span>
                                    From Number/Name
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons text-[#004aad] text-base">call</span>
                                    </div>
                                    <input 
                                        type="text" 
                                        id="nexmo_from" 
                                        name="nexmo_from" 
                                        placeholder="SIJIL"
                                        value="{{ isset($smsConfig) && $smsConfig->provider == 'nexmo' && isset($smsConfig->settings['from']) ? $smsConfig->settings['from'] : '' }}"
                                        class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                        :disabled="!isEditing"
                                    >
                                </div>
                                <p class="mt-1 text-[10px] text-gray-500">Sender ID (alphanumeric name or phone number)</p>
                            </div>
                        </div>
                    </div>
                    <!-- AWS SNS Settings -->
                    <div id="aws_sns_settings" class="border-b border-gray-200 pb-5" style="display: none;">
                        <h2 class="text-sm font-semibold text-gray-700 mb-4">AWS SNS Settings</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- AWS Key -->
                            <div>
                                <label for="aws_key" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">vpn_key</span>
                                    AWS Access Key ID
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons text-[#004aad] text-base">key</span>
                                    </div>
                                    <input 
                                        type="text" 
                                        id="aws_key" 
                                        name="aws_key" 
                                        value="{{ isset($smsConfig) && $smsConfig->provider == 'aws_sns' && isset($smsConfig->settings['key']) ? $smsConfig->settings['key'] : '' }}"
                                        class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                        :disabled="!isEditing"
                                    >
                                </div>
                                <p class="mt-1 text-[10px] text-gray-500">Your AWS access key ID</p>
                            </div>
                            
                            <!-- AWS Secret -->
                            <div>
                                <label for="aws_secret" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">vpn_key</span>
                                    AWS Secret Access Key
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons text-[#004aad] text-base">lock</span>
                                    </div>
                                    <input 
                                        type="password" 
                                        id="aws_secret" 
                                        name="aws_secret" 
                                        value="{{ isset($smsConfig) && $smsConfig->provider == 'aws_sns' && isset($smsConfig->settings['secret']) ? $smsConfig->settings['secret'] : '' }}"
                                        class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                        :disabled="!isEditing"
                                    >
                                </div>
                                <p class="mt-1 text-[10px] text-gray-500">Your AWS secret access key</p>
                            </div>
                            
                            <!-- AWS Region -->
                            <div>
                                <label for="aws_region" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">public</span>
                                    AWS Region
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons text-[#004aad] text-base">language</span>
                                    </div>
                                    <select 
                                        id="aws_region" 
                                        name="aws_region" 
                                        class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                        :disabled="!isEditing"
                                    >
                                        <option value="us-east-1" {{ isset($smsConfig) && $smsConfig->provider == 'aws_sns' && isset($smsConfig->settings['region']) && $smsConfig->settings['region'] == 'us-east-1' ? 'selected' : '' }}>US East (N. Virginia)</option>
                                        <option value="us-east-2" {{ isset($smsConfig) && $smsConfig->provider == 'aws_sns' && isset($smsConfig->settings['region']) && $smsConfig->settings['region'] == 'us-east-2' ? 'selected' : '' }}>US East (Ohio)</option>
                                        <option value="us-west-1" {{ isset($smsConfig) && $smsConfig->provider == 'aws_sns' && isset($smsConfig->settings['region']) && $smsConfig->settings['region'] == 'us-west-1' ? 'selected' : '' }}>US West (N. California)</option>
                                        <option value="us-west-2" {{ isset($smsConfig) && $smsConfig->provider == 'aws_sns' && isset($smsConfig->settings['region']) && $smsConfig->settings['region'] == 'us-west-2' ? 'selected' : '' }}>US West (Oregon)</option>
                                        <option value="ap-south-1" {{ isset($smsConfig) && $smsConfig->provider == 'aws_sns' && isset($smsConfig->settings['region']) && $smsConfig->settings['region'] == 'ap-south-1' ? 'selected' : '' }}>Asia Pacific (Mumbai)</option>
                                        <option value="ap-northeast-2" {{ isset($smsConfig) && $smsConfig->provider == 'aws_sns' && isset($smsConfig->settings['region']) && $smsConfig->settings['region'] == 'ap-northeast-2' ? 'selected' : '' }}>Asia Pacific (Seoul)</option>
                                        <option value="ap-southeast-1" {{ isset($smsConfig) && $smsConfig->provider == 'aws_sns' && isset($smsConfig->settings['region']) && $smsConfig->settings['region'] == 'ap-southeast-1' ? 'selected' : '' }}>Asia Pacific (Singapore)</option>
                                        <option value="ap-southeast-2" {{ isset($smsConfig) && $smsConfig->provider == 'aws_sns' && isset($smsConfig->settings['region']) && $smsConfig->settings['region'] == 'ap-southeast-2' ? 'selected' : '' }}>Asia Pacific (Sydney)</option>
                                        <option value="ap-northeast-1" {{ isset($smsConfig) && $smsConfig->provider == 'aws_sns' && isset($smsConfig->settings['region']) && $smsConfig->settings['region'] == 'ap-northeast-1' ? 'selected' : '' }}>Asia Pacific (Tokyo)</option>
                                        <option value="eu-central-1" {{ isset($smsConfig) && $smsConfig->provider == 'aws_sns' && isset($smsConfig->settings['region']) && $smsConfig->settings['region'] == 'eu-central-1' ? 'selected' : '' }}>Europe (Frankfurt)</option>
                                        <option value="eu-west-1" {{ isset($smsConfig) && $smsConfig->provider == 'aws_sns' && isset($smsConfig->settings['region']) && $smsConfig->settings['region'] == 'eu-west-1' ? 'selected' : '' }}>Europe (Ireland)</option>
                                        <option value="eu-west-2" {{ isset($smsConfig) && $smsConfig->provider == 'aws_sns' && isset($smsConfig->settings['region']) && $smsConfig->settings['region'] == 'eu-west-2' ? 'selected' : '' }}>Europe (London)</option>
                                    </select>
                                </div>
                                <p class="mt-1 text-[10px] text-gray-500">AWS region where SNS is configured</p>
                            </div>
                        </div>
                    </div>
                    <!-- Infobip Settings -->
                    <div id="infobip_settings" class="border-b border-gray-200 pb-5" style="display: none;">
                        <h2 class="text-sm font-semibold text-gray-700 mb-4">Infobip Settings</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Infobip API Key -->
                            <div>
                                <label for="infobip_key" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">vpn_key</span>
                                    Infobip API Key
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons text-[#004aad] text-base">key</span>
                                    </div>
                                    <input 
                                        type="text" 
                                        id="infobip_key" 
                                        name="infobip_key" 
                                        value="{{ isset($smsConfig) && $smsConfig->provider == 'infobip' && isset($smsConfig->settings['key']) ? $smsConfig->settings['key'] : '' }}"
                                        class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                        :disabled="!isEditing"
                                    >
                                </div>
                                <p class="mt-1 text-[10px] text-gray-500">Your Infobip API key</p>
                            </div>
                            
                            <!-- Infobip Base URL -->
                            <div>
                                <label for="infobip_base_url" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">link</span>
                                    Infobip Base URL
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons text-[#004aad] text-base">public</span>
                                    </div>
                                    <input 
                                        type="text" 
                                        id="infobip_base_url" 
                                        name="infobip_base_url" 
                                        placeholder="https://api.infobip.com"
                                        value="{{ isset($smsConfig) && $smsConfig->provider == 'infobip' && isset($smsConfig->settings['base_url']) ? $smsConfig->settings['base_url'] : '' }}"
                                        class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                        :disabled="!isEditing"
                                    >
                                </div>
                                <p class="mt-1 text-[10px] text-gray-500">Your Infobip API base URL</p>
                            </div>
                            
                            <!-- From Name/Number -->
                    <div>
                                <label for="infobip_from" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">phone</span>
                                    From Name/Number
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons text-[#004aad] text-base">call</span>
                                    </div>
                                    <input 
                                        type="text" 
                                        id="infobip_from" 
                                        name="infobip_from" 
                                        placeholder="InfoSMS"
                                        value="{{ isset($smsConfig) && $smsConfig->provider == 'infobip' && isset($smsConfig->settings['from']) ? $smsConfig->settings['from'] : '' }}"
                                        class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                        :disabled="!isEditing"
                                    >
                                </div>
                                <p class="mt-1 text-[10px] text-gray-500">Sender ID (alphanumeric name or phone number)</p>
                            </div>
                        </div>
                    </div>
                    <div class="border-b border-gray-200 pb-5">
                        <h2 class="text-sm font-semibold text-gray-700 mb-4">SMS Template</h2>
                        <div class="relative">
                            <div class="absolute top-3 left-0 pl-3 flex items-start pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">description</span>
                            </div>
                            <textarea 
                                id="sms_template" 
                                name="sms_template" 
                                rows="3" 
                                class="w-full text-xs border-gray-300 rounded min-h-[60px] focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                :disabled="!isEditing"
                            >{{ isset($smsConfig) && $smsConfig->default_template ? $smsConfig->default_template : 'Hello {name}, your event {event_name} is starting soon. Please don\'t forget to bring your ID. Thank you!' }}</textarea>
                        </div>
                        <p class="mt-1 text-[10px] text-gray-500">Available variables: {name}, {event_name}, {date}, {location}</p>
                    </div>
                    
                    <div class="mt-4">
                        <h2 class="text-sm font-semibold text-gray-700 mb-2 flex items-center">
                            <span class="material-icons text-primary-DEFAULT mr-2">sms</span>
                            Test SMS Configuration
                        </h2>
                        <div class="flex items-center p-4 bg-gray-50 border border-gray-200 rounded-md">
                        <button 
                            type="button" 
                                id="testSmsBtn"
                                onclick="sendTestSms()"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-xs flex items-center shadow-sm border border-blue-700"
                                :class="{'bg-gray-400 hover:bg-gray-500 cursor-not-allowed border-gray-500': !isEditing, 'bg-blue-600 hover:bg-blue-700 border-blue-700': isEditing}"
                            :disabled="!isEditing"
                        >
                            <span class="material-icons text-xs mr-1">sms</span>
                            Send Test SMS
                        </button>
                        <span class="text-xs text-gray-500 ml-3">Test your SMS configuration by sending a test message.</span>
                    </div>
                </div>
                    
                    
            </form>
            </div>
        </div>
    </div>
    
    <script>
        // Enable disabled fields and submit a form by id
        window.enableAndSubmit = function(formId) {
            var form = document.getElementById(formId);
            if (!form) return;
            // Enable all disabled inputs temporarily so their values are submitted
            var disabled = form.querySelectorAll('input:disabled, select:disabled, textarea:disabled');
            disabled.forEach(function(el){ el.disabled = false; });
            form.submit();
        };
        // Switch between tabs
        function switchTab(tab) {
            if (tab === 'email') {
                document.getElementById('emailTab').style.display = 'block';
                document.getElementById('smsTab').style.display = 'none';
                document.getElementById('emailTabButton').classList.add('border-primary-DEFAULT', 'text-primary-DEFAULT');
                document.getElementById('emailTabButton').classList.remove('border-transparent', 'text-gray-500');
                document.getElementById('smsTabButton').classList.add('border-transparent', 'text-gray-500');
                document.getElementById('smsTabButton').classList.remove('border-primary-DEFAULT', 'text-primary-DEFAULT');
            } else {
                document.getElementById('emailTab').style.display = 'none';
                document.getElementById('smsTab').style.display = 'block';
                document.getElementById('smsTabButton').classList.add('border-primary-DEFAULT', 'text-primary-DEFAULT');
                document.getElementById('smsTabButton').classList.remove('border-transparent', 'text-gray-500');
                document.getElementById('emailTabButton').classList.add('border-transparent', 'text-gray-500');
                document.getElementById('emailTabButton').classList.remove('border-primary-DEFAULT', 'text-primary-DEFAULT');
            }
        }
        
        // Toggle mail driver settings
        function toggleMailDriverSettings() {
            const driver = document.getElementById('mail_driver').value;
            
            // Hide all driver-specific settings
            document.getElementById('smtp_settings').style.display = 'none';
            document.getElementById('mailgun_settings').style.display = 'none';
            document.getElementById('ses_settings').style.display = 'none';
            
            // Show the selected driver's settings
            if (driver === 'smtp') {
                document.getElementById('smtp_settings').style.display = 'block';
            } else if (driver === 'mailgun') {
                document.getElementById('mailgun_settings').style.display = 'block';
            } else if (driver === 'ses') {
                document.getElementById('ses_settings').style.display = 'block';
            }
        }
        
        // Toggle SMS provider settings
        function toggleSmsProviderSettings() {
            const provider = document.getElementById('sms_provider').value;
            
            // Hide all provider-specific settings
            document.getElementById('twilio_settings').style.display = 'none';
            document.getElementById('nexmo_settings').style.display = 'none';
            document.getElementById('aws_sns_settings').style.display = 'none';
            document.getElementById('infobip_settings').style.display = 'none';
            
            // Show the selected provider's settings
            if (provider === 'twilio') {
                document.getElementById('twilio_settings').style.display = 'block';
            } else if (provider === 'nexmo') {
                document.getElementById('nexmo_settings').style.display = 'block';
            } else if (provider === 'aws_sns') {
                document.getElementById('aws_sns_settings').style.display = 'block';
            } else if (provider === 'infobip') {
                document.getElementById('infobip_settings').style.display = 'block';
            }
        }
        
        // Send test email
        function sendTestEmail() {
            // Get the form data
            const formData = new FormData(document.getElementById('emailForm'));
            
            // Send the test email request
            fetch('{{ route('config.deliver.test-email') }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Test email sent successfully!');
                } else {
                    alert('Failed to send test email: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error: ' + error);
            });
        }

        // Show test email modal
        function showTestEmailModal() {
            document.getElementById('testEmailModal').classList.remove('hidden');
        }

        // Hide test email modal
        function hideTestEmailModal() {
            document.getElementById('testEmailModal').classList.add('hidden');
        }

        // Send test email to a specific address
        function sendTestEmailToAddress() {
            const emailAddress = document.getElementById('test_email').value;
            if (!emailAddress) {
                alert('Please enter an email address to send the test email to.');
                return;
            }

            const formData = new FormData();
            formData.append('email_address', emailAddress);

            fetch('{{ route('config.deliver.test-email-to-address') }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Test email sent successfully to ' + emailAddress + '!');
                    hideTestEmailModal();
                } else {
                    alert('Failed to send test email to ' + emailAddress + ': ' + data.message);
                }
            })
            .catch(error => {
                alert('Error: ' + error);
            });
        }
        
        // Send test SMS
        function sendTestSms() {
            // Get the form data
            const formData = new FormData(document.getElementById('smsForm'));
            
            // Send the test SMS request
            fetch('{{ route('config.deliver.test-sms') }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Test SMS sent successfully!');
                } else {
                    alert('Failed to send test SMS: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error: ' + error);
            });
        }
        
        // Initialize settings based on selected values
        document.addEventListener('DOMContentLoaded', function() {
            toggleMailDriverSettings();
            toggleSmsProviderSettings();
            
            // Show success message if it exists
            @if(session('success'))
                alert('{{ session('success') }}');
            @endif
            
            // Show error message if it exists
            @if(session('error'))
                alert('{{ session('error') }}');
            @endif
            
            // Show validation errors if they exist
            @if($errors->any())
                let errorMessage = 'Validation errors:\n';
                @foreach($errors->all() as $error)
                    errorMessage += '- {{ $error }}\n';
                @endforeach
                alert(errorMessage);
            @endif
        });
    </script>
</x-app-layout> 
