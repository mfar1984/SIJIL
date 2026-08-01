{{--
    Recycle Bin tab.

    Rendered outside the Global Config <form> because every row carries its own
    restore/delete form, and nested forms are invalid HTML.
--}}
<div x-show="activeTab === 'recycle-bin'" x-cloak>
    @if(!$recycleBin)
        <div class="bg-gray-50 border border-gray-200 rounded p-6 text-center text-xs text-gray-500">
            You do not have permission to view the Recycle Bin.
        </div>
    @else
        <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
            <div>
                <h3 class="text-sm font-medium text-gray-800">Recycle Bin</h3>
                <p class="text-xs text-gray-500 mt-1">
                    Deleted records are kept here instead of being removed from the database. Restore them any time,
                    or delete permanently once you are sure.
                </p>
            </div>

            @can('recycle_bin.delete')
            @if($recycleBin['total'] > 0)
            <form method="POST" action="{{ route('settings.recycle-bin.empty') }}"
                  onsubmit="return confirm('Permanently delete ALL {{ $recycleBin['total'] }} record(s) in the Recycle Bin? This cannot be undone.')">
                @csrf
                <button type="submit"
                        class="h-9 px-3 bg-red-600 hover:bg-red-700 text-white rounded text-xs flex items-center shrink-0 transition-colors duration-200 ease-in-out">
                    <span class="material-icons-outlined text-xs mr-1">delete_forever</span>
                    Empty Recycle Bin
                </button>
            </form>
            @endif
            @endcan
        </div>

        <div class="mb-4 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-blue-50 rounded-md p-4 border border-blue-100">
                <p class="text-xs text-blue-700 font-medium">Records In Bin</p>
                <p class="text-2xl font-bold text-blue-800">{{ $recycleBin['total'] }}</p>
            </div>
            <div class="bg-green-50 rounded-md p-4 border border-green-100">
                <p class="text-xs text-green-700 font-medium">Restorable Types</p>
                <p class="text-2xl font-bold text-green-800">{{ collect($recycleBin['types'])->where('count', '>', 0)->count() }}</p>
            </div>
            <div class="bg-amber-50 rounded-md p-4 border border-amber-100">
                <p class="text-xs text-amber-700 font-medium">Tracked Modules</p>
                <p class="text-2xl font-bold text-amber-800">{{ count($recycleBin['types']) }}</p>
            </div>
        </div>

        @if($recycleBin['total'] === 0)
            <div class="bg-gray-50 border border-gray-200 rounded p-8 text-center">
                <span class="material-icons-outlined text-gray-400" style="font-size: 40px;">delete_outline</span>
                <p class="text-xs text-gray-500 mt-2">Recycle Bin is empty. Nothing has been deleted.</p>
            </div>
        @else
            @foreach($recycleBin['types'] as $type)
                @if($type['count'] > 0)
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center">
                            <span class="material-icons-outlined text-primary-DEFAULT text-sm mr-2">{{ $type['icon'] }}</span>
                            <h4 class="text-xs font-medium text-gray-700">
                                {{ $type['plural'] }}
                                <span class="ml-1 text-gray-400">({{ $type['count'] }})</span>
                            </h4>
                        </div>

                        @can('recycle_bin.delete')
                        <form method="POST" action="{{ route('settings.recycle-bin.empty') }}"
                              onsubmit="return confirm('Permanently delete all {{ $type['count'] }} {{ $type['plural'] }}? This cannot be undone.')">
                            @csrf
                            <input type="hidden" name="type" value="{{ $type['slug'] }}">
                            <button type="submit" class="text-xs text-red-600 underline">Delete all</button>
                        </form>
                        @endcan
                    </div>

                    <div class="overflow-x-auto border border-gray-200 rounded">
                        <table class="min-w-full border-collapse">
                            <thead>
                                <tr class="bg-primary-light text-white text-xs uppercase">
                                    <th class="py-3 px-4 text-left rounded-tl">Record</th>
                                    <th class="py-3 px-4 text-left">Details</th>
                                    <th class="py-3 px-4 text-left">Deleted</th>
                                    <th class="py-3 px-4 text-center rounded-tr">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($type['items'] as $item)
                                <tr class="text-xs hover:bg-gray-50">
                                    <td class="py-3 px-4 font-medium text-gray-800">{{ $item['title'] }}</td>
                                    <td class="py-3 px-4 text-gray-500">{{ $item['subtitle'] ?? '—' }}</td>
                                    <td class="py-3 px-4 text-gray-500">
                                        {{ $item['deleted_at'] ? $item['deleted_at']->format('d M Y H:i') : '—' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="flex justify-center space-x-2">
                                            @can('recycle_bin.restore')
                                            <form method="POST" action="{{ route('settings.recycle-bin.restore', ['type' => $type['slug'], 'id' => $item['id']]) }}">
                                                @csrf
                                                <button type="submit" class="p-1 bg-green-50 rounded hover:bg-green-100 border border-green-100" title="Restore">
                                                    <span class="material-icons-outlined text-green-600 text-xs">restore_from_trash</span>
                                                </button>
                                            </form>
                                            @endcan
                                            @can('recycle_bin.delete')
                                            <form method="POST" action="{{ route('settings.recycle-bin.destroy', ['type' => $type['slug'], 'id' => $item['id']]) }}"
                                                  onsubmit="return confirm('Permanently delete this {{ $type['label'] }}? This cannot be undone.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1 bg-red-50 rounded hover:bg-red-100 border border-red-100" title="Delete permanently">
                                                    <span class="material-icons-outlined text-red-600 text-xs">delete_forever</span>
                                                </button>
                                            </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($type['truncated'])
                        <p class="text-xs text-gray-400 mt-2">
                            Showing the 50 most recently deleted {{ strtolower($type['plural']) }}.
                        </p>
                    @endif
                </div>
                @endif
            @endforeach
        @endif
    @endif
</div>
