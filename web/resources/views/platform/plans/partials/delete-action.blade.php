@props([
    'plan',
    'variant' => 'menu',
])

@if ($plan->canDelete())
    @if ($variant === 'menu')
        <form method="POST" action="{{ route('platform.plans.destroy', $plan) }}" onsubmit="return confirm(@js(__('Delete :name permanently? This cannot be undone.', ['name' => $plan->name])))">
            @csrf
            @method('DELETE')
            <button type="submit" class="flex w-full items-center rounded-md px-2 py-1.5 text-sm font-medium text-rose-600 hover:bg-rose-50">
                {{ __('Delete') }}
            </button>
        </form>
    @else
        <button
            type="button"
            class="flex w-full items-center rounded-md px-2 py-1.5 text-sm font-medium text-rose-600 hover:bg-rose-50"
            @click="$dispatch('open-delete-plan')"
        >
            {{ __('Delete') }}
        </button>
    @endif
@elseif ($plan->isArchived())
    @php
        $quotationsCount = (int) ($plan->quotations_count ?? $plan->quotations()->count());
    @endphp
    <p class="px-2 py-1.5 text-xs leading-relaxed text-slate-500">
        @if ($plan->ongoingSubscriptionsCount() > 0)
            {{ trans_choice(
                'Delete unavailable while :count subscription is active.|Delete unavailable while :count subscriptions are active.',
                $plan->ongoingSubscriptionsCount(),
                ['count' => number_format($plan->ongoingSubscriptionsCount())],
            ) }}
        @elseif ($quotationsCount > 0)
            {{ trans_choice(
                'Delete unavailable while :count quotation is linked to this plan.|Delete unavailable while :count quotations are linked to this plan.',
                $quotationsCount,
                ['count' => number_format($quotationsCount)],
            ) }}
        @else
            {{ __('Delete unavailable for this plan.') }}
        @endif
    </p>
@endif
