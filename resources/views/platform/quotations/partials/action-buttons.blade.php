@props([
    'quotation',
    'context' => 'list',
])

@php
    $isList = $context === 'list';
    $size = $isList ? 'sm' : 'default';
@endphp

@if ($isList)
    <x-ui.action-icon-group {{ $attributes }}>
        <x-ui.action-icon
            icon="view"
            :href="route('platform.quotations.show', $quotation)"
            :title="__('View')"
        />

        @if ($quotation->canEdit())
            <x-ui.action-icon
                icon="edit"
                :href="route('platform.quotations.edit', $quotation)"
                :title="__('Edit')"
            />
        @endif

        @if ($quotation->canSend())
            <form method="POST" action="{{ route('platform.quotations.send', $quotation) }}" class="inline">
                @csrf
                <x-ui.action-icon icon="play" type="submit" :title="__('Send')" />
            </form>
        @endif

        <x-ui.action-icon
            icon="download"
            :href="route('platform.quotations.download', $quotation)"
            :title="__('Download')"
        />

        @if ($quotation->canDuplicate())
            <form method="POST" action="{{ route('platform.quotations.duplicate', $quotation) }}" class="inline">
                @csrf
                <x-ui.action-icon icon="agreement" type="submit" :title="__('Duplicate')" />
            </form>
        @endif

        @if ($quotation->canMarkAccepted())
            <form method="POST" action="{{ route('platform.quotations.accept', $quotation) }}" class="inline">
                @csrf
                <x-ui.action-icon icon="complete" type="submit" :title="__('Mark Accepted')" />
            </form>
        @endif

        @if ($quotation->canMarkRejected())
            <form method="POST" action="{{ route('platform.quotations.reject', $quotation) }}" class="inline">
                @csrf
                <x-ui.action-icon icon="cancel" type="submit" :title="__('Mark Rejected')" />
            </form>
        @endif

        @if ($quotation->canStartOnboarding())
            <x-ui.action-icon
                icon="booking"
                :href="route('platform.quotations.onboard', $quotation)"
                :title="__('Start Onboarding')"
            />
        @endif

        @if ($quotation->canCreateSubscription())
            <form method="POST" action="{{ route('platform.quotations.create-subscription', $quotation) }}" class="inline">
                @csrf
                <x-ui.action-icon icon="invoice" type="submit" :title="__('Create Subscription')" />
            </form>
        @endif
    </x-ui.action-icon-group>
@else
    <div {{ $attributes->class(['inline-flex flex-wrap items-center justify-end gap-1.5']) }}>
        @if ($quotation->canEdit())
            <x-ui.button variant="soft" :size="$size" :href="route('platform.quotations.edit', $quotation)" :title="__('Edit')">
                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                </svg>
                {{ __('Edit') }}
            </x-ui.button>
        @endif

        @if ($quotation->canSend())
            <x-ui.button type="button" variant="default" :title="__('Send')" @click="$dispatch('open-send-quotation')">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                </svg>
                {{ __('Send') }}
            </x-ui.button>
        @endif

        <x-ui.button variant="soft" :size="$size" :href="route('platform.quotations.download', $quotation)" :title="__('Download')">
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
            </svg>
            {{ __('Download PDF') }}
        </x-ui.button>

        @if ($quotation->canDuplicate())
            <form method="POST" action="{{ route('platform.quotations.duplicate', $quotation) }}">
                @csrf
                <x-ui.button type="submit" variant="soft" :size="$size" :title="__('Duplicate')">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.148m8.625 8.252a9.06 9.06 0 0 0 1.5-.148V7.875c0-.621-.504-1.125-1.125-1.125H15a9.06 9.06 0 0 0-1.5.148m-3 9.75a2.25 2.25 0 0 1-2.25-2.25V5.625c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125v9.75a2.25 2.25 0 0 1-2.25 2.25h-3Z" />
                    </svg>
                    {{ __('Duplicate') }}
                </x-ui.button>
            </form>
        @endif

        @if ($quotation->canMarkAccepted())
            <form method="POST" action="{{ route('platform.quotations.accept', $quotation) }}">
                @csrf
                <x-ui.button type="submit" variant="success" :size="$size" :title="__('Mark Accepted')">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    {{ __('Accept') }}
                </x-ui.button>
            </form>
        @endif

        @if ($quotation->canMarkRejected())
            <form method="POST" action="{{ route('platform.quotations.reject', $quotation) }}">
                @csrf
                <x-ui.button type="submit" variant="destructive" :size="$size" :title="__('Mark Rejected')">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    {{ __('Reject') }}
                </x-ui.button>
            </form>
        @endif

        @if ($quotation->canStartOnboarding())
            <x-ui.button variant="default" :size="$size" :href="route('platform.quotations.onboard', $quotation)" :title="__('Start Onboarding')">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                </svg>
                {{ __('Onboard') }}
            </x-ui.button>
        @endif

        @if ($quotation->canCreateSubscription())
            <form method="POST" action="{{ route('platform.quotations.create-subscription', $quotation) }}">
                @csrf
                <x-ui.button type="submit" variant="default" :size="$size" :title="__('Create Subscription')">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                    </svg>
                    {{ __('Subscribe') }}
                </x-ui.button>
            </form>
        @endif
    </div>
@endif
