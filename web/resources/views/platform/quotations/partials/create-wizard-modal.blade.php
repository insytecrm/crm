@php
    $quotationPlans = $quotationPlans ?? [];
    $leadSelectOptions = $leadSelectOptions ?? ($leadSearchOptions ?? []);
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
    <div
        x-on:preset-quotation-lead.window="applyLead($event.detail)"
        x-data="{
                step: {{ (int) old('_quotation_wizard_step', 1) }},
                leadId: @js(old('platform_lead_id')),
                leads: @js($leadSelectOptions),
                companyName: @js(old('company_name', '')),
                ownerName: @js(old('owner_name', '')),
                email: @js(old('email', '')),
                phone: @js(old('phone', '')),
                reraNumber: @js(old('rera_number', '')),
                gstNumber: @js(old('gst_number', '')),
                planId: @js($defaultPlanId ? (int) $defaultPlanId : null),
                billingCycle: @js(old('billing_cycle', 'monthly')),
                planPrice: {{ $defaultPlanPrice }},
                discountAmount: {{ $defaultDiscount }},
                taxAmount: {{ $defaultTax }},
                taxTouched: {{ old('tax_amount') !== null ? 'true' : 'false' }},
                validUntil: @js(old('valid_until', now()->addDays(7)->toDateString())),
                taxRate: {{ (float) $defaultTaxRate }},
                plans: @js($quotationPlans),
                applyLead(lead) {
                    if (! lead) return;
                    this.leadId = lead.id;
                    this.companyName = lead.company_name || '';
                    this.ownerName = lead.contact_person || '';
                    this.email = lead.email || '';
                    this.phone = lead.phone || '';
                    this.reraNumber = lead.rera_number || '';
                    this.gstNumber = lead.gst_number || '';
                },
                selectLead(event) {
                    const lead = this.leads.find(item => item.id == event.target.value);
                    if (lead) this.applyLead(lead);
                },
                syncPlanPrice() {
                    const plan = this.plans.find(item => item.id == this.planId);
                    if (! plan) return;
                    this.planPrice = this.billingCycle === 'annual' ? plan.price_annual : plan.price_monthly;
                    if (! this.taxTouched) this.recalculateTax();
                },
                recalculateTax() {
                    const taxable = Math.max(this.planPrice - this.discountAmount, 0);
                    this.taxAmount = Math.round(taxable * this.taxRate);
                },
                totalAmount() {
                    return Math.max(this.planPrice - this.discountAmount, 0) + this.taxAmount;
                },
            }"
        x-init="syncPlanPrice()"
    >
        <x-modal name="create-quotation" maxWidth="2xl" :show="$openQuotationModal" focusable>
            <x-ui.modal.header
                :title="__('Create Quotation')"
                :description="__('Select a lead, confirm company details, then choose plan and pricing.')"
                modal-name="create-quotation"
            />

            <form method="POST" action="{{ route('platform.quotations.modal.store') }}" x-ref="form" x-on:submit="if (step !== 3) { $event.preventDefault(); step++; }">
                @csrf
                <input type="hidden" name="_quotation_wizard" value="1">
                <input type="hidden" name="_quotation_wizard_step" :value="step">
                <input type="hidden" name="platform_lead_id" :value="leadId">
                <input type="hidden" name="owner_name" :value="ownerName">
                <input type="hidden" name="email" :value="email">
                <input type="hidden" name="phone" :value="phone">
                <input type="hidden" name="plan_price" :value="planPrice">
                <input type="hidden" name="discount_amount" :value="discountAmount">
                <input type="hidden" name="tax_amount" :value="taxAmount">

                <x-ui.modal.body class="max-h-[60vh] space-y-4 overflow-y-auto">
                    <p class="text-xs font-medium text-slate-500" x-text="'Step ' + step + ' of 3'"></p>

                    <div x-show="step === 1" class="space-y-4">
                        <div>
                            <x-input-label for="quotation_lead_id" :value="__('Lead')" />
                            <select id="quotation_lead_id" class="mt-1 block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-navy focus:ring-navy" x-model="leadId" x-on:change="selectLead($event)" required>
                                <option value="">{{ __('Select a lead') }}</option>
                                <template x-for="lead in leads" :key="lead.id">
                                    <option :value="lead.id" x-text="lead.company_name"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <x-input-label for="quotation_company_name" :value="__('Company Name')" />
                            <x-text-input id="quotation_company_name" name="company_name" type="text" class="mt-1 block w-full" x-model="companyName" required />
                        </div>
                        <div>
                            <x-input-label for="quotation_rera_number" :value="__('RERA Number (optional)')" />
                            <x-text-input id="quotation_rera_number" name="rera_number" type="text" class="mt-1 block w-full" x-model="reraNumber" />
                        </div>
                        <div>
                            <x-input-label for="quotation_gst_number" :value="__('GST Number (optional)')" />
                            <x-text-input id="quotation_gst_number" name="gst_number" type="text" class="mt-1 block w-full" x-model="gstNumber" />
                        </div>
                    </div>

                    <div x-show="step === 2" class="space-y-4">
                        <div>
                            <x-input-label for="quotation_plan_id" :value="__('Plan')" />
                            <select id="quotation_plan_id" name="plan_id" class="mt-1 block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-navy focus:ring-navy" x-model="planId" x-on:change="syncPlanPrice()" required>
                                <template x-for="plan in plans" :key="plan.id">
                                    <option :value="plan.id" x-text="plan.name"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <x-input-label for="quotation_billing_cycle" :value="__('Billing Cycle')" />
                            <select id="quotation_billing_cycle" name="billing_cycle" class="mt-1 block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-navy focus:ring-navy" x-model="billingCycle" x-on:change="syncPlanPrice()" required>
                                <option value="monthly">{{ __('Monthly') }}</option>
                                <option value="annual">{{ __('Annual') }}</option>
                            </select>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <x-input-label for="quotation_discount_amount" :value="__('Discount')" />
                                <x-text-input id="quotation_discount_amount" type="number" min="0" class="mt-1 block w-full" x-model.number="discountAmount" x-on:input="recalculateTax()" />
                            </div>
                            <div>
                                <x-input-label for="quotation_tax_amount" :value="__('Tax')" />
                                <x-text-input id="quotation_tax_amount" type="number" min="0" class="mt-1 block w-full" x-model.number="taxAmount" x-on:input="taxTouched = true" />
                            </div>
                        </div>
                        <div>
                            <x-input-label for="quotation_valid_until" :value="__('Valid Until')" />
                            <x-text-input id="quotation_valid_until" name="valid_until" type="date" class="mt-1 block w-full" x-model="validUntil" required />
                        </div>
                    </div>

                    <div x-show="step === 3" class="space-y-2 text-sm">
                        <p class="font-medium text-black">{{ __('Review') }}</p>
                        <p><span class="text-slate-500">{{ __('Company') }}:</span> <span x-text="companyName"></span></p>
                        <p><span class="text-slate-500">{{ __('Total') }}:</span> ₹<span x-text="totalAmount().toLocaleString('en-IN')"></span></p>
                    </div>
                </x-ui.modal.body>

                <x-ui.modal.footer class="justify-between">
                    <x-ui.button type="button" variant="outline" x-show="step > 1" @click="step--">{{ __('Back') }}</x-ui.button>
                    <div class="ms-auto flex gap-2">
                        <x-ui.button type="button" variant="outline" @click="$dispatch('close-modal', 'create-quotation')">{{ __('Cancel') }}</x-ui.button>
                        <x-ui.button type="submit" variant="default" x-text="step === 3 ? {{ \Illuminate\Support\Js::from(__('Create Quotation')) }} : {{ \Illuminate\Support\Js::from(__('Next')) }}"></x-ui.button>
                    </div>
                </x-ui.modal.footer>
            </form>
        </x-modal>
    </div>
@endpush
