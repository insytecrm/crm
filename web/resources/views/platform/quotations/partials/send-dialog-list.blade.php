<div
    x-data="{
        open: false,
        sendUrl: '',
        company: '',
        email: '',
        number: '',
        amount: '',
    }"
    x-on:open-send-quotation-list.window="
        sendUrl = $event.detail.sendUrl;
        company = $event.detail.company;
        email = $event.detail.email;
        number = $event.detail.number;
        amount = $event.detail.amount;
        open = true;
    "
    x-cloak
>
    <div
        x-show="open"
        class="fixed inset-0 z-50 flex items-center justify-center bg-navy/40 p-4"
        @keydown.escape.window="open = false"
    >
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl" @click.outside="open = false">
            <h2 class="text-lg font-semibold text-black">{{ __('Send Quotation') }}</h2>
            <p class="mt-2 text-sm text-slate-500">
                <span>{{ __('Email quotation') }}</span>
                <span class="font-medium text-black" x-text="'#' + (number || '—')"></span>
                <span>{{ __('to the prospect.') }}</span>
            </p>

            <dl class="mt-4 space-y-2 text-sm">
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Company') }}</dt>
                    <dd class="font-medium text-black" x-text="company || '—'"></dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Amount') }}</dt>
                    <dd class="font-medium text-black" x-text="amount || '—'"></dd>
                </div>
            </dl>

            <form method="POST" x-bind:action="sendUrl" class="mt-5 space-y-4">
                @csrf
                <div>
                    <x-input-label for="send_quotation_list_email" :value="__('Recipient email')" />
                    <x-text-input
                        id="send_quotation_list_email"
                        name="email"
                        type="email"
                        class="mt-1 block w-full"
                        x-model="email"
                        required
                    />
                </div>

                <div class="flex justify-end gap-2">
                    <x-ui.button type="button" variant="outline" @click="open = false">{{ __('Cancel') }}</x-ui.button>
                    <x-ui.button type="submit" variant="default">{{ __('Send') }}</x-ui.button>
                </div>
            </form>
        </div>
    </div>
</div>
