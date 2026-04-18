<x-app-layout>
    <x-slot name="breadcrumb">
        <span>PWA Management</span>
        <span class="mx-2">/</span>
        <span>Email Templates</span>
        <span class="mx-2">/</span>
        <span>Create</span>
    </x-slot>

    <x-slot name="title">Create PWA Email Template</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-start">
                <div class="flex items-center">
                    <span class="material-icons mr-2 text-indigo-500">email</span>
                    <h1 class="text-xl font-bold text-gray-800">Create Email Template</h1>
                </div>
                <a href="{{ route('pwa.templates') }}" class="bg-gray-100 text-gray-700 px-3 py-1 rounded text-xs font-medium">Back</a>
            </div>
        </div>

        <form method="POST" action="{{ route('pwa.templates.store') }}" class="p-4 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Name</label>
                <input type="text" name="name" required class="w-full px-2 py-1 text-xs border border-gray-300 rounded" />
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Type</label>
                <select name="type" class="w-full px-2 py-1 text-xs border border-gray-300 rounded">
                    <option value="custom">Custom</option>
                    <option value="welcome">Welcome</option>
                    <option value="password_reset">Password Reset</option>
                    <option value="event_reminder">Event Reminder</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Subject</label>
                <input type="text" name="subject" required class="w-full px-2 py-1 text-xs border border-gray-300 rounded" />
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Content</label>
                <textarea name="content" rows="10" class="w-full px-2 py-1 text-xs border border-gray-300 rounded" placeholder="Use variables like @{{name}} @{{email}} @{{pwa_link}}"></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-2 border-t border-gray-200">
                <a href="{{ route('pwa.templates') }}" class="bg-gray-100 text-gray-700 px-3 py-1 rounded text-xs font-medium">Cancel</a>
                <button type="submit" class="bg-indigo-500 text-white px-3 py-1 rounded text-xs font-medium">Create</button>
            </div>
        </form>
    </div>
</x-app-layout>


