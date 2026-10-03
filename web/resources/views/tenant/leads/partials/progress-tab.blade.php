@php
    $nextStepAction = app(\App\Support\LeadNextStepAction::class)->for($lead);
    $booking = $lead->latestBooking;
    $bookingProgress = $booking ? app(\App\Support\BookingProgress::class)->for($booking) : null;
@endphp

<div class="space-y-5">
    @if ($nextStepAction)
        <div class="rounded-xl border border-sky-100 bg-sky-50/60 p-4">
            <p class="text-xs font-semibold uppercase tracking-wider text-sky-700">{{ __('Recommended next step') }}</p>
            <div class="mt-3 flex flex-wrap items-center gap-2">
                @if ($nextStepAction['type'] === 'log_interaction')
                    <x-ui.button
                        type="button"
                        variant="default"
                        size="sm"
                        @click="$dispatch('open-modal', @js($nextStepAction['modal']))"
                    >
                        {{ $nextStepAction['label'] }}
                    </x-ui.button>
                @elseif ($nextStepAction['type'] === 'modal' && filled($nextStepAction['modal']))
                    <x-ui.button
                        type="button"
                        variant="default"
                        size="sm"
                        @click="$dispatch('open-modal', @js($nextStepAction['modal']))"
                    >
                        {{ $nextStepAction['label'] }}
                    </x-ui.button>
                @elseif ($nextStepAction['type'] === 'deal' && $booking)
                    <x-ui.button
                        type="button"
                        variant="default"
                        size="sm"
                        :href="route('tenant.bookings.index')"
                    >
                        {{ $nextStepAction['label'] }}
                    </x-ui.button>
                @endif
                <x-ui.button
                    type="button"
                    variant="outline"
                    size="sm"
                    @click="$dispatch('open-modal', 'log-interaction-{{ $lead->id }}')"
                >
                    {{ __('Log interaction') }}
                </x-ui.button>
            </div>
        </div>
    @endif

    @include('tenant.leads.partials.history-tab', ['lead' => $lead])

    @if ($booking && $bookingProgress)
        <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-500">{{ __('Deal progress') }}</h3>
                    <p class="mt-1 text-sm text-black">{{ $booking->property?->listLabel() ?? __('Booking') }} · #{{ $booking->id }}</p>
                </div>
                <x-tenant.progress-dots :completed="$bookingProgress['completed']" :total="$bookingProgress['total']" />
            </div>
            <div class="mt-3">
                <x-ui.button type="button" variant="outline" size="sm" :href="route('tenant.bookings.index')">
                    {{ __('Open bookings') }}
                </x-ui.button>
            </div>
        </div>
    @endif
</div>
