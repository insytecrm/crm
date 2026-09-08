@php
    use App\Enums\LeadStatus;
@endphp

<x-tenant-layout :title="$portal->label() . ' | InSyte CRM'">
    @if (session('status'))
        <div class="mb-4 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
        <div>
            <a href="{{ route('tenant.settings.index', ['tab' => 'integrations']) }}" class="text-sm font-medium text-slate-500 hover:text-navy">
                ← {{ __('Back') }}
            </a>
            <h1 class="mt-3 text-2xl font-semibold text-black">{{ $portal->label() }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ __('Webhook Configuration') }}</p>
        </div>
    </div>

    <div class="space-y-4 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm sm:p-6" x-data="{ copied: null, showSecret: false }">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-base font-semibold text-black">{{ __('Configuration') }}</h2>
            @if ($endpoint->is_active)
                <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-emerald-700 ring-1 ring-emerald-100">
                    {{ __('Active') }}
                </span>
            @endif
        </div>

        <div>
            <label class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Webhook URL') }}</label>
            <div class="mt-2 flex flex-col gap-2 sm:flex-row sm:items-center">
                <input
                    type="text"
                    readonly
                    value="{{ $webhookUrl }}"
                    class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 font-mono text-sm text-black"
                    data-testid="portal-webhook-url"
                >
                <x-ui.button
                    type="button"
                    variant="outline"
                    x-on:click="navigator.clipboard.writeText(@js($webhookUrl)); copied = 'url'"
                >
                    <span x-text="copied === 'url' ? @js(__('Copied')) : @js(__('Copy'))"></span>
                </x-ui.button>
            </div>
        </div>

        <div>
            <label class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Webhook Secret') }}</label>
            <div class="mt-2 flex flex-col gap-2 sm:flex-row sm:items-center">
                <input
                    :type="showSecret ? 'text' : 'password'"
                    readonly
                    value="{{ $webhookSecret }}"
                    class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 font-mono text-sm text-black"
                    data-testid="portal-webhook-secret"
                >
                <div class="flex gap-2">
                    <x-ui.button
                        type="button"
                        variant="outline"
                        x-on:click="showSecret = !showSecret"
                    >
                        <span x-text="showSecret ? @js(__('Hide')) : @js(__('Show'))"></span>
                    </x-ui.button>
                    <x-ui.button
                        type="button"
                        variant="outline"
                        x-on:click="navigator.clipboard.writeText(@js($webhookSecret)); copied = 'secret'"
                    >
                        <span x-text="copied === 'secret' ? @js(__('Copied')) : @js(__('Copy'))"></span>
                    </x-ui.button>
                </div>
            </div>
            @if ($endpoint->generated_at)
                <p class="mt-2 text-xs text-slate-400">
                    {{ __('Generated :date', ['date' => $endpoint->generated_at->timezone(config('app.timezone'))->format('M j, Y g:i A')]) }}
                </p>
            @endif
        </div>

        <div class="rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm text-slate-600">
            <p class="font-medium text-black">{{ __('How to authenticate') }}</p>
            <p class="mt-1">{{ __('Send the webhook secret as a Bearer token or X-Webhook-Secret header.') }}</p>
            <pre class="mt-3 overflow-x-auto rounded-lg bg-slate-900 px-3 py-2 font-mono text-xs text-slate-100">Authorization: Bearer {{ $webhookSecret }}</pre>
        </div>

        @if ($canManage)
            <form
                method="POST"
                action="{{ route('tenant.settings.integrations.portal.regenerate', ['portal' => $portal->value]) }}"
                onsubmit="return confirm(@js(__('Regenerating will invalidate the current webhook secret immediately. Continue?')))"
            >
                @csrf
                <x-ui.button type="submit" variant="destructive">
                    {{ __('Regenerate webhook secret') }}
                </x-ui.button>
            </form>
        @endif
    </div>

    <div class="mt-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
        @foreach ([
            ['label' => __('Total Leads'), 'value' => $stats['total']],
            ['label' => __('New'), 'value' => $stats['new']],
            ['label' => __('Contacted'), 'value' => $stats['contacted']],
            ['label' => __('Converted'), 'value' => $stats['converted']],
        ] as $stat)
            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ $stat['label'] }}</p>
                <p class="mt-2 text-2xl font-semibold text-black">{{ $stat['value'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-6 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm sm:p-6">
        <h2 class="text-base font-semibold text-black">{{ __('Recent Leads') }}</h2>

        @if ($stats['recent']->isEmpty())
            <p class="mt-4 text-sm text-slate-500">{{ __('No leads received from :portal yet.', ['portal' => $portal->label()]) }}</p>
        @else
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="border-b border-slate-100 text-xs uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="px-2 py-2 font-semibold">{{ __('Name') }}</th>
                            <th class="px-2 py-2 font-semibold">{{ __('Phone') }}</th>
                            <th class="px-2 py-2 font-semibold">{{ __('Status') }}</th>
                            <th class="px-2 py-2 font-semibold">{{ __('Received') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($stats['recent'] as $lead)
                            <tr>
                                <td class="px-2 py-3 font-medium text-black">{{ $lead->name }}</td>
                                <td class="px-2 py-3 text-slate-600">{{ $lead->phone ?: '—' }}</td>
                                <td class="px-2 py-3 text-slate-600">
                                    {{ $lead->status instanceof LeadStatus ? $lead->status->label() : $lead->status }}
                                </td>
                                <td class="px-2 py-3 text-slate-600">
                                    {{ $lead->created_at?->timezone(config('app.timezone'))->format('M j, Y g:i A') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-tenant-layout>
