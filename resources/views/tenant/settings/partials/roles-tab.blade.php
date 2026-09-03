@php
    use App\Enums\SettingsTab;
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-semibold text-black">{{ SettingsTab::Roles->label() }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ __('Manage role cards, toggle status, and edit permission switches from the editor.') }}</p>
        </div>
        <x-ui.button type="button" variant="default" @click="$dispatch('open-modal', 'create-settings-role')">
            {{ __('Add Role') }}
        </x-ui.button>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($roles as $role)
            @include('tenant.settings.partials.role-card', ['role' => $role])
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-slate-200 bg-slate-50/60 px-4 py-12 text-center text-sm text-slate-500">
                {{ __('No roles yet.') }}
            </div>
        @endforelse
    </div>
</div>

@push('modals')
    @include('tenant.settings.partials.role-form-modal', [
        'modalName' => 'create-settings-role',
        'title' => __('Add Role'),
        'action' => route('tenant.settings.roles.store'),
        'method' => 'POST',
        'role' => null,
        'permissionGroups' => $permissionGroups,
    ])

    @foreach ($roles as $role)
        @include('tenant.settings.partials.role-form-modal', [
            'modalName' => 'edit-settings-role-'.$role->id,
            'title' => __('Edit Role'),
            'action' => route('tenant.settings.roles.update', $role),
            'method' => 'PATCH',
            'role' => $role,
            'permissionGroups' => $permissionGroups,
        ])
    @endforeach
@endpush
