<x-app-layout :title="__('Create Plan') . ' | InSyte CRM'">
    <x-platform.page-header
        :title="__('Review Plan')"
        :description="__('Step 5 of 5 — Review')"
    >
        <x-slot:actions>
            <x-ui.button variant="outline" :href="route('platform.plans.wizard.limits')">{{ __('Back') }}</x-ui.button>
        </x-slot:actions>
    </x-platform.page-header>

    <x-platform.plan-wizard-steps :step="$step" />

    <div class="mx-auto max-w-2xl space-y-4">
        <x-platform.panel :title="__('Review Plan')" compact>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Plan') }}</dt>
                    <dd class="font-medium text-black">{{ $draft['name'] }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Monthly') }}</dt>
                    <dd class="font-medium text-black">₹{{ number_format((int) $draft['price_monthly']) }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Annual') }}</dt>
                    <dd class="font-medium text-black">₹{{ number_format((int) $draft['price_annual']) }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Trial') }}</dt>
                    <dd class="font-medium text-black">
                        {{ ! empty($draft['trial_enabled']) ? trans_choice(':count day|:count days', (int) ($draft['trial_days'] ?? 7), ['count' => (int) ($draft['trial_days'] ?? 7)]) : __('No trial') }}
                    </dd>
                </div>
            </dl>
        </x-platform.panel>

        <x-platform.panel :title="__('Features')" compact>
            <ul class="space-y-1 text-sm text-black">
                @forelse ($includedModules as $label)
                    <li>{{ $label }}</li>
                @empty
                    <li class="text-slate-500">{{ __('No modules selected.') }}</li>
                @endforelse
            </ul>
            @if (count($includedIntegrations) > 0)
                <p class="mt-3 text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Integrations') }}</p>
                <ul class="mt-1 space-y-1 text-sm text-black">
                    @foreach ($includedIntegrations as $label)
                        <li>{{ $label }}</li>
                    @endforeach
                </ul>
            @endif
        </x-platform.panel>

        <x-platform.panel :title="__('Limits')" compact>
            <dl class="space-y-2 text-sm">
                @foreach ($limitRows as $row)
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">{{ $row['label'] }}</dt>
                        <dd class="font-medium text-black">
                            {{ $row['value'] === null || $row['value'] === '' ? __('Unlimited') : number_format((int) $row['value']).($row['unit'] ?? '') }}
                        </dd>
                    </div>
                @endforeach
            </dl>
        </x-platform.panel>

        <form method="POST" action="{{ route('platform.plans.store') }}" class="flex justify-end">
            @csrf
            <x-ui.button type="submit" variant="default">{{ __('Create Plan') }}</x-ui.button>
        </form>
    </div>
</x-app-layout>
