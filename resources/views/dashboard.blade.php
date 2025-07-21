<x-app-layout>
    <!-- Header removed as it's redundant with breadcrumb -->

    <x-slot name="breadcrumb">
        <span>Dashboard</span>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-card class="bg-gradient-to-br from-blue-500 to-blue-600 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-white/80 text-sm">Total Events</p>
                    <h3 class="text-2xl font-semibold mt-1">24</h3>
                </div>
                <span class="material-icons text-3xl text-white/70">event</span>
            </div>
            <div class="mt-4 text-xs text-white/80 flex items-center">
                <span class="material-icons text-xs mr-1">trending_up</span>
                <span>12% increase from last month</span>
            </div>
        </x-card>

        <x-card class="bg-gradient-to-br from-green-500 to-green-600 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-white/80 text-sm">Total Participants</p>
                    <h3 class="text-2xl font-semibold mt-1">1,254</h3>
                </div>
                <span class="material-icons text-3xl text-white/70">groups</span>
            </div>
            <div class="mt-4 text-xs text-white/80 flex items-center">
                <span class="material-icons text-xs mr-1">trending_up</span>
                <span>8% increase from last month</span>
            </div>
        </x-card>

        <x-card class="bg-gradient-to-br from-amber-500 to-amber-600 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-white/80 text-sm">Certificates Issued</p>
                    <h3 class="text-2xl font-semibold mt-1">957</h3>
                </div>
                <span class="material-icons text-3xl text-white/70">card_membership</span>
            </div>
            <div class="mt-4 text-xs text-white/80 flex items-center">
                <span class="material-icons text-xs mr-1">trending_up</span>
                <span>15% increase from last month</span>
            </div>
        </x-card>

        <x-card class="bg-gradient-to-br from-purple-500 to-purple-600 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-white/80 text-sm">Active Campaigns</p>
                    <h3 class="text-2xl font-semibold mt-1">6</h3>
                </div>
                <span class="material-icons text-3xl text-white/70">campaign</span>
            </div>
            <div class="mt-4 text-xs text-white/80 flex items-center">
                <span class="material-icons text-xs mr-1">trending_up</span>
                <span>2 new campaigns this month</span>
            </div>
        </x-card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <x-card>
                <x-slot name="header">
                    <div class="flex justify-between items-center">
                        <h3 class="font-medium">Recent Events</h3>
                        <a href="#" class="text-sm text-primary-light hover:underline">View All</a>
                    </div>
                </x-slot>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-[#5170ff] text-white">
                            <tr>
                                <x-table.heading>Event Name</x-table.heading>
                                <x-table.heading>Date</x-table.heading>
                                <x-table.heading>Participants</x-table.heading>
                                <x-table.heading>Status</x-table.heading>
                                <x-table.heading>Actions</x-table.heading>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 text-sm">
                            <x-table.row>
                                <x-table.cell>Annual Conference 2023</x-table.cell>
                                <x-table.cell>12 Jun 2023</x-table.cell>
                                <x-table.cell>245</x-table.cell>
                                <x-table.cell>
                                    <x-badge type="completed">Completed</x-badge>
                                </x-table.cell>
                                <x-table.cell>
                                    <div class="flex space-x-1">
                                        <x-action-button type="view" title="View"></x-action-button>
                                        <x-action-button type="edit" title="Edit"></x-action-button>
                                    </div>
                                </x-table.cell>
                            </x-table.row>
                            <x-table.row :even="true">
                                <x-table.cell>Workshop Series</x-table.cell>
                                <x-table.cell>23 Jun 2023</x-table.cell>
                                <x-table.cell>78</x-table.cell>
                                <x-table.cell>
                                    <x-badge type="active">Active</x-badge>
                                </x-table.cell>
                                <x-table.cell>
                                    <div class="flex space-x-1">
                                        <x-action-button type="view" title="View"></x-action-button>
                                        <x-action-button type="edit" title="Edit"></x-action-button>
                                    </div>
                                </x-table.cell>
                            </x-table.row>
                            <x-table.row>
                                <x-table.cell>Training Program</x-table.cell>
                                <x-table.cell>30 Jun 2023</x-table.cell>
                                <x-table.cell>120</x-table.cell>
                                <x-table.cell>
                                    <x-badge type="pending">Pending</x-badge>
                                </x-table.cell>
                                <x-table.cell>
                                    <div class="flex space-x-1">
                                        <x-action-button type="view" title="View"></x-action-button>
                                        <x-action-button type="edit" title="Edit"></x-action-button>
                                    </div>
                                </x-table.cell>
                            </x-table.row>
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
        
        <div class="lg:col-span-1">
            <x-card>
                <x-slot name="header">
                    <h3 class="font-medium">Quick Actions</h3>
                </x-slot>
                
                <div class="space-y-2">
                    <a href="#" class="block w-full bg-white border border-gray-200 rounded-md p-3 hover:bg-gray-50 transition-colors group">
                        <div class="flex items-center">
                            <div class="mr-3 p-2 bg-blue-100 rounded-md text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                <span class="material-icons">add_circle</span>
                            </div>
                            <div>
                                <h4 class="font-medium">Create New Event</h4>
                                <p class="text-xs text-gray-500">Set up a new event or workshop</p>
                            </div>
                        </div>
                    </a>
                    
                    <a href="#" class="block w-full bg-white border border-gray-200 rounded-md p-3 hover:bg-gray-50 transition-colors group">
                        <div class="flex items-center">
                            <div class="mr-3 p-2 bg-green-100 rounded-md text-green-600 group-hover:bg-green-600 group-hover:text-white transition-colors">
                                <span class="material-icons">card_membership</span>
                            </div>
                            <div>
                                <h4 class="font-medium">Generate Certificates</h4>
                                <p class="text-xs text-gray-500">Issue certificates for participants</p>
                            </div>
                        </div>
                    </a>
                    
                    <a href="#" class="block w-full bg-white border border-gray-200 rounded-md p-3 hover:bg-gray-50 transition-colors group">
                        <div class="flex items-center">
                            <div class="mr-3 p-2 bg-amber-100 rounded-md text-amber-600 group-hover:bg-amber-600 group-hover:text-white transition-colors">
                                <span class="material-icons">campaign</span>
                            </div>
                            <div>
                                <h4 class="font-medium">New Campaign</h4>
                                <p class="text-xs text-gray-500">Create an email or SMS campaign</p>
                            </div>
                        </div>
                    </a>
                    
                    <a href="#" class="block w-full bg-white border border-gray-200 rounded-md p-3 hover:bg-gray-50 transition-colors group">
                        <div class="flex items-center">
                            <div class="mr-3 p-2 bg-purple-100 rounded-md text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition-colors">
                                <span class="material-icons">summarize</span>
                            </div>
                            <div>
                                <h4 class="font-medium">Generate Reports</h4>
                                <p class="text-xs text-gray-500">Access attendance and event reports</p>
                            </div>
                        </div>
                    </a>
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>
