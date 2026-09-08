<x-tenant-layout :title="__('Properties') . ' | InSyte CRM'">
    <div
        @if ($openEditPropertyId)
            x-data
            x-init="$nextTick(() => $dispatch('open-modal', 'edit-property-{{ $openEditPropertyId }}'))"
        @endif
    >
    <x-auth-session-status class="mb-3 rounded-lg bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700" :status="session('status')" />

    <div class="mb-4 flex gap-2">
        <x-tenant.stat-card
            comfortable
            :label="__('Total')"
            :value="$statistics['total']"
            accent="navy"
            :href="route('tenant.properties.index', ['filter' => 'all'])"
            :active="$filter === \App\Enums\PropertyFilter::All"
        >
            <x-slot:icon>
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                </svg>
            </x-slot:icon>
        </x-tenant.stat-card>

        <x-tenant.stat-card
            comfortable
            :label="__('Active')"
            :value="$statistics['active']"
            accent="emerald"
            :href="route('tenant.properties.index', ['filter' => 'active'])"
            :active="$filter === \App\Enums\PropertyFilter::Active"
        >
            <x-slot:icon>
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </x-slot:icon>
        </x-tenant.stat-card>

        <x-tenant.stat-card
            comfortable
            :label="__('Deactive')"
            :value="$statistics['inactive']"
            accent="rose"
            :href="route('tenant.properties.index', ['filter' => 'inactive'])"
            :active="$filter === \App\Enums\PropertyFilter::Inactive"
        >
            <x-slot:icon>
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                </svg>
            </x-slot:icon>
        </x-tenant.stat-card>

        <x-tenant.stat-card
            comfortable
            :label="__('Featured')"
            :value="$statistics['featured']"
            accent="amber"
            :href="route('tenant.properties.index', ['filter' => 'featured'])"
            :active="$filter === \App\Enums\PropertyFilter::Featured"
        >
            <x-slot:icon>
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                </svg>
            </x-slot:icon>
        </x-tenant.stat-card>
    </div>

    <x-tenant.list-toolbar>
        <x-slot:search>
            <form method="GET" action="{{ route('tenant.properties.index') }}" class="w-full">
                <input type="hidden" name="filter" value="{{ $filter->value }}">
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
            <p class="text-sm text-slate-500">{{ $filter->emptyMessage() }}</p>
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
