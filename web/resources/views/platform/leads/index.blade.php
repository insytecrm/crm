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

    <div class="mb-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-7">
        <x-platform.kpi-card :label="__('Total Leads')" :value="number_format($summary['total'])" />
        <x-platform.kpi-card :label="__('New Leads')" :value="number_format($summary['new_leads'])" accent="sky" />
        <x-platform.kpi-card :label="__('Demos')" :value="number_format($summary['demos'])" />
        <x-platform.kpi-card :label="__('Trials')" :value="number_format($summary['trials'])" accent="amber" />
        <x-platform.kpi-card :label="__('Quoted')" :value="number_format($summary['quoted'])" accent="amber" />
        <x-platform.kpi-card :label="__('Paid')" :value="number_format($summary['paid'])" />
        <x-platform.kpi-card :label="__('Live')" :value="number_format($summary['live'])" accent="emerald" />
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
            <x-platform.manageable-table.wrapper :item-ids="$leads->pluck('id')->all()">
                <x-platform.manageable-table.bulk-bar
                    :delete-url="route('platform.leads.bulk-destroy')"
                    :confirm-message="__('Delete the selected leads?')"
                />

                <div class="overflow-x-auto">
                <table class="min-w-full text-left">
                    <thead>
                        <tr class="border-b border-slate-100 text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <x-platform.manageable-table.checkbox-header />
                            <th class="px-3 py-3">{{ __('Lead Details') }}</th>
                            <th class="px-3 py-3">{{ __('Phone') }}</th>
                            <th class="px-3 py-3">{{ __('Stage') }}</th>
                            <th class="px-3 py-3">{{ __('Account Status') }}</th>
                            <th class="px-3 py-3">{{ __('Next Action') }}</th>
                            <th class="px-3 py-3">{{ __('Created Date') }}</th>
                            <th class="px-3 py-3 text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($leads as $lead)
                            <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition">
                                <x-platform.manageable-table.checkbox-cell :id="$lead->id" />
                                <td class="px-3 py-3">
                                    <div class="flex flex-col gap-0.5">
                                        <a href="{{ route('platform.leads.show', $lead) }}" class="text-sm font-semibold text-black hover:text-navy">
                                            {{ $lead->company_name }}
                                        </a>
                                        <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 text-xs text-slate-500">
                                            <span>{{ $lead->contact_person }}</span>
                                            <span class="text-slate-300">·</span>
                                            <span>{{ $lead->source->label() }}</span>
                                            @if ($lead->owner)
                                                <span class="text-slate-300">·</span>
                                                <span>{{ $lead->owner->name }}</span>
                                            @endif
                                        </div>
                                        <a href="mailto:{{ $lead->email }}" class="text-xs text-slate-500 hover:text-navy">{{ $lead->email }}</a>
                                    </div>
                                </td>
                                <td class="px-3 py-3">
                                    @if ($lead->phone)
                                        <a href="{{ $lead->callUrl() }}" class="inline-flex items-center gap-1.5 text-sm text-slate-600 hover:text-navy">
                                            <svg class="size-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                                            </svg>
                                            {{ $lead->phone }}
                                        </a>
                                    @else
                                        <span class="text-sm text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3">
                                    @include('platform.leads.partials.stage-select', ['lead' => $lead, 'compact' => true])
                                </td>
                                <td class="px-3 py-3">
                                    @if ($lead->accountStatusValue())
                                        <div class="inline-flex items-center gap-1.5">
                                            <x-platform.status-badge :status="$lead->accountStatusValue()" />
                                            @if ($lead->accountStatusShowsDue())
                                                <span class="text-xs font-semibold text-orange-600">({{ __('Due') }})</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-sm text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-sm text-slate-600">{{ $lead->nextActionDisplay() }}</td>
                                <td class="px-3 py-3 text-sm text-slate-600">{{ $lead->created_at?->timezone(config('app.timezone'))->format('d M Y') ?? '—' }}</td>
                                <td class="px-3 py-3 text-end">
                                    @include('platform.leads.partials.action-buttons', ['lead' => $lead])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            </x-platform.manageable-table.wrapper>
            <div class="mt-4">{{ $leads->links() }}</div>
        @endif
    </x-platform.panel>

    @include('platform.leads.partials.add-lead-modal')
    @include('platform.leads.partials.index-action-modals')
    @include('platform.leads.partials.partner-workflow-modal', [
        'trialPlans' => $trialPlans ?? [],
        'askEmailCredentials' => $askEmailCredentials ?? false,
        'alwaysEmailCredentials' => $alwaysEmailCredentials ?? false,
    ])
    @include('platform.quotations.partials.create-wizard-modal', [
        'openQuotationModal' => $openQuotationModal ?? false,
        'leadSelectOptions' => $leadSelectOptions ?? [],
        'quotationPlans' => $quotationPlans ?? [],
    ])
</x-app-layout>
