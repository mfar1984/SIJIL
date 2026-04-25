@php
    use Illuminate\Support\Facades\Schema;
@endphp

<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Certificate</span>
        <span class="mx-2 text-gray-500">/</span>
        <span><a href="{{ route('template.designer') }}" class="text-primary-DEFAULT hover:underline">Template Designer</a></span>
        <span class="mx-2 text-gray-500">/</span>
        <span>View Template</span>
    </x-slot>

    <x-slot name="title">Template: {{ $template->name }}</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center">
                        <span class="material-icons-outlined mr-2 text-primary-DEFAULT">preview</span>
                        <h1 class="text-xl font-bold text-gray-800">Template Preview</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-8">{{ $template->name }}</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('template.designer') }}" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        <span class="material-icons-outlined text-xs mr-1">arrow_back</span>
                        Back to Templates
                    </a>
                    @can('templates.update')
                    <a href="{{ route('template.edit', $template->id) }}" class="bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        <span class="material-icons-outlined text-xs mr-1">edit</span>
                        Edit Template
                    </a>
                    @endcan
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <div class="flex flex-col md:flex-row gap-6">
                <div class="md:w-1/3 space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                            <span class="material-icons-outlined text-primary-DEFAULT text-base mr-1">title</span>
                            Template Name
                        </label>
                        <p class="text-sm ml-6">{{ $template->name }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                            <span class="material-icons-outlined text-primary-DEFAULT text-base mr-1">notes</span>
                            Description
                        </label>
                        <p class="text-sm ml-6">{{ $template->description ?: 'No description provided' }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                            <span class="material-icons-outlined text-primary-DEFAULT text-base mr-1">crop_landscape</span>
                            Orientation
                        </label>
                        <p class="text-sm ml-6 capitalize">{{ $template->orientation }}</p>
                    </div>
                    
                    @if(Schema::hasColumn('certificate_templates', 'is_active'))
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                <span class="material-icons-outlined text-primary-DEFAULT text-base mr-1">toggle_on</span>
                                Status
                            </label>
                            <div class="ml-6">
                                @if($template->is_active)
                                    <span class="bg-status-active-bg text-status-active-text px-2 py-1 rounded-full text-xs">Active</span>
                                @else
                                    <span class="bg-status-pending-bg text-status-pending-text px-2 py-1 rounded-full text-xs">Inactive</span>
                                @endif
                            </div>
                        </div>
                    @endif
                    
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                            <span class="material-icons-outlined text-primary-DEFAULT text-base mr-1">calendar_today</span>
                            Created Date
                        </label>
                        <p class="text-sm ml-6">{{ $template->created_at->format('d M Y, H:i') }}</p>
                    </div>
                    
                    <div class="border-t border-gray-200 pt-4 mt-6">
                        <div class="flex gap-2">
                            @can('templates.create')
                            <a href="{{ route('template.designer.create', ['id' => $template->id]) }}" class="bg-gradient-to-r from-purple-600 to-purple-500 hover:from-purple-700 hover:to-purple-600 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                                <span class="material-icons-outlined text-xs mr-1">design_services</span>
                                Edit Design
                            </a>
                            <form action="{{ route('template.duplicate', $template->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                                    <span class="material-icons-outlined text-xs mr-1">content_copy</span>
                                    Duplicate
                                </button>
                            </form>
                            @endcan
                        </div>
                    </div>
                </div>
                
                <div class="md:w-2/3">
                    <div class="border border-gray-300 rounded-lg p-4 bg-gray-50">
                        <h2 class="text-xs font-medium text-gray-700 mb-3 flex items-center">
                            <span class="material-icons-outlined text-primary-DEFAULT text-base mr-1">preview</span>
                            Certificate Preview
                        </h2>
                        
                        @php
                            $isLandscape = strtolower(trim($template->orientation)) === 'landscape';
                            $width = $isLandscape ? '600px' : '420px';
                            $height = $isLandscape ? '420px' : '594px';
                        @endphp
                        
                        <div class="relative mx-auto overflow-hidden bg-white shadow-md" style="width: {{ $width }}; height: {{ $height }};">
                            <div id="pdf-preview-container" class="w-full h-full flex items-center justify-center">
                                <div class="text-center">
                                    <span class="material-icons-outlined text-gray-400 text-5xl mb-2">hourglass_empty</span>
                                    <p class="text-gray-500 text-sm">Loading preview...</p>
                                </div>
                            </div>
                        </div>
                        
                        <p class="text-xs text-gray-500 mt-3 text-center">Preview with sample data, actual fonts and QR code.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        (function() {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', init);
            } else {
                init();
            }
            
            function init() {
                const container = document.getElementById('pdf-preview-container');
                const templateId = {{ $template->id }};
                const url = `/template-designer/${templateId}/preview-pdf`;
                
                fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('HTTP ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        container.innerHTML = '<iframe src="' + data.preview_url + '#toolbar=0&navpanes=0&scrollbar=0&view=FitH" class="w-full h-full border-0" style="pointer-events: none;"></iframe>';
                    } else {
                        container.innerHTML = '<div class="text-center p-4"><span class="material-icons-outlined text-red-500 text-5xl mb-2">error_outline</span><p class="text-red-600 text-sm">' + (data.error || 'Failed to generate preview') + '</p></div>';
                    }
                })
                .catch(error => {
                    container.innerHTML = '<div class="text-center p-4"><span class="material-icons-outlined text-red-500 text-5xl mb-2">error_outline</span><p class="text-red-600 text-sm">Error: ' + error.message + '</p></div>';
                });
            }
        })();
    </script>
</x-app-layout> 