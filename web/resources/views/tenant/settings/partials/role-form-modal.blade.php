@php
    $selectedKeys = collect(old('permissions', $role?->permissions->pluck('key')->all() ?? []));
    $isLocked = $role?->isAdministrator() ?? false;
@endphp

<x-modal :name="$modalName" maxWidth="2xl">
    <div class="max-h-[85vh] overflow-y-auto p-6">
        <h2 class="text-lg font-bold text-black">{{ $title }}</h2>

        @if ($isLocked)
            <p class="mt-2 rounded-xl border border-amber-100 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                {{ __('Administrators always have full access to all current and future permissions.') }}
            </p>
        @endif

        <form method="POST" action="{{ $action }}" class="mt-4 space-y-6">
            @csrf
            @if ($method !== 'POST')
                @method($method)
            @endif

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <x-input-label for="{{ $modalName }}_name" :value="__('Role Name')" />
                    <x-text-input
                        id="{{ $modalName }}_name"
                        name="name"
                        type="text"
                        class="mt-1 block w-full"
                        :value="old('name', $role?->name)"
                        required
                    />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="{{ $modalName }}_description" :value="__('Description')" />
                    <textarea
                        id="{{ $modalName }}_description"
                        name="description"
                        rows="2"
                        class="mt-1 block w-full rounded-lg border-slate-200 text-sm focus:border-navy focus:ring-navy"
                    >{{ old('description', $role?->description) }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('description')" />
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <h3 class="text-sm font-semibold text-black">{{ __('Permissions') }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ __('Toggle access for current features. New features added later will appear here.') }}</p>
                </div>

                @foreach ($permissionGroups as $group => $permissions)
                    <div class="overflow-hidden rounded-2xl border border-slate-100">
                        <div class="border-b border-slate-100 bg-slate-50 px-4 py-3">
                            <h4 class="text-sm font-semibold text-black">{{ $group }}</h4>
                        </div>
                        <div class="divide-y divide-slate-100">
                            @foreach ($permissions as $permission)
                                <x-ui.switch
                                    name="permissions[]"
                                    :value="$permission->key"
                                    :label="$permission->label"
                                    :description="$permission->description"
                                    :checked="$isLocked || $selectedKeys->contains($permission->key)"
                                    :disabled="$isLocked"
                                    class="rounded-none border-0 px-4 py-3"
                                />
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex justify-end gap-2">
                <x-ui.button type="button" variant="outline" @click="$dispatch('close-modal', '{{ $modalName }}')">{{ __('Cancel') }}</x-ui.button>
                <x-ui.button type="submit" variant="default">{{ $role ? __('Save Role') : __('Create Role') }}</x-ui.button>
            </div>
        </form>
    </div>
</x-modal>
