<div
    x-data="{ open: false }"
    x-on:open-archive-plan.window="open = true"
    x-cloak
>
    <div
        x-show="open"
        class="fixed inset-0 z-50 flex items-center justify-center bg-navy/40 p-4"
        @keydown.escape.window="open = false"
    >
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl" @click.outside="open = false">
            <h2 class="text-lg font-semibold text-black">{{ __('Archive :name?', ['name' => $plan->name]) }}</h2>
            <p class="mt-2 text-sm text-slate-500">
                {{ __('This plan will no longer be available for new Channel Partners.') }}
            </p>
            <p class="mt-2 text-sm text-slate-500">
                {{ __('Existing Channel Partners using this plan will remain on their current subscription.') }}
            </p>
            <div class="mt-6 flex justify-end gap-2">
                <x-ui.button type="button" variant="outline" @click="open = false">{{ __('Cancel') }}</x-ui.button>
                <form method="POST" action="{{ route('platform.plans.archive', $plan) }}">
                    @csrf
                    <x-ui.button type="submit" variant="destructive">{{ __('Archive Plan') }}</x-ui.button>
                </form>
            </div>
        </div>
    </div>
</div>
