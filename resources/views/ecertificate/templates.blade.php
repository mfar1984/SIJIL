<x-app-layout>
    <x-slot name="breadcrumb">
        <span>PWA Management</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Email Templates</span>
    </x-slot>

    <x-slot name="title">PWA Email Templates</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center">
                        <span class="material-icons-outlined mr-2 text-primary-DEFAULT">email</span>
                        <h1 class="text-xl font-bold text-gray-800">PWA Email Templates</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-8">
                        The emails sent to mobile app users: their welcome message and password resets
                    </p>
                </div>
                <div class="flex gap-2">
                    @can('pwa_templates.export')
                    <a href="{{ route('pwa.templates.export') }}" class="h-9 px-3 border border-gray-300 rounded text-xs text-gray-700 hover:bg-gray-50 flex items-center shrink-0">
                        <span class="material-icons-outlined text-xs mr-1">download</span>
                        Export CSV
                    </a>
                    @endcan
                    @can('pwa_templates.create')
                    <a href="{{ route('pwa.templates.create') }}" class="bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        <span class="material-icons-outlined text-xs mr-1">add_circle</span>
                        New Template
                    </a>
                    @endcan
                </div>
            </div>
        </div>

        <div class="p-4">
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-2 rounded mb-4 text-xs">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-2 rounded mb-4 text-xs">{{ session('error') }}</div>
            @endif

            {{-- Only counts that are actually recorded are shown here. Open, click
                 and bounce rates were displayed before but nothing in the system
                 ever records those events, so they were permanently zero. --}}
            <div class="mb-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-blue-50 rounded-md p-4 border border-blue-100">
                    <p class="text-xs text-blue-700 font-medium">Emails sent</p>
                    <p class="text-2xl font-bold text-blue-800">{{ $emailStats['total_sent'] ?? 0 }}</p>
                </div>
                <div class="bg-green-50 rounded-md p-4 border border-green-100">
                    <p class="text-xs text-green-700 font-medium">Welcome emails</p>
                    <p class="text-2xl font-bold text-green-800">{{ $emailStats['welcome_emails'] ?? 0 }}</p>
                </div>
                <div class="bg-amber-50 rounded-md p-4 border border-amber-100">
                    <p class="text-xs text-amber-700 font-medium">Password resets</p>
                    <p class="text-2xl font-bold text-amber-800">{{ $emailStats['password_resets'] ?? 0 }}</p>
                </div>
            </div>

            <!-- Templates -->
            <div class="overflow-visible border border-gray-200 rounded">
                <table class="min-w-full border-collapse">
                    <thead>
                        <tr class="bg-primary-light text-white text-xs uppercase">
                            <th class="py-3 px-4 text-left rounded-tl">Template</th>
                            <th class="py-3 px-4 text-left">Sent when</th>
                            <th class="py-3 px-4 text-left">Subject</th>
                            <th class="py-3 px-4 text-left">Times sent</th>
                            <th class="py-3 px-4 text-left">Updated</th>
                            <th class="py-3 px-4 text-left">Status</th>
                            <th class="py-3 px-4 text-center rounded-tr">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($templates as $template)
                            @php
                                // Explains in plain words what triggers each email.
                                $trigger = match($template->type) {
                                    'welcome' => 'A new mobile app account is created',
                                    'password_reset' => 'Someone resets a password',
                                    'event_reminder' => 'Sent manually before an event',
                                    default => 'Sent manually',
                                };
                            @endphp
                            <tr class="text-xs hover:bg-gray-50">
                                <td class="py-3 px-4 font-medium">
                                    {{ $template->name }}
                                    <span class="block text-gray-400 font-normal">{{ $template->scope === 'global' ? 'Used by everyone' : 'Yours only' }}</span>
                                </td>
                                <td class="py-3 px-4 text-gray-600">{{ $trigger }}</td>
                                <td class="py-3 px-4 text-gray-600">{{ Str::limit($template->subject, 45) ?: '—' }}</td>
                                <td class="py-3 px-4">{{ $template->times_used ?? 0 }}</td>
                                <td class="py-3 px-4 text-gray-500">{{ optional($template->updated_at)->diffForHumans() ?? '—' }}</td>
                                <td class="py-3 px-4">
                                    @if($template->is_active)
                                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Active</span>
                                    @else
                                        <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded-full text-xs">Inactive</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex justify-center space-x-2">
                                        <button type="button" onclick="previewTemplate({{ $template->id }})" class="p-1 bg-blue-50 rounded hover:bg-blue-100 border border-blue-100" title="Preview">
                                            <span class="material-icons-outlined text-primary-DEFAULT text-xs">visibility</span>
                                        </button>
                                        @can('pwa_templates.update')
                                        <a href="{{ route('pwa.templates.edit', $template->id) }}" class="p-1 bg-yellow-50 rounded hover:bg-yellow-100 border border-yellow-100" title="Edit">
                                            <span class="material-icons-outlined text-yellow-600 text-xs">edit</span>
                                        </a>
                                        <button type="button" onclick="openSendTestModal({{ $template->id }})" class="p-1 bg-green-50 rounded hover:bg-green-100 border border-green-100" title="Send a test to yourself">
                                            <span class="material-icons-outlined text-green-600 text-xs">send</span>
                                        </button>
                                        @endcan
                                        @can('pwa_templates.delete')
                                        <form method="POST" action="{{ route('pwa.templates.destroy', $template->id) }}" onsubmit="return confirm('Delete this template? Emails of this type will fall back to the built-in wording.')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1 bg-red-50 rounded hover:bg-red-100 border border-red-100" title="Delete">
                                                <span class="material-icons-outlined text-red-600 text-xs">delete</span>
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="text-xs">
                                <td colspan="7" class="py-8 px-4 text-center text-gray-500">
                                    No email templates yet. They are created automatically the first time this page loads.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Quick edit -->
            @if(isset($primaryTemplate))
            <div class="mt-6 border border-gray-200 rounded">
                <div class="bg-gray-50 border-b border-gray-200 px-4 py-3 flex flex-wrap items-center justify-between gap-2">
                    <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                        <span class="material-icons-outlined text-primary-DEFAULT mr-2">edit_note</span>
                        Quick edit: {{ $primaryTemplate->name }}
                    </h2>
                    <p class="text-xs text-gray-500">Use the pencil icon above to edit any other template</p>
                </div>

                <form method="POST" action="{{ route('pwa.templates.update', $primaryTemplate->id) }}" class="p-4 space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="flex flex-col md:flex-row md:items-start gap-3">
                        <label for="subject" class="text-xs font-medium text-gray-700 md:w-48 shrink-0 md:pt-2">
                            Email subject
                        </label>
                        <div class="flex-1">
                            <input type="text" name="subject" id="subject" value="{{ old('subject', $primaryTemplate->subject) }}"
                                   class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                        </div>
                    </div>

                    <div class="flex flex-col md:flex-row md:items-start gap-3">
                        <label for="content" class="text-xs font-medium text-gray-700 md:w-48 shrink-0 md:pt-2">
                            Email content
                        </label>
                        <div class="flex-1">
                            <textarea name="content" id="content" rows="12"
                                      class="w-full text-xs border-gray-300 rounded px-3 py-2 font-mono focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">{{ old('content', $primaryTemplate->content) }}</textarea>
                            <p class="text-xs text-gray-500 mt-1">HTML is allowed. Use the placeholders below.</p>
                        </div>
                    </div>

                    <div class="flex flex-col md:flex-row md:items-start gap-3">
                        <span class="text-xs font-medium text-gray-700 md:w-48 shrink-0 md:pt-1">Placeholders</span>
                        <div class="flex-1">
                            <div class="flex flex-wrap gap-2 text-xs">
                                @foreach(['name', 'email', 'password', 'pwa_link', 'login_url', 'event_name', 'organization', 'support_email'] as $variable)
                                    <code class="bg-gray-100 text-gray-700 px-2 py-1 rounded">@{{{{ $variable }}}}</code>
                                @endforeach
                            </div>
                            <p class="text-xs text-gray-500 mt-2">
                                Each one is swapped for the real value when the email is sent.
                                <span class="font-medium">@{{password}}</span> only works in the welcome and password reset emails.
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-col md:flex-row gap-3 pt-2 border-t border-gray-200">
                        <span class="md:w-48 shrink-0"></span>
                        <div class="flex-1 flex flex-wrap items-center gap-2">
                            <button type="submit" class="h-9 px-3 bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 text-white rounded shadow-sm text-xs font-medium flex items-center shrink-0">
                                <span class="material-icons-outlined text-xs mr-1">save</span>
                                Save template
                            </button>
                            <button type="button" onclick="previewTemplate({{ $primaryTemplate->id }})" class="h-9 px-3 border border-gray-300 rounded text-xs text-gray-700 hover:bg-gray-50 flex items-center shrink-0">
                                <span class="material-icons-outlined text-xs mr-1">visibility</span>
                                Preview
                            </button>
                            <button type="button" onclick="openSendTestModal({{ $primaryTemplate->id }})" class="h-9 px-3 border border-gray-300 rounded text-xs text-gray-700 hover:bg-gray-50 flex items-center shrink-0">
                                <span class="material-icons-outlined text-xs mr-1">send</span>
                                Send test
                            </button>
                            <button type="button" onclick="resetTemplate({{ $primaryTemplate->id }})" class="text-xs text-red-600 underline shrink-0">
                                Reset to default wording
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            @endif
        </div>
    </div>

    <!-- Preview Modal -->
    <div id="preview-modal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 modal-backdrop-glass" onclick="hidePreviewModal()"></div>
        <div class="relative max-w-3xl mx-auto my-12 bg-white rounded shadow-lg border border-gray-200 max-h-[85vh] flex flex-col">
            <div class="flex justify-between items-center px-4 py-3 border-b border-gray-200 flex-none">
                <h3 class="text-sm font-semibold text-gray-800">Email preview</h3>
                <button class="text-gray-500 hover:text-gray-700" onclick="hidePreviewModal()" aria-label="Close">
                    <span class="material-icons-outlined text-base">close</span>
                </button>
            </div>
            <div class="px-4 py-3 flex-1 overflow-y-auto">
                <div class="mb-3">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Subject</label>
                    <div id="preview-subject" class="text-xs border border-gray-200 rounded px-3 py-2 bg-gray-50"></div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Content</label>
                    <div id="preview-content" class="prose prose-sm max-w-none border border-gray-200 rounded px-3 py-3"></div>
                </div>
            </div>
            <div class="px-4 py-3 border-t border-gray-200 flex justify-end flex-none">
                <button class="h-9 px-3 border border-gray-300 rounded text-xs text-gray-700 hover:bg-gray-50" onclick="hidePreviewModal()">Close</button>
            </div>
        </div>
    </div>

    <!-- Send Test Modal -->
    <div id="send-test-modal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 modal-backdrop-glass" onclick="closeSendTestModal()"></div>
        <div class="relative max-w-md mx-auto my-24 bg-white rounded shadow-lg border border-gray-200">
            <div class="flex justify-between items-center px-4 py-3 border-b border-gray-200">
                <h3 class="text-sm font-semibold text-gray-800">Send a test email</h3>
                <button class="text-gray-500 hover:text-gray-700" onclick="closeSendTestModal()" aria-label="Close">
                    <span class="material-icons-outlined text-base">close</span>
                </button>
            </div>
            <form id="send-test-form" method="POST" action="#" class="px-4 py-4">
                @csrf
                <label for="send-test-email" class="block text-xs font-medium text-gray-700 mb-1">Send to</label>
                <input id="send-test-email" name="email_address" type="email" required placeholder="you@example.com"
                       class="w-full h-9 text-xs border-gray-300 rounded px-3 mb-2 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                <p class="text-xs text-gray-500 mb-4">
                    Placeholders are filled with sample values so you can check the wording.
                </p>
                <div class="flex justify-end gap-2">
                    <button type="button" class="h-9 px-3 border border-gray-300 rounded text-xs text-gray-700 hover:bg-gray-50" onclick="closeSendTestModal()">Cancel</button>
                    <button type="submit" class="h-9 px-3 bg-primary-DEFAULT hover:bg-primary-dark text-white rounded text-xs font-medium">Send test</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showPreviewModal() {
            document.getElementById('preview-modal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        function hidePreviewModal() {
            document.getElementById('preview-modal').classList.add('hidden');
            document.body.style.overflow = '';
        }
        function previewTemplate(templateId) {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            fetch(`/pwa/templates/${templateId}/preview`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }
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
            .catch(() => alert('Preview failed. Please try again.'));
        }
        function openSendTestModal(templateId) {
            document.getElementById('send-test-form').action = `/pwa/templates/${templateId}/send-test`;
            document.getElementById('send-test-modal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        function closeSendTestModal() {
            document.getElementById('send-test-modal').classList.add('hidden');
            document.body.style.overflow = '';
        }
        function resetTemplate(templateId) {
            if (!confirm('Replace this template with the built-in default wording?')) return;
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            fetch(`/pwa/templates/${templateId}/reset-default`, { method: 'POST', headers: { 'X-CSRF-TOKEN': token } })
                .then(() => window.location.reload())
                .catch(() => alert('Failed to reset.'));
        }
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') { hidePreviewModal(); closeSendTestModal(); }
        });
    </script>
</x-app-layout>
