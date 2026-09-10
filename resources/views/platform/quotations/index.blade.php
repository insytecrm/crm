<x-app-layout :title="__('Quotations') . ' | InSyte CRM'">
    <x-platform.page-header
        :title="__('Quotations')"
        :description="__('Manage commercial proposals sent to Channel Partners.')"
    >
        <x-slot:actions>
            <x-ui.button
                type="button"
                variant="default"
                x-on:click="$dispatch('open-modal', 'create-quotation')"
            >
                {{ __('+ Create Quotation') }}
            </x-ui.button>
        </x-slot:actions>
    </x-platform.page-header>

    <x-auth-session-status class="mb-4 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700" :status="session('status')" />

    <div class="mb-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
        <x-platform.kpi-card :label="__('Total Quotations')" :value="number_format($summary['total'])" />
        <x-platform.kpi-card :label="__('Draft')" :value="number_format($summary['draft'])" accent="slate" />
        <x-platform.kpi-card :label="__('Sent')" :value="number_format($summary['sent'])" accent="amber" />
        <x-platform.kpi-card :label="__('Accepted')" :value="number_format($summary['accepted'])" accent="emerald" />
        <x-platform.kpi-card :label="__('Expired')" :value="number_format($summary['expired'])" accent="rose" />
    </div>

    <form method="GET" action="{{ route('platform.quotations') }}" class="mb-4 flex flex-wrap gap-2">
        <input
            type="search"
            name="search"
            value="{{ $filters['search'] }}"
            placeholder="{{ __('Search company or quotation...') }}"
            class="min-w-[16rem] flex-1 rounded-lg border-slate-200 text-sm shadow-sm focus:border-navy focus:ring-navy"
        >
        <select name="status" class="rounded-lg border-slate-200 text-sm shadow-sm focus:border-navy focus:ring-navy" onchange="this.form.submit()">
            @foreach ($statusOptions as $option)
                <option value="{{ $option['value'] }}" @selected($filters['status'] === $option['value'])>{{ $option['label'] }}</option>
            @endforeach
        </select>
        <input
            type="date"
            name="date"
            value="{{ $filters['date'] }}"
            class="rounded-lg border-slate-200 text-sm shadow-sm focus:border-navy focus:ring-navy"
            onchange="this.form.submit()"
        >
        <x-ui.button type="submit" variant="outline">{{ __('Search') }}</x-ui.button>
    </form>

    <x-platform.panel compact>
        @if ($quotations->isEmpty())
            <p class="text-sm text-slate-500">{{ __('No quotations match these filters.') }}</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-left">
                    <thead>
                        <tr class="border-b border-slate-100 text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-3 py-3">{{ __('Quotation #') }}</th>
                            <th class="px-3 py-3">{{ __('Company') }}</th>
                            <th class="px-3 py-3">{{ __('Plan') }}</th>
                            <th class="px-3 py-3">{{ __('Amount') }}</th>
                            <th class="px-3 py-3">{{ __('Valid Until') }}</th>
                            <th class="px-3 py-3">{{ __('Status') }}</th>
                            <th class="px-3 py-3">{{ __('Created Date') }}</th>
                            <th class="px-3 py-3 text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($quotations as $quotation)
                            <tr class="border-b border-slate-50">
                                <td class="px-3 py-3 text-sm font-medium text-black">
                                    <a href="{{ route('platform.quotations.show', $quotation) }}" class="hover:text-navy">#{{ $quotation->number }}</a>
                                </td>
                                <td class="px-3 py-3 text-sm text-slate-600">{{ $quotation->companyDisplayName() }}</td>
                                <td class="px-3 py-3 text-sm text-slate-600">{{ $quotation->plan?->name ?? '—' }}</td>
                                <td class="px-3 py-3 text-sm text-black">{{ $quotation->amountLabel() }}</td>
                                <td class="px-3 py-3 text-sm text-slate-600">{{ $quotation->valid_until?->timezone(config('app.timezone'))->format('d M Y') ?? '—' }}</td>
                                <td class="px-3 py-3"><x-platform.status-badge :status="$quotation->status" /></td>
                                <td class="px-3 py-3 text-sm text-slate-600">{{ $quotation->created_at?->timezone(config('app.timezone'))->format('d M Y') ?? '—' }}</td>
                                <td class="px-3 py-3 text-end">
                                    @include('platform.quotations.partials.action-buttons', [
                                        'quotation' => $quotation,
                                        'context' => 'list',
                                    ])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $quotations->links() }}</div>
        @endif
    </x-platform.panel>

    @include('platform.quotations.partials.create-wizard-modal')
    @include('platform.quotations.partials.send-dialog-list')
</x-app-layout>
