<x-app-layout :title="__('Edit Plan') . ' | InSyte CRM'">
    <x-platform.page-header
        :title="__('Edit Plan')"
        :description="$plan->name"
    >
        <x-slot:actions>
            <x-ui.button variant="outline" :href="route('platform.plans.show', $plan)">{{ __('Cancel') }}</x-ui.button>
        </x-slot:actions>
    </x-platform.page-header>

    @if ($partnersCount > 0)
        <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            {{ __('Changes may affect :count Channel Partners currently using this plan.', ['count' => number_format($partnersCount)]) }}
        </div>
    @endif

    <x-auth-session-status class="mb-4 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700" :status="session('status')" />

    <form method="POST" action="{{ route('platform.plans.update', $plan) }}" class="space-y-4" x-data="{ trial: {{ old('trial_enabled', $plan->trial_enabled) ? 'true' : 'false' }} }">
        @csrf
        @method('PUT')

        <x-platform.panel :title="__('Basic Information')">
            <div class="space-y-4">
                <div>
                    <x-input-label for="name" :value="__('Plan Name')" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $plan->name)" required />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>
                <div>
                    <x-input-label for="description" :value="__('Description')" />
                    <textarea id="description" name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $plan->description) }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('description')" />
                </div>
                <div>
                    <x-input-label for="status" :value="__('Plan Status')" />
                    <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected(old('status', $plan->status->value) === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </x-platform.panel>

        <x-platform.panel :title="__('Pricing')">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="price_monthly" :value="__('Monthly Price')" />
                    <x-text-input id="price_monthly" name="price_monthly" type="number" min="0" class="mt-1 block w-full" :value="old('price_monthly', $plan->price_monthly)" required />
                </div>
                <div>
                    <x-input-label for="price_annual" :value="__('Annual Price')" />
                    <x-text-input id="price_annual" name="price_annual" type="number" min="0" class="mt-1 block w-full" :value="old('price_annual', $plan->price_annual)" required />
                </div>
            </div>
            <label class="mt-4 flex items-center gap-3 text-sm font-medium text-black">
                <input type="checkbox" name="trial_enabled" value="1" class="rounded border-slate-300 text-navy focus:ring-navy" x-model="trial" @checked(old('trial_enabled', $plan->trial_enabled))>
                {{ __('Enable free trial') }}
            </label>
            <div class="mt-3" x-show="trial" x-cloak>
                <x-input-label for="trial_days" :value="__('Trial Duration (days)')" />
                <x-text-input id="trial_days" name="trial_days" type="number" min="1" max="90" class="mt-1 block w-32" :value="old('trial_days', $plan->trial_days)" />
            </div>
        </x-platform.panel>

        <div id="features">
            <x-platform.panel :title="__('Features')">
                @include('platform.plans.partials.feature-fields', [
                    'modules' => $features,
                    'integrations' => $integrations,
                    'packs' => $packs,
                    'capabilitiesByFeature' => $capabilitiesByFeature,
                    'plan' => $plan,
                    'draft' => [],
                ])
            </x-platform.panel>
        </div>

        <div id="limits">
            <x-platform.panel :title="__('Limits')">
                @include('platform.plans.partials.limit-fields', [
                    'limits' => $limits,
                    'plan' => $plan,
                    'draft' => [],
                ])
            </x-platform.panel>
        </div>

        <div class="flex justify-end">
            <x-ui.button type="submit" variant="default">{{ __('Save Changes') }}</x-ui.button>
        </div>
    </form>

    <div class="mt-6">
        @include('platform.plans.partials.preset-editor', ['presets' => $presets])
    </div>
</x-app-layout>
