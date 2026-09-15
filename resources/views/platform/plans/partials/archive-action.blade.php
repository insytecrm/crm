@props([
    'plan',
    'variant' => 'menu',
])

@if ($plan->canArchive())
    @if ($variant === 'menu')
        <form method="POST" action="{{ route('platform.plans.archive', $plan) }}" onsubmit="return confirm(@js(__('Archive :name? It will no longer be available for new Channel Partners.', ['name' => $plan->name])))">
            @csrf
            <button type="submit" class="flex w-full items-center rounded-md px-2 py-1.5 text-sm font-medium text-rose-600 hover:bg-rose-50">
                {{ __('Archive') }}
            </button>
        </form>
    @else
        <button
            type="button"
            class="flex w-full items-center rounded-md px-2 py-1.5 text-sm font-medium text-rose-600 hover:bg-rose-50"
            @click="$dispatch('open-archive-plan')"
        >
            {{ __('Archive') }}
        </button>
    @endif
@elseif (! $plan->isArchived())
    <p class="px-2 py-1.5 text-xs leading-relaxed text-slate-500">
        {{ trans_choice(
            'Archive unavailable while :count subscription is active.|Archive unavailable while :count subscriptions are active.',
            $plan->ongoingSubscriptionsCount(),
            ['count' => number_format($plan->ongoingSubscriptionsCount())],
        ) }}
    </p>
@endif
