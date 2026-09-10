@props([
    'lead',
    'compact' => false,
])

@php
    use App\Enums\PlatformLeadStage;

    $stageClassMap = [
        'new_lead' => 'bg-sky-100 text-sky-700',
        'contacted' => 'bg-indigo-100 text-indigo-700',
        'demo_scheduled' => 'bg-violet-100 text-violet-700',
        'demo_completed' => 'bg-purple-100 text-purple-700',
        'quotation_sent' => 'bg-amber-100 text-amber-700',
        'quotation_accepted' => 'bg-emerald-100 text-emerald-700',
        'onboarding' => 'bg-teal-100 text-teal-700',
        'handover' => 'bg-cyan-100 text-cyan-700',
        'client_live' => 'bg-green-100 text-green-700',
        'retention' => 'bg-slate-100 text-slate-700',
    ];

    $currentStage = $lead->stage->value;
    $triggerClasses = 'flex h-8 min-w-[9rem] w-full items-center gap-1.5 rounded-lg border-0 '
        .($stageClassMap[$currentStage] ?? 'bg-slate-100 text-slate-700')
        .' px-2.5 text-left text-xs font-semibold shadow-sm ring-1 ring-inset ring-black/5 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-navy/30';
@endphp

<form
    method="POST"
    action="{{ route('platform.leads.stage.update', $lead) }}"
    class="{{ $compact ? 'inline min-w-[9rem]' : 'w-full max-w-xs' }}"
    @click.stop
    x-data="leadFieldSelect(@js([
        'field' => 'stage',
        'value' => $currentStage,
        'classes' => $stageClassMap,
        'widthClass' => 'min-w-[9rem]',
    ]))"
    x-on:selected="submitValue($event.detail)"
>
    @csrf
    @method('PATCH')

    <x-ui.select
        :id="'platform-lead-stage-'.$lead->id"
        name="stage"
        :options="collect(PlatformLeadStage::orderedCases())->map(fn (PlatformLeadStage $stage) => ['value' => $stage->value, 'label' => $stage->label()])->all()"
        :value="$currentStage"
        :trigger-class="$triggerClasses"
        :aria-label="__('Lead stage for :company', ['company' => $lead->company_name])"
        :min-menu-width="180"
        :portal="false"
        class="w-auto min-w-[9rem]"
    />
</form>
