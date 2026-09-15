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
            <p class="mt-2 text-sm text-slate-500">{{ __('Email quotation :number to the prospect.', ['number' => '#'.$quotation->number]) }}</p>

            <dl class="mt-4 space-y-2 text-sm">
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Company') }}</dt>
                    <dd class="font-medium text-black">{{ $partner['company_name'] }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Amount') }}</dt>
                    <dd class="font-medium text-black">{{ $quotation->amountLabel() }}</dd>
                </div>
            </dl>

            <form method="POST" action="{{ route('platform.quotations.send', $quotation) }}" class="mt-5 space-y-4">
                @csrf
                <div>
                    <x-input-label for="send_quotation_email" :value="__('Recipient email')" />
                    <x-text-input
                        id="send_quotation_email"
                        name="email"
                        type="email"
                        class="mt-1 block w-full"
                        :value="old('email', $quotation->email)"
                        required
                    />
                    <x-input-error class="mt-2" :messages="$errors->get('email')" />
                </div>

                <div class="flex justify-end gap-2">
                    <x-ui.button type="button" variant="outline" @click="open = false">{{ __('Cancel') }}</x-ui.button>
                    <x-ui.button type="submit" variant="default">{{ __('Send') }}</x-ui.button>
                </div>
            </form>
        </div>
    </div>
</div>
