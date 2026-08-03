{{--
    Appearance tab.

    Everything here now reaches the interface. Until App\Support\Branding existed
    the whole tab was decorative: the colours, font and custom CSS were stored and
    read by nothing, and all five brand images were uploaded, kept, and displayed
    nowhere - the sidebar and the sign-in page had /images/logo.png hard-coded and
    no page emitted a favicon link at all.

    Removed rather than left lying: Default Theme and "Allow users to choose their
    own theme", because no dark styling exists anywhere in the application;
    Sidebar Default State, because the sidebar is a fixed-width panel with no
    collapse mechanism; and Table Row Density, because table spacing is written
    into utility classes on every table rather than read from anything.
--}}
@php
    $brandPrimary = \App\Support\Branding::primary();
    $brandSecondary = \App\Support\Branding::secondary();
    $fontOptions = \App\Support\Branding::fontOptions();
    $currentFont = \App\Support\Branding::fontKey();

    $brandFields = [
        [
            'name' => 'org_logo',
            'label' => 'Organization Logo',
            'where' => 'Fallback for the sidebar and sign-in logos.',
            'hint' => 'Square works best, around 200x200px. Max 2MB.',
            'icon' => 'business',
        ],
        [
            'name' => 'favicon',
            'label' => 'Favicon',
            'where' => 'The browser tab icon on every page.',
            'hint' => 'PNG or WebP, 32x32px. Max 512KB.',
            'icon' => 'public',
        ],
        [
            'name' => 'sidebar_logo',
            'label' => 'Sidebar Logo',
            'where' => 'Top of the navigation panel.',
            'hint' => 'A wide image suits the space. Max 2MB.',
            'icon' => 'view_sidebar',
        ],
        [
            'name' => 'login_background',
            'label' => 'Login Background',
            'where' => 'Replaces the animated panel on the sign-in page.',
            'hint' => 'Around 1920x1080px. Max 4MB.',
            'icon' => 'wallpaper',
        ],
        [
            'name' => 'login_logo',
            'label' => 'Login Logo',
            'where' => 'Above the sign-in form and on the sign-in panel.',
            'hint' => 'Max 2MB.',
            'icon' => 'login',
        ],
    ];
@endphp

<div x-show="activeTab === 'appearance'" class="space-y-4"
     x-data="{
        primary: @js($brandPrimary),
        secondary: @js($brandSecondary),
        font: @js($currentFont),
        fonts: @js($fontOptions),
     }">

    {{-- ------------------------------------------------------------------
         Theme
    ------------------------------------------------------------------- --}}
    <div class="bg-white border border-gray-200 rounded-md shadow-sm">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                <span class="material-icons-outlined text-primary-DEFAULT mr-2">palette</span>
                Theme
            </h2>
        </div>

        <div class="p-4 space-y-3">
            <div class="bg-blue-50 border border-blue-200 rounded px-3 py-2">
                <p class="text-[11px] text-blue-800">
                    The colours below drive every button, table header, active tab and icon across the admin pages,
                    the sign-in page and the public registration and survey pages. They take effect on save with no
                    rebuild.
                </p>
            </div>

            <div class="flex flex-col md:flex-row md:items-start gap-3">
                <label for="primary_color" class="text-xs font-medium text-gray-700 md:w-40 md:pt-2">
                    Primary Colour
                </label>
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <input type="text" id="primary_color" name="primary_color" x-model="primary"
                               pattern="^#[0-9A-Fa-f]{6}$" maxlength="7"
                               class="w-full h-9 text-xs font-mono border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                        <input type="color" x-model="primary" aria-label="Pick primary colour"
                               class="h-9 w-10 border border-gray-300 rounded p-0 shrink-0 cursor-pointer">
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1">
                        Buttons, links, active navigation and icons. Six digit hex only; anything else is ignored and
                        the built-in colour is used.
                    </p>
                </div>
            </div>

            <div class="flex flex-col md:flex-row md:items-start gap-3">
                <label for="secondary_color" class="text-xs font-medium text-gray-700 md:w-40 md:pt-2">
                    Secondary Colour
                </label>
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <input type="text" id="secondary_color" name="secondary_color" x-model="secondary"
                               pattern="^#[0-9A-Fa-f]{6}$" maxlength="7"
                               class="w-full h-9 text-xs font-mono border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                        <input type="color" x-model="secondary" aria-label="Pick secondary colour"
                               class="h-9 w-10 border border-gray-300 rounded p-0 shrink-0 cursor-pointer">
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1">
                        Table headers and highlighted rows.
                    </p>
                </div>
            </div>

            <div class="flex flex-col md:flex-row md:items-start gap-3">
                <label for="font_family" class="text-xs font-medium text-gray-700 md:w-40 md:pt-2">
                    Font Family
                </label>
                <div class="flex-1">
                    <select id="font_family" name="font_family" x-model="font"
                            class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                        @foreach($fontOptions as $key => $label)
                            <option value="{{ $key }}" {{ $currentFont === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-gray-500 mt-1">
                        Loaded from Google Fonts, except System default which uses whatever the device provides and
                        makes no outbound request.
                    </p>
                </div>
            </div>

            {{-- Live preview, so the effect is visible before saving. --}}
            <div class="flex flex-col md:flex-row md:items-start gap-3">
                <span class="text-xs font-medium text-gray-700 md:w-40 md:pt-1">Preview</span>
                <div class="flex-1">
                    <div class="border border-gray-200 rounded overflow-hidden">
                        <div class="px-3 py-2 text-white text-xs uppercase font-medium"
                             :style="'background-color: ' + secondary">
                            Table header
                        </div>
                        <div class="p-3 bg-white space-y-2">
                            <div class="flex items-center gap-2">
                                <span class="material-icons-outlined text-sm" :style="'color: ' + primary">check_circle</span>
                                <span class="text-xs text-gray-700">An ordinary row of text</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" class="h-9 px-3 text-white text-xs rounded"
                                        :style="'background-color: ' + primary">
                                    Primary button
                                </button>
                                <span class="text-xs underline" :style="'color: ' + primary">A link</span>
                            </div>
                        </div>
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1">
                        Indicative only. Save to apply it to the whole system.
                    </p>
                </div>
            </div>

            <div class="flex flex-col md:flex-row md:items-start gap-3">
                <span class="text-xs font-medium text-gray-700 md:w-40 md:pt-1">Dark Mode</span>
                <div class="flex-1">
                    <div class="bg-gray-50 border border-gray-200 rounded px-3 py-2">
                        <p class="text-[11px] text-gray-600">
                            <span class="font-medium">Not implemented.</span> This tab carried a Default Theme
                            selector offering Light, Dark and System, and a checkbox letting users pick their own.
                            No dark styling exists in any view, so choosing Dark changed nothing. Both controls have
                            been removed rather than left implying a mode that does not exist.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ------------------------------------------------------------------
         Branding images
    ------------------------------------------------------------------- --}}
    <div class="bg-white border border-gray-200 rounded-md shadow-sm">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                <span class="material-icons-outlined text-primary-DEFAULT mr-2">branding_watermark</span>
                Branding Images
            </h2>
        </div>

        <div class="p-4">
            {{--
                The preview is the control: each tile is a label bound to a hidden
                file input, so clicking the image opens the picker with no separate
                button and no filename box.

                Each tile shows what is actually stored and says so when nothing
                is. The original markup pointed at /images/logo.png and
                /favicon.ico, files shipped with the application, so an
                unconfigured field looked configured.

                The cross in the top right corner clears the field back to the
                built-in default. It is a sibling of the label rather than a child,
                because a button inside a label for a file input opens the file
                picker instead of firing its own click handler.
            --}}
            <div class="grid grid-cols-5 gap-3">
                @foreach($brandFields as $field)
                    @php
                        $stored = \App\Support\Branding::asPath($config->{$field['name']} ?? null);
                    @endphp
                    <div>
                        <div class="relative">
                            <label for="{{ $field['name'] }}"
                                   id="{{ $field['name'] }}-tile"
                                   data-stored="{{ $stored ?: '' }}"
                                   title="{{ $field['hint'] }}"
                                   class="group block border rounded h-16 hover:bg-white cursor-pointer overflow-hidden relative transition-colors
                                          {{ $stored ? 'border-gray-200 bg-white' : 'border-dashed border-gray-300 bg-gray-50' }}
                                          hover:border-primary-DEFAULT"
                                   :class="{'opacity-50 pointer-events-none': !isEditing}">
                                <img id="{{ $field['name'] }}-preview"
                                     src="{{ $stored ?: '' }}"
                                     alt="{{ $field['label'] }}"
                                     class="w-full h-full object-contain p-1 {{ $stored ? '' : 'hidden' }}">

                                <span id="{{ $field['name'] }}-empty"
                                      class="absolute inset-0 flex flex-col items-center justify-center {{ $stored ? 'hidden' : '' }}">
                                    <span class="material-icons-outlined text-gray-300 text-xl group-hover:text-primary-DEFAULT">{{ $field['icon'] }}</span>
                                    <span class="text-[9px] text-gray-400 leading-none">Not set</span>
                                </span>

                                <span class="absolute inset-x-0 bottom-0 bg-gray-900/70 text-white text-[10px] text-center py-0.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                    Change
                                </span>
                            </label>

                            {{-- Clear back to default --}}
                            <button type="button"
                                    id="{{ $field['name'] }}-clear"
                                    title="Remove and use the default"
                                    aria-label="Remove {{ $field['label'] }} and use the default"
                                    onclick="clearBrandImage('{{ $field['name'] }}')"
                                    class="absolute -top-1.5 -right-1.5 z-10 h-4 w-4 rounded-full bg-white border border-gray-300 text-gray-500
                                           flex items-center justify-center shadow-sm hover:bg-red-50 hover:border-red-300 hover:text-red-600
                                           transition-colors {{ $stored ? '' : 'hidden' }}">
                                <span class="material-icons-outlined" style="font-size:11px;line-height:11px;">close</span>
                            </button>
                        </div>

                        <p class="text-[11px] font-medium text-gray-700 mt-1 truncate" title="{{ $field['label'] }}">{{ $field['label'] }}</p>
                        <p class="text-[10px] text-gray-500 leading-tight">{{ $field['where'] }}</p>
                        <p id="{{ $field['name'] }}-cleared" class="text-[10px] text-red-600 leading-tight mt-0.5 hidden">
                            Will be removed on save
                        </p>

                        {{-- 1 tells the server to delete the stored file and reset the
                             field. Picking a new file clears this again. --}}
                        <input type="hidden" name="remove_{{ $field['name'] }}" id="{{ $field['name'] }}-remove" value="0">

                        <input type="file"
                               name="{{ $field['name'] }}"
                               id="{{ $field['name'] }}"
                               accept="image/png,image/jpeg,image/webp"
                               class="hidden"
                               onchange="previewBrandImage('{{ $field['name'] }}', this)">
                    </div>
                @endforeach
            </div>

            <p class="text-[11px] text-gray-500 mt-3">
                PNG, JPEG or WebP. SVG is deliberately not accepted: it can carry scripts and would be served from
                this application's own origin. Where nothing is set, the sidebar and sign-in pages fall back to the
                organisation logo and then to the file shipped with the application.
                Use the cross on a tile to remove the current image and go back to the default.
            </p>

            <script>
                // Show the chosen file straight away and cancel any pending removal.
                function previewBrandImage(name, input) {
                    if (!input.files || !input.files[0]) return;

                    document.getElementById(name + '-preview').src = URL.createObjectURL(input.files[0]);
                    document.getElementById(name + '-preview').classList.remove('hidden');
                    document.getElementById(name + '-empty').classList.add('hidden');
                    document.getElementById(name + '-clear').classList.remove('hidden');
                    document.getElementById(name + '-cleared').classList.add('hidden');
                    document.getElementById(name + '-remove').value = '0';

                    const tile = document.getElementById(name + '-tile');
                    tile.classList.remove('border-dashed', 'border-gray-300', 'bg-gray-50');
                    tile.classList.add('border-gray-200', 'bg-white');
                }

                // The cross does one of two things depending on what is on the tile.
                //
                //  - A file has been picked but not saved: discard the pick and go
                //    back to whatever is stored. Nothing is flagged for removal.
                //  - Nothing pending: flag the stored image for removal on save.
                //
                // Either way nothing is deleted until Save Changes, so reloading
                // the page undoes it.
                function clearBrandImage(name) {
                    const input = document.getElementById(name);
                    const tile = document.getElementById(name + '-tile');
                    const preview = document.getElementById(name + '-preview');
                    const empty = document.getElementById(name + '-empty');
                    const clear = document.getElementById(name + '-clear');
                    const remove = document.getElementById(name + '-remove');
                    const notice = document.getElementById(name + '-cleared');

                    const discardingPick = input.files && input.files.length > 0;
                    const stored = tile.dataset.stored || '';

                    input.value = '';

                    // What the tile should show afterwards.
                    const showing = discardingPick ? stored : '';

                    if (showing) {
                        preview.src = showing;
                        preview.classList.remove('hidden');
                        empty.classList.add('hidden');
                        clear.classList.remove('hidden');
                        tile.classList.remove('border-dashed', 'border-gray-300', 'bg-gray-50');
                        tile.classList.add('border-gray-200', 'bg-white');
                    } else {
                        preview.removeAttribute('src');
                        preview.classList.add('hidden');
                        empty.classList.remove('hidden');
                        clear.classList.add('hidden');
                        tile.classList.remove('border-gray-200', 'bg-white');
                        tile.classList.add('border-dashed', 'border-gray-300', 'bg-gray-50');
                    }

                    remove.value = (!discardingPick && stored) ? '1' : '0';
                    notice.classList.toggle('hidden', remove.value !== '1');
                }
            </script>
        </div>
    </div>

    {{-- ------------------------------------------------------------------
         Interface
    ------------------------------------------------------------------- --}}
    <div class="bg-white border border-gray-200 rounded-md shadow-sm">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                <span class="material-icons-outlined text-primary-DEFAULT mr-2">tune</span>
                Interface
            </h2>
        </div>

        <div class="p-4 space-y-3">
            <div class="flex flex-col md:flex-row md:items-start gap-3">
                <span class="text-xs font-medium text-gray-700 md:w-40 md:pt-1">Options</span>
                <div class="flex-1 space-y-2">
                    <label class="flex items-start">
                        <input type="hidden" name="show_welcome_message" value="0">
                        <input type="checkbox" name="show_welcome_message" value="1"
                               class="mt-0.5 rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                               {{ (old('show_welcome_message', $config->show_welcome_message ?? true)) ? 'checked' : '' }}>
                        <span class="ml-2">
                            <span class="text-xs text-gray-700">Greet the user on the dashboard</span>
                            <span class="block text-[11px] text-gray-500">
                                Replaces the "Dashboard" heading with a time-of-day greeting and the person's first
                                name. Unticked, the heading reads "Dashboard".
                            </span>
                        </span>
                    </label>
                    <label class="flex items-start">
                        <input type="hidden" name="show_help_icons" value="0">
                        <input type="checkbox" name="show_help_icons" value="1"
                               class="mt-0.5 rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                               {{ (old('show_help_icons', $config->show_help_icons ?? true)) ? 'checked' : '' }}>
                        <span class="ml-2">
                            <span class="text-xs text-gray-700">Show help icons next to field labels</span>
                            <span class="block text-[11px] text-gray-500">
                                The question mark icons that reveal a hint on hover, throughout the settings and
                                event forms.
                            </span>
                        </span>
                    </label>
                </div>
            </div>

            <div class="flex flex-col md:flex-row md:items-start gap-3">
                <span class="text-xs font-medium text-gray-700 md:w-40 md:pt-1">Layout</span>
                <div class="flex-1">
                    <div class="bg-gray-50 border border-gray-200 rounded px-3 py-2">
                        <p class="text-[11px] text-gray-600">
                            <span class="font-medium">Sidebar Default State and Table Row Density have been
                            removed.</span> The sidebar is a fixed width panel with no collapse control to set a
                            default for, and table spacing is written into utility classes on each of the 40-odd
                            tables rather than read from a setting. Both stored a value that nothing consulted.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ------------------------------------------------------------------
         Custom CSS
    ------------------------------------------------------------------- --}}
    <div class="bg-white border border-gray-200 rounded-md shadow-sm">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                <span class="material-icons-outlined text-primary-DEFAULT mr-2">code</span>
                Custom CSS
            </h2>
        </div>

        <div class="p-4">
            {{-- The stored default used to be a comment block with a rule
                 referencing variables that never existed, so every installation
                 carried dead CSS that looked like a working example. --}}
            <textarea id="custom_css" name="custom_css" rows="6"
                      placeholder=".sidebar-label { letter-spacing: 0.01em; }"
                      class="w-full text-xs border-gray-300 rounded px-3 py-2 font-mono focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                      :disabled="!isEditing">{{ old('custom_css', $config->custom_css) }}</textarea>

            <div class="mt-2 bg-amber-50 border border-amber-200 rounded px-3 py-2">
                <p class="text-[11px] text-amber-800">
                    Injected into every page after the stylesheet, so it overrides anything above it. A mistake here
                    can make the interface unusable for everyone, including you. The brand colours are available as
                    <span class="font-mono">var(--brand-primary)</span> and
                    <span class="font-mono">var(--brand-primary-light)</span>.
                </p>
            </div>
        </div>
    </div>
</div>
