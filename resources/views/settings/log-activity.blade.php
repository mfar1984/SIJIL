<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Settings</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Log Activity</span>
    </x-slot>

    <x-slot name="title">Log Activity</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center">
                        <span class="material-icons mr-2 text-primary-DEFAULT">event_note</span>
                        <h1 class="text-xl font-bold text-gray-800">Log Activity</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-8">Monitor and track system activities</p>
                </div>
                <div class="flex space-x-2">
                    <button class="bg-gradient-to-r from-red-600 to-red-500 hover:from-red-700 hover:to-red-600 text-white px-3 py-1 rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        <span class="material-icons text-xs mr-1">delete_sweep</span>
                        Clear Logs
                    </button>
                    <button class="bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 text-white px-3 py-1 rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        <span class="material-icons text-xs mr-1">file_download</span>
                        Export Logs
                    </button>
                </div>
            </div>
        </div>
        
        <div class="p-4">
            <!-- Filters -->
            <div class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="search" class="block text-xs font-medium text-gray-700 mb-1">Search</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-icons text-[#004aad] text-base">search</span>
                        </div>
                        <input type="text" id="search" name="search" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" placeholder="Search logs...">
                    </div>
                </div>
                
                <div>
                    <label for="log_type" class="block text-xs font-medium text-gray-700 mb-1">Log Type</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-icons text-[#004aad] text-base">category</span>
                        </div>
                        <select id="log_type" name="log_type" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                            <option value="">All Types</option>
                            <option value="login">Login Activity</option>
                            <option value="data">Data Modification</option>
                            <option value="system">System Events</option>
                            <option value="error">Errors</option>
                        </select>
                    </div>
                </div>
                
                <div>
                    <label for="date_range" class="block text-xs font-medium text-gray-700 mb-1">Date Range</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-icons text-[#004aad] text-base">date_range</span>
                        </div>
                        <select id="date_range" name="date_range" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                            <option value="today">Today</option>
                            <option value="yesterday">Yesterday</option>
                            <option value="last7days">Last 7 Days</option>
                            <option value="last30days">Last 30 Days</option>
                            <option value="custom">Custom Range</option>
                        </select>
                    </div>
                </div>
                
                <div class="flex items-end">
                    <button type="button" class="bg-primary-DEFAULT hover:bg-primary-dark text-white px-4 py-2 rounded-md text-xs flex items-center">
                        <span class="material-icons text-xs mr-1">filter_list</span>
                        Apply Filter
                    </button>
                    <button type="button" class="ml-2 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-md text-xs flex items-center">
                        <span class="material-icons text-xs mr-1">refresh</span>
                        Reset
                    </button>
                </div>
            </div>
            
            <!-- Log Summary -->
            <div class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-blue-50 rounded-md p-4 border border-blue-100">
                    <p class="text-xs text-blue-700 font-medium">Total Logs</p>
                    <p class="text-2xl font-bold text-blue-800">2,547</p>
                    <p class="text-[10px] text-blue-600 mt-1">All recorded activities</p>
                </div>
                
                <div class="bg-green-50 rounded-md p-4 border border-green-100">
                    <p class="text-xs text-green-700 font-medium">User Activity</p>
                    <p class="text-2xl font-bold text-green-800">1,892</p>
                    <p class="text-[10px] text-green-600 mt-1">User-initiated actions</p>
                </div>
                
                <div class="bg-amber-50 rounded-md p-4 border border-amber-100">
                    <p class="text-xs text-amber-700 font-medium">System Events</p>
                    <p class="text-2xl font-bold text-amber-800">543</p>
                    <p class="text-[10px] text-amber-600 mt-1">Automated system operations</p>
                </div>
                
                <div class="bg-red-50 rounded-md p-4 border border-red-100">
                    <p class="text-xs text-red-700 font-medium">Errors</p>
                    <p class="text-2xl font-bold text-red-800">112</p>
                    <p class="text-[10px] text-red-600 mt-1">Errors and warnings</p>
                </div>
            </div>
            
            <!-- Logs Table -->
            <div class="overflow-visible border border-gray-200 rounded">
                <table class="min-w-full border-collapse">
                    <thead>
                        <tr class="bg-primary-light text-white text-xs uppercase">
                            <th class="py-3 px-4 text-left rounded-tl">ID</th>
                            <th class="py-3 px-4 text-left">Timestamp</th>
                            <th class="py-3 px-4 text-left">User</th>
                            <th class="py-3 px-4 text-left">IP Address</th>
                            <th class="py-3 px-4 text-left">Action</th>
                            <th class="py-3 px-4 text-left">Type</th>
                            <th class="py-3 px-4 text-left">Status</th>
                            <th class="py-3 px-4 text-center rounded-tr">Details</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr class="text-xs hover:bg-gray-50">
                            <td class="py-3 px-4 font-medium">#LOG-1001</td>
                            <td class="py-3 px-4">2023-06-15 09:32:45</td>
                            <td class="py-3 px-4">admin@example.com</td>
                            <td class="py-3 px-4">192.168.1.100</td>
                            <td class="py-3 px-4">User Login</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">Authentication</span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Success</span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex justify-center">
                                    <button class="p-1 bg-blue-50 rounded hover:bg-blue-100 border border-blue-100" title="View Details">
                                        <span class="material-icons text-primary-DEFAULT text-xs">visibility</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr class="text-xs hover:bg-gray-50">
                            <td class="py-3 px-4 font-medium">#LOG-1002</td>
                            <td class="py-3 px-4">2023-06-15 10:15:22</td>
                            <td class="py-3 px-4">admin@example.com</td>
                            <td class="py-3 px-4">192.168.1.100</td>
                            <td class="py-3 px-4">Create Event</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded-full text-xs">Data Modification</span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Success</span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex justify-center">
                                    <button class="p-1 bg-blue-50 rounded hover:bg-blue-100 border border-blue-100" title="View Details">
                                        <span class="material-icons text-primary-DEFAULT text-xs">visibility</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr class="text-xs hover:bg-gray-50">
                            <td class="py-3 px-4 font-medium">#LOG-1003</td>
                            <td class="py-3 px-4">2023-06-15 11:05:37</td>
                            <td class="py-3 px-4">system</td>
                            <td class="py-3 px-4">127.0.0.1</td>
                            <td class="py-3 px-4">Database Backup</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 bg-amber-100 text-amber-800 rounded-full text-xs">System</span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Success</span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex justify-center">
                                    <button class="p-1 bg-blue-50 rounded hover:bg-blue-100 border border-blue-100" title="View Details">
                                        <span class="material-icons text-primary-DEFAULT text-xs">visibility</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr class="text-xs hover:bg-gray-50">
                            <td class="py-3 px-4 font-medium">#LOG-1004</td>
                            <td class="py-3 px-4">2023-06-15 11:45:12</td>
                            <td class="py-3 px-4">organizer@example.com</td>
                            <td class="py-3 px-4">192.168.1.105</td>
                            <td class="py-3 px-4">Certificate Generation</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded-full text-xs">Data Modification</span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs">Failed</span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex justify-center">
                                    <button class="p-1 bg-blue-50 rounded hover:bg-blue-100 border border-blue-100" title="View Details">
                                        <span class="material-icons text-primary-DEFAULT text-xs">visibility</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="mb-2 sm:mb-0 text-xs text-gray-500">
                    Showing <span class="font-medium">1</span> to <span class="font-medium">4</span> of <span class="font-medium">2,547</span> entries
                </div>
                <div class="flex justify-end">
                    <div class="flex items-center space-x-1">
                        <a href="#" class="px-2 py-1 text-gray-500 hover:text-primary-DEFAULT rounded-none text-xs opacity-50 cursor-not-allowed">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M15.707 15.707a1 1 0 01-1.414 0l-5-5a1 1 0 010-1.414l5-5a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 010 1.414zm-6 0a1 1 0 01-1.414 0l-5-5a1 1 0 010-1.414l5-5a1 1 0 011.414 1.414L5.414 10l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" /></svg>
                        </a>
                        <a href="#" class="px-2 py-1 text-gray-500 hover:text-primary-DEFAULT rounded-none text-xs mr-2 opacity-50 cursor-not-allowed">
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                        </a>
                        <span class="w-6 h-6 flex items-center justify-center bg-primary-light text-white rounded-full shadow-sm text-xs font-medium">1</span>
                        <a href="#" class="px-2 py-1 text-gray-600 hover:text-primary-DEFAULT rounded-none text-xs font-medium">2</a>
                        <a href="#" class="px-2 py-1 text-gray-600 hover:text-primary-DEFAULT rounded-none text-xs font-medium">3</a>
                        <a href="#" class="px-2 py-1 text-gray-600 hover:text-primary-DEFAULT rounded-none text-xs font-medium">4</a>
                        <a href="#" class="px-2 py-1 text-gray-500 hover:text-primary-DEFAULT rounded-none text-xs ml-2">
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
                        </a>
                        <a href="#" class="px-2 py-1 text-gray-500 hover:text-primary-DEFAULT rounded-none text-xs">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414zM10 4.293a1 1 0 011.414 0l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414-1.414L14.586 10l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Log Detail Modal -->
    <div x-data="{ showModal: false, logDetails: {} }">
        <div
            x-show="showModal"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
            style="display: none;"
        >
            <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4 overflow-hidden" @click.away="showModal = false">
                <div class="px-6 py-4 bg-primary-light text-white flex items-center justify-between">
                    <h3 class="text-lg font-medium">Log Entry Details</h3>
                    <button @click="showModal = false" class="text-white hover:text-gray-200">
                        <span class="material-icons">close</span>
                    </button>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 gap-4">
                        <div class="border-b border-gray-200 pb-4">
                            <div class="flex justify-between">
                                <div>
                                    <p class="text-xs font-medium text-gray-500">Log ID</p>
                                    <p class="text-sm font-bold" x-text="logDetails.id || '#LOG-1001'"></p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500">Timestamp</p>
                                    <p class="text-sm" x-text="logDetails.timestamp || '2023-06-15 09:32:45'"></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs font-medium text-gray-500">User</p>
                                <p class="text-sm" x-text="logDetails.user || 'admin@example.com'"></p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500">IP Address</p>
                                <p class="text-sm" x-text="logDetails.ip || '192.168.1.100'"></p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500">Action</p>
                                <p class="text-sm" x-text="logDetails.action || 'User Login'"></p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500">Type</p>
                                <p class="text-sm">
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs" x-text="logDetails.type || 'Authentication'"></span>
                                </p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500">Status</p>
                                <p class="text-sm">
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs" x-text="logDetails.status || 'Success'"></span>
                                </p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500">User Agent</p>
                                <p class="text-sm" x-text="logDetails.userAgent || 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'"></p>
                            </div>
                        </div>
                        
                        <div>
                            <p class="text-xs font-medium text-gray-500">Description</p>
                            <p class="text-sm" x-text="logDetails.description || 'User successfully logged in to the system.'"></p>
                        </div>
                        
                        <div>
                            <p class="text-xs font-medium text-gray-500">Additional Data</p>
                            <pre class="text-xs bg-gray-50 p-3 rounded border border-gray-200 overflow-auto max-h-40" x-text="logDetails.data || '{\n  \"browser\": \"Chrome\",\n  \"os\": \"Windows\",\n  \"session_id\": \"sess_12345\",\n  \"login_method\": \"password\"\n}'"></pre>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end">
                    <button @click="showModal = false" class="bg-primary-DEFAULT hover:bg-primary-dark text-white px-4 py-2 rounded-md text-xs">
                        Close
                    </button>
                </div>
            </div>
        </div>
        
        <script>
            document.querySelectorAll('button[title="View Details"]').forEach(button => {
                button.addEventListener('click', function() {
                    let row = this.closest('tr');
                    let id = row.querySelector('td:first-child').textContent;
                    let timestamp = row.querySelector('td:nth-child(2)').textContent;
                    let user = row.querySelector('td:nth-child(3)').textContent;
                    let ip = row.querySelector('td:nth-child(4)').textContent;
                    let action = row.querySelector('td:nth-child(5)').textContent;
                    let type = row.querySelector('td:nth-child(6) span').textContent;
                    let status = row.querySelector('td:nth-child(7) span').textContent;
                    
                    // Set the data in Alpine.js store
                    let details = {
                        id: id,
                        timestamp: timestamp,
                        user: user,
                        ip: ip,
                        action: action,
                        type: type,
                        status: status
                    };
                    
                    // Open the modal with the data
                    let modal = document.querySelector('[x-data*="showModal"]').__x.$data;
                    modal.logDetails = details;
                    modal.showModal = true;
                });
            });
        </script>
    </div>
</x-app-layout> 