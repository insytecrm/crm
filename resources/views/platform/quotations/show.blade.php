<x-app-layout :title="'#'.$quotation->number.' | InSyte CRM'">
    <div class="mb-4">
        <a href="{{ route('platform.quotations') }}" class="text-sm font-medium text-slate-500 hover:text-navy">← {{ __('Quotations') }}</a>
    </div>

    <x-platform.page-header
        :title="'#'.$quotation->number"
        :description="$quotation->companyDisplayName().' · '.($quotation->plan?->name ?? '—')"
    >
        <x-slot:actions>
            <x-platform.status-badge :status="$quotation->status" />
            @include('platform.quotations.partials.action-buttons', [
                'quotation' => $quotation,
                'context' => 'detail',
            ])
        </x-slot:actions>
    </x-platform.page-header>

    <x-auth-session-status class="mb-4 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700" :status="session('status')" />
    <x-input-error class="mb-4" :messages="$errors->get('quotation')" />

    @if (! empty($handover) && is_array($handover))
        <x-platform.panel class="mb-4 border-emerald-100" :title="__('Handover login details')" compact>
            <p class="mb-4 text-sm text-slate-600">{{ __('Copy these once and share them with the client. The password is not shown again.') }}</p>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Company') }}</dt>
                    <dd class="font-medium text-black">{{ $handover['company_name'] ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Login URL') }}</dt>
                    <dd class="font-medium text-black break-all">
                        <a href="{{ $handover['login_url'] ?? '#' }}" class="text-navy hover:underline" target="_blank" rel="noopener">{{ $handover['login_url'] ?? '—' }}</a>
                    </dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Admin Email') }}</dt>
                    <dd class="font-medium text-black">{{ $handover['admin_email'] ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Temporary Password') }}</dt>
                    <dd class="font-semibold text-black font-mono">{{ $handover['admin_password'] ?? '—' }}</dd>
                </div>
            </dl>
            <div class="mt-4 flex flex-wrap gap-2">
                @if (! empty($handover['partner_url']))
                    <x-ui.button variant="outline" :href="$handover['partner_url']">{{ __('Open Channel Partner') }}</x-ui.button>
                @endif
                @if (! empty($handover['subscription_url']))
                    <x-ui.button variant="outline" :href="$handover['subscription_url']">{{ __('View Subscription') }}</x-ui.button>
                @endif
            </div>
        </x-platform.panel>
    @endif

    @if ($quotation->isAccepted())
        <x-platform.panel class="mb-4" :title="__('Accepted Information')" compact>
            <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <dt class="text-sm text-slate-500">{{ __('Accepted Date') }}</dt>
                    <dd class="mt-1 font-semibold text-black">{{ $quotation->accepted_at?->timezone(config('app.timezone'))->format('d M Y') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-slate-500">{{ __('Accepted By') }}</dt>
                    <dd class="mt-1 font-semibold text-black">{{ $quotation->accepted_by_name ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-slate-500">{{ __('Plan') }}</dt>
                    <dd class="mt-1 font-semibold text-black">{{ $quotation->plan?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-slate-500">{{ __('Billing') }}</dt>
                    <dd class="mt-1 font-semibold text-black">{{ $quotation->billing_cycle?->label() ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-slate-500">{{ __('Amount') }}</dt>
                    <dd class="mt-1 font-semibold text-black">{{ $quotation->amountLabel() }}</dd>
                </div>
            </dl>
        </x-platform.panel>

        <x-platform.panel class="mb-4" :title="__('Next Action')" compact>
            @if ($quotation->canStartOnboarding())
                <p class="text-sm text-slate-600">{{ __('Quotation accepted. Start onboarding to create the client workspace and hand over login credentials.') }}</p>
                <div class="mt-4">
                    <x-ui.button variant="default" :href="route('platform.quotations.onboard', $quotation)">{{ __('Start Onboarding') }}</x-ui.button>
                </div>
            @elseif ($quotation->canCreateSubscription())
                <p class="text-sm text-slate-600">{{ __('Quotation accepted. Continue to subscription and billing.') }}</p>
                <form method="POST" action="{{ route('platform.quotations.create-subscription', $quotation) }}" class="mt-4">
                    @csrf
                    <x-ui.button type="submit" variant="default">{{ __('Create Subscription') }}</x-ui.button>
                </form>
            @elseif ($quotation->subscription)
                <p class="text-sm text-slate-600">{{ __('Client onboarded. Subscription and first invoice are ready.') }}</p>
                <div class="mt-4 flex flex-wrap gap-2">
                    @if ($quotation->tenant)
                        <x-ui.button variant="outline" :href="route('tenants.show', $quotation->tenant)">{{ __('Open Channel Partner') }}</x-ui.button>
                    @endif
                    <x-ui.button variant="outline" :href="route('platform.revenue.subscriptions.show', $quotation->subscription)">{{ __('View Subscription') }}</x-ui.button>
                </div>
            @endif
        </x-platform.panel>
    @endif

    @if ($quotation->platformLead)
        <x-platform.panel class="mb-4" :title="__('Related Lead')" compact>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="font-semibold text-black">{{ $quotation->platformLead->company_name }}</p>
                    <p class="mt-1 text-sm text-slate-600">{{ $quotation->platformLead->contact_person }} · {{ $quotation->platformLead->stage->label() }}</p>
                </div>
                <x-ui.button variant="outline" size="sm" :href="route('platform.leads.show', $quotation->platformLead)">{{ __('View Lead') }}</x-ui.button>
            </div>
        </x-platform.panel>
    @endif

    <div class="grid gap-4 lg:grid-cols-2">
        <x-platform.panel :title="__('Quotation Summary')" compact>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Company') }}</dt>
                    <dd class="font-medium text-black">{{ $partner['company_name'] }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Plan') }}</dt>
                    <dd class="font-medium text-black">{{ $quotation->plan?->name ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Billing') }}</dt>
                    <dd class="font-medium text-black">{{ $quotation->billing_cycle?->label() ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Quotation Amount') }}</dt>
                    <dd class="font-medium text-black">{{ $quotation->amountLabel() }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Valid Until') }}</dt>
                    <dd class="font-medium text-black">{{ $quotation->valid_until?->timezone(config('app.timezone'))->format('d M Y') ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Status') }}</dt>
                    <dd><x-platform.status-badge :status="$quotation->status" /></dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Created') }}</dt>
                    <dd class="font-medium text-black">{{ $quotation->created_at?->timezone(config('app.timezone'))->format('d M Y') ?? '—' }}</dd>
                </div>
            </dl>
        </x-platform.panel>

        <x-platform.panel :title="__('Customer Information')" compact>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Company Name') }}</dt>
                    <dd class="font-medium text-black">{{ $partner['company_name'] }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Owner Name') }}</dt>
                    <dd class="font-medium text-black">{{ $partner['owner_name'] }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Email') }}</dt>
                    <dd class="font-medium text-black">
                        @if ($partner['email'] !== '—')
                            <a href="mailto:{{ $partner['email'] }}" class="text-navy hover:underline">{{ $partner['email'] }}</a>
                        @else
                            —
                        @endif
                    </dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Phone') }}</dt>
                    <dd class="font-medium text-black">{{ $partner['phone'] }}</dd>
                </div>
            </dl>
        </x-platform.panel>

        <x-platform.panel :title="__('Plan & Pricing')" compact>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Plan') }}</dt>
                    <dd class="font-medium text-black">{{ $quotation->plan?->name ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Billing Cycle') }}</dt>
                    <dd class="font-medium text-black">{{ $quotation->billing_cycle?->label() ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Price') }}</dt>
                    <dd class="font-medium text-black">{{ $quotation->amountLabel() }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Trial') }}</dt>
                    <dd class="font-medium text-black">{{ $quotation->trialLabel() }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Discount') }}</dt>
                    <dd class="font-medium text-black">{{ \App\Support\Platform\BillingMoney::format((int) $quotation->discount_amount) }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Tax') }}</dt>
                    <dd class="font-medium text-black">{{ \App\Support\Platform\BillingMoney::format((int) $quotation->tax_amount) }}</dd>
                </div>
                <div class="flex justify-between gap-3 border-t border-slate-100 pt-3">
                    <dt class="font-semibold text-black">{{ __('Total') }}</dt>
                    <dd class="font-semibold text-black">{{ $quotation->totalLabel() }}</dd>
                </div>
            </dl>
        </x-platform.panel>

        <x-platform.panel :title="__('Quotation Timeline')" compact>
            @php
                $timeline = $quotation->timeline();
            @endphp
            @if ($timeline === [])
                <p class="text-sm text-slate-500">{{ __('No timeline events yet.') }}</p>
            @else
                <ol class="space-y-0 text-sm">
                    @foreach ($timeline as $index => $event)
                        <li class="flex flex-col">
                            <div class="flex items-start gap-3">
                                <span class="mt-1 size-2.5 shrink-0 rounded-full bg-navy"></span>
                                <div>
                                    <p class="font-medium text-black">{{ $event['label'] }}</p>
                                    <p class="text-slate-500">{{ $event['at']?->timezone(config('app.timezone'))->format('d M Y H:i') }}</p>
                                </div>
                            </div>
                            @if ($index < count($timeline) - 1)
                                <div class="ml-1 border-l border-slate-200 py-2 pl-0" aria-hidden="true">
                                    <span class="ml-[-1px] block pl-3 text-slate-300">↓</span>
                                </div>
                            @endif
                        </li>
                    @endforeach
                </ol>
            @endif
        </x-platform.panel>
    </div>

    @include('platform.quotations.partials.send-dialog')
</x-app-layout>
