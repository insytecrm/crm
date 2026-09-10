<x-app-layout :title="__('Leads') . ' | InSyte CRM'">
    <x-platform.page-header
        :title="__('Leads')"
        :description="__('Manage your InSyte customer journey from enquiry to retention.')"
    >
        <x-slot:actions>
            <x-ui.button
                type="button"
                variant="default"
                x-on:click="$dispatch('open-modal', 'add-platform-lead')"
            >
                {{ __('+ Add Lead') }}
            </x-ui.button>
        </x-slot:actions>
    </x-platform.page-header>

    <x-auth-session-status class="mb-4 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700" :status="session('status')" />

    <div class="mb-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-6">
        <x-platform.kpi-card :label="__('Total Leads')" :value="number_format($summary['total'])" />
        <x-platform.kpi-card :label="__('New Leads')" :value="number_format($summary['new_leads'])" accent="sky" />
        <x-platform.kpi-card :label="__('Demos')" :value="number_format($summary['demos'])" />
        <x-platform.kpi-card :label="__('Quotations')" :value="number_format($summary['quotations'])" accent="amber" />
        <x-platform.kpi-card :label="__('Onboarding')" :value="number_format($summary['onboarding'])" />
        <x-platform.kpi-card :label="__('Client Live')" :value="number_format($summary['client_live'])" accent="emerald" />
    </div>

    <form method="GET" action="{{ route('platform.leads') }}" class="mb-4 flex flex-wrap gap-2">
        <input
            type="search"
            name="search"
            value="{{ $filters['search'] }}"
            placeholder="{{ __('Search company, name, email or phone...') }}"
            class="min-w-[16rem] flex-1 rounded-lg border-slate-200 text-sm shadow-sm focus:border-navy focus:ring-navy"
        >
        <select name="stage" class="rounded-lg border-slate-200 text-sm shadow-sm focus:border-navy focus:ring-navy" onchange="this.form.submit()">
            @foreach ($stageOptions as $option)
                <option value="{{ $option['value'] }}" @selected($filters['stage'] === $option['value'])>{{ $option['label'] }}</option>
            @endforeach
        </select>
        <select name="source" class="rounded-lg border-slate-200 text-sm shadow-sm focus:border-navy focus:ring-navy" onchange="this.form.submit()">
            @foreach ($sourceOptions as $option)
                <option value="{{ $option['value'] }}" @selected($filters['source'] === $option['value'])>{{ $option['label'] }}</option>
            @endforeach
        </select>
        <select name="owner_id" class="rounded-lg border-slate-200 text-sm shadow-sm focus:border-navy focus:ring-navy" onchange="this.form.submit()">
            <option value="">{{ __('All owners') }}</option>
            @foreach ($owners as $owner)
                <option value="{{ $owner['id'] }}" @selected((int) ($filters['owner_id'] ?? 0) === $owner['id'])>{{ $owner['name'] }}</option>
            @endforeach
        </select>
        <select name="account_status" class="rounded-lg border-slate-200 text-sm shadow-sm focus:border-navy focus:ring-navy" onchange="this.form.submit()">
            @foreach ($accountStatusOptions as $option)
                <option value="{{ $option['value'] }}" @selected(($filters['account_status'] ?? 'all') === $option['value'])>{{ $option['label'] }}</option>
            @endforeach
        </select>
        <x-ui.button type="submit" variant="outline">{{ __('Search') }}</x-ui.button>
    </form>

    <x-platform.panel compact>
        @if ($leads->isEmpty())
            <p class="text-sm text-slate-500">{{ __('No leads match these filters.') }}</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-left">
                    <thead>
                        <tr class="border-b border-slate-100 text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-3 py-3">{{ __('Company') }}</th>
                            <th class="px-3 py-3">{{ __('Contact Person') }}</th>
                            <th class="px-3 py-3">{{ __('Phone') }}</th>
                            <th class="px-3 py-3">{{ __('Email') }}</th>
                            <th class="px-3 py-3">{{ __('Source') }}</th>
                            <th class="px-3 py-3">{{ __('Stage') }}</th>
                            <th class="px-3 py-3">{{ __('Account Status') }}</th>
                            <th class="px-3 py-3">{{ __('Owner') }}</th>
                            <th class="px-3 py-3">{{ __('Next Action') }}</th>
                            <th class="px-3 py-3">{{ __('Created Date') }}</th>
                            <th class="px-3 py-3 text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($leads as $lead)
                            <tr class="border-b border-slate-50">
                                <td class="px-3 py-3 text-sm font-medium text-black">
                                    <a href="{{ route('platform.leads.show', $lead) }}" class="hover:text-navy">{{ $lead->company_name }}</a>
                                </td>
                                <td class="px-3 py-3 text-sm text-slate-600">{{ $lead->contact_person }}</td>
                                <td class="px-3 py-3 text-sm text-slate-600">{{ $lead->phone }}</td>
                                <td class="px-3 py-3 text-sm text-slate-600">
                                    <a href="mailto:{{ $lead->email }}" class="hover:text-navy">{{ $lead->email }}</a>
                                </td>
                                <td class="px-3 py-3 text-sm text-slate-600">{{ $lead->source->label() }}</td>
                                <td class="px-3 py-3">
                                    @include('platform.leads.partials.stage-select', ['lead' => $lead, 'compact' => true])
                                </td>
                                <td class="px-3 py-3">
                                    @if ($lead->accountStatusValue())
                                        <x-platform.status-badge :status="$lead->accountStatusValue()" />
                                    @else
                                        <span class="text-sm text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-sm text-slate-600">{{ $lead->owner?->name ?? '—' }}</td>
                                <td class="px-3 py-3 text-sm text-slate-600">{{ $lead->nextActionDisplay() }}</td>
                                <td class="px-3 py-3 text-sm text-slate-600">{{ $lead->created_at?->timezone(config('app.timezone'))->format('d M Y') ?? '—' }}</td>
                                <td class="px-3 py-3 text-end">
                                    <x-ui.popover side="bottom" align="end" width="44" content-class="p-1" close-on-content-click>
                                        <x-slot:trigger>
                                            <button type="button" class="inline-flex size-8 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-black" aria-label="{{ __('Actions') }}">⋯</button>
                                        </x-slot:trigger>
                                        <x-ui.popover.item :href="route('platform.leads.show', $lead)">{{ __('View') }}</x-ui.popover.item>
                                        <x-ui.popover.item :href="route('platform.leads.show', $lead)">{{ __('Edit') }}</x-ui.popover.item>
                                        <x-ui.popover.item
                                            as="button"
                                            type="button"
                                            x-on:click="$dispatch('open-lead-note', { leadId: {{ $lead->id }} })"
                                        >{{ __('Add Note') }}</x-ui.popover.item>
                                        <x-ui.popover.item
                                            as="button"
                                            type="button"
                                            x-on:click="$dispatch('preset-quotation-lead', @js([
                                                'id' => $lead->id,
                                                'company_name' => $lead->company_name,
                                                'contact_person' => $lead->contact_person,
                                                'email' => $lead->email,
                                                'phone' => $lead->phone,
                                                'stage' => $lead->stage->label(),
                                            ])); $dispatch('open-modal', 'create-quotation')"
                                        >{{ __('Create Quotation') }}</x-ui.popover.item>
                                    </x-ui.popover>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $leads->links() }}</div>
        @endif
    </x-platform.panel>

    @include('platform.leads.partials.add-lead-modal')
    @include('platform.leads.partials.index-action-modals')
    @include('platform.quotations.partials.create-wizard-modal', [
        'openQuotationModal' => $openQuotationModal ?? false,
        'leadSearchOptions' => $leadSearchOptions ?? [],
    ])
</x-app-layout>
