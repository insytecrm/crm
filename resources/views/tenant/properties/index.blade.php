<x-tenant-layout :title="__('Properties') . ' | InSyte CRM'">
    <div
        @if ($openEditPropertyId)
            x-data
            x-init="$nextTick(() => $dispatch('open-modal', 'edit-property-{{ $openEditPropertyId }}'))"
        @endif
    >
    <x-auth-session-status class="mb-3 rounded-lg bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700" :status="session('status')" />

    <x-tenant.list-toolbar>
        <x-slot:search>
            <form method="GET" action="{{ route('tenant.properties.index') }}" class="w-full">
                <x-auth.icon-input
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="{{ __('Search by project, developer, or location...') }}"
                >
                    <x-slot:icon>
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                    </x-slot:icon>
                </x-auth.icon-input>
            </form>
        </x-slot:search>

        <x-ui.button variant="default" :href="route('tenant.properties.create')">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            {{ __('Add Property') }}
        </x-ui.button>
    </x-tenant.list-toolbar>

    @if ($properties->isEmpty())
        <div class="rounded-2xl border border-slate-100 bg-white px-4 py-12 text-center shadow-sm">
            <p class="text-sm text-slate-500">{{ __('No properties yet.') }}</p>
        </div>
    @else
        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
            @foreach ($properties as $property)
                @include('tenant.properties.partials.property-card', ['property' => $property])
            @endforeach
        </div>

        @if ($properties->hasPages())
            <div class="mt-4">{{ $properties->links() }}</div>
        @endif
    @endif

    @push('modals')
        @foreach ($properties as $property)
            @include('tenant.properties.partials.property-details-modal', ['property' => $property])
            @include('tenant.properties.partials.edit-property-modal', [
                'property' => $property,
                'propertyTypes' => $propertyTypes,
                'projectStatuses' => $projectStatuses,
            ])
        @endforeach
    @endpush
    </div>
</x-tenant-layout>
