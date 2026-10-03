@php
    $trialPlans = $trialPlans ?? [];
    $askEmailCredentials = $askEmailCredentials ?? false;
    $alwaysEmailCredentials = $alwaysEmailCredentials ?? false;
    $defaultPlanId = $trialPlans[0]['id'] ?? null;
@endphp

@push('modals')
    <div
        x-on:open-partner-workflow.window="open($event.detail)"
        x-data="{
            mode: 'start_trial',
            step: 1,
            leadId: null,
            companyName: '',
            contactPerson: '',
            email: '',
            phone: '',
            reraNumber: '',
            gstNumber: '',
            planId: @js($defaultPlanId),
            trialDays: 7,
            plans: @js($trialPlans),
            quotation: null,
            adminName: '',
            adminEmail: '',
            slug: '',
            emailCredentials: true,
            modalName: 'partner-workflow',
            title() {
                if (this.mode === 'start_trial') return @js(__('Start Trial'));
                if (this.mode === 'activate') return @js(__('Activate Subscription'));
                return @js(__('Onboard Channel Partner'));
            },
            description() {
                if (this.mode === 'start_trial') return @js(__('Verify details, pick a plan, and create a trial workspace.'));
                if (this.mode === 'activate') return @js(__('Upgrade the trial workspace to the paid plan using the same login.'));
                return @js(__('Create the paid workspace after invoice payment.'));
            },
            submitLabel() {
                if (this.mode === 'start_trial') return @js(__('Start Trial'));
                if (this.mode === 'activate') return @js(__('Activate Subscription'));
                return @js(__('Create Channel Partner'));
            },
            formAction() {
                if (! this.leadId) return '#';
                const routes = {
                    start_trial: @js(str_replace('999999', '__ID__', route('platform.leads.start-trial', ['lead' => 999999]))),
                    onboard: @js(str_replace('999999', '__ID__', route('platform.leads.onboard', ['lead' => 999999]))),
                    activate: @js(str_replace('999999', '__ID__', route('platform.leads.activate-subscription', ['lead' => 999999]))),
                };
                return (routes[this.mode] || '#').replace('__ID__', this.leadId);
            },
            open(payload) {
                this.mode = payload.mode || 'start_trial';
                this.step = 1;
                this.leadId = payload.leadId;
                this.companyName = payload.company_name || '';
                this.contactPerson = payload.contact_person || '';
                this.email = payload.email || '';
                this.phone = payload.phone || '';
                this.reraNumber = payload.rera_number || '';
                this.gstNumber = payload.gst_number || '';
                this.quotation = payload.quotation || null;
                this.adminName = payload.contact_person || '';
                this.adminEmail = payload.email || '';
                this.slug = '';
                this.planId = payload.plan_id || @js($defaultPlanId);
                this.trialDays = payload.trial_days || 7;
                this.$dispatch('open-modal', 'partner-workflow');
            },
            next() { if (this.step < 4) this.step++; },
            back() { if (this.step > 1) this.step--; },
        }"
    >
        <x-modal name="partner-workflow" maxWidth="2xl" focusable>
            <div class="rounded-t-2xl bg-navy-dark px-5 py-3.5">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex min-w-0 items-center gap-2.5">
                        <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-white/20 text-white [&_svg]:size-4 [&_svg]:text-white">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h2 class="text-base font-bold leading-tight text-white" x-text="title()"></h2>
                            <p class="mt-0.5 text-xs leading-snug text-white/80" x-text="description()"></p>
                        </div>
                    </div>
                    <button
                        type="button"
                        class="shrink-0 rounded-md p-1 text-white/80 transition hover:bg-white/10 hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/50"
                        x-on:click="$dispatch('close-modal', 'partner-workflow')"
                        aria-label="{{ __('Close') }}"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <form method="POST" x-bind:action="formAction()">
                @csrf
                <template x-if="mode === 'start_trial'">
                    <div class="hidden">
                        <input type="hidden" name="company_name" x-bind:value="companyName">
                        <input type="hidden" name="email" x-bind:value="adminEmail">
                        <input type="hidden" name="phone" x-bind:value="phone">
                        <input type="hidden" name="plan_id" x-bind:value="planId">
                        <input type="hidden" name="trial_days" x-bind:value="trialDays">
                        <input type="hidden" name="slug" x-bind:value="slug">
                        <input type="hidden" name="rera_number" x-bind:value="reraNumber">
                        <input type="hidden" name="gst_number" x-bind:value="gstNumber">
                    </div>
                </template>
                <template x-if="mode === 'onboard'">
                    <div class="hidden">
                        <input type="hidden" name="rera_number" x-bind:value="reraNumber">
                        <input type="hidden" name="gst_number" x-bind:value="gstNumber">
                        <input type="hidden" name="admin_name" x-bind:value="adminName">
                        <input type="hidden" name="admin_email" x-bind:value="adminEmail">
                        <input type="hidden" name="slug" x-bind:value="slug">
                    </div>
                </template>
                <template x-if="mode === 'activate'">
                    <div class="hidden">
                        <input type="hidden" name="rera_number" x-bind:value="reraNumber">
                        <input type="hidden" name="gst_number" x-bind:value="gstNumber">
                    </div>
                </template>

                <x-ui.modal.body class="max-h-[60vh] space-y-4 overflow-y-auto">
                    <p class="text-xs font-medium text-slate-500" x-text="'Step ' + step + ' of 4'"></p>

                    <div x-show="step === 1" class="space-y-4">
                        <div>
                            <x-input-label :value="__('Company Name')" />
                            <x-text-input type="text" class="mt-1 block w-full" x-model="companyName" x-bind:readonly="mode !== 'start_trial'" x-bind:name="mode === 'start_trial' ? 'company_name' : null" x-bind:required="mode === 'start_trial'" />
                        </div>
                        <div>
                            <x-input-label :value="__('Contact Person')" />
                            <x-text-input type="text" class="mt-1 block w-full" x-model="contactPerson" readonly />
                        </div>
                        <div x-show="mode === 'start_trial'">
                            <x-input-label :value="__('Email')" />
                            <x-text-input type="email" class="mt-1 block w-full" x-model="email" required />
                            <p class="mt-1 text-xs text-slate-500">{{ __('Used as the admin login email.') }}</p>
                        </div>
                        <div x-show="mode === 'start_trial'">
                            <x-input-label :value="__('Phone')" />
                            <x-text-input type="text" class="mt-1 block w-full" x-model="phone" />
                        </div>
                        <div>
                            <x-input-label :value="__('RERA Number (optional)')" />
                            <x-text-input type="text" class="mt-1 block w-full" x-model="reraNumber" />
                        </div>
                        <div>
                            <x-input-label :value="__('GST Number (optional)')" />
                            <x-text-input type="text" class="mt-1 block w-full" x-model="gstNumber" />
                        </div>
                    </div>

                    <div x-show="step === 2" class="space-y-4">
                        <template x-if="mode === 'start_trial'">
                            <div class="space-y-4">
                                <div>
                                    <x-input-label :value="__('Plan')" />
                                    <select class="mt-1 block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-navy focus:ring-navy" x-model="planId" required>
                                        @foreach ($trialPlans as $plan)
                                            <option value="{{ $plan['id'] }}">{{ $plan['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-input-label :value="__('Trial Days')" />
                                    <x-text-input type="number" min="1" max="90" class="mt-1 block w-full" x-model="trialDays" required />
                                </div>
                            </div>
                        </template>
                        <template x-if="mode !== 'start_trial'">
                            <div class="space-y-3 text-sm">
                                <template x-if="quotation">
                                    <dl class="space-y-2 rounded-xl border border-slate-100 p-4">
                                        <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __('Quotation') }}</dt><dd class="font-medium" x-text="'#' + quotation.number"></dd></div>
                                        <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __('Plan') }}</dt><dd class="font-medium" x-text="quotation.plan"></dd></div>
                                        <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __('Billing') }}</dt><dd class="font-medium" x-text="quotation.billing_cycle"></dd></div>
                                        <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __('Total Paid') }}</dt><dd class="font-medium" x-text="quotation.total"></dd></div>
                                    </dl>
                                </template>
                                <p x-show="mode === 'activate'" class="text-xs text-slate-500">{{ __('The existing trial login will be kept.') }}</p>
                            </div>
                        </template>
                    </div>

                    <div x-show="step === 3" class="space-y-4">
                        <template x-if="mode === 'onboard'">
                            <div class="space-y-4">
                                <div>
                                    <x-input-label :value="__('Admin Name')" />
                                    <x-text-input type="text" class="mt-1 block w-full" x-model="adminName" required />
                                </div>
                                <div>
                                    <x-input-label :value="__('Admin Email')" />
                                    <x-text-input type="email" class="mt-1 block w-full" x-model="adminEmail" required />
                                </div>
                                <div>
                                    <x-input-label :value="__('Workspace slug (optional)')" />
                                    <x-text-input type="text" class="mt-1 block w-full" x-model="slug" />
                                </div>
                            </div>
                        </template>
                        <template x-if="mode === 'start_trial'">
                            <div class="space-y-4">
                                <div>
                                    <x-input-label :value="__('Admin Name')" />
                                    <x-text-input type="text" class="mt-1 block w-full" x-model="adminName" readonly />
                                </div>
                                <div>
                                    <x-input-label :value="__('Admin Email')" />
                                    <x-text-input type="email" class="mt-1 block w-full" x-model="adminEmail" required />
                                </div>
                                <div>
                                    <x-input-label :value="__('Workspace slug (optional)')" />
                                    <x-text-input type="text" class="mt-1 block w-full" x-model="slug" />
                                </div>
                            </div>
                        </template>
                        <p x-show="mode === 'activate'" class="text-sm text-slate-600">{{ __('Trial workspace found. Admin login will be kept and the plan will be upgraded.') }}</p>
                    </div>

                    <div x-show="step === 4" class="space-y-2 text-sm">
                        <p class="font-medium text-black" x-text="submitLabel()"></p>
                        <p class="text-slate-600" x-show="mode === 'start_trial'">{{ __('A trial Channel Partner workspace will be created.') }}</p>
                        <p class="text-slate-600" x-show="mode === 'activate'">{{ __('The trial workspace will move to a paid subscription with the same login.') }}</p>
                        <p class="text-slate-600" x-show="mode === 'onboard'">{{ __('A new Channel Partner will be created with an active paid subscription.') }}</p>
                        @if ($alwaysEmailCredentials)
                            <p x-show="mode === 'start_trial' || mode === 'onboard'" class="rounded-lg border border-slate-100 bg-slate-50 p-3 text-slate-700">
                                {{ __('Login details will be emailed automatically using Utilities SMTP.') }}
                            </p>
                        @elseif ($askEmailCredentials)
                            <label x-show="mode === 'onboard' || mode === 'start_trial'" class="inline-flex items-start gap-2 rounded-lg border border-slate-100 p-3 text-sm text-slate-700">
                                <input type="checkbox" value="1" class="mt-0.5 rounded border-slate-300 text-navy focus:ring-navy" x-model="emailCredentials">
                                <span>
                                    <span class="font-medium text-black">{{ __('Email login details') }}</span>
                                    <span class="mt-0.5 block text-xs text-slate-500">{{ __('Send credentials using Utilities SMTP.') }}</span>
                                </span>
                            </label>
                        @endif
                    </div>

                    <template x-if="emailCredentials && (mode === 'start_trial' || mode === 'onboard')">
                        <input type="hidden" name="email_credentials" value="1">
                    </template>
                </x-ui.modal.body>

                <x-ui.modal.footer class="justify-between">
                    <x-ui.button type="button" variant="outline" x-show="step > 1" x-on:click="back()">{{ __('Back') }}</x-ui.button>
                    <div class="ms-auto flex gap-2">
                        <x-ui.button type="button" variant="outline" x-on:click="$dispatch('close-modal', 'partner-workflow')">{{ __('Cancel') }}</x-ui.button>
                        <x-ui.button type="button" variant="default" x-show="step < 4" x-on:click="next()">{{ __('Next') }}</x-ui.button>
                        <x-ui.button type="submit" variant="default" x-show="step === 4" x-cloak x-text="submitLabel()"></x-ui.button>
                    </div>
                </x-ui.modal.footer>
            </form>
        </x-modal>
    </div>
@endpush
