<x-modal name="edit-property-{{ $property->id }}" maxWidth="2xl">
    <div class="flex max-h-[90vh] flex-col">
        <div class="shrink-0 border-b border-slate-100 px-4 py-4 sm:px-6">
            <h2 class="text-lg font-bold text-black">{{ __('Edit Property') }}</h2>
            <p class="mt-0.5 text-sm text-slate-500">{{ $property->project_name }}</p>
        </div>

        <form
            method="POST"
            action="{{ route('tenant.properties.update', $property) }}"
            enctype="multipart/form-data"
            class="flex min-h-0 flex-1 flex-col"
        >
            @csrf
            @method('PATCH')
            <input type="hidden" name="_edit_property_id" value="{{ $property->id }}">

            <div class="min-h-0 flex-1 space-y-3 overflow-y-auto px-4 py-4 sm:px-6">
                @include('tenant.properties.partials.property-form-fields', [
                    'property' => $property,
                    'propertyTypes' => $propertyTypes,
                    'projectStatuses' => $projectStatuses,
                    'idPrefix' => 'edit_'.$property->id.'_',
                ])
            </div>

            <div class="flex shrink-0 justify-end gap-2 border-t border-slate-100 px-4 py-4 sm:px-6">
                <x-ui.button type="button" variant="outline" @click="$dispatch('close-modal', 'edit-property-{{ $property->id }}')">{{ __('Cancel') }}</x-ui.button>
                <x-ui.button type="submit" variant="default">{{ __('Save Changes') }}</x-ui.button>
            </div>
        </form>
    </div>
</x-modal>
