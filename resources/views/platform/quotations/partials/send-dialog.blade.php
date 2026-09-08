<div
    x-data="{ open: false }"
    x-on:open-send-quotation.window="open = true"
    x-cloak
>
    <div
        x-show="open"
        class="fixed inset-0 z-50 flex items-center justify-center bg-navy/40 p-4"
        @keydown.escape.window="open = false"
    >
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl" @click.outside="open = false">
            <h2 class="text-lg font-semibold text-black">{{ __('Send Quotation') }}</h2>
            <p class="mt-2 text-sm text-slate-500">{{ __('Send this quotation to:') }}</p>
            <dl class="mt-4 space-y-2 text-sm">
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Company') }}</dt>
                    <dd class="font-medium text-black">{{ $partner['company_name'] }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Email') }}</dt>
                    <dd class="font-medium text-black">{{ $partner['email'] }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Quotation') }}</dt>
                    <dd class="font-medium text-black">#{{ $quotation->number }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Amount') }}</dt>
                    <dd class="font-medium text-black">{{ $quotation->amountLabel() }}</dd>
                </div>
            </dl>
            <div class="mt-6 flex justify-end gap-2">
                <x-ui.button type="button" variant="outline" @click="open = false">{{ __('Cancel') }}</x-ui.button>
                <form method="POST" action="{{ route('platform.quotations.send', $quotation) }}">
                    @csrf
                    <x-ui.button type="submit" variant="default">{{ __('Send Quotation') }}</x-ui.button>
                </form>
            </div>
        </div>
    </div>
</div>
