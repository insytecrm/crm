<x-app-layout :title="__('Create Plan') . ' | InSyte CRM'">
    <x-platform.page-header
        :title="__('Create Plan')"
        :description="__('Step 1 of 5 — Basic Info')"
    >
        <x-slot:actions>
            <x-ui.button variant="outline" :href="route('platform.plans')">{{ __('Cancel') }}</x-ui.button>
        </x-slot:actions>
    </x-platform.page-header>

    <x-platform.plan-wizard-steps :step="$step" />

    <form method="POST" action="{{ route('platform.plans.wizard.basic.store') }}" class="mx-auto max-w-2xl">
        @csrf
        <x-platform.panel :title="__('Basic Info')">
            <div class="space-y-4">
                <div>
                    <x-input-label for="name" :value="__('Plan Name')" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $draft['name'] ?? '')" required autofocus />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>
                <div>
                    <x-input-label for="description" :value="__('Description')" />
                    <textarea id="description" name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $draft['description'] ?? '') }}</textarea>
                </div>
                <div>
                    <x-input-label for="status" :value="__('Plan Status')" />
                    <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected(old('status', $draft['status'] ?? 'active') === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mt-6 flex justify-end">
                <x-ui.button type="submit" variant="default">{{ __('Continue') }}</x-ui.button>
            </div>
        </x-platform.panel>
    </form>
</x-app-layout>
