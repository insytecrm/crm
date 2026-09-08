@php
    $siteVisitProperties = $siteVisitProperties ?? \App\Models\Property::bookingFormOptions();
@endphp

@foreach ($activities as $activity)
    @unless ($activity['is_completed'] ?? false)
        @include('tenant.activities.partials.reschedule-modal', [
            'modalName' => 'reschedule-event-'.$activity['event_id'],
            'action' => route('tenant.scheduled-events.reschedule', $activity['event_id']),
            'isFollowUp' => $activity['kind'] === 'follow_up',
            'eventId' => $activity['event_id'],
            'scheduledAt' => $activity['scheduled_at'],
            'notes' => $activity['notes'],
            'priority' => $activity['priority'] ?? null,
        ])
        @if ($activity['kind'] === 'follow_up')
            @include('tenant.activities.partials.complete-follow-up-modal', [
                'modalName' => 'complete-follow-up-'.$activity['event_id'],
                'action' => route('tenant.scheduled-events.complete-follow-up', $activity['event_id']),
                'activityLabel' => $activity['label'],
                'eventId' => $activity['event_id'],
                'properties' => $siteVisitProperties ?? null,
            ])
        @else
            @include('tenant.activities.partials.complete-site-visit-modal', [
                'modalName' => 'complete-site-visit-'.$activity['event_id'],
                'action' => route('tenant.scheduled-events.complete-site-visit', $activity['event_id']),
                'activityLabel' => $activity['label'],
                'eventId' => $activity['event_id'],
                'properties' => $siteVisitProperties ?? null,
            ])
        @endif
    @endunless
@endforeach
