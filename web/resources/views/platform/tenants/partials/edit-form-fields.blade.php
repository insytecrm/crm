@php
    $fieldId = $fieldId ?? $tenant->id;
    $statuses = $statuses ?? \App\Enums\TenantStatus::cases();
@endphp

<div class="space-y-4">
    <x-platform.panel :title="__('Company Information')" compact>
        <div class="space-y-4">
            <div>
                <x-input-label :for="'name-'.$fieldId" :value="__('Company Name')" />
                <x-text-input :id="'name-'.$fieldId" name="name" type="text" class="mt-1 block w-full" :value="old('name', $tenant->name)" required />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>
            <div>
                <x-input-label :for="'slug-'.$fieldId" :value="__('Slug')" />
                <x-text-input :id="'slug-'.$fieldId" type="text" class="mt-1 block w-full bg-slate-50" :value="$tenant->id" disabled />
            </div>
            <div>
                <x-input-label :for="'location-'.$fieldId" :value="__('Location')" />
                <x-text-input :id="'location-'.$fieldId" name="location" type="text" class="mt-1 block w-full" :value="old('location', $tenant->location)" />
                <x-input-error class="mt-2" :messages="$errors->get('location')" />
            </div>
        </div>
    </x-platform.panel>

    <x-platform.panel :title="__('Account Information')" compact>
        <div class="space-y-4">
            <div>
                <x-input-label :for="'status-'.$fieldId" :value="__('Status')" />
                <x-ui.combobox
                    :id="'status-'.$fieldId"
                    name="status"
                    :options="collect($statuses)->map(fn ($status) => ['value' => $status->value, 'label' => ucfirst($status->value)])->all()"
                    :value="old('status', $tenant->status->value)"
                    :searchable="false"
                    required
                    class="mt-1"
                />
                <x-input-error class="mt-2" :messages="$errors->get('status')" />
            </div>
        </div>
    </x-platform.panel>

    <x-platform.panel :title="__('Contact Information')" compact>
        <div class="space-y-4">
            <div>
                <x-input-label :for="'owner_name-'.$fieldId" :value="__('Owner Name')" />
                <x-text-input :id="'owner_name-'.$fieldId" name="owner_name" type="text" class="mt-1 block w-full" :value="old('owner_name', $tenant->owner_name)" />
                <x-input-error class="mt-2" :messages="$errors->get('owner_name')" />
            </div>
            <div>
                <x-input-label :for="'email-'.$fieldId" :value="__('Email')" />
                <x-text-input :id="'email-'.$fieldId" name="email" type="email" class="mt-1 block w-full" :value="old('email', $tenant->email)" />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />
            </div>
            <div>
                <x-input-label :for="'phone-'.$fieldId" :value="__('Phone')" />
                <x-text-input :id="'phone-'.$fieldId" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $tenant->phone)" />
                <x-input-error class="mt-2" :messages="$errors->get('phone')" />
            </div>
        </div>
    </x-platform.panel>
</div>
