<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Survey</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>{{ $survey->title }}</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Responses</span>
    </x-slot>

    <x-slot name="title">Survey Responses</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center">
                        <span class="material-icons mr-2 text-primary-DEFAULT">format_list_bulleted</span>
                        <h1 class="text-xl font-bold text-gray-800">Responses for: {{ $survey->title }}</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-8">
                        {{ $responses->total() }} responses collected
                    </p>
                </div>
                <div class="flex space-x-3">
                    @can('survey_responses.read')
                    <a href="{{ route('survey.analytics', $survey) }}" class="bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white px-3 py-1 rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                        <span class="material-icons text-xs mr-1">insights</span>
                        View Analytics
                    </a>
                    @endcan
                    @can('survey_responses.export')
                    <button onclick="exportToCsv()" class="bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 text-white px-3 py-1 rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                        <span class="material-icons text-xs mr-1">file_download</span>
                        Export to CSV
                    </button>
                    @endcan
                    <a href="{{ route('survey.show', $survey) }}" class="bg-gradient-to-r from-gray-500 to-gray-400 hover:from-gray-600 hover:to-gray-500 text-white px-3 py-1 rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                        <span class="material-icons text-xs mr-1">arrow_back</span>
                        Back to Survey
                    </a>
                </div>
            </div>
        </div>
        
        <div class="p-4">
            @if($responses->isEmpty())
                <div class="bg-gray-50 border border-gray-200 rounded p-6 text-center">
                    <div class="flex justify-center">
                        <span class="material-icons text-gray-400 text-5xl">question_answer</span>
                    </div>
                    <h3 class="mt-2 text-gray-500 text-lg font-medium">No responses yet</h3>
                    <p class="mt-1 text-gray-400 text-sm">There are no responses to this survey yet.</p>
                </div>
            @else
                <!-- Show Entries, Search & Filter Row -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-2">
                    <form method="GET" action="{{ route('survey.responses', $survey) }}" class="flex flex-wrap gap-2 items-center justify-between w-full">
                        <!-- Show Entries Dropdown -->
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-600 font-medium">Show</span>
                            <select name="per_page" onchange="this.form.submit()" class="appearance-none px-2 py-1 text-xs border border-gray-300 rounded focus:ring focus:ring-primary-light focus:border-primary-light bg-white bg-no-repeat bg-right w-[60px] font-medium" style="background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23888%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><polyline points=%226 9 12 15 18 9%22></polyline></svg>'); background-position: right 0.25rem center; background-size: 0.75em;">
                                <option value="10" @if(request('per_page', 10) == 10) selected @endif>10</option>
                                <option value="25" @if(request('per_page') == 25) selected @endif>25</option>
                                <option value="50" @if(request('per_page') == 50) selected @endif>50</option>
                                <option value="100" @if(request('per_page') == 100) selected @endif>100</option>
                            </select>
                            <span class="text-xs text-gray-600">entries per page</span>
                        </div>
                        <!-- Search & Filter Controls -->
                        <div class="flex flex-wrap gap-2 items-center">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search respondent, email..." class="border border-gray-300 rounded px-2 py-1 text-xs focus:ring focus:ring-primary-light focus:border-primary-light" id="searchInput" />
                            <select name="status" onchange="this.form.submit()" class="appearance-none px-3 py-1.5 pr-8 text-xs border border-gray-300 rounded focus:ring focus:ring-primary-light focus:border-primary-light bg-white bg-no-repeat bg-right w-[120px]" style="background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23888%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><polyline points=%226 9 12 15 18 9%22></polyline></svg>'); background-position: right 0.75rem center; background-size: 1em;">
                                <option value="">All Status</option>
                                <option value="completed" @if(request('status') == 'completed') selected @endif>Completed</option>
                                <option value="incomplete" @if(request('status') == 'incomplete') selected @endif>Incomplete</option>
                            </select>
                            <select name="source" onchange="this.form.submit()" class="appearance-none px-3 py-1.5 pr-8 text-xs border border-gray-300 rounded focus:ring focus:ring-primary-light focus:border-primary-light bg-white bg-no-repeat bg-right w-[120px]" style="background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23888%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><polyline points=%226 9 12 15 18 9%22></polyline></svg>'); background-position: right 0.75rem center; background-size: 1em;">
                                <option value="">All Source</option>
                                <option value="user" @if(request('source') == 'user') selected @endif>Registered User</option>
                                <option value="participant" @if(request('source') == 'participant') selected @endif>Participant</option>
                                <option value="public" @if(request('source') == 'public') selected @endif>Public</option>
                            </select>
                            <button type="submit" class="bg-primary-light text-white px-3 py-1 h-[36px] rounded text-xs font-medium flex items-center justify-center" title="Search">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4-4m0 0A7 7 0 104 4a7 7 0 0013 13z" />
                                </svg>
                            </button>
                            @if(request('search') || request('status') || request('source'))
                                <a href="{{ route('survey.responses', $survey) }}?per_page={{ request('per_page', 10) }}" class="text-xs text-gray-500 underline ml-2">Reset</a>
                            @endif
                        </div>
                    </form>
                </div>
                <!-- Search Results Summary -->
                @if(request('search') || request('status') || request('source'))
                    <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-2 rounded mb-4 text-xs">
                        <span class="font-medium">Search Results:</span>
                        @if(request('search'))
                            <span class="ml-2">Searching for "{{ request('search') }}"</span>
                        @endif
                        @if(request('status'))
                            <span class="ml-2">Status: {{ ucfirst(request('status')) }}</span>
                        @endif
                        @if(request('source'))
                            <span class="ml-2">Source: {{ ucfirst(request('source')) }}</span>
                        @endif
                        <span class="ml-2">({{ $responses->total() }} results)</span>
                    </div>
                @endif
                
                <!-- Responses Table -->
                <div class="overflow-visible border border-gray-200 rounded mb-4">
                    <table class="min-w-full border-collapse">
                        <thead>
                            <tr class="bg-blue-600 text-white text-xs font-bold uppercase">
                                <th class="py-3 px-4 text-left rounded-tl">Respondent</th>
                                <th class="py-3 px-4 text-left">Submitted</th>
                                <th class="py-3 px-4 text-left">Source</th>
                                <th class="py-3 px-4 text-left">Time Taken</th>
                                <th class="py-3 px-4 text-center rounded-tr">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($responses as $response)
                                <tr class="text-xs hover:bg-gray-50">
                                    <td class="py-3 px-4">
                                        <div class="font-medium">{{ $response->respondent_display_name }}</div>
                                        @if($response->respondent_display_email)
                                            <div class="text-gray-500">{{ $response->respondent_display_email }}</div>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($response->completed_at)
                                            <div>{{ $response->completed_at->format('d M Y') }}</div>
                                            <div class="text-gray-500">{{ $response->completed_at->format('h:i A') }}</div>
                                        @else
                                            <span class="text-gray-400">Not completed</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($response->user_id)
                                            <span class="bg-purple-50 text-purple-700 px-2 py-1 rounded-full text-[10px] border border-purple-100">
                                                Registered User
                                            </span>
                                        @elseif($response->participant_id)
                                            <span class="bg-blue-50 text-blue-700 px-2 py-1 rounded-full text-[10px] border border-blue-100">
                                                Participant
                                            </span>
                                        @else
                                            <span class="bg-gray-50 text-gray-700 px-2 py-1 rounded-full text-[10px] border border-gray-200">
                                                Public
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($response->time_taken)
                                            @php
                                                $seconds = round($response->time_taken * 60); // time_taken in minutes (float)
                                                $minutes = floor($seconds / 60);
                                                $remainingSeconds = $seconds % 60;
                                            @endphp
                                            @if($minutes > 0)
                                                {{ $minutes }} minute{{ $minutes > 1 ? 's' : '' }}@if($remainingSeconds > 0) {{ $remainingSeconds }} seconds @endif
                                            @else
                                                {{ $remainingSeconds }} seconds
                                            @endif
                                        @else
                                            <span class="text-gray-400">N/A</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="flex justify-center space-x-2">
                                            <button onclick="viewResponse({{ $response->id }})" class="p-1 bg-blue-50 rounded hover:bg-blue-100 border border-blue-100" title="View Details">
                                                <span class="material-icons text-primary-DEFAULT text-xs">visibility</span>
                                            </button>
                                            
                                            <form action="{{ route('survey.responses.destroy', [$survey, $response]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this response?');" class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1 bg-red-50 rounded hover:bg-red-100 border border-red-100" title="Delete Response">
                                                    <span class="material-icons text-red-600 text-xs">delete</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-xs text-gray-400">No responses found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination Row -->
                <div class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div class="mb-2 sm:mb-0 text-xs text-gray-500">
                        Showing {{ $responses->firstItem() ?? 0 }} to {{ $responses->lastItem() ?? 0 }} of {{ $responses->total() }} entries
                        @if($responses->total() > 0)
                            ({{ request('per_page', 10) }} per page)
                        @endif
                    </div>
                    <div class="flex justify-end">
                        {{ $responses->appends(request()->query())->links('components.pagination-modern') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
    
    <!-- Response Details Modal -->
    <div id="response-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto font-sans text-gray-700">
            <div class="p-4 border-b border-gray-200 flex justify-between items-center sticky top-0 bg-white">
                <h3 class="text-lg font-medium font-sans text-gray-800" id="modal-title">Response Details</h3>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                    <span class="material-icons">close</span>
                </button>
            </div>
            
            <div class="p-4 font-sans text-gray-700 text-sm" id="modal-content">
                <div class="flex items-center justify-center h-40">
                    <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary-DEFAULT"></div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function viewResponse(responseId) {
            // Show modal and loading indicator
            document.getElementById('response-modal').classList.remove('hidden');
            document.getElementById('modal-content').innerHTML = `
                <div class="flex items-center justify-center h-40">
                    <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary-DEFAULT"></div>
                </div>
            `;
            
            // Fetch response details via AJAX
            fetch(`/survey/{{ $survey->id }}/responses/${responseId}`)
                .then(response => response.json())
                .then(data => {
                    const modalContent = document.getElementById('modal-content');
                    
                    // Build the response details HTML
                    let html = `
                        <div class="bg-gray-50 border border-gray-200 p-4 rounded mb-4 font-sans text-gray-700 text-sm">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs font-medium text-gray-500 font-sans">Respondent</p>
                                    <p class="text-sm font-sans text-gray-800">
                                        ${data.response.respondent_display_name || 'N/A'}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 font-sans">Email</p>
                                    <p class="text-sm font-sans text-gray-800">
                                        ${data.response.respondent_display_email || 'N/A'}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 font-sans">Submitted On</p>
                                    <p class="text-sm font-sans text-gray-800">
                                        ${data.response.completed_at || 'Not completed'}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 font-sans">IP Address</p>
                                    <p class="text-sm font-sans text-gray-800">
                                        ${data.response.ip_address || 'N/A'}
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-center mb-3 font-sans">
                            <span class="material-icons mr-2 text-primary-DEFAULT">question_answer</span>
                            <h4 class="font-medium text-sm font-sans text-gray-800">Responses</h4>
                        </div>
                    `;
                    
                    // Add each question and answer
                    data.questions.forEach((question, index) => {
                        const answer = data.response.response_data[question.id] || 'No response';
                        
                        html += `
                            <div class="mb-4 pb-4 border-b border-gray-200">
                                <p class="font-medium mb-1 text-xs">
                                    ${index + 1}. ${question.question_text}
                                    ${question.required ? '<span class="text-red-500">*</span>' : ''}
                                </p>
                                
                                ${question.description ? `<p class="text-[10px] text-gray-500 mb-2">${question.description}</p>` : ''}
                                
                                <div class="mt-2 bg-gray-50 p-3 rounded border border-gray-200">
                        `;
                        
                        // Format answer based on question type
                        if (question.question_type === 'checkbox' && Array.isArray(answer)) {
                            html += `<ul class="list-disc list-inside text-xs">`;
                            answer.forEach(item => {
                                html += `<li>${item}</li>`;
                            });
                            html += `</ul>`;
                        } else if (question.question_type === 'rating') {
                            const starRating = parseInt(answer);
                            html += `<div class="flex items-center">`;
                            for (let i = 1; i <= 5; i++) {
                                if (i <= starRating) {
                                    html += `<span class="material-icons text-yellow-500">star</span>`;
                                } else {
                                    html += `<span class="material-icons text-gray-300">star_outline</span>`;
                                }
                            }
                            html += ` <span class="ml-2 text-xs">(${answer}/5)</span>`;
                            html += `</div>`;
                        } else if (answer === 'No response') {
                            html += `<span class="text-gray-400 text-xs">${answer}</span>`;
                        } else {
                            html += `<p class="text-xs">${answer}</p>`;
                        }
                        
                        html += `
                                </div>
                            </div>
                        `;
                    });
                    
                    modalContent.innerHTML = html;
                })
                .catch(error => {
                    document.getElementById('modal-content').innerHTML = `
                        <div class="bg-red-50 text-red-800 p-4 rounded border border-red-200">
                            <p class="text-xs">Error loading response data.</p>
                        </div>
                    `;
                    console.error('Error:', error);
                });
        }
        
        function closeModal() {
            document.getElementById('response-modal').classList.add('hidden');
        }
        
        // Close modal when clicking outside
        document.getElementById('response-modal').addEventListener('click', function(event) {
            if (event.target === this) {
                closeModal();
            }
        });
        
        function exportToCsv() {
            window.location.href = "{{ route('survey.responses.export', $survey) }}";
        }
    </script>
</x-app-layout>
