<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Config</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Deliver</span>
    </x-slot>

    <x-slot name="title">Config Deliver</x-slot>

    {{-- [x-cloak] is defined globally in resources/css/app.css. --}}

    <div class="bg-white rounded shadow-md border border-gray-300" x-data="{ tab: 'email' }">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
                <span class="material-icons-outlined mr-2 text-primary-DEFAULT">settings_applications</span>
                <h1 class="text-xl font-bold text-gray-800">Config Deliver</h1>
            </div>
            <p class="text-xs text-gray-500 mt-1 ml-8">
                How this account sends email and SMS. Each channel can be switched on or off on its own.
            </p>
        </div>

        <div class="p-4">
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-3 py-2 rounded mb-3 text-xs">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('warning'))
                <div class="bg-amber-50 border border-amber-200 text-amber-800 px-3 py-2 rounded mb-3 text-xs">
                    {{ session('warning') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 px-3 py-2 rounded mb-3 text-xs">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-3 py-2 rounded mb-3 text-xs">
                    <p class="font-medium mb-1">Nothing was saved. Please check the fields marked below.</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="border-b border-gray-200 mb-4">
                <div class="flex flex-wrap -mb-px">
                    <button type="button" @click="tab = 'email'"
                            class="inline-flex items-center py-3 px-4 text-xs font-medium leading-5 border-b-2 focus:outline-none transition"
                            :class="tab === 'email' ? 'border-primary-DEFAULT text-primary-DEFAULT' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                        <span class="material-icons-outlined text-sm mr-2">email</span>
                        Email
                        @if($emailEnabled)
                            <span class="ml-2 px-1.5 py-0.5 rounded bg-green-100 text-green-700 text-[10px] font-medium">On</span>
                        @else
                            <span class="ml-2 px-1.5 py-0.5 rounded bg-gray-100 text-gray-500 text-[10px] font-medium">Off</span>
                        @endif
                    </button>

                    <button type="button" @click="tab = 'sms'"
                            class="inline-flex items-center py-3 px-4 text-xs font-medium leading-5 border-b-2 focus:outline-none transition"
                            :class="tab === 'sms' ? 'border-primary-DEFAULT text-primary-DEFAULT' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                        <span class="material-icons-outlined text-sm mr-2">sms</span>
                        SMS
                        @if($smsEnabled)
                            <span class="ml-2 px-1.5 py-0.5 rounded bg-green-100 text-green-700 text-[10px] font-medium">On</span>
                        @else
                            <span class="ml-2 px-1.5 py-0.5 rounded bg-gray-100 text-gray-500 text-[10px] font-medium">Off</span>
                        @endif
                    </button>
                </div>
            </div>

            <div x-show="tab === 'email'">
                @include('config.partials.email-form')
            </div>

            <div x-show="tab === 'sms'" x-cloak>
                @include('config.partials.sms-form')
            </div>
        </div>
    </div>

    {{-- Test SMS: the number is asked for here rather than kept on the form, so a
         test never goes to a stale address left in a field. --}}
    <div id="testSmsModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
         onclick="if(event.target === this) closeTestSmsModal()">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded bg-white">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-200">
                <h3 class="text-sm font-semibold text-gray-800 flex items-center">
                    <span class="material-icons-outlined text-primary-DEFAULT mr-2 text-base">sms</span>
                    Send test SMS
                </h3>
                <button type="button" onclick="closeTestSmsModal()" class="text-gray-400 hover:text-gray-600">
                    <span class="material-icons-outlined text-base">close</span>
                </button>
            </div>

            <label for="testPhoneNumber" class="block text-xs font-medium text-gray-700 mb-1">Phone number</label>
            <input type="tel" id="testPhoneNumber"
                   class="w-full h-9 text-xs border-gray-300 rounded-[1px] px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                   placeholder="+60123456789"
                   onkeypress="if(event.key === 'Enter') confirmSendTestSms()">
            <p class="mt-1 text-[11px] text-gray-500">Include the country code.</p>

            <div class="mt-5 flex justify-end gap-2">
                <button type="button" onclick="closeTestSmsModal()"
                        class="h-9 px-3 border border-gray-300 rounded text-xs font-medium text-gray-700 hover:bg-gray-50">
                    Cancel
                </button>
                <button type="button" id="confirmSendSmsBtn" onclick="confirmSendTestSms()"
                        class="h-9 px-3 rounded text-xs font-medium text-white bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 inline-flex items-center">
                    <span class="material-icons-outlined text-sm mr-1">send</span>
                    Send
                </button>
            </div>
        </div>
    </div>

    <script>
        function sendTestEmailToAddress() {
            const input = document.getElementById('test_email');
            const emailAddress = input.value.trim();

            if (!emailAddress) {
                alert('Enter an address to send the test to.');
                input.focus();
                return;
            }

            const formData = new FormData();
            formData.append('email_address', emailAddress);

            fetch('{{ route('config.deliver.test-email-to-address') }}', {
                method: 'POST',
                body: formData,
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
            .then(response => response.json())
            .then(data => alert(data.message))
            .catch(error => alert('Error: ' + error));
        }

        function sendTestSms() {
            document.getElementById('testSmsModal').classList.remove('hidden');
            document.getElementById('testPhoneNumber').focus();
        }

        function closeTestSmsModal() {
            document.getElementById('testSmsModal').classList.add('hidden');
            document.getElementById('testPhoneNumber').value = '';
        }

        function confirmSendTestSms() {
            const phoneNumber = document.getElementById('testPhoneNumber').value.trim();

            if (!phoneNumber) {
                alert('Enter a phone number.');
                return;
            }

            const sendBtn = document.getElementById('confirmSendSmsBtn');
            const originalText = sendBtn.innerHTML;
            sendBtn.disabled = true;
            sendBtn.innerHTML = '<span class="material-icons-outlined text-sm mr-1 animate-spin">refresh</span>Sending';

            const formData = new FormData();
            formData.append('test_phone', phoneNumber);

            fetch('{{ route('config.deliver.test-sms') }}', {
                method: 'POST',
                body: formData,
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
            .then(response => response.json())
            .then(data => {
                sendBtn.disabled = false;
                sendBtn.innerHTML = originalText;

                if (data.success) {
                    closeTestSmsModal();
                }

                alert(data.message);
            })
            .catch(error => {
                sendBtn.disabled = false;
                sendBtn.innerHTML = originalText;
                alert('Error: ' + error);
            });
        }
    </script>
</x-app-layout>
