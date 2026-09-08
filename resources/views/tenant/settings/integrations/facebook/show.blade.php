@php
    use App\Enums\FacebookPageConnectionStatus;
@endphp

<x-tenant-layout :title="__('Facebook Lead Ads').' | InSyte CRM'">
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

    @error('activate')
        <div class="mb-4 rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
            {{ $message }}
        </div>
    @enderror

    <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
        <div>
            <a href="{{ route('tenant.settings.index', ['tab' => 'integrations']) }}" class="text-sm font-medium text-slate-500 hover:text-navy">
                ← {{ __('Back') }}
            </a>
            <h1 class="mt-3 text-2xl font-semibold text-black">{{ __('Facebook Lead Ads') }}</h1>
            <p class="mt-1 text-sm text-slate-500">
                {{ __('Connect one Facebook Page to receive leads from its ad campaigns and Lead Forms.') }}
            </p>
        </div>
    </div>

    @if ($connection === null)
        <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm sm:p-6">
            <h2 class="text-base font-semibold text-black">{{ __('Connect a Facebook Page') }}</h2>
            <p class="mt-1 text-sm text-slate-500">
                {{ __('Enter the Page ID for the Facebook Page that runs your Lead Ads.') }}
            </p>

            <div class="mt-4 rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-800">
                {{ __('If your ads are managed by an agency, ask your agency to give you the required access to this Facebook Page before connecting.') }}
            </div>

            <p class="mt-4 text-sm text-slate-500">
                {{ __('After you save the Page ID, click Connect Facebook & Verify to sign in with the Facebook account that can manage this Page.') }}
            </p>

            @if ($canManage)
                <form method="POST" action="{{ route('tenant.settings.integrations.facebook.store') }}" class="mt-5 space-y-4">
                    @csrf
                    <div>
                        <label for="page_id" class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                            {{ __('Facebook Page ID') }}
                        </label>
                        <input
                            id="page_id"
                            name="page_id"
                            type="text"
                            value="{{ old('page_id') }}"
                            required
                            placeholder="{{ __('e.g. 123456789012345') }}"
                            class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-black"
                        >
                        @error('page_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-ui.button type="submit">{{ __('Save Page ID') }}</x-ui.button>
                </form>
            @else
                <p class="mt-4 text-sm text-slate-500">{{ __('You need integration manage permission to connect Facebook.') }}</p>
            @endif
        </div>
    @else
        <div class="space-y-4 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm sm:p-6">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-base font-semibold text-black">{{ __('Page connection') }}</h2>
                <span @class([
                    'rounded-full px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wide ring-1',
                    'bg-emerald-50 text-emerald-700 ring-emerald-100' => $connection->status === FacebookPageConnectionStatus::Connected,
                    'bg-amber-50 text-amber-700 ring-amber-100' => in_array($connection->status, [FacebookPageConnectionStatus::Verified, FacebookPageConnectionStatus::Paused], true),
                    'bg-slate-50 text-slate-600 ring-slate-100' => $connection->status === FacebookPageConnectionStatus::Draft,
                ])>
                    {{ $connection->status->label() }}
                </span>
            </div>

            <div>
                <label class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Page ID') }}</label>
                <input
                    type="text"
                    readonly
                    value="{{ $connection->page_id }}"
                    class="mt-2 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-black"
                >
            </div>

            @if ($connection->page_name)
                <div>
                    <label class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Page name') }}</label>
                    <input
                        type="text"
                        readonly
                        value="{{ $connection->page_name }}"
                        class="mt-2 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-black"
                    >
                </div>
            @endif

            <div class="rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-800">
                {{ __('If your ads are managed by an agency, ask your agency to give you the required access to this Facebook Page before connecting.') }}
            </div>

            @if ($connection->last_error)
                <div class="rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ $connection->last_error }}
                </div>
            @endif

            <div class="flex flex-wrap gap-2">
                @if ($canManage && ! in_array($connection->status, [FacebookPageConnectionStatus::Connected, FacebookPageConnectionStatus::Paused], true))
                    <form method="POST" action="{{ route('tenant.settings.integrations.facebook.verify', $connection) }}">
                        @csrf
                        <x-ui.button type="submit">
                            {{ filled($connection->page_access_token) ? __('Verify Page') : __('Connect Facebook & Verify') }}
                        </x-ui.button>
                    </form>
                @endif

                @if ($canManage && $connection->isConnected())
                    <form method="POST" action="{{ route('tenant.settings.integrations.facebook.pause', $connection) }}">
                        @csrf
                        <x-ui.button type="submit" variant="outline">{{ __('Pause') }}</x-ui.button>
                    </form>
                @endif

                @if ($canManage && $connection->isPaused())
                    <form method="POST" action="{{ route('tenant.settings.integrations.facebook.resume', $connection) }}">
                        @csrf
                        <x-ui.button type="submit">{{ __('Resume') }}</x-ui.button>
                    </form>
                @endif

                @if ($canManage)
                    <form
                        method="POST"
                        action="{{ route('tenant.settings.integrations.facebook.destroy', $connection) }}"
                        onsubmit="return confirm(@js(__('Disconnect this Facebook Page? Lead import will stop.')))"
                    >
                        @csrf
                        @method('DELETE')
                        <x-ui.button type="submit" variant="destructive">{{ __('Disconnect') }}</x-ui.button>
                    </form>
                @endif
            </div>
        </div>

        @if (in_array($connection->status, [FacebookPageConnectionStatus::Connected, FacebookPageConnectionStatus::Paused], true))
            <div class="mt-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
                @foreach ([
                    ['label' => __('Synced'), 'value' => $connection->total_synced],
                    ['label' => __('Skipped'), 'value' => $connection->total_skipped],
                    ['label' => __('Failed'), 'value' => $connection->total_failed],
                    ['label' => __('Last lead'), 'value' => $connection->last_lead_at?->timezone(config('app.timezone'))->format('M j, g:i A') ?? __('Not yet')],
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
                <h2 class="text-base font-semibold text-black">{{ __('Campaigns') }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ __('Campaigns discovered for this Facebook Page.') }}</p>

                @if (empty($connection->campaigns))
                    <p class="mt-4 text-sm text-slate-500">{{ __('No campaigns were returned for this Page. You can still select Lead Forms below.') }}</p>
                @else
                    <ul class="mt-4 divide-y divide-slate-100 rounded-xl border border-slate-100">
                        @foreach ($connection->campaigns as $campaign)
                            <li class="flex items-center justify-between gap-3 px-4 py-3 text-sm">
                                <span class="font-medium text-black">{{ $campaign['name'] ?? __('Untitled campaign') }}</span>
                                <span class="text-slate-500">{{ $campaign['status'] ?? '—' }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="mt-6 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm sm:p-6">
                <h2 class="text-base font-semibold text-black">{{ __('Lead Forms & field mapping') }}</h2>
                <p class="mt-1 text-sm text-slate-500">
                    {{ __('Select the Lead Forms that should send leads into this workspace, then map form fields to CRM columns.') }}
                </p>

                @if ($canManage)
                    <form
                        method="POST"
                        action="{{ route('tenant.settings.integrations.facebook.activate', $connection) }}"
                        class="mt-4 space-y-5"
                    >
                        @csrf
                        @method('PUT')

                        <div class="space-y-2">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Lead Forms') }}</p>
                            @forelse ($connection->lead_forms ?? [] as $form)
                                <label class="flex items-start gap-3 rounded-xl border border-slate-100 px-3 py-3 text-sm">
                                    <input
                                        type="checkbox"
                                        name="form_ids[]"
                                        value="{{ $form['id'] }}"
                                        class="mt-0.5 rounded border-slate-300 text-navy focus:ring-navy"
                                        @checked(in_array($form['id'], old('form_ids', $connection->selected_form_ids ?? []), true))
                                    >
                                    <span>
                                        <span class="font-medium text-black">{{ $form['name'] ?? __('Untitled form') }}</span>
                                        @if (! empty($form['status']))
                                            <span class="ml-2 text-slate-400">{{ $form['status'] }}</span>
                                        @endif
                                    </span>
                                </label>
                            @empty
                                <p class="text-sm text-slate-500">{{ __('No Lead Forms found on this Page. Create a Lead Form in Meta Ads Manager, then verify again.') }}</p>
                            @endforelse
                            @error('form_ids')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-4">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Map fields') }}</p>
                            @foreach ($mappableFields as $field)
                                <div>
                                    <label for="field_map_{{ $field->value }}" class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                                        {{ $field->label() }}
                                        @if ($field->isRequired())
                                            <span class="text-red-500">*</span>
                                        @endif
                                    </label>
                                    <select
                                        id="field_map_{{ $field->value }}"
                                        name="field_map[{{ $field->value }}]"
                                        @required($field->isRequired())
                                        class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-black"
                                    >
                                        <option value="">{{ __('— Not mapped —') }}</option>
                                        @foreach ($connection->form_fields ?? [] as $formField)
                                            <option
                                                value="{{ $formField['key'] }}"
                                                @selected(old("field_map.{$field->value}", $connection->field_map[$field->value] ?? null) === $formField['key'])
                                            >
                                                {{ $formField['label'] ?? $formField['key'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error("field_map.{$field->value}")
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endforeach
                        </div>

                        <x-ui.button type="submit">
                            {{ $connection->status === FacebookPageConnectionStatus::Connected || $connection->status === FacebookPageConnectionStatus::Paused
                                ? __('Update mapping')
                                : __('Activate') }}
                        </x-ui.button>
                    </form>
                @else
                    <dl class="mt-4 space-y-2 text-sm">
                        <div class="border-b border-slate-50 py-2">
                            <dt class="text-slate-500">{{ __('Selected forms') }}</dt>
                            <dd class="mt-1 font-medium text-black">
                                {{ collect($connection->selected_form_ids ?? [])->implode(', ') ?: '—' }}
                            </dd>
                        </div>
                        @foreach ($mappableFields as $field)
                            @if (! empty($connection->field_map[$field->value] ?? null))
                                <div class="flex justify-between gap-3 border-b border-slate-50 py-2">
                                    <dt class="text-slate-500">{{ $field->label() }}</dt>
                                    <dd class="font-medium text-black">{{ $connection->field_map[$field->value] }}</dd>
                                </div>
                            @endif
                        @endforeach
                    </dl>
                @endif
            </div>
        @endif
    @endif
</x-tenant-layout>
