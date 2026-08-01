{{-- Sharing and preview. Expects: $survey --}}

@if($survey->status !== 'published')
    <div class="bg-amber-50 border border-amber-200 rounded px-3 py-2 mb-3">
        <p class="text-xs text-amber-800">
            This survey is not published yet. The link below will show a "not accepting responses" page until you publish it.
        </p>
    </div>
@endif

<div class="border border-gray-200 rounded mb-3">
    <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
        <h2 class="text-sm font-semibold text-gray-700 flex items-center">
            <span class="material-icons-outlined text-primary-DEFAULT mr-2">link</span>
            Survey link
        </h2>
    </div>

    <div class="p-4 space-y-3" x-data="{ copied: false, failed: false }">
        <div class="flex items-center gap-2">
            <input type="text" readonly id="survey-link" value="{{ $survey->public_url }}"
                   class="flex-1 h-9 text-xs border-gray-300 bg-gray-50 rounded-[1px] px-3">

            {{-- Goes through window.copyToClipboard (resources/js/app.js) instead of
                 navigator.clipboard directly. That object does not exist over plain
                 HTTP, so calling it here threw and the button did nothing. --}}
            <button type="button"
                    @click="copyToClipboard(document.getElementById('survey-link').value).then(ok => {
                        copied = ok;
                        failed = !ok;
                        setTimeout(() => { copied = false; failed = false }, 2500);
                    })"
                    class="bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out shrink-0">
                <span class="material-icons-outlined text-xs mr-1" x-text="copied ? 'check' : (failed ? 'error_outline' : 'content_copy')"></span>
                <span x-text="copied ? 'Copied' : (failed ? 'Blocked' : 'Copy')"></span>
            </button>
        </div>

        <p class="text-[11px] text-gray-500" x-show="failed" x-cloak>
            The browser blocked the copy. Select the link above and copy it by hand.
        </p>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ $survey->public_url }}" target="_blank" rel="noopener"
               class="inline-flex items-center text-xs text-primary-DEFAULT hover:underline">
                <span class="material-icons-outlined text-xs mr-1">open_in_new</span>
                Open the form as a respondent sees it
            </a>

            <span class="text-gray-300">|</span>

            <a href="{{ route('survey.qrcode-image', $survey) }}"
               class="inline-flex items-center text-xs text-primary-DEFAULT hover:underline">
                <span class="material-icons-outlined text-xs mr-1">qr_code</span>
                Download QR code (SVG)
            </a>
        </div>
    </div>
</div>

<div class="border border-gray-200 rounded">
    <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
        <h2 class="text-sm font-semibold text-gray-700 flex items-center">
            <span class="material-icons-outlined text-primary-DEFAULT mr-2">visibility</span>
            Summary
        </h2>
    </div>

    <div class="p-4">
        <dl class="space-y-2 text-xs">
            <div class="flex">
                <dt class="w-44 text-gray-500">Status</dt>
                <dd class="text-gray-800">{{ $survey->status_label }}</dd>
            </div>
            <div class="flex">
                <dt class="w-44 text-gray-500">Who can answer</dt>
                <dd class="text-gray-800">
                    {{ $survey->isParticipantsOnly() ? 'Registered participants only' : 'Anyone with the link' }}
                </dd>
            </div>
            <div class="flex">
                <dt class="w-44 text-gray-500">Repeat responses</dt>
                <dd class="text-gray-800">{{ $survey->allow_multiple_responses ? 'Allowed' : 'One per person' }}</dd>
            </div>
            <div class="flex">
                <dt class="w-44 text-gray-500">Opens at</dt>
                <dd class="text-gray-800">{{ $survey->opens_at?->format('d M Y, H:i') ?? 'As soon as published' }}</dd>
            </div>
            <div class="flex">
                <dt class="w-44 text-gray-500">Closes at</dt>
                <dd class="text-gray-800">{{ $survey->expires_at?->format('d M Y, H:i') ?? 'No closing date' }}</dd>
            </div>
            <div class="flex">
                <dt class="w-44 text-gray-500">Published at</dt>
                <dd class="text-gray-800">{{ $survey->published_at?->format('d M Y, H:i') ?? '—' }}</dd>
            </div>
        </dl>
    </div>
</div>
