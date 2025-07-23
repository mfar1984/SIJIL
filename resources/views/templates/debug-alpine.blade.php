<x-app-layout>
    <x-slot name="title">Debug Alpine</x-slot>
    
    @php
        $jsonData = json_encode(['width' => 297, 'height' => 210, 'elements' => []]);
    @endphp
    
    <div class="py-6" x-data="{
        step: 2,
        templateData: {{ $jsonData }},
        addText() {
            this.templateData.elements.push({
                type: 'text',
                content: '@{{participant_name}}',
                id: Date.now()
            });
        }
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                <h2 class="text-xl font-semibold">Alpine Debugging</h2>
                
                <div class="mt-4">
                    <button @click="addText()" class="px-4 py-2 bg-blue-500 text-white rounded">
                        Add Text Element
                    </button>
                </div>
                
                <pre class="mt-4 p-4 bg-gray-100 rounded text-sm" x-text="JSON.stringify(templateData, null, 2)"></pre>
            </div>
        </div>
    </div>
</x-app-layout> 