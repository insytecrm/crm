<x-app-layout :title="__('Create Quotation') . ' | InSyte CRM'">
    <x-platform.page-header
        :title="__('Review Quotation')"
        :description="__('Step 4 of 4 — Review')"
    >
        <x-slot:actions>
            <x-ui.button variant="outline" :href="route('platform.quotations.wizard.pricing')">{{ __('Back') }}</x-ui.button>
        </x-slot:actions>
    </x-platform.page-header>

    <x-platform.quotation-wizard-steps :step="$step" />

    <form method="POST" action="{{ route('platform.quotations.store') }}" class="mx-auto max-w-2xl">
        @csrf
        <x-platform.panel :title="__('Review Quotation')">
            <div class="space-y-6 text-sm">
                <div>
                    <h3 class="font-semibold text-black">{{ __('Company') }}</h3>
                    <p class="mt-1 text-slate-600">{{ $draft['company_name'] ?? '—' }}</p>
                    <p class="mt-1 text-slate-500">{{ $draft['owner_name'] ?? '—' }} · {{ $draft['email'] ?? '—' }}</p>
                </div>
                <div>
                    <h3 class="font-semibold text-black">{{ __('Plan') }}</h3>
                    <p class="mt-1 text-slate-600">{{ $plan->name }}</p>
                </div>
                <div>
                    <h3 class="font-semibold text-black">{{ __('Billing') }}</h3>
                    <p class="mt-1 text-slate-600">{{ $billingCycle->label() }}</p>
                </div>
                <div>
                    <h3 class="font-semibold text-black">{{ __('Trial') }}</h3>
                    <p class="mt-1 text-slate-600">
                        @if (! empty($draft['trial_enabled']))
                            {{ trans_choice(':count day|:count days', (int) ($draft['trial_days'] ?? 7), ['count' => (int) ($draft['trial_days'] ?? 7)]) }}
                        @else
                            {{ __('No trial') }}
                        @endif
                    </p>
                </div>
                <div>
                    <h3 class="mb-2 font-semibold text-black">{{ __('Pricing') }}</h3>
                    <dl class="space-y-2">
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">{{ __('Subtotal') }}</dt>
                            <dd class="font-medium text-black">{{ \App\Support\Platform\BillingMoney::format((int) $pricing['plan_price']) }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">{{ __('Discount') }}</dt>
                            <dd class="font-medium text-black">{{ \App\Support\Platform\BillingMoney::format((int) $pricing['discount_amount']) }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">{{ __('Tax') }}</dt>
                            <dd class="font-medium text-black">{{ \App\Support\Platform\BillingMoney::format((int) $pricing['tax_amount']) }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 border-t border-slate-100 pt-2">
                            <dt class="font-semibold text-black">{{ __('Total') }}</dt>
                            <dd class="font-semibold text-black">{{ \App\Support\Platform\BillingMoney::format((int) $pricing['total']) }}</dd>
                        </div>
                    </dl>
                </div>
                <div>
                    <x-input-label for="valid_until" :value="__('Valid Until')" />
                    <x-text-input
                        id="valid_until"
                        name="valid_until"
                        type="date"
                        class="mt-1 block w-full"
                        :value="old('valid_until', $validUntil)"
                        required
                    />
                    <x-input-error class="mt-2" :messages="$errors->get('valid_until')" />
                </div>
            </div>

            <div class="mt-6 flex justify-between gap-2">
                <x-ui.button variant="outline" :href="route('platform.quotations.wizard.pricing')">{{ __('Back') }}</x-ui.button>
                <x-ui.button type="submit" variant="default">{{ __('Create Quotation') }}</x-ui.button>
            </div>
        </x-platform.panel>
    </form>
</x-app-layout>
