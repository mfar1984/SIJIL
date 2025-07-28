<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Profile</span>
    </x-slot>
    <x-slot name="title">Edit Profile</x-slot>
    <div class="bg-white rounded shadow-md border border-gray-300 w-full mx-auto">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
                <span class="material-icons mr-2 text-primary-DEFAULT">person</span>
                <h1 class="text-xl font-bold text-gray-800">Edit Profile</h1>
            </div>
            <p class="text-xs text-gray-500 mt-1 ml-8">Update your personal and organization information</p>
        </div>
        <div class="p-6">
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li class="text-xs">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form method="POST" action="{{ route('profile.update') }}" class="space-y-6" enctype="multipart/form-data">
                @csrf
                @method('patch')
                <!-- Profile Image Upload -->
                <div class="flex items-center mb-6">
                    <div class="mr-4">
                        @if($user->profile_image ?? false)
                            <img src="{{ asset('storage/' . $user->profile_image) }}" alt="Profile Image" class="w-20 h-20 rounded-full object-cover border border-gray-300" />
                        @else
                            <span class="material-icons text-gray-300 w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center text-5xl">person</span>
                        @endif
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Profile Image</label>
                        <input type="file" name="profile_image" accept="image/*" class="block text-xs text-gray-500" />
                        <p class="text-[10px] text-gray-400 mt-1">JPG, PNG, GIF. Max 2MB.</p>
                    </div>
                </div>
                <!-- Basic Information -->
                <div class="border-b border-gray-200 pb-5">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Basic Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <!-- Name -->
                        <div>
                            <label for="name" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">person</span>
                                Name
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">badge</span>
                                </div>
                                <input type="text" name="name" id="name" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('name', $user->name) }}" required>
                            </div>
                        </div>
                        <!-- Email -->
                        <div>
                            <label for="email" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">email</span>
                                Email Address
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">alternate_email</span>
                                </div>
                                <input type="email" name="email" id="email" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('email', $user->email) }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <!-- Phone -->
                        <div>
                            <label for="phone" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">phone</span>
                                Phone Number
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">call</span>
                                </div>
                                <input type="tel" name="phone" id="phone" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('phone', $user->phone) }}" placeholder="+60123456789">
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Address Information -->
                <div class="border-b border-gray-200 pb-5">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Address Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label for="address_line1" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">home</span>
                                Address Line 1
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">location_on</span>
                                </div>
                                <input type="text" name="address_line1" id="address_line1" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('address_line1', $user->address_line1) }}">
                            </div>
                        </div>
                        <div class="md:col-span-2">
                            <label for="address_line2" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">apartment</span>
                                Address Line 2
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">pin_drop</span>
                                </div>
                                <input type="text" name="address_line2" id="address_line2" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('address_line2', $user->address_line2) }}">
                            </div>
                        </div>
                        <div>
                            <label for="state" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">map</span>
                                State
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">location_city</span>
                                </div>
                                <input type="text" name="state" id="state" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('state', $user->state) }}">
                            </div>
                        </div>
                        <div>
                            <label for="city" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">location_city</span>
                                City
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">apartment</span>
                                </div>
                                <input type="text" name="city" id="city" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('city', $user->city) }}">
                            </div>
                        </div>
                        <div>
                            <label for="postcode" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">local_post_office</span>
                                Postcode
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">markunread_mailbox</span>
                                </div>
                                <input type="text" name="postcode" id="postcode" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('postcode', $user->postcode) }}">
                            </div>
                        </div>
                        <div>
                            <label for="country" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">public</span>
                                Country
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">flag</span>
                                </div>
                                <input type="text" name="country" id="country" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('country', $user->country) ?? 'Malaysia' }}">
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Organization Information -->
                <div class="pb-5">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Organization Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="org_type" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">category</span>
                                Organization Type
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">corporate_fare</span>
                                </div>
                                <input type="text" name="org_type" id="org_type" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('org_type', $user->org_type) }}">
                            </div>
                        </div>
                        <div>
                            <label for="org_name" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">business</span>
                                Organization Name
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">domain</span>
                                </div>
                                <input type="text" name="org_name" id="org_name" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('org_name', $user->org_name) }}">
                            </div>
                        </div>
                        <div class="md:col-span-2">
                            <label for="org_address_line1" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">home</span>
                                Organization Address Line 1
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">home</span>
                                </div>
                                <input type="text" name="org_address_line1" id="org_address_line1" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('org_address_line1', $user->org_address_line1) }}">
                            </div>
                        </div>
                        <div class="md:col-span-2">
                            <label for="org_address_line2" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">apartment</span>
                                Organization Address Line 2
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">apartment</span>
                                </div>
                                <input type="text" name="org_address_line2" id="org_address_line2" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('org_address_line2', $user->org_address_line2) }}">
                            </div>
                        </div>
                        <div>
                            <label for="org_state" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-xs mr-1 text-primary-DEFAULT">map</span>
                                Organization State
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">location_city</span>
                                </div>
                                <input type="text" name="org_state" id="org_state" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('org_state', $user->org_state) }}">
                            </div>
                        </div>
                        <div>
                            <label for="org_city" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-xs mr-1 text-primary-DEFAULT">location_city</span>
                                Organization City
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">apartment</span>
                                </div>
                                <input type="text" name="org_city" id="org_city" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('org_city', $user->org_city) }}">
                            </div>
                        </div>
                        <div>
                            <label for="org_postcode" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-xs mr-1 text-primary-DEFAULT">local_post_office</span>
                                Organization Postcode
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">markunread_mailbox</span>
                                </div>
                                <input type="text" name="org_postcode" id="org_postcode" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('org_postcode', $user->org_postcode) }}">
                            </div>
                        </div>
                        <div>
                            <label for="org_country" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-xs mr-1 text-primary-DEFAULT">public</span>
                                Organization Country
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">flag</span>
                                </div>
                                <input type="text" name="org_country" id="org_country" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('org_country', $user->org_country) ?? 'Malaysia' }}">
                            </div>
                        </div>
                        <div>
                            <label for="org_telephone" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-xs mr-1 text-primary-DEFAULT">phone</span>
                                Organization Telephone
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">call</span>
                                </div>
                                <input type="tel" name="org_telephone" id="org_telephone" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('org_telephone', $user->org_telephone) }}">
                            </div>
                        </div>
                        <div>
                            <label for="org_fax" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-xs mr-1 text-primary-DEFAULT">print</span>
                                Organization Fax
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">fax</span>
                                </div>
                                <input type="tel" name="org_fax" id="org_fax" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('org_fax', $user->org_fax) }}">
                            </div>
                        </div>
                        <div>
                            <label for="org_email" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-xs mr-1 text-primary-DEFAULT">email</span>
                                Organization Email
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">alternate_email</span>
                                </div>
                                <input type="email" name="org_email" id="org_email" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('org_email', $user->org_email) }}">
                            </div>
                        </div>
                        <div>
                            <label for="org_website" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-xs mr-1 text-primary-DEFAULT">language</span>
                                Organization Website
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">public</span>
                                </div>
                                <input type="url" name="org_website" id="org_website" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('org_website', $user->org_website) }}" placeholder="https://example.com">
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Password Update -->
                <div class="border-t border-gray-200 pt-4 mt-6">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Change Password</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="password" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-xs mr-1 text-primary-DEFAULT">lock</span>
                                New Password <span class="ml-1 text-gray-400">(optional)</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">vpn_key</span>
                                </div>
                                <input type="password" name="password" id="password" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                            </div>
                        </div>
                        <div>
                            <label for="password_confirmation" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-xs mr-1 text-primary-DEFAULT">check_circle</span>
                                Confirm Password <span class="ml-1 text-gray-400">(optional)</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">verified</span>
                                </div>
                                <input type="password" name="password_confirmation" id="password_confirmation" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="border-t border-gray-200 pt-4 mt-6 flex justify-end space-x-3">
                    <a 
                        href="{{ route('dashboard') }}" 
                        class="px-3 py-1 bg-gradient-to-r from-red-600 to-red-500 hover:from-red-700 hover:to-red-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center"
                    >
                        <span class="material-icons text-xs mr-1">cancel</span>
                        Cancel
                    </a>
                    <button 
                        type="submit" 
                        class="px-3 py-1 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center"
                    >
                        <span class="material-icons text-xs mr-1">save</span>
                        Update Profile
                    </button>
                </div>
            </form>
            <div class="border-t border-gray-200 pt-4 mt-6">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>
