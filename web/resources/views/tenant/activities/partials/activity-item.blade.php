@php
    $lead = $activity['lead'];
    $occurredAt = $activity['occurred_at'];
    $isFollowUp = $activity['kind'] === 'follow_up';
    $timeLabel = $occurredAt->isToday()
        ? $occurredAt->format('g:i A')
        : $occurredAt->format('M j, g:i A');
@endphp

<tr class="align-middle transition hover:bg-slate-50/60">
    <x-tenant.manageable-table.checkbox-cell :id="$activity['event_id']" />
    <td x-show="isColumnVisible('time')" class="whitespace-nowrap px-4 py-3 align-middle text-sm font-medium text-black">
        {{ $timeLabel }}
    </td>
    <td x-show="isColumnVisible('activity')" class="whitespace-nowrap px-4 py-3 align-middle">
        <span @class([
            'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold',
            'bg-sky-100 text-sky-700' => $isFollowUp,
            'bg-amber-100 text-amber-700' => ! $isFollowUp,
        ])>
            {{ $activity['label'] }}
        </span>
    </td>
    <td x-show="isColumnVisible('lead')" class="whitespace-nowrap px-4 py-3 align-middle text-sm font-medium text-black">
        <x-tenant.lead-link :lead="$lead" />
    </td>
    @if ($showPropertyColumn ?? false)
        <td x-show="isColumnVisible('property')" class="whitespace-nowrap px-4 py-3 align-middle text-sm text-slate-600">
            {{ $activity['property_label'] ?? '—' }}
        </td>
    @endif
    <td x-show="isColumnVisible('notes')" class="max-w-xs px-4 py-3 align-middle text-sm text-slate-600">
        <span class="line-clamp-2">{{ $activity['notes'] ?? '—' }}</span>
    </td>
    <td x-show="isColumnVisible('status')" class="whitespace-nowrap px-4 py-3 align-middle">
        <x-tenant.activity-status-badge :activity="$activity" />
    </td>
    <x-tenant.manageable-table.custom-column-cells :record-id="$activity['event_id']" />
    <td x-show="isColumnVisible('actions')" class="whitespace-nowrap px-4 py-3 align-middle text-end">
        @include('tenant.activities.partials.activity-action-icons', ['activity' => $activity])
    </td>
</tr>
