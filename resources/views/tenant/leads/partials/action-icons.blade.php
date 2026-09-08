@props([
    'lead',
    'compact' => false,
    'listView' => false,
    'showEdit' => false,
    'inlineEdit' => false,
    'alpine' => false,
    'hideCreateBooking' => false,
])

@php
    $iconSize = $listView ? 'xs' : ($compact ? 'sm' : 'md');
    $groupGap = $listView ? 'gap-1' : 'gap-1.5';
@endphp

<x-ui.action-icon-group :gap="$groupGap" {{ $attributes }}>
    @if ($lead->callUrl())
        <span @if ($alpine) x-show="actions.call" x-cloak @endif class="inline">
            <form
                method="POST"
                action="{{ route('tenant.leads.activities.store', $lead) }}"
                class="inline"
            >
                @csrf
                <input type="hidden" name="type" value="call_made">
                <input type="hidden" name="redirect_url" value="{{ $lead->callUrl() }}">
                <x-ui.action-icon
                    icon="call"
                    type="submit"
                    :size="$iconSize"
                    :title="__('Call')"
                />
            </form>
        </span>
    @endif
    @if ($lead->whatsAppUrl())
        <span @if ($alpine) x-show="actions.whatsapp" x-cloak @endif class="inline">
            <x-ui.action-icon
                icon="whatsapp"
                type="button"
                :size="$iconSize"
                :title="__('WhatsApp')"
                @click="$dispatch('open-modal', 'send-whatsapp'); $dispatch('prepare-whatsapp', {{ $lead->id }})"
            />
        </span>
    @endif
    <span @if ($alpine) x-show="actions.follow_up" x-cloak @endif class="inline">
        <x-ui.action-icon
            icon="follow-up"
            type="button"
            :size="$iconSize"
            :title="__('Follow-up')"
            @click="$dispatch('open-modal', 'follow-up-{{ $lead->id }}')"
        />
    </span>
    <span @if ($alpine) x-show="actions.site_visit" x-cloak @endif class="inline">
        <x-ui.action-icon
            icon="site-visit"
            type="button"
            :size="$iconSize"
            :title="__('Site Visit')"
            @click="$dispatch('open-modal', 'site-visit-{{ $lead->id }}')"
        />
    </span>
    @unless ($hideCreateBooking || $lead->hasBooking())
        <span @if ($alpine) x-show="actions.create_booking" x-cloak @endif class="inline">
            @if ($listView)
                <x-ui.action-icon
                    icon="booking"
                    type="button"
                    :size="$iconSize"
                    :title="__('Create Booking')"
                    @click.stop="$dispatch('open-modal', 'create-booking-{{ $lead->id }}')"
                />
            @else
                <x-ui.action-icon
                    icon="booking"
                    type="button"
                    :size="$iconSize"
                    :title="__('Create Booking')"
                    @click.stop="$dispatch('open-modal', 'create-booking')"
                />
            @endif
        </span>
    @endunless
    @if ($showEdit)
        @if ($inlineEdit)
            <x-ui.action-icon
                icon="edit"
                type="button"
                :size="$iconSize"
                :title="__('Edit')"
                @click="editing = true"
            />
        @else
            <x-ui.action-icon
                icon="edit"
                type="button"
                :size="$iconSize"
                :title="__('Edit')"
                @click="$dispatch('open-modal', 'edit-lead-{{ $lead->id }}')"
            />
        @endif
    @endif
</x-ui.action-icon-group>
