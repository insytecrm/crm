<x-app-layout :title="$plan->name . ' | InSyte CRM'">
    <x-platform.plan-shell :plan="$plan" :shell="$shell">
        <div class="grid gap-4 lg:grid-cols-4">
            <x-platform.panel class="lg:col-span-4" :title="__('Plan summary')" compact>
                <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                    <div>
                        <dt class="text-sm text-slate-500">{{ __('Plan Name') }}</dt>
                        <dd class="mt-1 font-semibold text-black">{{ $plan->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-slate-500">{{ __('Monthly Price') }}</dt>
                        <dd class="mt-1 font-semibold text-black">{{ $plan->monthlyPriceLabel() }} {{ __(' / month') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-slate-500">{{ __('Annual Price') }}</dt>
                        <dd class="mt-1 font-semibold text-black">{{ $plan->annualPriceLabel() }} {{ __(' / year') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-slate-500">{{ __('Trial') }}</dt>
                        <dd class="mt-1 font-semibold text-black">{{ $plan->trialLabel() }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-slate-500">{{ __('Active Partners') }}</dt>
                        <dd class="mt-1 font-semibold text-black">{{ number_format($shell['partners_count']) }}</dd>
                    </div>
                </dl>
            </x-platform.panel>
        </div>

        <div class="mt-4 grid gap-4 lg:grid-cols-2">
            <x-platform.panel :title="__('Pricing')" compact>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">{{ __('Monthly') }}</dt>
                        <dd class="font-medium text-black">{{ $plan->monthlyPriceLabel() }} {{ __(' / month') }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">{{ __('Annual') }}</dt>
                        <dd class="font-medium text-black">{{ $plan->annualPriceLabel() }} {{ __(' / year') }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">{{ __('Trial') }}</dt>
                        <dd class="font-medium text-black">{{ $plan->trial_enabled ? __(':days-day free trial', ['days' => $plan->trial_days]) : __('No trial') }}</dd>
                    </div>
                </dl>
            </x-platform.panel>

            <x-platform.panel :title="__('Included Features')" compact>
                <ul class="space-y-2 text-sm">
                    @foreach ($includedFeatures as $feature)
                        <li class="flex items-center justify-between gap-3">
                            <span class="text-black">{{ $feature['label'] }}</span>
                            <span class="{{ $feature['included'] ? 'text-emerald-600' : 'text-slate-300' }}">
                                {{ $feature['included'] ? '✓' : '○' }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            </x-platform.panel>
        </div>
    </x-platform.plan-shell>
</x-app-layout>
