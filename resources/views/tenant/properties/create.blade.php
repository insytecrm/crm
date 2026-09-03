<x-tenant-layout :title="__('Add Property') . ' | InSyte CRM'">
    <div class="mb-2">
        <a href="{{ route('tenant.properties.index') }}" class="text-xs font-medium text-black hover:underline">&larr; {{ __('Back to Properties') }}</a>
    </div>

    <form method="POST" action="{{ route('tenant.properties.store') }}" enctype="multipart/form-data" class="property-form space-y-3">
        @csrf

        @include('tenant.properties.partials.property-form-fields', [
            'propertyTypes' => $propertyTypes,
            'projectStatuses' => $projectStatuses,
        ])

        <div class="property-form-actions">
            <x-ui.button variant="outline" size="sm" :href="route('tenant.properties.index')">{{ __('Cancel') }}</x-ui.button>
            <x-ui.button type="submit" variant="default" size="sm">{{ __('Create Property') }}</x-ui.button>
        </div>
    </form>
</x-tenant-layout>
