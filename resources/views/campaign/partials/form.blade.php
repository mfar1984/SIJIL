{{--
    The campaign form, shared by create and edit.

    Create and edit used to be two files with the same fields, the same option
    lists and ~250 lines of the same JavaScript, laid out differently enough that
    they looked like different features. One file means a change lands in both.

    Expects:
      $campaign - Campaign model, or null on create
      $events   - events the account may target
--}}
@php
    use App\Models\Campaign;

    $isEdit = isset($campaign) && $campaign !== null;
    $content = $isEdit ? ($campaign->content ?? []) : [];
    $criteria = $isEdit ? ($campaign->filter_criteria ?? []) : [];

    $type = old('campaign_type', $isEdit ? $campaign->campaign_type : Campaign::TYPE_EMAIL);
    $audience = old('audience_type', $isEdit ? $campaign->audience_type : '');
    $schedule = old('schedule_type', $isEdit ? $campaign->schedule_type : Campaign::SCHEDULE_NOW);

    $customEmails = old('custom_emails', implode(', ', $criteria['custom_emails'] ?? []));

    // Shared control classes. Repeating these by hand is how create and edit drifted
    // into using different border radii for the same field.
    $input = 'w-full h-9 text-xs border-gray-300 rounded-[1px] px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50';
    $select = $input . ' leading-[1rem]';
    $area = 'w-full text-xs border-gray-300 rounded-[1px] px-3 py-2 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50';
    $radio = 'h-4 w-4 shrink-0 mt-[1px] border-gray-300 text-primary-DEFAULT focus:ring-primary-light focus:ring-offset-0';
@endphp

<form id="campaignForm" class="space-y-3"
      method="POST"
      action="{{ $isEdit ? route('campaign.update', ['campaign' => $campaign->id]) : route('campaign.store') }}"
      x-data="{
          type: '{{ $type }}',
          audience: '{{ $audience }}',
          schedule: '{{ $schedule }}',
          smsLength: {{ strlen(old('sms_message', $content['message'] ?? '')) }}
      }">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-3 py-2 rounded text-xs">
            <p class="font-medium mb-1">Nothing was saved. Please check the fields marked below.</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="border border-gray-200 rounded">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                <span class="material-icons-outlined text-primary-DEFAULT mr-2">campaign</span>
                Campaign details
            </h2>
        </div>

        <div class="p-4 grid grid-cols-1 md:grid-cols-[11rem_1fr] gap-x-4 gap-y-4 md:items-center">
            <x-form-row name="campaign_name" label="Campaign name" :required="true">
                <input type="text" name="campaign_name" id="campaign_name" required
                       value="{{ old('campaign_name', $isEdit ? $campaign->name : '') }}"
                       placeholder="What this campaign is for"
                       class="{{ $input }}">
            </x-form-row>

            <x-form-row name="campaign_description" label="Description" :top="true"
                        help="Internal note. Recipients never see this.">
                <textarea name="campaign_description" id="campaign_description" rows="3"
                          class="{{ $area }}">{{ old('campaign_description', $isEdit ? $campaign->description : '') }}</textarea>
            </x-form-row>

            <x-form-row name="start_date" label="Start date" :required="true">
                <input type="date" name="start_date" id="start_date" required
                       value="{{ old('start_date', $isEdit && $campaign->start_date ? $campaign->start_date->format('Y-m-d') : '') }}"
                       class="{{ $input }}">
            </x-form-row>

            <x-form-row name="end_date" label="End date"
                        help="Optional. Leave empty for a one-off send.">
                <input type="date" name="end_date" id="end_date"
                       value="{{ old('end_date', $isEdit && $campaign->end_date ? $campaign->end_date->format('Y-m-d') : '') }}"
                       class="{{ $input }}">
            </x-form-row>
        </div>
    </div>

    <div class="border border-gray-200 rounded">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                <span class="material-icons-outlined text-primary-DEFAULT mr-2">category</span>
                Channel
            </h2>
        </div>

        {{-- Two cards rather than a select: the choice changes which half of the
             form applies, so it deserves to be visible at a glance. --}}
        <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach([
                Campaign::TYPE_EMAIL => ['mail', 'Email', 'Sent through the delivery settings for this account.'],
                Campaign::TYPE_SMS => ['sms', 'SMS', 'Needs an Infobip gateway enabled for this account. SMS has no fallback.'],
            ] as $value => [$icon, $title, $note])
                <label class="flex border rounded p-3 cursor-pointer transition-colors"
                       :class="type === '{{ $value }}' ? 'border-primary-DEFAULT bg-primary-DEFAULT/5' : 'border-gray-200 hover:border-gray-300'">
                    <div class="flex items-start gap-2.5 w-full">
                        <input type="radio" name="campaign_type" value="{{ $value }}"
                               x-model="type" class="{{ $radio }}">

                        <span class="material-icons-outlined text-base shrink-0"
                              :class="type === '{{ $value }}' ? 'text-primary-DEFAULT' : 'text-gray-400'">{{ $icon }}</span>

                        <span class="min-w-0">
                            <span class="block text-xs font-medium text-gray-800">{{ $title }}</span>
                            <span class="block text-[11px] text-gray-500 leading-4 mt-0.5">{{ $note }}</span>
                        </span>
                    </div>
                </label>
            @endforeach

            @error('campaign_type')
                <p class="text-[11px] text-red-600 md:col-span-2">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="border border-gray-200 rounded">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                <span class="material-icons-outlined text-primary-DEFAULT mr-2">groups</span>
                Audience
            </h2>
        </div>

        <div class="p-4 grid grid-cols-1 md:grid-cols-[11rem_1fr] gap-x-4 gap-y-4 md:items-center">
            <x-form-row name="audience_type" label="Send to" :required="true">
                <select name="audience_type" id="audience_type" required x-model="audience"
                        class="{{ $select }}">
                    <option value="">Choose an audience</option>
                    <option value="{{ Campaign::AUDIENCE_ALL }}">Everyone on my events</option>
                    <option value="{{ Campaign::AUDIENCE_EVENT }}">One event</option>
                    <option value="{{ Campaign::AUDIENCE_FILTER }}">Filtered participants</option>
                    {{-- Hidden for SMS: an address list has no phone numbers, so the
                         pairing could only ever send to nobody. --}}
                    <option value="{{ Campaign::AUDIENCE_EMAILS }}"
                            x-bind:disabled="type === '{{ Campaign::TYPE_SMS }}'">A list of addresses</option>
                </select>

                <p class="text-[11px] text-amber-700 mt-1.5" x-cloak
                   x-show="type === '{{ Campaign::TYPE_SMS }}' && audience === '{{ Campaign::AUDIENCE_EMAILS }}'">
                    An address list cannot be used for SMS. Choose an event or a filter.
                </p>
            </x-form-row>

            <template x-if="audience === '{{ Campaign::AUDIENCE_EVENT }}'">
                <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-[11rem_1fr] gap-x-4 gap-y-4 md:items-center">
                    {{-- Inside x-if, so the field only exists while it applies. That
                         makes a plain required attribute safe: a hidden-but-required
                         field is what stopped this form submitting before. --}}
                    <x-form-row name="event_id" label="Event" :required="true">
                        <select name="event_id" id="event_id" required class="{{ $select }}">
                            <option value="">Choose an event</option>
                            @foreach($events as $event)
                                <option value="{{ $event->id }}"
                                        {{ (int) old('event_id', $isEdit ? $campaign->event_id : null) === $event->id ? 'selected' : '' }}>
                                    {{ $event->name }}
                                </option>
                            @endforeach
                        </select>
                    </x-form-row>
                </div>
            </template>

            <template x-if="audience === '{{ Campaign::AUDIENCE_FILTER }}'">
                <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-[11rem_1fr] gap-x-4 gap-y-4 md:items-center">
                    <x-form-row name="filter_age" label="Age"
                                help="Participants with no date of birth recorded are left out when an age is chosen.">
                        <select name="filter_age" id="filter_age" class="{{ $select }}">
                            @foreach(['' => 'Any age', '18-24' => '18 to 24', '25-34' => '25 to 34', '35-44' => '35 to 44', '45+' => '45 and over'] as $value => $text)
                                <option value="{{ $value }}"
                                        {{ old('filter_age', $criteria['age'] ?? '') === $value ? 'selected' : '' }}>
                                    {{ $text }}
                                </option>
                            @endforeach
                        </select>
                    </x-form-row>

                    <x-form-row name="filter_gender" label="Gender">
                        <select name="filter_gender" id="filter_gender" class="{{ $select }}">
                            @foreach(['' => 'Any gender', 'male' => 'Male', 'female' => 'Female'] as $value => $text)
                                <option value="{{ $value }}"
                                        {{ old('filter_gender', $criteria['gender'] ?? '') === $value ? 'selected' : '' }}>
                                    {{ $text }}
                                </option>
                            @endforeach
                        </select>
                    </x-form-row>

                    <x-form-row name="filter_attendance" label="Attendance"
                                help="Based on whether a check-in was recorded for the participant.">
                        <select name="filter_attendance" id="filter_attendance" class="{{ $select }}">
                            @foreach(['' => 'Any status', 'attended' => 'Checked in', 'not_attended' => 'Never checked in'] as $value => $text)
                                <option value="{{ $value }}"
                                        {{ old('filter_attendance', $criteria['attendance'] ?? '') === $value ? 'selected' : '' }}>
                                    {{ $text }}
                                </option>
                            @endforeach
                        </select>
                    </x-form-row>
                </div>
            </template>

            <template x-if="audience === '{{ Campaign::AUDIENCE_EMAILS }}'">
                <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-[11rem_1fr] gap-x-4 gap-y-4 md:items-center">
                    <x-form-row name="custom_emails" label="Addresses" :required="true" :top="true"
                                help="Separate with commas. Anything that is not a valid address is rejected, not silently dropped.">
                        <textarea name="custom_emails" id="custom_emails" rows="3" required
                                  placeholder="someone@example.com, someone.else@example.com"
                                  class="{{ $area }}">{{ $customEmails }}</textarea>
                    </x-form-row>
                </div>
            </template>
        </div>
    </div>

    {{-- Email content. x-show rather than x-if so TinyMCE keeps its textarea in the
         DOM: re-creating the element would drop the editor instance. --}}
    <div class="border border-gray-200 rounded" x-show="type === '{{ Campaign::TYPE_EMAIL }}'" x-cloak>
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                <span class="material-icons-outlined text-primary-DEFAULT mr-2">mail</span>
                Email content
            </h2>
        </div>

        <div class="p-4 grid grid-cols-1 md:grid-cols-[11rem_1fr] gap-x-4 gap-y-4 md:items-center">
            <x-form-row name="email_subject" label="Subject" :required="true">
                {{-- :required, not a bare required attribute. A hidden field that is
                     still required makes the browser refuse to submit without
                     showing anything, which is why an SMS campaign could not be
                     saved from this form. --}}
                <input type="text" name="email_subject" id="email_subject"
                       :required="type === '{{ Campaign::TYPE_EMAIL }}'"
                       value="{{ old('email_subject', $content['subject'] ?? '') }}"
                       class="{{ $input }}">
            </x-form-row>

            <x-form-row name="email_layout" label="Starting layout" :top="true"
                        help="Fills the editor with a starting point. Not saved with the campaign.">
                <select id="email_layout" class="{{ $select }}">
                    <option value="">Start from an empty message</option>
                    <option value="welcome">Welcome</option>
                    <option value="certificate">Certificate ready</option>
                </select>
            </x-form-row>

            <x-form-row name="email_content" label="Message" :required="true" :top="true"
                        help="{name} and {email} are replaced for each recipient.">
                <textarea name="email_content" id="email_content" rows="15"
                          class="w-full text-xs border border-gray-300 rounded-[1px]">{{ old('email_content', $content['body'] ?? '') }}</textarea>
            </x-form-row>
        </div>
    </div>

    <div class="border border-gray-200 rounded" x-show="type === '{{ Campaign::TYPE_SMS }}'" x-cloak>
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                <span class="material-icons-outlined text-primary-DEFAULT mr-2">sms</span>
                SMS content
            </h2>
        </div>

        <div class="p-4 grid grid-cols-1 md:grid-cols-[11rem_1fr] gap-x-4 gap-y-4 md:items-center">
            <x-form-row name="sms_message" label="Message" :required="true" :top="true">
                <textarea name="sms_message" id="sms_message" rows="3" maxlength="160"
                          :required="type === '{{ Campaign::TYPE_SMS }}'"
                          x-on:input="smsLength = $event.target.value.length"
                          placeholder="Hi {name}, your certificate is ready."
                          class="{{ $area }}">{{ old('sms_message', $content['message'] ?? '') }}</textarea>

                <p class="text-[11px] mt-1.5" :class="smsLength > 160 ? 'text-red-600' : 'text-gray-500'">
                    <span x-text="smsLength">0</span>/160 characters. {name} is replaced for each recipient.
                </p>
            </x-form-row>
        </div>
    </div>

    <div class="border border-gray-200 rounded">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                <span class="material-icons-outlined text-primary-DEFAULT mr-2">schedule</span>
                Timing
            </h2>
        </div>

        <div class="p-4 grid grid-cols-1 md:grid-cols-[11rem_1fr] gap-x-4 gap-y-4 md:items-center">
            <x-form-row label="When to send" :top="true">
                <div class="space-y-2">
                    @foreach([
                        Campaign::SCHEDULE_NOW => ['Send as soon as it is saved', 'Only when you use Save and send.'],
                        Campaign::SCHEDULE_LATER => ['Send at a set time', 'Saved and queued until the moment below.'],
                    ] as $value => [$title, $note])
                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input type="radio" name="schedule_type" value="{{ $value }}"
                                   x-model="schedule" class="{{ $radio }}">
                            <span class="min-w-0">
                                <span class="block text-xs text-gray-700 leading-4">{{ $title }}</span>
                                <span class="block text-[11px] text-gray-500 leading-4 mt-0.5">{{ $note }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </x-form-row>

            <template x-if="schedule === '{{ Campaign::SCHEDULE_LATER }}'">
                <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-[11rem_1fr] gap-x-4 gap-y-4 md:items-center">
                    <x-form-row name="scheduled_date" label="Date" :required="true">
                        <input type="date" name="scheduled_date" id="scheduled_date" required
                               value="{{ old('scheduled_date', $isEdit && $campaign->scheduled_at ? $campaign->scheduled_at->format('Y-m-d') : '') }}"
                               class="{{ $input }}">
                    </x-form-row>

                    <x-form-row name="scheduled_time" label="Time" :required="true"
                                help="Server time.">
                        <input type="time" name="scheduled_time" id="scheduled_time" required
                               value="{{ old('scheduled_time', $isEdit && $campaign->scheduled_at ? $campaign->scheduled_at->format('H:i') : '') }}"
                               class="{{ $input }}">
                    </x-form-row>
                </div>
            </template>
        </div>
    </div>

    <div class="flex flex-wrap justify-end items-center gap-2 pt-1">
        <a href="{{ route('campaign.index') }}"
           class="h-9 px-3 border border-gray-300 rounded text-xs font-medium text-gray-700 hover:bg-gray-50 inline-flex items-center">
            Cancel
        </a>

        <button type="submit" name="save_draft" value="1"
                class="h-9 px-3 border border-gray-300 rounded text-xs font-medium text-gray-700 hover:bg-gray-50 inline-flex items-center">
            <span class="material-icons-outlined text-sm mr-1">save</span>
            {{ $isEdit ? 'Save changes' : 'Save as draft' }}
        </button>

        {{-- Edit used to have no way to start a campaign, and the process route was
             not referenced anywhere, so a draft could never be sent from the UI. --}}
        @if(! $isEdit || $campaign->isSendable())
            <button type="submit" name="save_send" value="1"
                    class="h-9 px-4 rounded text-xs font-medium text-white bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 inline-flex items-center shadow-sm">
                <span class="material-icons-outlined text-sm mr-1">send</span>
                <span x-text="schedule === '{{ Campaign::SCHEDULE_LATER }}' ? 'Save and schedule' : 'Save and send'"></span>
            </button>
        @endif
    </div>
</form>

<script src="{{ asset('js/tinymce/js/tinymce/tinymce.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const layouts = {
            welcome: '<p>Hi {name},</p><p>Thank you for registering. We will be in touch with the details shortly.</p>',
            certificate: '<p>Hi {name},</p><p>Your certificate is ready. You can download it from your account.</p>',
        };

        tinymce.init({
            selector: '#email_content',
            plugins: 'autolink link image lists table code help wordcount preview fontsize fontfamily lineheight',
            toolbar: [
                'fontfamily fontsize | forecolor backcolor | bold italic underline | alignleft aligncenter alignright alignjustify | lineheight',
                'bullist numlist | link image | table | code'
            ],
            menubar: false,
            statusbar: false,
            height: 400,
            promotion: false,
            branding: false,
            convert_urls: false,
            relative_urls: false,
            remove_script_host: false,
            entity_encoding: 'raw',
            resize: false,
        });

        const layoutPicker = document.getElementById('email_layout');

        layoutPicker.addEventListener('change', function () {
            const layout = layouts[this.value];

            if (!layout) {
                return;
            }

            if (!confirm('Replace the current message with the ' + this.options[this.selectedIndex].text + ' layout?')) {
                this.value = '';
                return;
            }

            tinymce.get('email_content').setContent(layout);
        });

        // The editor keeps its content in an iframe, so it has to be flushed back
        // into the textarea before the form is read. The old code looked the
        // textarea up by an id that a wrapping div also carried, so it wrote .value
        // onto the div and the body only survived by accident.
        document.getElementById('campaignForm').addEventListener('submit', function () {
            const editor = tinymce.get('email_content');

            if (editor) {
                editor.save();
            }
        });
    });
</script>
