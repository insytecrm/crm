<x-app-layout :title="__('Edit :name', ['name' => $tenant->name]) . ' | InSyte CRM'">
    <x-platform.page-header
        :title="__('Edit Channel Partner')"
        :description="$tenant->name"
    >
        <x-slot:actions>
            <x-ui.button variant="outline" :href="route('tenants.show', $tenant)">
                {{ __('Cancel') }}
            </x-ui.button>
        </x-slot:actions>
    </x-platform.page-header>

    <form method="POST" action="{{ route('tenants.update', $tenant) }}" class="mx-auto max-w-2xl space-y-4">
        @csrf
        @method('PUT')

        @include('platform.tenants.partials.edit-form-fields', [
            'tenant' => $tenant,
            'statuses' => $statuses,
            'fieldId' => $tenant->id,
        ])

        <div class="flex items-center gap-3">
            <x-ui.button type="submit" variant="default">{{ __('Save') }}</x-ui.button>
            <x-ui.button variant="link" :href="route('tenants.show', $tenant)">{{ __('Cancel') }}</x-ui.button>
        </div>
    </form>
</x-app-layout>
