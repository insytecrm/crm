@props([
    'tenant',
    'shell',
])

<div class="mb-4">
    <a href="{{ route('tenants.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 transition-colors hover:text-navy">
        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
        </svg>
        {{ __('Channel Partners') }}
    </a>
</div>

<div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div class="min-w-0">
        <h1 class="text-2xl font-bold tracking-tight text-black">{{ $shell['name'] }}</h1>
        <p class="mt-1 text-sm text-slate-500">
            @if ($shell['status'])
                <x-platform.status-badge :status="$shell['status']" class="align-middle" />
                @if ($shell['show_due'] ?? false)
                    <span class="text-xs font-semibold text-orange-600">({{ __('Due') }})</span>
                @endif
            @endif
            <span class="mx-1.5 text-slate-300">·</span>
            <span>{{ $shell['plan_label'] }}</span>
            <span class="mx-1.5 text-slate-300">·</span>
            <span class="font-mono text-xs text-slate-400">{{ $shell['tenant_db_id'] }}</span>
        </p>
        <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-sm text-slate-500">
            <span>{{ __('Owner') }}: <span class="font-medium text-black">{{ $shell['owner_name'] ?: '—' }}</span></span>
            <span>{{ __('Joined') }}: <span class="font-medium text-black">{{ $shell['joined_label'] }}</span></span>
        </div>
    </div>

    <div class="flex shrink-0 flex-wrap items-center gap-2">
        @foreach ($shell['header_actions'] ?? [] as $action)
            @if ($action['modal_event'] && ($shell['lead'] ?? null))
                @php
                    $lead = $shell['lead'];
                    $acceptedQuotation = $lead->acceptedQuotation();
                    $workflowPayload = [
                        'mode' => $action['modal_event'],
                        'leadId' => $lead->id,
                        'company_name' => $lead->company_name,
                        'contact_person' => $lead->contact_person,
                        'email' => $lead->email,
                        'phone' => $lead->phone,
                        'rera_number' => $lead->rera_number,
                        'gst_number' => $lead->gst_number,
                        'quotation' => $acceptedQuotation ? [
                            'number' => $acceptedQuotation->number,
                            'plan' => $acceptedQuotation->plan?->name,
                            'billing_cycle' => $acceptedQuotation->billing_cycle?->label(),
                            'total' => $acceptedQuotation->totalLabel(),
                        ] : null,
                    ];
                @endphp
                @php
                    $workflowClick = "\$dispatch('open-partner-workflow', ".Illuminate\Support\Js::from($workflowPayload).")";
                @endphp
                <x-ui.button
                    type="button"
                    :variant="$action['variant']"
                    x-on:click="{!! $workflowClick !!}"
                >
                    {{ $action['label'] }}
                </x-ui.button>
            @elseif (! empty($action['href']) && ! ($action['disabled'] ?? false))
                <form method="POST" action="{{ $action['href'] }}" class="inline" @if ($action['confirm']) onsubmit="return confirm(@js($action['confirm']))" @endif>
                    @csrf
                    <x-ui.button type="submit" :variant="$action['variant']">{{ $action['label'] }}</x-ui.button>
                </form>
            @endif
        @endforeach
        <x-ui.button variant="outline" :href="$shell['workspace_url']" target="_blank">
            {{ __('Access Workspace') }}
        </x-ui.button>
        <x-ui.button
            type="button"
            variant="default"
            data-partner-id="{{ $tenant->id }}"
            x-on:click="$dispatch('open-edit-partner', $el.dataset.partnerId)"
        >
            {{ __('Edit') }}
        </x-ui.button>
        <x-ui.popover side="bottom" align="end" width="52" content-class="p-1" close-on-content-click>
            <x-slot:trigger>
                <button
                    type="button"
                    class="inline-flex size-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 shadow-sm hover:bg-slate-50"
                    aria-label="{{ __('More actions') }}"
                >
                    <svg class="size-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="5" cy="12" r="1.6" />
                        <circle cx="12" cy="12" r="1.6" />
                        <circle cx="19" cy="12" r="1.6" />
                    </svg>
                </button>
            </x-slot:trigger>
            @foreach ($shell['more_actions'] as $action)
                @if (($action['method'] ?? null) === 'DELETE' && ! empty($action['href']))
                    <form method="POST" action="{{ $action['href'] }}" onsubmit="return confirm('{{ __('Delete this channel partner and its database?') }}');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="flex w-full items-center rounded-md px-2 py-1.5 text-sm font-medium text-rose-600 hover:bg-rose-50">
                            {{ $action['label'] }}
                        </button>
                    </form>
                @elseif (! empty($action['href']) && ! ($action['disabled'] ?? false))
                    <x-ui.popover.item :href="$action['href']">{{ $action['label'] }}</x-ui.popover.item>
                @else
                    <button type="button" disabled class="flex w-full cursor-not-allowed items-center rounded-md px-2 py-1.5 text-sm font-medium text-slate-400">
                        {{ $action['label'] }}
                    </button>
                @endif
            @endforeach
        </x-ui.popover>
    </div>
</div>

<nav class="mb-6 flex gap-1 overflow-x-auto border-b border-slate-200" aria-label="{{ __('Partner sections') }}">
    @foreach ($shell['tabs'] as $tab)
        <a
            href="{{ $tab['href'] }}"
            @class([
                'shrink-0 border-b-2 px-3 py-2.5 text-sm font-medium transition-colors',
                'border-navy text-navy' => $tab['active'],
                'border-transparent text-slate-500 hover:border-slate-300 hover:text-black' => ! $tab['active'],
            ])
        >
            {{ $tab['label'] }}
        </a>
    @endforeach
</nav>

<x-auth-session-status class="mb-4 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700" :status="session('status')" />

{{ $slot }}

@include('platform.tenants.partials.edit-drawers', ['tenants' => collect([$tenant])])
@include('platform.leads.partials.partner-workflow-modal', [
    'trialPlans' => $shell['trial_plans'] ?? [],
    'askEmailCredentials' => $shell['ask_email_credentials'] ?? false,
    'alwaysEmailCredentials' => $shell['always_email_credentials'] ?? false,
])
