@props([
    'activity',
])

@php
    $lead = $activity['lead'];
    $isFollowUp = $activity['kind'] === 'follow_up';
    $rescheduleRoute = route('tenant.scheduled-events.reschedule', $activity['event_id']);
    $rescheduleModalName = 'reschedule-event-'.$activity['event_id'];
    $completeModalName = $isFollowUp
        ? 'complete-follow-up-'.$activity['event_id']
        : 'complete-site-visit-'.$activity['event_id'];
@endphp

<x-ui.action-icon-group {{ $attributes }}>
    @if ($lead->callUrl())
        <form
            method="POST"
            action="{{ route('tenant.leads.activities.store', $lead) }}"
            class="inline"
        >
            @csrf
            <input type="hidden" name="type" value="call_made">
            <input type="hidden" name="redirect_url" value="{{ $lead->callUrl() }}">
            <x-ui.action-icon icon="call" type="submit" :title="__('Call')" />
        </form>
    @endif
    @if ($lead->whatsAppUrl())
        <form
            method="POST"
            action="{{ route('tenant.leads.activities.store', $lead) }}"
            class="inline"
        >
            @csrf
            <input type="hidden" name="type" value="whatsapp_message">
            <input type="hidden" name="redirect_url" value="{{ $lead->whatsAppUrl() }}">
            <x-ui.action-icon icon="whatsapp" type="submit" :title="__('WhatsApp')" />
        </form>
    @endif
    @unless ($activity['is_completed'] ?? false)
        <x-ui.action-icon
            type="button"
            icon="reminder"
            :title="__('Reschedule')"
            @click="$dispatch('open-modal', '{{ $rescheduleModalName }}')"
        />
        <x-ui.action-icon
            type="button"
            icon="complete"
            :title="__('Complete')"
            @click="$dispatch('open-modal', '{{ $completeModalName }}')"
        />
    @endunless
</x-ui.action-icon-group>
