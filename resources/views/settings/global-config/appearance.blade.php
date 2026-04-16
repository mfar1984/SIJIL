<div x-show="activeTab === 'appearance'" class="space-y-2">
    <div class="bg-white border border-gray-200 rounded-md shadow-sm">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                <span class="material-icons text-primary-DEFAULT mr-2">palette</span>
                Theme Settings
            </h2>
        </div>
        
        <div class="p-4">
        <div class="space-y-3">
            <!-- Primary Color -->
            <div class="flex flex-col md:flex-row md:items-start gap-1">
                <label for="primary_color" class="text-xs font-medium text-gray-700 md:w-40 pt-2">
                    Primary Color
                </label>
                <div class="flex-1">
                    <div class="flex items-center">
                        <input 
                            type="text" 
                            id="primary_color" 
                            name="primary_color" 
                            value="{{ old('primary_color', $config->primary_color ?? '#004aad') }}" 
                            class="w-full text-xs border-gray-300 rounded-[1px] focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                            :class="{'bg-gray-50': !isEditing}"
                            :disabled="!isEditing"
                        >
                        <input 
                            type="color" 
                            value="{{ old('primary_color', $config->primary_color ?? '#004aad') }}" 
                            class="h-[34px] w-10 border-0 p-0 ml-2"
                        >
                    </div>
                    <p class="mt-1 text-[10px] text-gray-500">Primary accent color for buttons and highlights</p>
                </div>
            </div>
            
            <!-- Secondary Color -->
            <div class="flex flex-col md:flex-row md:items-start gap-1">
                <label for="secondary_color" class="text-xs font-medium text-gray-700 md:w-40 pt-2">
                    Secondary Color
                </label>
                <div class="flex-1">
                    <div class="flex items-center">
                        <input 
                            type="text" 
                            id="secondary_color" 
                            name="secondary_color" 
                            value="{{ old('secondary_color', $config->secondary_color ?? '#38bdf8') }}" 
                            class="w-full text-xs border-gray-300 rounded-[1px] focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                            :class="{'bg-gray-50': !isEditing}"
                            :disabled="!isEditing"
                        >
                        <input 
                            type="color" 
                            value="{{ old('secondary_color', $config->secondary_color ?? '#38bdf8') }}" 
                            class="h-[34px] w-10 border-0 p-0 ml-2"
                        >
                    </div>
                    <p class="mt-1 text-[10px] text-gray-500">Secondary color for gradients and accents</p>
                </div>
            </div>
            
            <!-- Default Theme -->
            <div class="flex flex-col md:flex-row md:items-start gap-1">
                <label for="default_theme" class="text-xs font-medium text-gray-700 md:w-40 pt-2">
                    Default Theme
                </label>
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-icons text-[#004aad] text-base">style</span>
                        </div>
                        <select 
                            id="default_theme" 
                            name="default_theme" 
                            class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                            :class="{'bg-gray-50': !isEditing}"
                            :disabled="!isEditing"
                        >
                            <option value="light" {{ (old('default_theme', $config->default_theme ?? 'light') == 'light') ? 'selected' : '' }}>Light</option>
                            <option value="dark" {{ (old('default_theme', $config->default_theme ?? 'light') == 'dark') ? 'selected' : '' }}>Dark</option>
                            <option value="system" {{ (old('default_theme', $config->default_theme ?? 'light') == 'system') ? 'selected' : '' }}>System Default</option>
                        </select>
                    </div>
                    <p class="mt-1 text-[10px] text-gray-500">Default color theme for new users</p>
                </div>
            </div>
            
            <!-- Font Family -->
            <div class="flex flex-col md:flex-row md:items-start gap-1">
                <label for="font_family" class="text-xs font-medium text-gray-700 md:w-40 pt-2">
                    Font Family
                </label>
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-icons text-[#004aad] text-base">font_download</span>
                        </div>
                        <select 
                            id="font_family" 
                            name="font_family" 
                            class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                            :class="{'bg-gray-50': !isEditing}"
                            :disabled="!isEditing"
                        >
                            <option value="inter" {{ (old('font_family', $config->font_family ?? 'inter') == 'inter') ? 'selected' : '' }}>Inter</option>
                            <option value="roboto" {{ (old('font_family', $config->font_family ?? 'inter') == 'roboto') ? 'selected' : '' }}>Roboto</option>
                            <option value="poppins" {{ (old('font_family', $config->font_family ?? 'inter') == 'poppins') ? 'selected' : '' }}>Poppins</option>
                            <option value="opensans" {{ (old('font_family', $config->font_family ?? 'inter') == 'opensans') ? 'selected' : '' }}>Open Sans</option>
                            <option value="system" {{ (old('font_family', $config->font_family ?? 'inter') == 'system') ? 'selected' : '' }}>System Default</option>
                        </select>
                    </div>
                    <p class="mt-1 text-[10px] text-gray-500">Font family for the user interface</p>
                </div>
            </div>
            
            <div class="flex flex-col md:flex-row md:items-start gap-1">
                <div class="md:w-40"></div>
                <div class="flex-1">
                    <label class="flex items-center">
                        <input type="hidden" name="allow_user_theme_choice" value="0">
                        <input 
                            type="checkbox" 
                            name="allow_user_theme_choice"
                            value="1"
                            class="rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                            {{ (old('allow_user_theme_choice', $config->allow_user_theme_choice ?? true)) ? 'checked' : '' }}
                            :disabled="!isEditing"
                        >
                        <span class="ml-2 text-xs text-gray-700">Allow users to choose their own theme</span>
                    </label>
                </div>
            </div>
        </div>
        </div>
    </div>
    
    <div class="bg-white border border-gray-200 rounded-md shadow-sm">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                <span class="material-icons text-primary-DEFAULT mr-2">branding_watermark</span>
                Branding Settings
            </h2>
        </div>
        
        <div class="p-4">
        <div class="space-y-3">
            <!-- Favicon -->
            <div class="flex flex-col md:flex-row md:items-start gap-1">
                <label for="favicon" class="text-xs font-medium text-gray-700 md:w-40 pt-2">
                    Favicon
                </label>
                <div class="flex-1">
                    <div class="flex items-center space-x-4">
                        <div class="w-10 h-10 border border-gray-300 rounded flex items-center justify-center bg-gray-50">
                            <img src="/favicon.ico" alt="Favicon" class="max-w-full max-h-full p-1">
                        </div>
                        <div>
                            <label class="bg-white border border-gray-300 text-xs text-gray-700 hover:bg-gray-50 px-3 py-2 rounded cursor-pointer"
                                :class="{'opacity-50 cursor-not-allowed': !isEditing}"
                                :disabled="!isEditing"
                            >
                                <span class="material-icons text-xs mr-1 inline-block align-text-bottom">upload</span>
                                Upload Favicon
                                <input type="file" name="favicon" class="hidden" :disabled="!isEditing">
                            </label>
                            <p class="text-[10px] text-gray-500 mt-1">ICO/PNG format (32x32px)</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Login Background -->
            <div class="flex flex-col md:flex-row md:items-start gap-1">
                <label for="login_background" class="text-xs font-medium text-gray-700 md:w-40 pt-2">
                    Login Background
                </label>
                <div class="flex-1">
                    <label class="bg-white border border-gray-300 text-xs text-gray-700 hover:bg-gray-50 px-3 py-2 rounded cursor-pointer"
                        :class="{'opacity-50 cursor-not-allowed': !isEditing}"
                        :disabled="!isEditing"
                    >
                        <span class="material-icons text-xs mr-1 inline-block align-text-bottom">upload</span>
                        Upload Background
                        <input type="file" name="login_background" class="hidden" :disabled="!isEditing">
                    </label>
                    <p class="text-[10px] text-gray-500 mt-1">Recommended size: 1920x1080px, max 2MB</p>
                </div>
            </div>
            
            <!-- Custom CSS -->
            <div class="flex flex-col md:flex-row md:items-start gap-1">
                <label for="custom_css" class="text-xs font-medium text-gray-700 md:w-40 pt-2">
                    Custom CSS
                </label>
                <div class="flex-1">
                    <textarea 
                        id="custom_css" 
                        name="custom_css" 
                        rows="4" 
                        class="w-full text-xs border-gray-300 rounded focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50 font-mono"
                        :class="{'bg-gray-50': !isEditing}"
                        :disabled="!isEditing"
                    >{{ old('custom_css', $config->custom_css ?? '/* Custom CSS code */
.custom-header {
  background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
}') }}</textarea>
                    <p class="mt-1 text-[10px] text-gray-500">Custom CSS to apply to the application (use with caution)</p>
                </div>
            </div>
        </div>
        </div>
    </div>
    
    <div class="bg-white border border-gray-200 rounded-md shadow-sm">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                <span class="material-icons text-primary-DEFAULT mr-2">view_quilt</span>
                Layout Settings
            </h2>
        </div>
        
        <div class="p-4">
        <div class="space-y-3">
            <!-- Sidebar Default State -->
            <div class="flex flex-col md:flex-row md:items-start gap-1">
                <label for="sidebar_default" class="text-xs font-medium text-gray-700 md:w-40 pt-2">
                    Sidebar Default State
                </label>
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-icons text-[#004aad] text-base">view_sidebar</span>
                        </div>
                        <select 
                            id="sidebar_default" 
                            name="sidebar_default" 
                            class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                            :class="{'bg-gray-50': !isEditing}"
                            :disabled="!isEditing"
                        >
                            <option value="expanded" {{ (old('sidebar_default', $config->sidebar_default ?? 'expanded') == 'expanded') ? 'selected' : '' }}>Expanded</option>
                            <option value="collapsed" {{ (old('sidebar_default', $config->sidebar_default ?? 'expanded') == 'collapsed') ? 'selected' : '' }}>Collapsed</option>
                            <option value="remember" {{ (old('sidebar_default', $config->sidebar_default ?? 'expanded') == 'remember') ? 'selected' : '' }}>Remember Last State</option>
                        </select>
                    </div>
                    <p class="mt-1 text-[10px] text-gray-500">Default sidebar state when user first logs in</p>
                </div>
            </div>
            
            <!-- Table Row Density -->
            <div class="flex flex-col md:flex-row md:items-start gap-1">
                <label for="table_density" class="text-xs font-medium text-gray-700 md:w-40 pt-2">
                    Table Row Density
                </label>
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-icons text-[#004aad] text-base">table_rows</span>
                        </div>
                        <select 
                            id="table_density" 
                            name="table_density" 
                            class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                            :class="{'bg-gray-50': !isEditing}"
                            :disabled="!isEditing"
                        >
                            <option value="compact" {{ (old('table_density', $config->table_density ?? 'default') == 'compact') ? 'selected' : '' }}>Compact</option>
                            <option value="default" {{ (old('table_density', $config->table_density ?? 'default') == 'default') ? 'selected' : '' }}>Default</option>
                            <option value="comfortable" {{ (old('table_density', $config->table_density ?? 'default') == 'comfortable') ? 'selected' : '' }}>Comfortable</option>
                        </select>
                    </div>
                    <p class="mt-1 text-[10px] text-gray-500">Spacing density for table rows</p>
                </div>
            </div>
            
            <div class="flex flex-col md:flex-row md:items-start gap-1">
                <div class="md:w-40"></div>
                <div class="flex-1">
                    <label class="flex items-center">
                        <input type="hidden" name="show_welcome_message" value="0">
                        <input 
                            type="checkbox" 
                            name="show_welcome_message"
                            value="1"
                            class="rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                            {{ (old('show_welcome_message', $config->show_welcome_message ?? true)) ? 'checked' : '' }}
                            :disabled="!isEditing"
                        >
                        <span class="ml-2 text-xs text-gray-700">Show welcome message on dashboard</span>
                    </label>
                </div>
            </div>
            
            <div class="flex flex-col md:flex-row md:items-start gap-1">
                <div class="md:w-40"></div>
                <div class="flex-1">
                    <label class="flex items-center">
                        <input type="hidden" name="show_help_icons" value="0">
                        <input 
                            type="checkbox" 
                            name="show_help_icons"
                            value="1"
                            class="rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                            {{ (old('show_help_icons', $config->show_help_icons ?? true)) ? 'checked' : '' }}
                            :disabled="!isEditing"
                        >
                        <span class="ml-2 text-xs text-gray-700">Show help icons beside form fields</span>
                    </label>
                </div>
            </div>
        </div>
        </div>
    </div>
</div>
