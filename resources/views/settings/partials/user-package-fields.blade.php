{{--
    Package field of User Management (create and edit), under Account Settings.

    Shown only while the Role select holds "Organizer": only an Organizer can hold
    a package, and the server makes every other role Permanent whatever is posted.
    While hidden, the inputs are disabled so they are not submitted at all.

    Expiry is calculated as start + the package's duration in months (no overflow:
    31 Jan + 1 month = 28/29 Feb) whenever the package or the start date changes,
    and the administrator may then adjust it. Opening the edit form does not
    recalculate anything, so saving an unrelated change posts the stored dates
    back and leaves the package untouched.

    Variables: $packages (collection), $user (null on create).
--}}
@php
    $editing = isset($user) && $user;
    $editingSelf = $editing && $user->id === auth()->id();
    $today = now()->format('Y-m-d');
    $initialPackage = (string) old('package_id', $editing ? ($user->package_id ?? '') : '');
    $initialStart = (string) old('package_started_at', $editing && $user->package_started_at ? $user->package_started_at->format('Y-m-d') : $today);
    $initialExpiry = (string) old('package_expires_at', $editing && $user->package_expires_at ? $user->package_expires_at->format('Y-m-d') : '');
    $grouped = $packages->groupBy('category');
@endphp

@if($editingSelf)
    <div class="flex flex-col md:flex-row md:items-center gap-3">
        <span class="text-xs font-medium text-gray-700 md:w-40">Package</span>
        <div class="flex-1 text-xs text-gray-700">
            @if($user->package_expires_at)
                {{ $user->package?->name ?? 'Package' }}, expires {{ \App\Support\SystemSettings::date($user->package_expires_at) }}
            @else
                Permanent (no expiry)
            @endif
            <p class="text-[11px] text-gray-500 mt-1">You cannot change your own package.</p>
        </div>
    </div>
@else
<div class="space-y-3"
     x-data="userPackageFields({
        packageId: @js($initialPackage),
        start: @js($initialStart),
        expiry: @js($initialExpiry),
        today: @js($today),
     })">
    {{-- Package --}}
    <div class="flex flex-col md:flex-row md:items-center gap-3">
        <label for="package_id" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
            Package
            <div class="tooltip-wrapper" x-data="{ show: false }">
                <span class="material-icons-outlined text-gray-400 text-sm cursor-help"
                      @mouseenter="show = true"
                      @mouseleave="show = false">
                    help_outline
                </span>
                <div x-show="show" x-transition class="tooltip-content">
                    Permanent works as before, with no expiry. A package applies to Organizers only: when it expires the account cannot sign in and its events stop until it is renewed.
                </div>
            </div>
        </label>
        <div class="flex-1">
            <template x-if="!isOrganizer">
                <p class="text-xs text-gray-600 h-9 flex items-center">Permanent (no expiry). Packages apply to the Organizer role only.</p>
            </template>
            <div x-show="isOrganizer">
                <select name="package_id" id="package_id" x-model="packageId" @change="recalculate()" :disabled="!isOrganizer"
                        class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                    <option value="">Permanent (no expiry)</option>
                    @foreach(\App\Models\Package::CATEGORIES as $category => $categoryLabel)
                        @if($grouped->has($category))
                            <optgroup label="{{ $categoryLabel }}">
                                @foreach($grouped[$category] as $package)
                                    <option value="{{ $package->id }}" data-duration="{{ $package->duration_months }}">
                                        {{ $package->label() }}{{ $package->is_active ? '' : ' (no longer offered)' }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                    @endforeach
                </select>
                @error('package_id')<p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>@enderror
                @if($editing && $user->package_source === 'purchase')
                    <p class="text-[11px] text-gray-500 mt-1">This package was bought online. Leave the dates as they are to keep it unchanged.</p>
                @endif
            </div>
        </div>
    </div>

    <template x-if="isOrganizer && packageId">
        <div class="space-y-3">
            {{-- Start date --}}
            <div class="flex flex-col md:flex-row md:items-center gap-3">
                <label for="package_started_at" class="text-xs font-medium text-gray-700 md:w-40">Package Start</label>
                <div class="flex-1">
                    <input type="date" name="package_started_at" id="package_started_at" x-model="start" @change="recalculate()" required
                           class="w-full md:w-64 h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                    @error('package_started_at')<p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Expiry date --}}
            <div class="flex flex-col md:flex-row md:items-center gap-3">
                <label for="package_expires_at" class="text-xs font-medium text-gray-700 md:w-40">Package Expiry</label>
                <div class="flex-1">
                    <input type="date" name="package_expires_at" id="package_expires_at" x-model="expiry" :min="start" required
                           class="w-full md:w-64 h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                    <p class="text-[11px] text-gray-500 mt-1">Calculated from the start date and the package duration. The account works until the end of this day.</p>
                    @error('package_expires_at')<p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>
    </template>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('userPackageFields', (initial) => ({
            packageId: initial.packageId,
            start: initial.start || initial.today,
            expiry: initial.expiry,
            today: initial.today,
            isOrganizer: false,

            init() {
                const role = document.getElementById('role');
                const read = () => {
                    const option = role ? role.options[role.selectedIndex] : null;
                    this.isOrganizer = !!option && option.value !== '' && option.text.trim() === 'Organizer';
                };

                read();

                if (role) {
                    role.addEventListener('change', read);
                }

                // A package chosen before a validation error came back without
                // an expiry: fill it in.
                if (this.packageId && !this.expiry) {
                    this.recalculate();
                }
            },

            duration() {
                const option = document.querySelector('#package_id option[value="' + this.packageId + '"]');

                return option ? parseInt(option.dataset.duration, 10) || 0 : 0;
            },

            recalculate() {
                if (!this.packageId) {
                    return;
                }

                if (!this.start) {
                    this.start = this.today;
                }

                this.expiry = this.addMonths(this.start, this.duration());
            },

            // Same rule as Carbon::addMonthsNoOverflow().
            addMonths(value, months) {
                const [y, m, d] = value.split('-').map(Number);

                if (!y || !m || !d) {
                    return '';
                }

                const total = (m - 1) + months;
                const year = y + Math.floor(total / 12);
                const month = ((total % 12) + 12) % 12;
                const lastDay = new Date(Date.UTC(year, month + 1, 0)).getUTCDate();
                const day = Math.min(d, lastDay);

                return year + '-' + String(month + 1).padStart(2, '0') + '-' + String(day).padStart(2, '0');
            },
        }));
    });
</script>
@endif
