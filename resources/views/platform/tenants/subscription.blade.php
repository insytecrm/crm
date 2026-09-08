<x-app-layout :title="__('Subscription') . ' · ' . $tenant->name . ' | InSyte CRM'">
    <x-platform.partner-shell :tenant="$tenant" :shell="$shell">
        <div class="mb-4">
            <h2 class="text-lg font-semibold text-black">{{ __('Subscription') }}</h2>
            <p class="mt-1 text-sm text-slate-500">
                <span class="font-medium text-black">{{ $subscription['plan_label'] }}</span>
                <span class="mx-1.5 text-slate-300">·</span>
                {{ $subscription['price_label'] }}
                <span class="mx-1.5 text-slate-300">·</span>
                <x-platform.status-badge :status="$subscription['status']" />
            </p>
        </div>

        <div class="grid gap-4 lg:grid-cols-3">
            <x-platform.panel class="lg:col-span-2" :title="__('Subscription')" compact>
                <dl class="space-y-3 text-sm">
                    @foreach ($subscription['details'] as $row)
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-slate-500">{{ $row['label'] }}</dt>
                            <dd class="font-medium text-black">{{ $row['value'] }}</dd>
                        </div>
                    @endforeach
                </dl>
            </x-platform.panel>

            <x-platform.panel :title="__('Actions')" compact>
                <div class="flex flex-col gap-2">
                    @if ($partnerSubscription)
                        <x-ui.button
                            type="button"
                            variant="outline"
                            class="w-full justify-center"
                            x-on:click="$dispatch('open-modal', 'partner-change-plan')"
                        >
                            {{ __('Change Plan') }}
                        </x-ui.button>
                        <x-ui.button
                            type="button"
                            variant="outline"
                            class="w-full justify-center"
                            x-on:click="$dispatch('open-modal', 'partner-extend-trial')"
                        >
                            {{ __('Extend Trial') }}
                        </x-ui.button>
                        <x-ui.button
                            type="button"
                            variant="outline"
                            class="w-full justify-center"
                            x-on:click="$dispatch('open-modal', 'partner-apply-discount')"
                        >
                            {{ __('Apply Discount') }}
                        </x-ui.button>
                        @if ($partnerSubscription->status === \App\Enums\SubscriptionStatus::Paused)
                            <form method="POST" action="{{ route('platform.revenue.subscriptions.resume', $partnerSubscription) }}">
                                @csrf
                                <x-ui.button type="submit" variant="outline" class="w-full justify-center">
                                    {{ __('Resume') }}
                                </x-ui.button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('platform.revenue.subscriptions.pause', $partnerSubscription) }}">
                                @csrf
                                <x-ui.button type="submit" variant="outline" class="w-full justify-center">
                                    {{ __('Pause') }}
                                </x-ui.button>
                            </form>
                        @endif
                        <form
                            method="POST"
                            action="{{ route('platform.revenue.subscriptions.cancel', $partnerSubscription) }}"
                            onsubmit="return confirm(@js(__('Cancel this subscription?')));"
                        >
                            @csrf
                            <x-ui.button type="submit" variant="destructive" class="w-full justify-center">
                                {{ __('Cancel') }}
                            </x-ui.button>
                        </form>
                    @else
                        <p class="text-sm text-slate-500">{{ __('No live subscription is available for these actions yet.') }}</p>
                    @endif
                </div>
            </x-platform.panel>
        </div>

        <x-platform.panel class="mt-4" :title="__('Billing History')" compact>
            @if (count($subscription['invoices']) === 0)
                <p class="text-sm text-slate-500">{{ __('Billing history will appear here when Revenue & Billing is connected.') }}</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead>
                            <tr>
                                <th class="px-3 py-3 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Date') }}</th>
                                <th class="px-3 py-3 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Invoice') }}</th>
                                <th class="px-3 py-3 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Amount') }}</th>
                                <th class="px-3 py-3 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($subscription['invoices'] as $invoice)
                                <tr>
                                    <td class="px-3 py-3 text-sm text-slate-600">{{ $invoice['date'] }}</td>
                                    <td class="px-3 py-3 text-sm font-medium text-black">{{ $invoice['invoice'] }}</td>
                                    <td class="px-3 py-3 text-sm text-black">{{ $invoice['amount'] }}</td>
                                    <td class="px-3 py-3"><x-platform.status-badge :status="$invoice['status']" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-platform.panel>
    </x-platform.partner-shell>

    @if ($partnerSubscription)
        @push('modals')
            <x-modal name="partner-change-plan" maxWidth="lg" focusable>
                <div class="p-6">
                    <h2 class="text-lg font-bold text-black">{{ __('Change Plan') }}</h2>
                    <form method="POST" action="{{ route('platform.revenue.subscriptions.change-plan', $partnerSubscription) }}" class="mt-4 space-y-3">
                        @csrf
                        <div>
                            <x-input-label for="partner-plan-id" :value="__('Plan')" />
                            <select id="partner-plan-id" name="plan_id" required class="mt-1 block h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-black shadow-sm focus:border-navy focus:ring-navy">
                                @foreach ($plans as $plan)
                                    <option value="{{ $plan->id }}" @selected($partnerSubscription->plan_id === $plan->id)>{{ $plan->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="partner-billing-cycle" :value="__('Billing Cycle')" />
                            <select id="partner-billing-cycle" name="billing_cycle" required class="mt-1 block h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-black shadow-sm focus:border-navy focus:ring-navy">
                                @foreach (\App\Enums\BillingCycle::cases() as $cycle)
                                    <option value="{{ $cycle->value }}" @selected($partnerSubscription->billing_cycle === $cycle)>{{ $cycle->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex justify-end gap-2">
                            <x-ui.button type="button" variant="outline" x-on:click="$dispatch('close-modal', 'partner-change-plan')">{{ __('Cancel') }}</x-ui.button>
                            <x-ui.button type="submit" variant="default">{{ __('Update Plan') }}</x-ui.button>
                        </div>
                    </form>
                </div>
            </x-modal>

            <x-modal name="partner-extend-trial" maxWidth="md" focusable>
                <div class="p-6">
                    <h2 class="text-lg font-bold text-black">{{ __('Extend Trial') }}</h2>
                    <form method="POST" action="{{ route('platform.revenue.subscriptions.extend-trial', $partnerSubscription) }}" class="mt-4 space-y-3">
                        @csrf
                        <div>
                            <x-input-label for="partner-trial-days" :value="__('Days')" />
                            <x-text-input id="partner-trial-days" name="days" type="number" min="1" max="90" value="7" class="mt-1 block w-full" required />
                        </div>
                        <div class="flex justify-end gap-2">
                            <x-ui.button type="button" variant="outline" x-on:click="$dispatch('close-modal', 'partner-extend-trial')">{{ __('Cancel') }}</x-ui.button>
                            <x-ui.button type="submit" variant="default">{{ __('Extend Trial') }}</x-ui.button>
                        </div>
                    </form>
                </div>
            </x-modal>

            <x-modal name="partner-apply-discount" maxWidth="md" focusable>
                <div class="p-6">
                    <h2 class="text-lg font-bold text-black">{{ __('Apply Discount') }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ __('Amount is in INR rupees. If an open invoice exists, the discount is applied there.') }}</p>
                    <form method="POST" action="{{ route('platform.revenue.subscriptions.discount', $partnerSubscription) }}" class="mt-4 space-y-3">
                        @csrf
                        <div>
                            <x-input-label for="partner-discount-amount" :value="__('Amount (₹)')" />
                            <x-text-input id="partner-discount-amount" name="amount" type="number" min="1" class="mt-1 block w-full" required />
                            <x-input-error class="mt-2" :messages="$errors->get('amount')" />
                        </div>
                        <div>
                            <x-input-label for="partner-discount-reason" :value="__('Reason')" />
                            <x-text-input id="partner-discount-reason" name="reason" type="text" class="mt-1 block w-full" required />
                            <x-input-error class="mt-2" :messages="$errors->get('reason')" />
                        </div>
                        <div class="flex justify-end gap-2">
                            <x-ui.button type="button" variant="outline" x-on:click="$dispatch('close-modal', 'partner-apply-discount')">{{ __('Cancel') }}</x-ui.button>
                            <x-ui.button type="submit" variant="default">{{ __('Apply Discount') }}</x-ui.button>
                        </div>
                    </form>
                </div>
            </x-modal>
        @endpush
    @endif
</x-app-layout>
