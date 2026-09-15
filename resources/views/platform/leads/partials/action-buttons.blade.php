@props([
    'lead',
    'showView' => true,
])

@php
    use Illuminate\Support\Js;

    $acceptedQuotation = $lead->acceptedQuotation();
    $quotationPayload = $acceptedQuotation ? [
        'number' => $acceptedQuotation->number,
        'plan' => $acceptedQuotation->plan?->name,
        'billing_cycle' => $acceptedQuotation->billing_cycle?->label(),
        'total' => $acceptedQuotation->totalLabel(),
    ] : null;

    $basePayload = [
        'leadId' => $lead->id,
        'company_name' => $lead->company_name,
        'contact_person' => $lead->contact_person,
        'email' => $lead->email,
        'phone' => $lead->phone,
        'rera_number' => $lead->rera_number,
        'gst_number' => $lead->gst_number,
        'quotation' => $quotationPayload,
    ];

    $startTrialClick = "\$dispatch('open-partner-workflow', ".Js::from(array_merge($basePayload, ['mode' => 'start_trial'])).")";
    $onboardClick = "\$dispatch('open-partner-workflow', ".Js::from(array_merge($basePayload, ['mode' => 'onboard'])).")";
    $activateClick = "\$dispatch('open-partner-workflow', ".Js::from(array_merge($basePayload, ['mode' => 'activate'])).")";
    $presetQuotationClick = "\$dispatch('preset-quotation-lead', ".Js::from([
        'id' => $lead->id,
        'company_name' => $lead->company_name,
        'contact_person' => $lead->contact_person,
        'email' => $lead->email,
        'phone' => $lead->phone,
        'rera_number' => $lead->rera_number,
        'gst_number' => $lead->gst_number,
    ])."); \$dispatch('open-modal', 'create-quotation')";
    $addNoteClick = "\$dispatch('open-lead-note', ".Js::from(['leadId' => $lead->id]).")";
@endphp

<x-ui.action-icon-group {{ $attributes }}>
    @if ($showView)
        <x-ui.action-icon
            icon="view"
            :href="route('platform.leads.show', $lead)"
            :title="__('View')"
        />
    @endif

    @if ($lead->canStartTrial())
        <x-ui.action-icon
            icon="play"
            type="button"
            :title="__('Start Trial')"
            x-on:click="{!! $startTrialClick !!}"
        />
    @endif

    @if ($lead->canEndTrial())
        <form method="POST" action="{{ route('platform.leads.end-trial', $lead) }}" class="inline" onsubmit="return confirm(@js(__('End this trial? The channel partner record will be kept.')))">
            @csrf
            <x-ui.action-icon icon="cancel" type="submit" :title="__('End Trial')" />
        </form>
    @endif

    <x-ui.action-icon
        icon="agreement"
        type="button"
        :title="__('Create Quotation')"
        x-on:click="{!! $presetQuotationClick !!}"
    />

    @if ($lead->canOnboard())
        <x-ui.action-icon
            icon="booking"
            type="button"
            :title="__('Onboard')"
            x-on:click="{!! $onboardClick !!}"
        />
    @endif

    @if ($lead->canActivateSubscription())
        <x-ui.action-icon
            icon="complete"
            type="button"
            :title="__('Activate Subscription')"
            x-on:click="{!! $activateClick !!}"
        />
    @endif

    @if ($lead->callUrl())
        <x-ui.action-icon
            icon="call"
            :href="$lead->callUrl()"
            :title="__('Call')"
        />
    @endif

    @if ($lead->phone)
        @php
            $whatsappClick = "\$dispatch('open-platform-whatsapp', ".Js::from([
                'leadId' => $lead->id,
                'leadName' => $lead->contact_person,
                'phone' => $lead->phone,
            ]).")";
        @endphp
        <x-ui.action-icon
            icon="whatsapp"
            type="button"
            :title="__('WhatsApp')"
            x-on:click="{!! $whatsappClick !!}"
        />
    @endif

    <x-ui.action-icon
        icon="follow-up"
        type="button"
        :title="__('Add Note')"
        x-on:click="{!! $addNoteClick !!}"
    />
</x-ui.action-icon-group>
