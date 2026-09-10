@php
    use App\Enums\GoogleSheetConnectionStatus;

    $sheetProgress = app(\App\Support\GoogleSheetSetupProgress::class)->for($connection);
@endphp

<x-tenant-layout :title="$connection->name . ' | Google Sheets | InSyte CRM'">
    @if (session('status'))
        <div class="mb-4 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    @error('verify')
        <div class="mb-4 rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
            {{ $message }}
        </div>
    @enderror

    <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
        <div>
            <a href="{{ route('tenant.settings.integrations.google-sheets.index') }}" class="text-sm font-medium text-slate-500 hover:text-navy">
                ← {{ __('Back') }}
            </a>
            <h1 class="mt-3 text-2xl font-semibold text-black">{{ $connection->name }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ __('Google Sheet Configuration') }}</p>
        </div>
    </div>

    <div class="space-y-4 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm sm:p-6">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-base font-semibold text-black">{{ __('Configuration') }}</h2>
            <div class="flex items-center gap-2">
                <x-tenant.progress-dots :completed="$sheetProgress['step']" :total="$sheetProgress['total']" />
                <span @class([
                    'rounded-full px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wide ring-1',
                    'bg-emerald-50 text-emerald-700 ring-emerald-100' => $connection->status === GoogleSheetConnectionStatus::Connected,
                    'bg-amber-50 text-amber-700 ring-amber-100' => in_array($connection->status, [GoogleSheetConnectionStatus::Verified, GoogleSheetConnectionStatus::Paused], true),
                    'bg-slate-50 text-slate-600 ring-slate-100' => $connection->status === GoogleSheetConnectionStatus::Draft,
                ])>
                    {{ $connection->status->label() }}
                </span>
            </div>
        </div>

        <div>
            <label class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Sheet URL') }}</label>
            <input
                type="text"
                readonly
                value="{{ $connection->spreadsheet_url }}"
                class="mt-2 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-black"
            >
        </div>

        @if ($connection->sheet_title)
            <div>
                <label class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Tab') }}</label>
                <input
                    type="text"
                    readonly
                    value="{{ $connection->sheet_title }}"
                    class="mt-2 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-black"
                >
            </div>
        @endif

        <div class="rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm text-slate-600">
            <p class="font-medium text-black">{{ __('How to connect') }}</p>
            <p class="mt-1">
                {{ __('Share the sheet as “Anyone with the link can view”, verify access, map columns, then connect to start importing leads.') }}
            </p>
        </div>

        @if ($connection->last_error)
            <div class="rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ $connection->last_error }}
            </div>
        @endif

        @if ($canManage && ! in_array($connection->status, [GoogleSheetConnectionStatus::Connected, GoogleSheetConnectionStatus::Paused], true))
            <form method="POST" action="{{ route('tenant.settings.integrations.google-sheets.verify', $connection) }}">
                @csrf
                <x-ui.button type="submit">{{ __('Verify sheet') }}</x-ui.button>
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
                <x-ui.button type="submit" variant="destructive">{{ __('Disconnect') }}</x-ui.button>
            </form>
        @endif
    </div>

    @if (in_array($connection->status, [GoogleSheetConnectionStatus::Connected, GoogleSheetConnectionStatus::Paused], true))
        <div class="mt-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
            @foreach ([
                ['label' => __('Synced'), 'value' => $connection->total_synced],
                ['label' => __('Skipped'), 'value' => $connection->total_skipped],
                ['label' => __('Failed'), 'value' => $connection->total_failed],
                ['label' => __('Last row'), 'value' => $connection->last_synced_row],
            ] as $stat)
                <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ $stat['label'] }}</p>
                    <p class="mt-2 text-2xl font-semibold text-black">{{ $stat['value'] }}</p>
                </div>
            @endforeach
        </div>
    @endif

    @if ($connection->isVerified())
        <div class="mt-6 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm sm:p-6">
            <h2 class="text-base font-semibold text-black">{{ __('Map columns') }}</h2>
            <p class="mt-1 text-sm text-slate-500">
                {{ __('Match Google Sheet columns to CRM lead fields. Name is required.') }}
            </p>

            @error('column_map')
                <p class="mt-3 text-sm text-red-600">{{ $message }}</p>
            @enderror

            @if ($canManage)
                <form
                    method="POST"
                    action="{{ route('tenant.settings.integrations.google-sheets.connect', $connection) }}"
                    class="mt-4 space-y-4"
                >
                    @csrf
                    @method('PUT')

                    @foreach ($mappableFields as $field)
                        <div>
                            <label for="column_map_{{ $field->value }}" class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                                {{ $field->label() }}
                                @if ($field->isRequired())
                                    <span class="text-red-500">*</span>
                                @endif
                            </label>
                            <select
                                id="column_map_{{ $field->value }}"
                                name="column_map[{{ $field->value }}]"
                                @required($field->isRequired())
                                class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-black"
                            >
                                <option value="">{{ __('— Not mapped —') }}</option>
                                @foreach ($connection->headers ?? [] as $header)
                                    <option
                                        value="{{ $header }}"
                                        @selected(old("column_map.{$field->value}", $connection->column_map[$field->value] ?? null) === $header)
                                    >
                                        {{ $header }}
                                    </option>
                                @endforeach
                            </select>
                            @error("column_map.{$field->value}")
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endforeach

                    <x-ui.button type="submit">
                        {{ $connection->status === GoogleSheetConnectionStatus::Connected ? __('Update mapping') : __('Connect sheet') }}
                    </x-ui.button>
                </form>
            @else
                <dl class="mt-4 space-y-2 text-sm">
                    @foreach ($mappableFields as $field)
                        @if (! empty($connection->column_map[$field->value] ?? null))
                            <div class="flex justify-between gap-3 border-b border-slate-50 py-2">
                                <dt class="text-slate-500">{{ $field->label() }}</dt>
                                <dd class="font-medium text-black">{{ $connection->column_map[$field->value] }}</dd>
                            </div>
                        @endif
                    @endforeach
                </dl>
            @endif
        </div>
    @endif

    @if (in_array($connection->status, [GoogleSheetConnectionStatus::Connected, GoogleSheetConnectionStatus::Paused], true))
        <div class="mt-6 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm sm:p-6">
            <h2 class="text-base font-semibold text-black">{{ __('Sync status') }}</h2>
            <p class="mt-4 text-sm text-slate-600">
                {{ __('Last synced') }}:
                {{ $connection->last_synced_at?->timezone(config('app.timezone'))->format('M j, Y g:i A') ?? __('Not yet') }}
            </p>
            <p class="mt-2 text-sm text-slate-500">
                {{ __('New rows are imported automatically about every minute. Existing rows above the last imported row are not re-imported.') }}
            </p>
        </div>
    @endif
</x-tenant-layout>
