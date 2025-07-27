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
                    <a href="{{ route('survey.analytics', $survey) }}" class="bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white px-3 py-1 rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                        <span class="material-icons text-xs mr-1">insights</span>
                        View Analytics
                    </a>
                    <button onclick="exportToCsv()" class="bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 text-white px-3 py-1 rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                        <span class="material-icons text-xs mr-1">file_download</span>
                        Export to CSV
                    </button>
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
                <!-- Filters and sorting -->
                <div class="mb-4 bg-gray-50 border border-gray-200 rounded p-4 flex flex-wrap gap-4 items-center">
                    <div>
                        <label for="filter-date" class="block text-xs font-medium text-gray-700 mb-1">Date Range</label>
                        <select id="filter-date" class="block w-full rounded-[1px] border-gray-300 shadow-sm focus:border-primary-DEFAULT focus:ring focus:ring-primary-DEFAULT focus:ring-opacity-50 text-xs">
                            <option value="all">All Time</option>
                            <option value="today">Today</option>
                            <option value="week">This Week</option>
                            <option value="month">This Month</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="filter-status" class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                        <select id="filter-status" class="block w-full rounded-[1px] border-gray-300 shadow-sm focus:border-primary-DEFAULT focus:ring focus:ring-primary-DEFAULT focus:ring-opacity-50 text-xs">
                            <option value="all">All Responses</option>
                            <option value="completed">Completed</option>
                            <option value="incomplete">Incomplete</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="search-response" class="block text-xs font-medium text-gray-700 mb-1">Search</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-gray-400 text-xs">search</span>
                            </div>
                            <input type="text" id="search-response" placeholder="Search responses..." class="pl-10 block w-full rounded-[1px] border-gray-300 shadow-sm focus:border-primary-DEFAULT focus:ring focus:ring-primary-DEFAULT focus:ring-opacity-50 text-xs">
                        </div>
                    </div>
                </div>
                
                <!-- Responses Table -->
                <div class="overflow-visible border border-gray-200 rounded mb-4">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-gradient-to-r from-primary-600 to-primary-500 text-white text-xs uppercase">
                                <th class="py-3 px-4 text-left rounded-tl">Respondent</th>
                                <th class="py-3 px-4 text-left">Submitted</th>
                                <th class="py-3 px-4 text-left">Source</th>
                                <th class="py-3 px-4 text-left">Time Taken</th>
                                <th class="py-3 px-4 text-center rounded-tr">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach($responses as $response)
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
                                            {{ $response->time_taken }} min
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
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="mt-4">
                    {{ $responses->links() }}
                </div>
            @endif
        </div>
    </div>
    
    <!-- Response Details Modal -->
    <div id="response-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-4 border-b border-gray-200 flex justify-between items-center sticky top-0 bg-white">
                <h3 class="text-lg font-medium" id="modal-title">Response Details</h3>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                    <span class="material-icons">close</span>
                </button>
            </div>
            
            <div class="p-4" id="modal-content">
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
                        <div class="bg-gray-50 border border-gray-200 p-4 rounded mb-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs font-medium text-gray-500">Respondent</p>
                                    <p class="text-sm">
                                        ${data.response.respondent_display_name || 'N/A'}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500">Email</p>
                                    <p class="text-sm">
                                        ${data.response.respondent_display_email || 'N/A'}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500">Submitted On</p>
                                    <p class="text-sm">
                                        ${data.response.completed_at || 'Not completed'}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500">IP Address</p>
                                    <p class="text-sm">
                                        ${data.response.ip_address || 'N/A'}
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-center mb-3">
                            <span class="material-icons mr-2 text-primary-DEFAULT">question_answer</span>
                            <h4 class="font-medium text-sm">Responses</h4>
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
