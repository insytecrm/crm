<x-app-layout :title="__('Create Quotation') . ' | InSyte CRM'">
    <x-platform.page-header
        :title="__('Pricing')"
        :description="__('Step 3 of 4 — Pricing')"
    >
        <x-slot:actions>
            <x-ui.button variant="outline" :href="route('platform.quotations.wizard.plan')">{{ __('Back') }}</x-ui.button>
        </x-slot:actions>
    </x-platform.page-header>

    <x-platform.quotation-wizard-steps :step="$step" />

    <form
        method="POST"
        action="{{ route('platform.quotations.wizard.pricing.store') }}"
        class="mx-auto max-w-2xl"
        x-data="{
            planPrice: {{ (int) old('plan_price', $pricing['plan_price']) }},
            discount: {{ (int) old('discount_amount', $pricing['discount_amount']) }},
            tax: {{ (int) old('tax_amount', $pricing['tax_amount']) }},
            taxTouched: {{ old('tax_amount') !== null || array_key_exists('tax_amount', $draft) ? 'true' : 'false' }},
            rate: {{ (float) $defaultTaxRate }},
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
        <x-platform.panel :title="__('Pricing')">
            <div class="space-y-4">
                <div>
                    <x-input-label for="plan_price" :value="__('Plan Price')" />
                    <x-text-input
                        id="plan_price"
                        name="plan_price"
                        type="number"
                        min="0"
                        class="mt-1 block w-full"
                        x-model.number="planPrice"
                        @input="syncTax()"
                        required
                    />
                    <x-input-error class="mt-2" :messages="$errors->get('plan_price')" />
                </div>
                <div>
                    <x-input-label for="discount_amount" :value="__('Discount')" />
                    <x-text-input
                        id="discount_amount"
                        name="discount_amount"
                        type="number"
                        min="0"
                        class="mt-1 block w-full"
                        x-model.number="discount"
                        @input="syncTax()"
                        required
                    />
                    <x-input-error class="mt-2" :messages="$errors->get('discount_amount')" />
                </div>
                <div>
                    <x-input-label for="tax_amount" :value="__('Tax')" />
                    <x-text-input
                        id="tax_amount"
                        name="tax_amount"
                        type="number"
                        min="0"
                        class="mt-1 block w-full"
                        x-model.number="tax"
                        @input="taxTouched = true"
                        required
                    />
                    <p class="mt-1 text-xs text-slate-500">{{ __('Default is 18% GST on plan price minus discount. You can override the tax amount.') }}</p>
                    <x-input-error class="mt-2" :messages="$errors->get('tax_amount')" />
                </div>

                <dl class="space-y-3 rounded-xl border border-slate-100 bg-slate-50/70 p-4 text-sm">
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">{{ __('Subtotal') }}</dt>
                        <dd class="font-medium text-black" x-text="format(planPrice)"></dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">{{ __('Discount') }}</dt>
                        <dd class="font-medium text-black" x-text="format(discount)"></dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">{{ __('Tax') }}</dt>
                        <dd class="font-medium text-black" x-text="format(tax)"></dd>
                    </div>
                    <div class="flex justify-between gap-3 border-t border-slate-200 pt-3">
                        <dt class="font-semibold text-black">{{ __('Total') }}</dt>
                        <dd class="font-semibold text-black" x-text="format(total)"></dd>
                    </div>
                </dl>
            </div>

            <div class="mt-6 flex justify-end">
                <x-ui.button type="submit" variant="default">{{ __('Continue') }}</x-ui.button>
            </div>
        </x-platform.panel>
    </form>
</x-app-layout>
