<x-app-layout :title="__('Create Quotation') . ' | InSyte CRM'">
    <x-platform.page-header
        :title="__('Choose Plan')"
        :description="__('Step 2 of 4 — Plan')"
    >
        <x-slot:actions>
            <x-ui.button variant="outline" :href="route('platform.quotations.wizard.prospect')">{{ __('Back') }}</x-ui.button>
        </x-slot:actions>
    </x-platform.page-header>

    <x-platform.quotation-wizard-steps :step="$step" />

    @if ($plans->isEmpty())
        <x-platform.panel>
            <p class="text-sm text-slate-500">{{ __('No active plans available. Create a plan before sending quotations.') }}</p>
        </x-platform.panel>
    @else
        <form method="POST" action="{{ route('platform.quotations.wizard.plan.store') }}" class="mx-auto max-w-2xl" x-data="{ trialEnabled: @js((bool) old('trial_enabled', $draft['trial_enabled'] ?? true)) }">
            @csrf
            <x-platform.panel :title="__('Choose Plan')">
                <div class="space-y-4">
                    <fieldset class="space-y-3">
                        <legend class="text-sm font-medium text-slate-700">{{ __('Plan') }}</legend>
                        @foreach ($plans as $plan)
                            <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-4 hover:border-navy/40">
                                <input
                                    type="radio"
                                    name="plan_id"
                                    value="{{ $plan->id }}"
                                    class="mt-1"
                                    @checked((string) old('plan_id', $draft['plan_id'] ?? '') === (string) $plan->id)
                                    required
                                >
                                <span>
                                    <span class="block font-semibold text-black">{{ $plan->name }}</span>
                                    <span class="mt-1 block text-sm text-slate-500">
                                        {{ $plan->monthlyPriceLabel() }} {{ __(' / month') }}
                                        · {{ $plan->annualPriceLabel() }} {{ __(' / year') }}
                                    </span>
                                </span>
                            </label>
                        @endforeach
                        <x-input-error class="mt-2" :messages="$errors->get('plan_id')" />
                    </fieldset>

                    <fieldset class="space-y-3">
                        <legend class="text-sm font-medium text-slate-700">{{ __('Billing') }}</legend>
                        @foreach ($billingCycles as $cycle)
                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 hover:border-navy/40">
                                <input
                                    type="radio"
                                    name="billing_cycle"
                                    value="{{ $cycle->value }}"
                                    @checked(old('billing_cycle', $draft['billing_cycle'] ?? 'monthly') === $cycle->value)
                                    required
                                >
                                <span class="font-medium text-black">{{ $cycle->label() }}</span>
                            </label>
                        @endforeach
                        <x-input-error class="mt-2" :messages="$errors->get('billing_cycle')" />
                    </fieldset>

                    <div class="rounded-xl border border-slate-200 p-4">
                        <label class="flex items-center gap-3">
                            <input type="checkbox" name="trial_enabled" value="1" x-model="trialEnabled" @checked(old('trial_enabled', $draft['trial_enabled'] ?? true))>
                            <span class="font-medium text-black">{{ __('Include free trial') }}</span>
                        </label>
                        <div class="mt-3" x-show="trialEnabled">
                            <x-input-label for="trial_days" :value="__('Duration')" />
                            <x-text-input
                                id="trial_days"
                                name="trial_days"
                                type="number"
                                min="1"
                                max="90"
                                class="mt-1 block w-40"
                                :value="old('trial_days', $draft['trial_days'] ?? 7)"
                            />
                            <x-input-error class="mt-2" :messages="$errors->get('trial_days')" />
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <x-ui.button type="submit" variant="default">{{ __('Continue') }}</x-ui.button>
                </div>
            </x-platform.panel>
        </form>
    @endif
</x-app-layout>
