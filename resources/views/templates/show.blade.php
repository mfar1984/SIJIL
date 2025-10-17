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
                        <span class="material-icons mr-2 text-primary-DEFAULT">preview</span>
                        <h1 class="text-xl font-bold text-gray-800">Template Preview</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-8">{{ $template->name }}</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('template.designer') }}" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        <span class="material-icons text-xs mr-1">arrow_back</span>
                        Back to Templates
                    </a>
                    @can('templates.update')
                    <a href="{{ route('template.edit', $template->id) }}" class="bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        <span class="material-icons text-xs mr-1">edit</span>
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
                            <span class="material-icons text-primary-DEFAULT text-base mr-1">title</span>
                            Template Name
                        </label>
                        <p class="text-sm ml-6">{{ $template->name }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                            <span class="material-icons text-primary-DEFAULT text-base mr-1">notes</span>
                            Description
                        </label>
                        <p class="text-sm ml-6">{{ $template->description ?: 'No description provided' }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                            <span class="material-icons text-primary-DEFAULT text-base mr-1">crop_landscape</span>
                            Orientation
                        </label>
                        <p class="text-sm ml-6 capitalize">{{ $template->orientation }}</p>
                    </div>
                    
                    @if(Schema::hasColumn('certificate_templates', 'is_active'))
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                <span class="material-icons text-primary-DEFAULT text-base mr-1">toggle_on</span>
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
                            <span class="material-icons text-primary-DEFAULT text-base mr-1">calendar_today</span>
                            Created Date
                        </label>
                        <p class="text-sm ml-6">{{ $template->created_at->format('d M Y, H:i') }}</p>
                    </div>
                    
                    <div class="border-t border-gray-200 pt-4 mt-6">
                        <div class="flex gap-2">
                            @can('templates.create')
                            <a href="{{ route('template.designer.create', ['id' => $template->id]) }}" class="bg-gradient-to-r from-purple-600 to-purple-500 hover:from-purple-700 hover:to-purple-600 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                                <span class="material-icons text-xs mr-1">design_services</span>
                                Edit Design
                            </a>
                            <form action="{{ route('template.duplicate', $template->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                                    <span class="material-icons text-xs mr-1">content_copy</span>
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
                            <span class="material-icons text-primary-DEFAULT text-base mr-1">preview</span>
                            Certificate Preview
                        </h2>
                        
                        @php
                            $isLandscape = strtolower(trim($template->orientation)) === 'landscape';
                            $width = $isLandscape ? '600px' : '420px';
                            $height = $isLandscape ? '420px' : '594px';
                            $templateWidth = $isLandscape ? 297 : 210;
                            $templateHeight = $isLandscape ? 210 : 297;
                        @endphp
                        
                        <div class="relative mx-auto overflow-hidden bg-white shadow-md" style="width: {{ $width }}; height: {{ $height }};">
                            @if($template->background_pdf)
                                <iframe src="{{ $template->background_pdf }}#toolbar=0&navpanes=0&scrollbar=0&view=FitH" class="absolute top-0 left-0 w-full h-full border-0 pointer-events-none"></iframe>
                            @endif
                            
                            @if($template->template_data && is_array($template->template_data) && isset($template->template_data['elements']))
                                @foreach($template->template_data['elements'] as $element)
                                    @if($element['type'] == 'text')
                                        @php
                                            $x = ($element['x'] / $templateWidth) * 100;
                                            $y = ($element['y'] / $templateHeight) * 100;
                                            $fontSize = $element['fontSize'] ?? 16;
                                            $fontFamily = $element['fontFamily'] ?? 'Arial';
                                            $fontWeight = $element['fontWeight'] ?? 'normal';
                                            $fontStyle = $element['fontStyle'] ?? 'normal';
                                            $textDecoration = $element['textDecoration'] ?? 'none';
                                            $color = $element['color'] ?? '#000000';
                                            $textAlign = $element['textAlign'] ?? 'left';
                                            $transform = isset($element['textAlign']) && $element['textAlign'] == 'center' ? 'translateX(-50%)' : 'none';
                                            
                                            $content = $element['content'] ?? '';
                                            // Simple placeholder replacement
                                            $content = str_replace('{{participant_name}}', 'John Doe', $content);
                                            $content = str_replace('{{event_name}}', 'Sample Event', $content);
                                            $content = str_replace('{{event_date}}', date('d/m/Y'), $content);
                                            $content = str_replace('{{identity_card}}', '123456789012', $content);
                                        @endphp
                                        
                                        <div class="absolute" style="left: {{ $x }}%; top: {{ $y }}%; font-size: {{ $fontSize }}px; font-family: {{ $fontFamily }}; font-weight: {{ $fontWeight }}; font-style: {{ $fontStyle }}; text-decoration: {{ $textDecoration }}; color: {{ $color }}; text-align: {{ $textAlign }}; transform: {{ $transform }};">
                                            {{ $content }}
                                        </div>
                                    @elseif($element['type'] == 'image' && isset($element['src']))
                                        @php
                                            $x = ($element['x'] / $templateWidth) * 100;
                                            $y = ($element['y'] / $templateHeight) * 100;
                                            $width = ($element['width'] / $templateWidth) * 100;
                                            $height = ($element['height'] / $templateHeight) * 100;
                                        @endphp
                                        
                                        <div class="absolute" style="left: {{ $x }}%; top: {{ $y }}%; width: {{ $width }}%; height: {{ $height }}%;">
                                            <img src="{{ $element['src'] }}" class="w-full h-full" style="object-fit: contain;">
                                        </div>
                                    @endif
                                @endforeach
                            @else
                                <div class="flex items-center justify-center h-full">
                                    <p class="text-gray-500 text-xs">No template elements defined</p>
                                </div>
                            @endif
                        </div>
                        
                        <p class="text-xs text-gray-500 mt-3 text-center">Preview showing placeholder text. Actual certificates will display participant data.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 