@php
    use App\Enums\GoogleSheetConnectionStatus;
@endphp

<x-tenant-layout :title="__('Google Sheets') . ' | InSyte CRM'">
    @if (session('status'))
        <div class="mb-4 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    @error('sync')
        <div class="mb-4 rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
            {{ $message }}
        </div>
    @enderror

    <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
        <div>
            <a href="{{ route('tenant.settings.index', ['tab' => 'integrations']) }}" class="text-sm font-medium text-slate-500 hover:text-navy">
                ← {{ __('Back to Integrations') }}
            </a>
            <h1 class="mt-3 text-2xl font-semibold text-black">{{ __('Google Sheets') }}</h1>
            <p class="mt-1 text-sm text-slate-500">
                {{ __('Connect a Google Sheet to capture leads into this workspace. The sheet must be shared as “Anyone with the link can view”.') }}
            </p>
        </div>

        @if ($canManage)
            <x-ui.button type="button" @click="$dispatch('open-modal', 'add-google-sheet')">
                {{ __('Add New Sheet') }}
            </x-ui.button>
        @endif
    </div>

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        @foreach ([
            ['label' => __('Total Sheets'), 'value' => $summary['total_sheets']],
            ['label' => __('Total Synced'), 'value' => $summary['total_synced']],
            ['label' => __('Total Skipped'), 'value' => $summary['total_skipped']],
            ['label' => __('Total Failed'), 'value' => $summary['total_failed']],
        ] as $stat)
            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ $stat['label'] }}</p>
                <p class="mt-2 text-2xl font-semibold text-black">{{ $stat['value'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-4 flex flex-wrap gap-2">
        @if ($canManage)
            <form method="POST" action="{{ route('tenant.settings.integrations.google-sheets.sync-all') }}">
                @csrf
                <x-ui.button type="submit" variant="outline">
                    {{ __('Sync All Active Sheets') }}
                </x-ui.button>
            </form>
        @endif

        <x-ui.button
            type="button"
            variant="outline"
            href="{{ route('tenant.settings.integrations.google-sheets.index') }}"
        >
            {{ __('Refresh') }}
        </x-ui.button>
    </div>

    <div class="mt-6 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm sm:p-6">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-base font-semibold text-black">{{ __('All Sheets') }}</h2>
            <span class="text-xs font-medium text-slate-400">
                {{ __(':count sheet(s)', ['count' => $connections->count()]) }}
            </span>
        </div>

        @if ($connections->isEmpty())
            <p class="mt-4 text-sm text-slate-500">
                {{ __('No Google Sheets connected yet.') }}
            </p>
        @else
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="border-b border-slate-100 text-xs uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="px-2 py-2 font-semibold">{{ __('Sheet Name') }}</th>
                            <th class="px-2 py-2 font-semibold">{{ __('Status') }}</th>
                            <th class="px-2 py-2 font-semibold">{{ __('Synced') }}</th>
                            <th class="px-2 py-2 font-semibold">{{ __('Skipped') }}</th>
                            <th class="px-2 py-2 font-semibold">{{ __('Failed') }}</th>
                            <th class="px-2 py-2 font-semibold">{{ __('Last synced') }}</th>
                            <th class="px-2 py-2 font-semibold">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($connections as $connection)
                            <tr>
                                <td class="px-2 py-3">
                                    <a
                                        href="{{ route('tenant.settings.integrations.google-sheets.show', $connection) }}"
                                        class="font-medium text-black hover:text-navy"
                                    >
                                        {{ $connection->name }}
                                    </a>
                                    <p class="mt-0.5 text-xs text-slate-500">
                                        {{ $connection->sheet_title ?: __('Not verified yet') }}
                                    </p>
                                </td>
                                <td class="px-2 py-3">
                                    <span @class([
                                        'rounded-full px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wide ring-1',
                                        'bg-emerald-50 text-emerald-700 ring-emerald-100' => $connection->status === GoogleSheetConnectionStatus::Connected,
                                        'bg-amber-50 text-amber-700 ring-amber-100' => in_array($connection->status, [GoogleSheetConnectionStatus::Verified, GoogleSheetConnectionStatus::Paused], true),
                                        'bg-slate-50 text-slate-600 ring-slate-100' => $connection->status === GoogleSheetConnectionStatus::Draft,
                                    ])>
                                        {{ $connection->status->label() }}
                                    </span>
                                </td>
                                <td class="px-2 py-3 text-slate-600">{{ $connection->total_synced }}</td>
                                <td class="px-2 py-3 text-slate-600">{{ $connection->total_skipped }}</td>
                                <td class="px-2 py-3 text-slate-600">{{ $connection->total_failed }}</td>
                                <td class="px-2 py-3 text-slate-600">
                                    {{ $connection->last_synced_at?->timezone(config('app.timezone'))->format('M j, Y g:i A') ?? '—' }}
                                </td>
                                <td class="px-2 py-3">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <x-ui.button
                                            type="button"
                                            variant="outline"
                                            size="sm"
                                            href="{{ route('tenant.settings.integrations.google-sheets.show', $connection) }}"
                                        >
                                            {{ __('Configure') }}
                                        </x-ui.button>

                                        @if ($canManage && $connection->isConnected())
                                            <form method="POST" action="{{ route('tenant.settings.integrations.google-sheets.sync', $connection) }}">
                                                @csrf
                                                <x-ui.button type="submit" variant="outline" size="sm">{{ __('Sync') }}</x-ui.button>
                                            </form>
                                            <form method="POST" action="{{ route('tenant.settings.integrations.google-sheets.pause', $connection) }}">
                                                @csrf
                                                <x-ui.button type="submit" variant="outline" size="sm">{{ __('Pause') }}</x-ui.button>
                                            </form>
                                        @elseif ($canManage && $connection->isPaused())
                                            <form method="POST" action="{{ route('tenant.settings.integrations.google-sheets.resume', $connection) }}">
                                                @csrf
                                                <x-ui.button type="submit" variant="outline" size="sm">{{ __('Resume') }}</x-ui.button>
                                            </form>
                                        @endif

                                        @if ($canManage)
                                            <form
                                                method="POST"
                                                action="{{ route('tenant.settings.integrations.google-sheets.destroy', $connection) }}"
                                                onsubmit="return confirm(@js(__('Disconnect and remove this Google Sheet integration?')))"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <x-ui.button type="submit" variant="destructive" size="sm">{{ __('Delete') }}</x-ui.button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @if ($canManage)
        @push('modals')
            <x-modal name="add-google-sheet" maxWidth="lg" :show="$openAddModal" focusable>
                <form method="POST" action="{{ route('tenant.settings.integrations.google-sheets.store') }}" class="p-6">
                    @csrf
                    <h2 class="text-lg font-semibold text-black">{{ __('Add New Sheet') }}</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ __('Name the integration and paste a Google Sheet URL.') }}
                    </p>

                    <div class="mt-5 space-y-4">
                        <div>
                            <label for="name" class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Integration name') }}</label>
                            <input
                                id="name"
                                name="name"
                                type="text"
                                value="{{ old('name') }}"
                                required
                                class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-black"
                                placeholder="{{ __('Website form leads') }}"
                            >
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="spreadsheet_url" class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Google Sheet URL') }}</label>
                            <input
                                id="spreadsheet_url"
                                name="spreadsheet_url"
                                type="url"
                                value="{{ old('spreadsheet_url') }}"
                                required
                                class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-black"
                                placeholder="https://docs.google.com/spreadsheets/d/..."
                            >
                            @error('spreadsheet_url')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-2">
                        <x-ui.button type="button" variant="outline" @click="$dispatch('close-modal', 'add-google-sheet')">
                            {{ __('Cancel') }}
                        </x-ui.button>
                        <x-ui.button type="submit">{{ __('Continue') }}</x-ui.button>
                    </div>
                </form>
            </x-modal>
        @endpush
    @endif
</x-tenant-layout>
