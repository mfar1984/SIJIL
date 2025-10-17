<x-app-layout>
    <x-slot name="breadcrumb">
        <span>PWA Management</span>
        <span class="mx-2">/</span>
        <span>Email Templates</span>
        <span class="mx-2">/</span>
        <span>Edit</span>
    </x-slot>

    <x-slot name="title">Edit PWA Email Template</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-start">
                <div class="flex items-center">
                    <span class="material-icons mr-2 text-indigo-500">edit</span>
                    <h1 class="text-xl font-bold text-gray-800">Edit Email Template</h1>
                </div>
                <a href="{{ route('pwa.templates') }}" class="bg-gray-100 text-gray-700 px-3 py-1 rounded text-xs font-medium">Back</a>
            </div>
        </div>

        <form method="POST" action="{{ route('pwa.templates.update', $template->id) }}" class="p-4 space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Name</label>
                    <input type="text" value="{{ $template->name }}" disabled class="w-full px-2 py-1 text-xs border border-gray-200 rounded bg-gray-50" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Type</label>
                    <input type="text" value="{{ ucfirst(str_replace('_',' ', $template->type)) }}" disabled class="w-full px-2 py-1 text-xs border border-gray-200 rounded bg-gray-50" />
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Subject</label>
                <input type="text" name="subject" value="{{ old('subject', $template->subject) }}" required class="w-full px-2 py-1 text-xs border border-gray-300 rounded" />
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Content</label>
                <textarea name="content" rows="14" class="w-full px-2 py-1 text-xs border border-gray-300 rounded" placeholder="Use variables like @{{name}} @{{email}} @{{pwa_link}}">{{ old('content', $template->content) }}</textarea>
            </div>

            <div class="flex items-center">
                <label class="flex items-center text-xs text-gray-700">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $template->is_active) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="ml-2">Active</span>
                </label>
            </div>

            <div class="flex justify-between items-center pt-2 border-t border-gray-200">
                <form method="POST" action="{{ route('pwa.templates.reset-default', $template->id) }}" onsubmit="return confirm('Reset this template to default?')">
                    @csrf
                    <button class="text-red-600 text-xs font-medium">Reset to Default</button>
                </form>
                <div class="flex gap-2">
                    <a href="{{ route('pwa.templates') }}" class="bg-gray-100 text-gray-700 px-3 py-1 rounded text-xs font-medium">Cancel</a>
                    <button type="submit" class="bg-indigo-500 text-white px-3 py-1 rounded text-xs font-medium">Update Template</button>
                </div>
            </div>
        </form>
        
        <div class="p-4">
            <form method="POST" action="{{ route('pwa.templates.send-test', $template->id) }}">
                @csrf
                <button class="bg-gray-100 text-gray-700 px-3 py-1 rounded text-xs font-medium">Send Test</button>
            </form>
        </div>
    </div>
</x-app-layout>


