@php
    $plans = $plans ?? [];
    $openAddModal = $openAddModal ?? false;
@endphp

@push('modals')
    <x-modal name="add-channel-partner" maxWidth="2xl" :show="$openAddModal" focusable>
        <div
            x-data="channelPartnerWizard({
                step: {{ (int) old('_wizard_step', 1) }},
                planKey: @js(old('plan_key', 'growth')),
                billingCycle: @js(old('billing_cycle', 'monthly')),
                startTrial: @js(old('start_trial', false) ? true : false),
                trialDays: @js((int) old('trial_days', 7)),
                name: @js(old('name', '')),
                ownerName: @js(old('owner_name', '')),
                email: @js(old('email', '')),
                phone: @js(old('phone', '')),
                location: @js(old('location', '')),
                slug: @js(old('slug', '')),
                adminName: @js(old('admin_name', '')),
                adminEmail: @js(old('admin_email', '')),
                sendInvitation: @js(old('send_invitation', true) ? true : false),
                plans: @js(collect($plans)->map(fn ($plan) => [
                    'key' => $plan['key'],
                    'label' => $plan['label'],
                    'price_monthly_label' => $plan['price_monthly_label'],
                ])->values()->all()),
            })"
        >
            <x-ui.modal.header
                :title="__('Add Channel Partner')"
                :description="__('Set up company, plan, and admin in a few steps.')"
                modal-name="add-channel-partner"
            >
                <x-slot:icon>
                    <svg class="size-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                    </svg>
                </x-slot:icon>
            </x-ui.modal.header>

            <form method="POST" action="{{ route('tenants.wizard.store') }}" x-ref="form" x-on:submit="if (step !== 4) { $event.preventDefault(); }">
                @csrf
                <input type="hidden" name="_wizard" value="1">
                <input type="hidden" name="_wizard_step" :value="step">

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

                    {{-- Step 1: Company --}}
                    <div x-show="step === 1" x-cloak>
                        <x-ui.modal.section :title="__('Company')">
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-x-4 sm:gap-y-3">
                                <div class="sm:col-span-2">
                                    <x-ui.modal.field-label for="wizard_name" :value="__('Company Name')" required />
                                    <x-text-input id="wizard_name" name="name" type="text" class="mt-0.5 block w-full" x-model="name" x-bind:required="step === 1" />
                                    <x-input-error class="mt-1" :messages="$errors->get('name')" />
                                </div>
                                <div class="sm:col-span-2">
                                    <x-ui.modal.field-label for="wizard_owner_name" :value="__('Owner Name')" required />
                                    <x-text-input id="wizard_owner_name" name="owner_name" type="text" class="mt-0.5 block w-full" x-model="ownerName" x-bind:required="step === 1" />
                                    <x-input-error class="mt-1" :messages="$errors->get('owner_name')" />
                                </div>
                                <div class="sm:col-span-2">
                                    <x-ui.modal.field-label for="wizard_email" :value="__('Email')" required />
                                    <x-text-input id="wizard_email" name="email" type="email" class="mt-0.5 block w-full" x-model="email" x-bind:required="step === 1" />
                                    <x-input-error class="mt-1" :messages="$errors->get('email')" />
                                </div>
                                <div>
                                    <x-ui.modal.field-label for="wizard_phone" :value="__('Phone')" />
                                    <x-text-input id="wizard_phone" name="phone" type="text" class="mt-0.5 block w-full" x-model="phone" />
                                    <x-input-error class="mt-1" :messages="$errors->get('phone')" />
                                </div>
                                <div>
                                    <x-ui.modal.field-label for="wizard_location" :value="__('Location')" />
                                    <x-text-input id="wizard_location" name="location" type="text" class="mt-0.5 block w-full" x-model="location" />
                                    <x-input-error class="mt-1" :messages="$errors->get('location')" />
                                </div>
                                <div class="sm:col-span-2">
                                    <x-ui.modal.field-label for="wizard_slug" :value="__('Workspace slug (optional)')" />
                                    <x-text-input id="wizard_slug" name="slug" type="text" class="mt-0.5 block w-full" x-model="slug" />
                                    <p class="mt-1 text-xs text-slate-500">{{ __('Leave blank to generate from the company name.') }}</p>
                                    <x-input-error class="mt-1" :messages="$errors->get('slug')" />
                                </div>
                            </div>
                        </x-ui.modal.section>
                    </div>

                    {{-- Step 2: Plan --}}
                    <div x-show="step === 2" x-cloak>
                        <x-ui.modal.section :title="__('Plan')">
                            <div class="space-y-2.5">
                                <template x-for="plan in plans" :key="plan.key">
                                    <label class="flex cursor-pointer items-center justify-between gap-4 rounded-xl border border-slate-200 px-4 py-3 hover:border-navy/40 has-[:checked]:border-navy has-[:checked]:ring-1 has-[:checked]:ring-navy/20">
                                        <span class="flex items-center gap-3">
                                            <input type="radio" name="plan_key" class="text-navy focus:ring-navy" :value="plan.key" x-model="planKey" x-bind:required="step === 2">
                                            <span class="text-sm font-medium text-black" x-text="plan.label"></span>
                                        </span>
                                        <span class="text-xs text-slate-500" x-text="plan.price_monthly_label + ' / {{ __('month') }}'"></span>
                                    </label>
                                </template>
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('plan_key')" />
                        </x-ui.modal.section>

                        <x-ui.modal.section :title="__('Billing')" class="mt-4">
                            <div class="space-y-2.5">
                                <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 has-[:checked]:border-navy has-[:checked]:ring-1 has-[:checked]:ring-navy/20">
                                    <input type="radio" name="billing_cycle" value="monthly" class="text-navy focus:ring-navy" x-model="billingCycle" x-bind:required="step === 2">
                                    <span class="text-sm font-medium text-black">{{ __('Monthly') }}</span>
                                </label>
                                <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 has-[:checked]:border-navy has-[:checked]:ring-1 has-[:checked]:ring-navy/20">
                                    <input type="radio" name="billing_cycle" value="annual" class="text-navy focus:ring-navy" x-model="billingCycle">
                                    <span class="text-sm font-medium text-black">{{ __('Annual') }}</span>
                                </label>
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('billing_cycle')" />

                            <label class="mt-4 flex items-center gap-3 text-sm font-medium text-black">
                                <input type="checkbox" name="start_trial" value="1" class="rounded border-slate-300 text-navy focus:ring-navy" x-model="startTrial">
                                {{ __('Start free trial') }}
                            </label>
                            <div class="mt-3" x-show="startTrial" x-cloak>
                                <x-ui.modal.field-label for="wizard_trial_days" :value="__('Duration (days)')" />
                                <x-text-input id="wizard_trial_days" name="trial_days" type="number" min="1" max="90" class="mt-0.5 block w-32" x-model="trialDays" />
                                <x-input-error class="mt-1" :messages="$errors->get('trial_days')" />
                            </div>
                        </x-ui.modal.section>
                    </div>

                    {{-- Step 3: Admin --}}
                    <div x-show="step === 3" x-cloak>
                        <x-ui.modal.section :title="__('Admin user')">
                            <div class="grid grid-cols-1 gap-3 sm:gap-y-3">
                                <div>
                                    <x-ui.modal.field-label for="wizard_admin_name" :value="__('Name')" required />
                                    <x-text-input id="wizard_admin_name" name="admin_name" type="text" class="mt-0.5 block w-full" x-model="adminName" x-bind:required="step === 3" />
                                    <x-input-error class="mt-1" :messages="$errors->get('admin_name')" />
                                </div>
                                <div>
                                    <x-ui.modal.field-label for="wizard_admin_email" :value="__('Email')" required />
                                    <x-text-input id="wizard_admin_email" name="admin_email" type="email" class="mt-0.5 block w-full" x-model="adminEmail" x-bind:required="step === 3" />
                                    <x-input-error class="mt-1" :messages="$errors->get('admin_email')" />
                                </div>
                                <label class="flex items-center gap-3 text-sm font-medium text-black">
                                    <input type="checkbox" name="send_invitation" value="1" class="rounded border-slate-300 text-navy focus:ring-navy" x-model="sendInvitation">
                                    {{ __('Send invitation email') }}
                                </label>
                                <p class="text-xs text-slate-500">{{ __('Invitation delivery will connect when the email module is ready. An admin account is still created now.') }}</p>
                            </div>
                        </x-ui.modal.section>
                    </div>

                    {{-- Step 4: Review --}}
                    <div x-show="step === 4" x-cloak>
                        <x-ui.modal.section :title="__('Review')">
                            <dl class="space-y-3 rounded-xl border border-slate-100 bg-slate-50/70 p-4 text-sm">
                                <div class="flex justify-between gap-4">
                                    <dt class="text-slate-500">{{ __('Company') }}</dt>
                                    <dd class="font-medium text-black" x-text="name || '—'"></dd>
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
                                    <dd class="font-medium text-black" x-text="startTrial ? (trialDays + ' {{ __('days') }}') : @js(__('None'))"></dd>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <dt class="text-slate-500">{{ __('Admin') }}</dt>
                                    <dd class="text-end">
                                        <div class="font-medium text-black" x-text="adminName || '—'"></div>
                                        <div class="text-navy" x-text="adminEmail || '—'"></div>
                                    </dd>
                                </div>
                            </dl>
                        </x-ui.modal.section>
                    </div>
                </x-ui.modal.body>

                <x-ui.modal.footer class="justify-between sm:justify-between">
                    <div>
                        <x-ui.modal.cancel-button
                            modal-name="add-channel-partner"
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
                            <x-ui.modal.submit-button>{{ __('Create Channel Partner') }}</x-ui.modal.submit-button>
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
            Alpine.data('channelPartnerWizard', (config) => ({
                step: Number(config.step || 1),
                planKey: config.planKey || 'growth',
                billingCycle: config.billingCycle || 'monthly',
                startTrial: Boolean(config.startTrial),
                trialDays: Number(config.trialDays || 7),
                name: config.name || '',
                ownerName: config.ownerName || '',
                email: config.email || '',
                phone: config.phone || '',
                location: config.location || '',
                slug: config.slug || '',
                adminName: config.adminName || '',
                adminEmail: config.adminEmail || '',
                sendInvitation: config.sendInvitation !== false,
                plans: config.plans || [],
                steps: [
                    { n: 1, label: @js(__('Company')) },
                    { n: 2, label: @js(__('Plan')) },
                    { n: 3, label: @js(__('Admin')) },
                    { n: 4, label: @js(__('Review')) },
                ],

                get stepLabel() {
                    return @js(__('Step :step of 4')).replace(':step', String(this.step));
                },

                get selectedPlanLabel() {
                    const match = this.plans.find((plan) => plan.key === this.planKey);

                    return match ? match.label : '—';
                },

                next() {
                    if (! this.validateStep()) {
                        return;
                    }

                    if (this.step === 1 && ! this.adminName) {
                        this.adminName = this.ownerName;
                    }

                    if (this.step === 1 && ! this.adminEmail) {
                        this.adminEmail = this.email;
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
