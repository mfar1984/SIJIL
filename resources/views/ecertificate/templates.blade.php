<x-app-layout>
    <x-slot name="breadcrumb">
        <span>PWA Management</span>
        <span class="mx-2">/</span>
        <span>Email Templates</span>
    </x-slot>

    <x-slot name="title">PWA Email Templates</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center">
                        <span class="material-icons mr-2 text-indigo-500">email</span>
                        <h1 class="text-xl font-bold text-gray-800">PWA Email Templates</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-8">Customize welcome and password reset emails for PWA participants</p>
                </div>
                @can('pwa_templates.create')
                <a href="{{ route('pwa.templates.create') }}" class="bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-700 hover:to-indigo-600 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                    <span class="material-icons text-xs mr-1">add</span>
                    New Template
                </a>
                @endcan
            </div>
        </div>
        
        <div class="p-4">
            <!-- Templates List (dynamic) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                @forelse($templates as $template)
                    <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-semibold text-gray-800">{{ $template->name }}</h3>
                            @if($template->is_active)
                                <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded">Active</span>
                            @else
                                <span class="bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded">Inactive</span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-600 mb-3">Type: {{ ucfirst(str_replace('_',' ', $template->type ?? 'custom')) }}</p>
                        <div class="space-y-2 mb-3">
                            <div class="flex items-center text-xs text-gray-500">
                                <span class="material-icons text-xs mr-1">schedule</span>
                                Last updated: {{ optional($template->updated_at)->diffForHumans() ?? 'N/A' }}
                            </div>
                            <div class="flex items-center text-xs text-gray-500">
                                <span class="material-icons text-xs mr-1">send</span>
                                Times used: {{ $template->times_used ?? 0 }}
                            </div>
                        </div>
                        <div class="flex gap-2">
                            @can('pwa_templates.update')
                            <a href="{{ route('pwa.templates.edit', $template->id) }}" class="flex-1 bg-blue-500 text-white px-2 py-1 rounded text-xs font-medium text-center">Edit</a>
                            @endcan
                            <form method="POST" action="{{ route('pwa.templates.preview', $template->id) }}" onsubmit="event.preventDefault(); previewTemplate({{ $template->id }});">
                                @csrf
                                <button type="submit" class="flex-1 bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs font-medium">Preview</button>
                            </form>
                            @can('pwa_templates.delete')
                            <form method="POST" action="{{ route('pwa.templates.destroy', $template->id) }}" onsubmit="return confirm('Delete this template?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs" title="Delete">
                                    <span class="material-icons text-xs">delete</span>
                                </button>
                            </form>
                            @endcan
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 text-xs text-gray-500">No templates available.</div>
                @endforelse
            </div>

            <!-- Template Editor Section (bound to primaryTemplate) -->
            <div class="bg-white border border-gray-200 rounded-lg">
                @if(isset($primaryTemplate))
                <div class="p-4 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-800">Edit: {{ $primaryTemplate->name }}</h3>
                    <p class="text-xs text-gray-600">Quick edit for the selected template</p>
                </div>
                <form method="POST" action="{{ route('pwa.templates.update', $primaryTemplate->id) }}" class="p-4 space-y-4">
                    @csrf
                    @method('PUT')
                    <!-- Template Variables -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h4 class="text-xs font-semibold text-blue-800 mb-2">Available Variables</h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-xs">
                            <code class="bg-blue-100 text-blue-800 px-2 py-1 rounded">@{{name}}</code>
                            <code class="bg-blue-100 text-blue-800 px-2 py-1 rounded">@{{email}}</code>
                            <code class="bg-blue-100 text-blue-800 px-2 py-1 rounded">@{{password}}</code>
                            <code class="bg-blue-100 text-blue-800 px-2 py-1 rounded">@{{pwa_link}}</code>
                            <code class="bg-blue-100 text-blue-800 px-2 py-1 rounded">@{{event_name}}</code>
                            <code class="bg-blue-100 text-blue-800 px-2 py-1 rounded">@{{organization}}</code>
                            <code class="bg-blue-100 text-blue-800 px-2 py-1 rounded">@{{login_url}}</code>
                            <code class="bg-blue-100 text-blue-800 px-2 py-1 rounded">@{{support_email}}</code>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Email Subject</label>
                        <input type="text" name="subject" value="{{ old('subject', $primaryTemplate->subject) }}" class="w-full px-2 py-1 text-xs border border-gray-300 rounded" />
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Email Content</label>
                        <textarea name="content" rows="10" class="w-full px-2 py-1 text-xs border border-gray-300 rounded" placeholder="Use variables like @{{name}} @{{email}} @{{pwa_link}}">{{ old('content', $primaryTemplate->content) }}</textarea>
                    </div>

                    <div class="flex justify-between items-center">
                        <div class="flex gap-2">
                            <button type="submit" class="bg-indigo-500 hover:bg-indigo-600 text-white px-3 h-[36px] rounded text-xs font-medium flex items-center">Save Template</button>
                            <button type="button" onclick="previewTemplate({{ $primaryTemplate->id }})" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 h-[36px] rounded text-xs font-medium flex items-center">Preview Email</button>
                            <button type="button" onclick="openSendTestModal({{ $primaryTemplate->id }})" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 h-[36px] rounded text-xs font-medium flex items-center">Send Test</button>
                        </div>
                        <button type="button" onclick="resetTemplate({{ $primaryTemplate->id }})" class="text-red-600 text-xs font-medium">Reset to Default</button>
                    </div>
                </form>
                @else
                <div class="p-4">
                    <p class="text-xs text-gray-600">No templates available to edit.</p>
                </div>
                @endif
            </div>

            <!-- Email Statistics -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <h4 class="text-xs font-semibold text-gray-800 mb-3">Email Performance</h4>
                    <div class="space-y-3">
                        <div class="flex justify-between text-xs">
                            <span>Open Rate</span>
                            <span class="font-medium text-green-600">{{ $emailStats['open_rate'] ?? 0 }}%</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span>Click Rate</span>
                            <span class="font-medium text-blue-600">{{ $emailStats['click_rate'] ?? 0 }}%</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span>Bounce Rate</span>
                            <span class="font-medium text-red-600">{{ $emailStats['bounce_rate'] ?? 0 }}%</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <h4 class="text-xs font-semibold text-gray-800 mb-3">Recent Activity</h4>
                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between">
                            <span>Welcome emails sent</span>
                            <span class="font-medium">{{ $emailStats['welcome_emails'] ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Password resets</span>
                            <span class="font-medium">{{ $emailStats['password_resets'] ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Failed deliveries</span>
                            <span class="font-medium text-red-600">{{ $emailStats['failed_deliveries'] ?? 0 }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <h4 class="text-xs font-semibold text-gray-800 mb-3">Quick Actions</h4>
                    <div class="space-y-2">
                        @can('pwa_templates.update')
                        <form method="POST" action="{{ route('pwa.templates.bulk-email') }}">
                            @csrf
                            <button class="w-full bg-green-500 hover:bg-green-600 text-white px-2 h-[36px] rounded text-xs font-medium flex items-center justify-center">Send Welcome Email (Bulk)</button>
                        </form>
                        @endcan
                        @can('pwa_templates.export')
                        <a href="{{ route('pwa.templates.export') }}" class="w-full bg-blue-500 hover:bg-blue-600 text-white px-2 h-[36px] rounded text-xs font-medium flex items-center justify-center">Export Templates</a>
                        @endcan
                        @can('pwa_templates.update')
                        <form method="POST" action="{{ route('pwa.templates.bulk-email') }}">
                            @csrf
                            <button class="w-full bg-purple-500 hover:bg-purple-600 text-white px-2 h-[36px] rounded text-xs font-medium flex items-center justify-center">Bulk Email (Select Template)</button>
                        </form>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Preview Modal -->
    <div id="preview-modal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black bg-opacity-40" onclick="hidePreviewModal()"></div>
        <div class="relative max-w-3xl mx-auto my-12 bg-white rounded shadow-lg border border-gray-200 max-h-[85vh] flex flex-col">
            <div class="flex justify-between items-center px-4 py-3 border-b border-gray-200 flex-none">
                <h3 class="text-sm font-semibold text-gray-800">Email Preview</h3>
                <button class="text-gray-500 hover:text-gray-700" onclick="hidePreviewModal()" aria-label="Close">
                    <span class="material-icons text-base">close</span>
                </button>
            </div>
            <div class="px-4 py-3 flex-1 overflow-y-auto">
                <div class="mb-3">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Subject</label>
                    <div id="preview-subject" class="text-xs border border-gray-200 rounded px-2 py-2 bg-gray-50"></div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Content</label>
                    <div id="preview-content" class="prose prose-sm max-w-none border border-gray-200 rounded px-3 py-3"></div>
                </div>
            </div>
            <div class="px-4 py-3 border-t border-gray-200 flex justify-end flex-none">
                <button class="bg-indigo-500 text-white px-3 py-1 rounded text-xs font-medium" onclick="hidePreviewModal()">Close</button>
            </div>
        </div>
    </div>

    <!-- Send Test Modal -->
    <div id="send-test-modal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black bg-opacity-40" onclick="closeSendTestModal()"></div>
        <div class="relative max-w-md mx-auto my-24 bg-white rounded shadow-lg border border-gray-200">
            <div class="flex justify-between items-center px-4 py-3 border-b border-gray-200">
                <h3 class="text-sm font-semibold text-gray-800">Send Test Email</h3>
                <button class="text-gray-500 hover:text-gray-700" onclick="closeSendTestModal()" aria-label="Close">
                    <span class="material-icons text-base">close</span>
                </button>
            </div>
            <form id="send-test-form" method="POST" action="#" class="px-4 py-4">
                @csrf
                <label class="block text-xs font-medium text-gray-700 mb-1">Recipient Email</label>
                <input id="send-test-email" name="email_address" type="email" required placeholder="you@example.com" class="w-full px-2 py-1 text-xs border border-gray-300 rounded mb-4">
                <div class="flex justify-end gap-2">
                    <button type="button" class="bg-gray-100 text-gray-700 px-3 py-1 rounded text-xs" onclick="closeSendTestModal()">Cancel</button>
                    <button type="submit" class="bg-indigo-500 text-white px-3 py-1 rounded text-xs font-medium">Send</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showPreviewModal() {
            const modal = document.getElementById('preview-modal');
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        function hidePreviewModal() {
            const modal = document.getElementById('preview-modal');
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
        function previewTemplate(templateId) {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            fetch(`/pwa/templates/${templateId}/preview`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                }
            })
            .then(res => {
                if (!res.ok) throw new Error('Failed to preview template');
                return res.json();
            })
            .then(data => {
                document.getElementById('preview-subject').textContent = data.subject || '';
                document.getElementById('preview-content').innerHTML = data.content || '';
                showPreviewModal();
            })
            .catch(() => {
                alert('Preview failed. Please try again.');
            });
        }
        function postAction(url) {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            return fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': token } });
        }
        let currentSendTestTemplateId = null;
        function openSendTestModal(templateId){
            currentSendTestTemplateId = templateId;
            const form = document.getElementById('send-test-form');
            form.action = `/pwa/templates/${templateId}/send-test`;
            document.getElementById('send-test-modal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        function closeSendTestModal(){
            document.getElementById('send-test-modal').classList.add('hidden');
            document.body.style.overflow = '';
        }
        function resetTemplate(templateId) {
            if (!confirm('Reset to default?')) return;
            postAction(`/pwa/templates/${templateId}/reset-default`)
                .then(() => window.location.reload())
                .catch(() => alert('Failed to reset.'));
        }
        document.addEventListener('keydown', function(e){
            if (e.key === 'Escape') hidePreviewModal();
        });
    </script>
</x-app-layout> 