@props([
    'lead',
    'redirectToListing' => false,
    'portal' => false,
    'compact' => false,
])

@php
    $budgetClassMap = [
        'below_50_lakh' => 'bg-sky-100 text-sky-700',
        '50_lakh_to_70_lakh' => 'bg-blue-100 text-blue-700',
        '70_lakh_to_90_lakh' => 'bg-indigo-100 text-indigo-700',
        '90_lakh_to_1_2_crore' => 'bg-violet-100 text-violet-700',
        '1_2_crore_to_1_5_crore' => 'bg-purple-100 text-purple-700',
        '1_5_crore_to_2_crore' => 'bg-fuchsia-100 text-fuchsia-700',
        '2_crore_to_3_crore' => 'bg-amber-100 text-amber-700',
        '3_crore_to_5_crore' => 'bg-orange-100 text-orange-700',
        'above_5_crore' => 'bg-emerald-100 text-emerald-700',
    ];

    $currentBudget = $lead->budget?->value ?? '';
    $minWidthClass = $compact ? 'min-w-0' : 'min-w-[9rem]';
    $triggerClasses = 'flex '.($compact ? 'h-7' : 'h-8').' '.$minWidthClass.' w-full items-center gap-1 rounded-lg border-0 '.($budgetClassMap[$currentBudget] ?? 'bg-slate-100 text-slate-500').' px-2 text-left text-xs font-semibold shadow-sm ring-1 ring-inset ring-black/5 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-navy/30';
@endphp

<form
    method="POST"
    action="{{ route('tenant.leads.budget.update', $lead) }}"
    class="inline {{ $minWidthClass }} w-full max-w-full"
    @click.stop
    x-data="leadFieldSelect(@js([
        'field' => 'budget',
        'value' => $currentBudget,
        'classes' => $budgetClassMap,
        'widthClass' => $minWidthClass,
    ]))"
    x-on:selected="submitValue($event.detail)"
>
    @csrf
    @method('PATCH')
    @if ($redirectToListing)
        <input type="hidden" name="redirect_to_listing" value="1">
    @endif

    <x-ui.select
        :id="'lead-budget-'.$lead->id"
        name="budget"
        :options="collect(\App\Enums\LeadBudget::cases())->map(fn ($budget) => ['value' => $budget->value, 'label' => $budget->label()])->all()"
        :value="$currentBudget"
        :trigger-class="$triggerClasses"
        :placeholder="__('Select budget')"
        :aria-label="__('Budget for :name', ['name' => $lead->name])"
        :min-menu-width="220"
        :portal="$portal"
        class="w-full max-w-full {{ $compact ? '' : 'min-w-[9rem]' }}"
    />
</form>
