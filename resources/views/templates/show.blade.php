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

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between mb-6">
                <a href="{{ route('template.designer') }}" class="border border-gray-300 text-gray-700 hover:text-gray-900 px-4 py-2 rounded-md flex items-center">
                    <span class="material-icons mr-1">arrow_back</span>
                    Back to Templates
                </a>
                <a href="{{ route('template.edit', $template->id) }}" class="bg-primary-DEFAULT hover:bg-primary-dark text-white px-4 py-2 rounded-md flex items-center">
                    <span class="material-icons mr-1">edit</span>
                    Edit Template
                </a>
            </div>
            
            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-6">{{ $template->name }} - Template Preview</h2>
                
                <div class="flex flex-col md:flex-row gap-6">
                    <div class="md:w-1/3 space-y-4">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Template Name</h3>
                            <p class="mt-1 text-lg">{{ $template->name }}</p>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Description</h3>
                            <p class="mt-1">{{ $template->description ?: 'No description' }}</p>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Orientation</h3>
                            <p class="mt-1 capitalize">{{ $template->orientation }}</p>
                        </div>
                        
                        @if(Schema::hasColumn('certificate_templates', 'is_active'))
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Status</h3>
                                @if($template->is_active)
                                    <span class="inline-flex mt-1 items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex mt-1 items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Inactive
                                    </span>
                                @endif
                            </div>
                        @endif
                        
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Created Date</h3>
                            <p class="mt-1">{{ $template->created_at->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                    
                    <div class="md:w-2/3">
                        <div class="border-2 border-gray-200 rounded-lg p-4">
                            @php
                                $isLandscape = $template->orientation == 'landscape';
                                $width = $isLandscape ? '600px' : '420px';
                                $height = $isLandscape ? '420px' : '594px';
                                $templateWidth = $isLandscape ? 297 : 210;
                                $templateHeight = $isLandscape ? 210 : 297;
                            @endphp
                            
                            <div class="relative mx-auto overflow-hidden bg-white" style="width: {{ $width }}; height: {{ $height }};">
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
                                                // Handle all possible placeholder formats
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
                                        <p class="text-gray-500">No template elements defined</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- Debug Information -->
<div class="mt-6 p-4 bg-gray-100 rounded-lg">
    <h3 class="text-sm font-medium text-gray-700 mb-2">Debug Information</h3>
    
    <div class="space-y-2 text-xs">
        <div>
            <strong>Template ID:</strong> {{ $template->id }}
        </div>
        <div>
            <strong>Elements Count:</strong> 
            @if($template->template_data && is_array($template->template_data) && isset($template->template_data['elements']))
                {{ count($template->template_data['elements']) }}
                
                <div class="mt-2">
                    <strong>Elements Summary:</strong>
                    <ul class="list-disc pl-5 mt-1">
                        @foreach($template->template_data['elements'] as $index => $element)
                            <li>
                                Element {{ $index + 1 }}: 
                                {{ $element['type'] ?? 'unknown' }} 
                                at ({{ $element['x'] ?? '?' }}, {{ $element['y'] ?? '?' }})
                                @if($element['type'] == 'text')
                                    - Content: {{ $element['content'] ?? 'Empty' }}
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @else
                0 (No elements found)
            @endif
        </div>
        <div>
            <strong>Sample Placeholder Text:</strong>
            <span class="text-green-600">John Doe</span> would appear for <code>@{{ "{{participant_name}}" }}</code> (will be automatically replaced)
        </div>
    </div>
</div>
<!-- End Debug Information -->
</x-app-layout> 