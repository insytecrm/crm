@php
    $quotationPlans = $quotationPlans ?? [];
    $openQuotationModal = $openQuotationModal ?? false;
    $defaultTaxRate = $defaultTaxRate ?? \App\Support\Platform\QuotationPricing::DefaultTaxRate;
    $defaultPlanId = old('plan_id', $quotationPlans[0]['id'] ?? null);
    $defaultPlan = collect($quotationPlans)->firstWhere('id', (int) $defaultPlanId) ?? ($quotationPlans[0] ?? null);
    $defaultPlanPrice = (int) old('plan_price', $defaultPlan['price_monthly'] ?? 0);
    $defaultDiscount = (int) old('discount_amount', 0);
    $defaultTax = (int) old(
        'tax_amount',
        \App\Support\Platform\QuotationPricing::defaultTax($defaultPlanPrice, $defaultDiscount)
    );
@endphp

@push('modals')
    <x-modal name="create-quotation" maxWidth="2xl" :show="$openQuotationModal" focusable>
        <div
            x-data="quotationCreateWizard({
                step: {{ (int) old('_quotation_wizard_step', 1) }},
                companyName: @js(old('company_name', '')),
                ownerName: @js(old('owner_name', '')),
                email: @js(old('email', '')),
                phone: @js(old('phone', '')),
                planId: @js($defaultPlanId ? (int) $defaultPlanId : null),
                billingCycle: @js(old('billing_cycle', 'monthly')),
                trialEnabled: @js(old('trial_enabled', true) ? true : false),
                trialDays: @js((int) old('trial_days', $defaultPlan['trial_days'] ?? 7)),
                planPrice: {{ $defaultPlanPrice }},
                discountAmount: {{ $defaultDiscount }},
                taxAmount: {{ $defaultTax }},
                taxTouched: {{ old('tax_amount') !== null ? 'true' : 'false' }},
                validUntil: @js(old('valid_until', now()->addDays(7)->toDateString())),
                taxRate: {{ (float) $defaultTaxRate }},
                plans: @js($quotationPlans),
            })"
        >
            <x-ui.modal.header
                :title="__('Create Quotation')"
                :description="__('Capture company, plan, and pricing in a few steps.')"
                modal-name="create-quotation"
            >
                <x-slot:icon>
                    <svg class="size-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                </x-slot:icon>
            </x-ui.modal.header>

            <form method="POST" action="{{ route('platform.quotations.modal.store') }}" x-ref="form" x-on:submit="if (step !== 4) { $event.preventDefault(); }">
                @csrf
                <input type="hidden" name="_quotation_wizard" value="1">
                <input type="hidden" name="_quotation_wizard_step" :value="step">
                <input type="hidden" name="plan_price" :value="planPrice">
                <input type="hidden" name="discount_amount" :value="discountAmount">
                <input type="hidden" name="tax_amount" :value="taxAmount">

                <x-ui.modal.body class="max-h-[60vh] overflow-y-auto">
                    <div>
                        <p class="mb-2 text-xs font-medium text-slate-500" x-text="stepLabel"></p>
                        <ol class="flex flex-wrap items-center gap-1.5 text-[11px] sm:text-xs">
                            <template x-for="item in steps" :key="item.n">
                                <li class="contents">
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 font-semibold"
                                        :class="item.n === step ? 'bg-navy text-white' : (item.n < step ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500')"
                                    >
                                        <span class="tabular-nums" x-text="item.n"></span>
                                        <span x-text="item.label"></span>
                                    </span>
                                    <span class="text-slate-300" x-show="item.n < 4" aria-hidden="true">→</span>
                                </li>
                            </template>
                        </ol>
                    </div>

                    <div x-show="step === 1" x-cloak>
                        <x-ui.modal.section :title="__('Company')">
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-x-4 sm:gap-y-3">
                                <div class="sm:col-span-2">
                                    <x-ui.modal.field-label for="quote_company_name" :value="__('Company Name')" required />
                                    <x-text-input id="quote_company_name" name="company_name" type="text" class="mt-0.5 block w-full" x-model="companyName" x-bind:required="step === 1" />
                                    <x-input-error class="mt-1" :messages="$errors->get('company_name')" />
                                </div>
                                <div class="sm:col-span-2">
                                    <x-ui.modal.field-label for="quote_owner_name" :value="__('Owner Name')" required />
                                    <x-text-input id="quote_owner_name" name="owner_name" type="text" class="mt-0.5 block w-full" x-model="ownerName" x-bind:required="step === 1" />
                                    <x-input-error class="mt-1" :messages="$errors->get('owner_name')" />
                                </div>
                                <div>
                                    <x-ui.modal.field-label for="quote_email" :value="__('Email')" required />
                                    <x-text-input id="quote_email" name="email" type="email" class="mt-0.5 block w-full" x-model="email" x-bind:required="step === 1" />
                                    <x-input-error class="mt-1" :messages="$errors->get('email')" />
                                </div>
                                <div>
                                    <x-ui.modal.field-label for="quote_phone" :value="__('Phone')" />
                                    <x-text-input id="quote_phone" name="phone" type="text" class="mt-0.5 block w-full" x-model="phone" />
                                    <x-input-error class="mt-1" :messages="$errors->get('phone')" />
                                </div>
                            </div>
                        </x-ui.modal.section>
                    </div>

                    <div x-show="step === 2" x-cloak>
                        <x-ui.modal.section :title="__('Plan')">
                            <div class="space-y-3">
                                <template x-if="plans.length === 0">
                                    <p class="text-sm text-slate-500">{{ __('No active plans available. Create a plan first.') }}</p>
                                </template>
                                <template x-for="plan in plans" :key="plan.id">
                                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-3 hover:border-navy/40">
                                        <input
                                            type="radio"
                                            name="plan_id"
                                            class="mt-1"
                                            :value="plan.id"
                                            x-model.number="planId"
                                            x-on:change="syncPlanPrice()"
                                            x-bind:required="step === 2"
                                        >
                                        <span>
                                            <span class="block font-semibold text-black" x-text="plan.name"></span>
                                            <span class="mt-0.5 block text-sm text-slate-500" x-text="formatMoney(plan.price_monthly) + ' / month · ' + formatMoney(plan.price_annual) + ' / year'"></span>
                                        </span>
                                    </label>
                                </template>
                                <x-input-error class="mt-1" :messages="$errors->get('plan_id')" />

                                <fieldset class="space-y-2">
                                    <legend class="text-sm font-medium text-slate-700">{{ __('Billing') }}</legend>
                                    <label class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2">
                                        <input type="radio" name="billing_cycle" value="monthly" x-model="billingCycle" x-on:change="syncPlanPrice()" x-bind:required="step === 2">
                                        <span class="text-sm font-medium text-black">{{ __('Monthly') }}</span>
                                    </label>
                                    <label class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2">
                                        <input type="radio" name="billing_cycle" value="annual" x-model="billingCycle" x-on:change="syncPlanPrice()" x-bind:required="step === 2">
                                        <span class="text-sm font-medium text-black">{{ __('Annual') }}</span>
                                    </label>
                                    <x-input-error class="mt-1" :messages="$errors->get('billing_cycle')" />
                                </fieldset>

                                <div class="rounded-xl border border-slate-200 p-3">
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" name="trial_enabled" value="1" x-model="trialEnabled">
                                        <span class="text-sm font-medium text-black">{{ __('Include free trial') }}</span>
                                    </label>
                                    <div class="mt-2" x-show="trialEnabled" x-cloak>
                                        <x-ui.modal.field-label for="quote_trial_days" :value="__('Duration (days)')" />
                                        <x-text-input id="quote_trial_days" name="trial_days" type="number" min="1" max="90" class="mt-0.5 block w-32" x-model.number="trialDays" x-bind:required="step === 2 && trialEnabled" />
                                        <x-input-error class="mt-1" :messages="$errors->get('trial_days')" />
                                    </div>
                                </div>
                            </div>
                        </x-ui.modal.section>
                    </div>

                    <div x-show="step === 3" x-cloak>
                        <x-ui.modal.section :title="__('Pricing')">
                            <div class="space-y-3">
                                <div>
                                    <x-ui.modal.field-label for="quote_plan_price_display" :value="__('Plan Price')" />
                                    <x-text-input id="quote_plan_price_display" type="number" min="0" class="mt-0.5 block w-full" x-model.number="planPrice" x-on:input="syncTax()" x-bind:required="step === 3" />
                                </div>
                                <div>
                                    <x-ui.modal.field-label for="quote_discount_display" :value="__('Discount')" />
                                    <x-text-input id="quote_discount_display" type="number" min="0" class="mt-0.5 block w-full" x-model.number="discountAmount" x-on:input="syncTax()" x-bind:required="step === 3" />
                                    <x-input-error class="mt-1" :messages="$errors->get('discount_amount')" />
                                </div>
                                <div>
                                    <x-ui.modal.field-label for="quote_tax_display" :value="__('Tax')" />
                                    <x-text-input id="quote_tax_display" type="number" min="0" class="mt-0.5 block w-full" x-model.number="taxAmount" x-on:input="taxTouched = true" x-bind:required="step === 3" />
                                    <p class="mt-1 text-xs text-slate-500">{{ __('Default is 18% GST. You can override the tax amount.') }}</p>
                                    <x-input-error class="mt-1" :messages="$errors->get('tax_amount')" />
                                </div>
                                <dl class="space-y-2 rounded-xl border border-slate-100 bg-slate-50/70 p-3 text-sm">
                                    <div class="flex justify-between gap-3">
                                        <dt class="text-slate-500">{{ __('Subtotal') }}</dt>
                                        <dd class="font-medium text-black" x-text="formatMoney(planPrice)"></dd>
                                    </div>
                                    <div class="flex justify-between gap-3">
                                        <dt class="text-slate-500">{{ __('Discount') }}</dt>
                                        <dd class="font-medium text-black" x-text="formatMoney(discountAmount)"></dd>
                                    </div>
                                    <div class="flex justify-between gap-3">
                                        <dt class="text-slate-500">{{ __('Tax') }}</dt>
                                        <dd class="font-medium text-black" x-text="formatMoney(taxAmount)"></dd>
                                    </div>
                                    <div class="flex justify-between gap-3 border-t border-slate-200 pt-2">
                                        <dt class="font-semibold text-black">{{ __('Total') }}</dt>
                                        <dd class="font-semibold text-black" x-text="formatMoney(total)"></dd>
                                    </div>
                                </dl>
                            </div>
                        </x-ui.modal.section>
                    </div>

                    <div x-show="step === 4" x-cloak>
                        <x-ui.modal.section :title="__('Review')">
                            <dl class="space-y-2 text-sm">
                                <div class="flex justify-between gap-4">
                                    <dt class="text-slate-500">{{ __('Company') }}</dt>
                                    <dd class="text-end font-medium text-black" x-text="companyName || '—'"></dd>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <dt class="text-slate-500">{{ __('Owner') }}</dt>
                                    <dd class="text-end font-medium text-black" x-text="ownerName || '—'"></dd>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <dt class="text-slate-500">{{ __('Email') }}</dt>
                                    <dd class="text-end font-medium text-black" x-text="email || '—'"></dd>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <dt class="text-slate-500">{{ __('Plan') }}</dt>
                                    <dd class="font-medium text-black" x-text="selectedPlanLabel"></dd>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <dt class="text-slate-500">{{ __('Billing') }}</dt>
                                    <dd class="font-medium text-black" x-text="billingCycle === 'annual' ? @js(__('Annual')) : @js(__('Monthly'))"></dd>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <dt class="text-slate-500">{{ __('Trial') }}</dt>
                                    <dd class="font-medium text-black" x-text="trialEnabled ? (trialDays + ' {{ __('days') }}') : @js(__('None'))"></dd>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <dt class="text-slate-500">{{ __('Total') }}</dt>
                                    <dd class="font-semibold text-black" x-text="formatMoney(total)"></dd>
                                </div>
                            </dl>
                            <div class="mt-4">
                                <x-ui.modal.field-label for="quote_valid_until" :value="__('Valid Until')" required />
                                <x-text-input id="quote_valid_until" name="valid_until" type="date" class="mt-0.5 block w-full" x-model="validUntil" x-bind:required="step === 4" />
                                <x-input-error class="mt-1" :messages="$errors->get('valid_until')" />
                            </div>
                        </x-ui.modal.section>
                    </div>
                </x-ui.modal.body>

                <x-ui.modal.footer class="justify-between sm:justify-between">
                    <div>
                        <x-ui.modal.cancel-button
                            modal-name="create-quotation"
                            x-show="step === 1"
                            x-cloak
                        />
                        <button
                            type="button"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-100 px-3.5 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-navy focus-visible:ring-offset-2"
                            x-show="step > 1"
                            x-cloak
                            x-on:click="back()"
                        >
                            {{ __('Back') }}
                        </button>
                    </div>

                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-navy px-3.5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-navy/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-navy focus-visible:ring-offset-2"
                            x-show="step < 4"
                            x-cloak
                            x-on:click="next()"
                        >
                            {{ __('Continue') }}
                        </button>
                        <div x-show="step === 4" x-cloak>
                            <x-ui.modal.submit-button>{{ __('Create Quotation') }}</x-ui.modal.submit-button>
                        </div>
                    </div>
                </x-ui.modal.footer>
            </form>
        </div>
    </x-modal>
@endpush

@pushOnce('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('quotationCreateWizard', (config) => ({
                step: Number(config.step || 1),
                companyName: config.companyName || '',
                ownerName: config.ownerName || '',
                email: config.email || '',
                phone: config.phone || '',
                planId: config.planId ? Number(config.planId) : null,
                billingCycle: config.billingCycle || 'monthly',
                trialEnabled: Boolean(config.trialEnabled),
                trialDays: Number(config.trialDays || 7),
                planPrice: Number(config.planPrice || 0),
                discountAmount: Number(config.discountAmount || 0),
                taxAmount: Number(config.taxAmount || 0),
                taxTouched: Boolean(config.taxTouched),
                validUntil: config.validUntil || '',
                taxRate: Number(config.taxRate || 0.18),
                plans: config.plans || [],
                steps: [
                    { n: 1, label: @js(__('Company')) },
                    { n: 2, label: @js(__('Plan')) },
                    { n: 3, label: @js(__('Pricing')) },
                    { n: 4, label: @js(__('Review')) },
                ],

                get stepLabel() {
                    return @js(__('Step :step of 4')).replace(':step', String(this.step));
                },

                get selectedPlan() {
                    return this.plans.find((plan) => Number(plan.id) === Number(this.planId)) || null;
                },

                get selectedPlanLabel() {
                    return this.selectedPlan ? this.selectedPlan.name : '—';
                },

                get taxable() {
                    return Math.max(Number(this.planPrice || 0) - Number(this.discountAmount || 0), 0);
                },

                get defaultTax() {
                    return Math.round(this.taxable * this.taxRate);
                },

                get total() {
                    return this.taxable + Math.max(Number(this.taxAmount || 0), 0);
                },

                init() {
                    if (! this.planId && this.plans.length > 0) {
                        this.planId = Number(this.plans[0].id);
                        this.syncPlanPrice();
                    }
                },

                syncPlanPrice() {
                    const plan = this.selectedPlan;
                    if (! plan) {
                        return;
                    }

                    this.planPrice = this.billingCycle === 'annual'
                        ? Number(plan.price_annual || 0)
                        : Number(plan.price_monthly || 0);

                    if (plan.trial_enabled && ! this.trialDays) {
                        this.trialDays = Number(plan.trial_days || 7);
                    }

                    this.syncTax();
                },

                syncTax() {
                    if (! this.taxTouched) {
                        this.taxAmount = this.defaultTax;
                    }
                },

                formatMoney(amount) {
                    return '₹' + Number(amount || 0).toLocaleString('en-IN');
                },

                next() {
                    if (! this.validateStep()) {
                        return;
                    }

                    if (this.step === 2) {
                        this.syncPlanPrice();
                    }

                    this.step = Math.min(4, this.step + 1);
                },

                back() {
                    this.step = Math.max(1, this.step - 1);
                },

                validateStep() {
                    const form = this.$refs.form;
                    if (! form) {
                        return true;
                    }

                    const fields = [...form.querySelectorAll('[name]')].filter((field) => {
                        const panel = field.closest('[x-show]');
                        if (! panel) {
                            return field.required;
                        }

                        return panel.offsetParent !== null && field.required;
                    });

                    for (const field of fields) {
                        if (! field.checkValidity()) {
                            field.reportValidity();

                            return false;
                        }
                    }

                    return true;
                },
            }));
        });
    </script>
@endpushOnce
