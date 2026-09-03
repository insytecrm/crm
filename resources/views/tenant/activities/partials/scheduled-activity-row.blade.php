@php
    $lead = $activity['lead'];
    $occurredAt = $activity['occurred_at'];
    $isFollowUp = $activity['kind'] === 'follow_up';
@endphp

<tr class="align-middle transition hover:bg-slate-50/60">
    <x-tenant.manageable-table.checkbox-cell :id="$activity['event_id']" />
    <td x-show="isColumnVisible('lead')" class="whitespace-nowrap px-4 py-3 align-middle text-sm font-medium text-black">
        <x-tenant.lead-link :lead="$lead" />
    </td>
    <td x-show="isColumnVisible('phone')" class="whitespace-nowrap px-4 py-3 align-middle text-sm text-slate-600">{{ $lead->phone ?? '—' }}</td>
    <td x-show="isColumnVisible('assigned_to')" class="whitespace-nowrap px-4 py-3 align-middle text-sm text-slate-600">{{ $lead->assignedTo?->name ?? '—' }}</td>
    <td x-show="isColumnVisible('activity')" class="whitespace-nowrap px-4 py-3 align-middle">
        <span @class([
            'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold',
            'bg-sky-100 text-sky-700' => $isFollowUp,
            'bg-amber-100 text-amber-700' => ! $isFollowUp,
        ])>
            {{ $activity['label'] }}
        </span>
    </td>
    @if ($showPropertyColumn ?? false)
        <td x-show="isColumnVisible('property')" class="whitespace-nowrap px-4 py-3 align-middle text-sm text-slate-600">
            {{ $activity['property_label'] ?? '—' }}
        </td>
    @endif
    <td x-show="isColumnVisible('priority')" class="whitespace-nowrap px-4 py-3 align-middle">
        @if ($activity['priority'] ?? null)
            <x-tenant.scheduled-activity-priority-badge :priority="$activity['priority']" />
        @else
            <span class="text-sm text-slate-400">—</span>
        @endif
    </td>
    <td x-show="isColumnVisible('scheduled')" class="whitespace-nowrap px-4 py-3 align-middle text-sm text-slate-600">
        @if ($activity['is_completed'] ?? false)
            {{ $occurredAt->format('M j, Y g:i A') }}
        @elseif ($activity['is_overdue'] ?? false)
            <span class="font-medium text-amber-700">{{ $occurredAt->format('M j, Y g:i A') }}</span>
        @else
            {{ $occurredAt->format('M j, Y g:i A') }}
        @endif
    </td>
    <td x-show="isColumnVisible('completion_method')" class="whitespace-nowrap px-4 py-3 align-middle text-sm text-slate-600">
        {{ $activity['completion_method'] ?? '—' }}
    </td>
    <td x-show="isColumnVisible('completion_outcome')" class="whitespace-nowrap px-4 py-3 align-middle text-sm text-slate-600">
        {{ $activity['completion_outcome'] ?? '—' }}
    </td>
    <td x-show="isColumnVisible('next_step')" class="whitespace-nowrap px-4 py-3 align-middle text-sm text-slate-600">
        {{ $activity['next_step_type'] ?? '—' }}
    </td>
    <td x-show="isColumnVisible('stage')" class="whitespace-nowrap px-4 py-3 align-middle">
        <x-tenant.activity-status-badge :activity="$activity" />
    </td>
    <x-tenant.manageable-table.custom-column-cells :record-id="$activity['event_id']" />
    <td x-show="isColumnVisible('actions')" class="whitespace-nowrap px-4 py-3 align-middle text-end">
        @include('tenant.activities.partials.activity-action-icons', ['activity' => $activity])
    </td>
</tr>
