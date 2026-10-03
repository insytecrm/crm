<x-app-layout :title="__('Edit Quotation') . ' | InSyte CRM'">
    <x-platform.page-header
        :title="__('Edit Quotation')"
        :description="'#'.$quotation->number"
    >
        <x-slot:actions>
            <x-ui.button variant="outline" :href="route('platform.quotations.show', $quotation)">{{ __('Cancel') }}</x-ui.button>
        </x-slot:actions>
    </x-platform.page-header>

    <form
        method="POST"
        action="{{ route('platform.quotations.update', $quotation) }}"
        class="mx-auto max-w-2xl space-y-4"
        x-data="{
            planPrice: {{ (int) old('plan_price', $quotation->plan_price) }},
            discount: {{ (int) old('discount_amount', $quotation->discount_amount) }},
            tax: {{ (int) old('tax_amount', $quotation->tax_amount) }},
            taxTouched: true,
            trialEnabled: @js((bool) old('trial_enabled', $quotation->trial_enabled)),
            rate: {{ \App\Support\Platform\QuotationPricing::DefaultTaxRate }},
            get taxable() {
                return Math.max(this.planPrice - this.discount, 0)
            },
            get defaultTax() {
                return Math.round(this.taxable * this.rate)
            },
            get total() {
                return this.taxable + Math.max(this.tax, 0)
            },
            syncTax() {
                if (! this.taxTouched) {
                    this.tax = this.defaultTax
                }
            },
            format(amount) {
                return '₹' + Number(amount).toLocaleString('en-IN')
            }
        }"
    >
        @csrf
        @method('PUT')

        <x-platform.panel :title="__('Company')" compact>
            <div class="space-y-4">
                <div>
                    <x-input-label for="company_name" :value="__('Company Name')" />
                    <x-text-input id="company_name" name="company_name" type="text" class="mt-1 block w-full" :value="old('company_name', $quotation->company_name)" required />
                    <x-input-error class="mt-2" :messages="$errors->get('company_name')" />
                </div>
                <div>
                    <x-input-label for="owner_name" :value="__('Owner Name')" />
                    <x-text-input id="owner_name" name="owner_name" type="text" class="mt-1 block w-full" :value="old('owner_name', $quotation->owner_name)" required />
                    <x-input-error class="mt-2" :messages="$errors->get('owner_name')" />
                </div>
                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $quotation->email)" required />
                    <x-input-error class="mt-2" :messages="$errors->get('email')" />
                </div>
                <div>
                    <x-input-label for="phone" :value="__('Phone')" />
                    <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $quotation->phone)" />
                    <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                </div>
            </div>
        </x-platform.panel>

        <x-platform.panel :title="__('Plan')" compact>
            <div class="space-y-4">
                <div>
                    <x-input-label for="plan_id" :value="__('Plan')" />
                    <select id="plan_id" name="plan_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        @foreach ($plans as $plan)
                            <option value="{{ $plan->id }}" @selected((string) old('plan_id', $quotation->plan_id) === (string) $plan->id)>{{ $plan->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('plan_id')" />
                </div>
                <div>
                    <x-input-label for="billing_cycle" :value="__('Billing Cycle')" />
                    <select id="billing_cycle" name="billing_cycle" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        @foreach ($billingCycles as $cycle)
                            <option value="{{ $cycle->value }}" @selected(old('billing_cycle', $quotation->billing_cycle?->value) === $cycle->value)>{{ $cycle->label() }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('billing_cycle')" />
                </div>
                <div>
                    <label class="flex items-center gap-3">
                        <input type="checkbox" name="trial_enabled" value="1" x-model="trialEnabled" @checked(old('trial_enabled', $quotation->trial_enabled))>
                        <span class="font-medium text-black">{{ __('Include free trial') }}</span>
                    </label>
                    <div class="mt-3" x-show="trialEnabled">
                        <x-input-label for="trial_days" :value="__('Trial')" />
                        <x-text-input id="trial_days" name="trial_days" type="number" min="1" max="90" class="mt-1 block w-40" :value="old('trial_days', $quotation->trial_days ?? 7)" />
                        <x-input-error class="mt-2" :messages="$errors->get('trial_days')" />
                    </div>
                </div>
            </div>
        </x-platform.panel>

        <x-platform.panel :title="__('Pricing')" compact>
            <div class="space-y-4">
                <div>
                    <x-input-label for="plan_price" :value="__('Plan Price')" />
                    <x-text-input id="plan_price" name="plan_price" type="number" min="0" class="mt-1 block w-full" x-model.number="planPrice" required />
                </div>
                <div>
                    <x-input-label for="discount_amount" :value="__('Discount')" />
                    <x-text-input id="discount_amount" name="discount_amount" type="number" min="0" class="mt-1 block w-full" x-model.number="discount" required />
                </div>
                <div>
                    <x-input-label for="tax_amount" :value="__('Tax')" />
                    <x-text-input id="tax_amount" name="tax_amount" type="number" min="0" class="mt-1 block w-full" x-model.number="tax" required />
                </div>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">{{ __('Total') }}</dt>
                        <dd class="font-semibold text-black" x-text="format(total)"></dd>
                    </div>
                </dl>
            </div>
        </x-platform.panel>

        <x-platform.panel :title="__('Valid Until')" compact>
            <x-text-input id="valid_until" name="valid_until" type="date" class="mt-1 block w-full" :value="old('valid_until', optional($quotation->valid_until)->format('Y-m-d'))" required />
            <x-input-error class="mt-2" :messages="$errors->get('valid_until')" />
        </x-platform.panel>

        <div class="flex justify-end gap-2">
            <x-ui.button variant="outline" :href="route('platform.quotations.show', $quotation)">{{ __('Cancel') }}</x-ui.button>
            <x-ui.button type="submit" variant="default">{{ __('Save Changes') }}</x-ui.button>
        </div>
    </form>
</x-app-layout>
