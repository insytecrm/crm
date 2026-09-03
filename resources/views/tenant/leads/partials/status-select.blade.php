@props([
    'lead',
    'redirectToListing' => false,
])

@php
    use App\Enums\LeadStatus;

    $statusClassMap = [
        'new' => 'bg-sky-100 text-sky-700',
        'contacted' => 'bg-indigo-100 text-indigo-700',
        'qualified' => 'bg-violet-100 text-violet-700',
        'follow_up' => 'bg-amber-100 text-amber-700',
        'site_visit' => 'bg-orange-100 text-orange-700',
        'negotiation' => 'bg-purple-100 text-purple-700',
        'converted' => 'bg-emerald-100 text-emerald-700',
        'lost' => 'bg-rose-100 text-rose-700',
    ];

    $currentStatus = $lead->status->value;
    $triggerClasses = 'flex h-8 min-w-[8.5rem] w-full items-center gap-1.5 rounded-lg border-0 '.($statusClassMap[$currentStatus] ?? 'bg-slate-100 text-slate-700').' px-2.5 text-left text-xs font-semibold shadow-sm ring-1 ring-inset ring-black/5 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-navy/30';
    $selectableStatuses = $lead->status->isClosed()
        ? collect([$lead->status])
        : collect(LeadStatus::manuallySelectableCases());
@endphp

@if ($lead->status->isClosed())
    <x-tenant.status-badge :status="$lead->status" />
@else
    <form
        method="POST"
        action="{{ route('tenant.leads.status.update', $lead) }}"
        class="inline min-w-[8.5rem]"
        @click.stop
        x-data="leadFieldSelect(@js([
            'field' => 'status',
            'value' => $currentStatus,
            'classes' => $statusClassMap,
            'widthClass' => 'min-w-[8.5rem]',
        ]))"
        x-on:selected="submitValue($event.detail)"
    >
        @csrf
        @method('PATCH')
        @if ($redirectToListing)
            <input type="hidden" name="redirect_to_listing" value="1">
        @endif

        <x-ui.select
            :id="'lead-status-'.$lead->id"
            name="status"
            :options="$selectableStatuses->map(fn (LeadStatus $status) => ['value' => $status->value, 'label' => $status->label()])->all()"
            :value="$currentStatus"
            :trigger-class="$triggerClasses"
            :aria-label="__('Lead status for :name', ['name' => $lead->name])"
            :min-menu-width="160"
            :portal="false"
            class="w-auto min-w-[8.5rem]"
        />
    </form>
@endunless
