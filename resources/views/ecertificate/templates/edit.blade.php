<x-app-layout>
    <x-slot name="breadcrumb">
        <span>PWA Management</span>
        <span class="mx-2 text-gray-500">/</span>
        <span><a href="{{ route('pwa.templates') }}" class="text-primary-DEFAULT hover:underline">Email Templates</a></span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Edit</span>
    </x-slot>

    <x-slot name="title">Edit PWA Email Template</x-slot>

    <style>
        .tooltip-wrapper { position: relative; display: inline-flex; }
        .tooltip-content {
            position: absolute; bottom: 100%; left: 50%;
            transform: translateX(-50%) translateY(-4px);
            background-color: #1f2937; color: white;
            padding: 6px 10px; border-radius: 6px;
            font-size: 11px; white-space: nowrap;
            z-index: 1000; pointer-events: none;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .tooltip-content::after {
            content: ''; position: absolute;
            top: 100%; left: 50%; transform: translateX(-50%);
            border: 4px solid transparent; border-top-color: #1f2937;
        }
    </style>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-start gap-3">
                <div>
                    <div class="flex items-center">
                        <span class="material-icons-outlined mr-2 text-primary-DEFAULT">edit</span>
                        <h1 class="text-xl font-bold text-gray-800">Edit Email Template</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-8">Update the subject, content and status of this template</p>
                </div>
                <a href="{{ route('pwa.templates') }}" class="h-9 px-3 border border-gray-300 rounded text-xs font-medium text-gray-700 hover:bg-gray-50 flex items-center shrink-0">
                    <span class="material-icons-outlined text-xs mr-1">arrow_back</span>
                    Back
                </a>
            </div>
        </div>

        <div class="p-6">
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li class="text-xs">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="templateForm" method="POST" action="{{ route('pwa.templates.update', $template->id) }}" class="space-y-2">
                @csrf
                @method('PUT')

                <!-- Template Information Section -->
                <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                    <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                        <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-primary-DEFAULT mr-2">email</span>
                            Template Information
                        </h2>
                    </div>

                    <div class="p-4 space-y-3">
                        <!-- Name -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-48 shrink-0 flex items-center gap-1">
                                Template Name
                                <div class="tooltip-wrapper" x-data="{ show: false }">
                                    <span class="material-icons-outlined text-gray-400 text-sm cursor-help"
                                          @mouseenter="show = true"
                                          @mouseleave="show = false">
                                        help_outline
                                    </span>
                                    <div x-show="show" x-transition class="tooltip-content">
                                        The name is fixed once the template is created
                                    </div>
                                </div>
                            </label>
                            <div class="flex-1">
                                <input type="text" value="{{ $template->name }}" disabled
                                       class="w-full h-9 text-xs border-gray-300 rounded px-3 bg-gray-50 text-gray-500" />
                            </div>
                        </div>

                        <!-- Type -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-48 shrink-0 flex items-center gap-1">
                                Template Type
                                <div class="tooltip-wrapper" x-data="{ show: false }">
                                    <span class="material-icons-outlined text-gray-400 text-sm cursor-help"
                                          @mouseenter="show = true"
                                          @mouseleave="show = false">
                                        help_outline
                                    </span>
                                    <div x-show="show" x-transition class="tooltip-content">
                                        The type decides where the system uses this template
                                    </div>
                                </div>
                            </label>
                            <div class="flex-1">
                                <input type="text" value="{{ ucfirst(str_replace('_', ' ', $template->type)) }}" disabled
                                       class="w-full h-9 text-xs border-gray-300 rounded px-3 bg-gray-50 text-gray-500" />
                            </div>
                        </div>

                        <!-- Subject -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label for="subject" class="text-xs font-medium text-gray-700 md:w-48 shrink-0 flex items-center gap-1">
                                Email Subject <span class="text-red-500">*</span>
                                <div class="tooltip-wrapper" x-data="{ show: false }">
                                    <span class="material-icons-outlined text-gray-400 text-sm cursor-help"
                                          @mouseenter="show = true"
                                          @mouseleave="show = false">
                                        help_outline
                                    </span>
                                    <div x-show="show" x-transition class="tooltip-content">
                                        Variables work here too, for example @{{event_name}}
                                    </div>
                                </div>
                            </label>
                            <div class="flex-1">
                                <input type="text" name="subject" id="subject" value="{{ old('subject', $template->subject) }}" required
                                       class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" />
                                @error('subject')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="flex flex-col md:flex-row md:items-start gap-3">
                            <label for="content" class="text-xs font-medium text-gray-700 md:w-48 shrink-0 md:pt-2 flex items-center gap-1">
                                Email Content <span class="text-red-500">*</span>
                                <div class="tooltip-wrapper" x-data="{ show: false }">
                                    <span class="material-icons-outlined text-gray-400 text-sm cursor-help"
                                          @mouseenter="show = true"
                                          @mouseleave="show = false">
                                        help_outline
                                    </span>
                                    <div x-show="show" x-transition class="tooltip-content">
                                        HTML is allowed. Variables are replaced before sending
                                    </div>
                                </div>
                            </label>
                            <div class="flex-1">
                                <textarea name="content" id="content" rows="14" required
                                          class="w-full text-xs border-gray-300 rounded px-3 py-2 font-mono focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                          placeholder="Use variables like @{{name}} @{{email}} @{{pwa_link}}">{{ old('content', $template->content) }}</textarea>
                                <p class="text-xs text-gray-500 mt-1">
                                    Available variables:
                                    <code class="bg-gray-100 px-1 rounded">@{{name}}</code>
                                    <code class="bg-gray-100 px-1 rounded">@{{email}}</code>
                                    <code class="bg-gray-100 px-1 rounded">@{{password}}</code>
                                    <code class="bg-gray-100 px-1 rounded">@{{pwa_link}}</code>
                                    <code class="bg-gray-100 px-1 rounded">@{{login_url}}</code>
                                    <code class="bg-gray-100 px-1 rounded">@{{event_name}}</code>
                                    <code class="bg-gray-100 px-1 rounded">@{{organization}}</code>
                                    <code class="bg-gray-100 px-1 rounded">@{{support_email}}</code>
                                </p>
                                @error('content')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-48 shrink-0">Status</label>
                            <div class="flex-1">
                                <label class="flex items-center text-xs text-gray-700">
                                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $template->is_active) ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-primary-DEFAULT focus:ring-primary-light">
                                    <span class="ml-2">Active</span>
                                </label>
                                <p class="text-xs text-gray-500 mt-1">Inactive templates are skipped and the built-in default is used instead.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 pt-4">
                    <button type="submit" form="resetTemplateForm"
                            class="h-9 px-3 border border-red-300 rounded text-xs font-medium text-red-700 hover:bg-red-50 flex items-center justify-center transition-colors duration-200 ease-in-out">
                        <span class="material-icons-outlined text-xs mr-1">restart_alt</span>
                        Reset to Default
                    </button>
                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('pwa.templates') }}" class="px-3 h-[36px] bg-gradient-to-r from-red-600 to-red-500 hover:from-red-700 hover:to-red-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                            <span class="material-icons-outlined text-xs mr-1">cancel</span>
                            Cancel
                        </a>
                        <button type="submit" class="px-3 h-[36px] bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                            <span class="material-icons-outlined text-xs mr-1">save</span>
                            Update Template
                        </button>
                    </div>
                </div>
            </form>

            <!-- Send Test Section (separate form, kept outside the edit form) -->
            <form method="POST" action="{{ route('pwa.templates.send-test', $template->id) }}" class="mt-4">
                @csrf
                <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                    <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                        <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-primary-DEFAULT mr-2">send</span>
                            Send a Test
                        </h2>
                    </div>
                    <div class="p-4">
                        <div class="flex flex-col md:flex-row md:items-start gap-3">
                            <label for="email_address" class="text-xs font-medium text-gray-700 md:w-48 shrink-0 md:pt-2">
                                Recipient
                            </label>
                            <div class="flex-1">
                                {{-- Input and button sit in one group so the button stays
                                     beside the field instead of being pushed to the far
                                     edge of the card by a full-width input. --}}
                                <div class="flex items-center gap-2">
                                    <input type="email" name="email_address" id="email_address"
                                           value="{{ old('email_address') }}"
                                           placeholder="{{ auth()->user()->email }}"
                                           class="w-full md:max-w-xs h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" />
                                    <button type="submit" class="px-3 h-[36px] bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center shrink-0">
                                        <span class="material-icons-outlined text-xs mr-1">send</span>
                                        Send Test
                                    </button>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Leave blank to send to your own address. The test uses sample data, not saved changes, so update the template first.</p>
                                @error('email_address')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Reset form lives outside the edit form so the footer button never nests one form inside another -->
    <form id="resetTemplateForm" method="POST" action="{{ route('pwa.templates.reset-default', $template->id) }}"
          class="hidden" onsubmit="return confirm('Reset this template to its default subject and content? Your current changes will be lost.')">
        @csrf
    </form>
</x-app-layout>
