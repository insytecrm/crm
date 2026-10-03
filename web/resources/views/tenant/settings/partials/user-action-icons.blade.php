@props([
    'settingsUser',
    'currentUser',
])

@php
    $isSelf = $settingsUser->is($currentUser);
    $canToggleStatus = ! $isSelf;
@endphp

<x-ui.action-icon-group>
    <form
        method="POST"
        action="{{ route('tenant.settings.users.status.update', $settingsUser) }}"
        class="inline-flex"
        @change="$el.submit()"
    >
        @csrf
        @method('PATCH')
        <input type="hidden" name="is_active" value="0">
        <label
            title="{{ __('Active') }}"
            aria-label="{{ __('Toggle user status') }}"
            @class(['inline-flex items-center', 'cursor-pointer' => $canToggleStatus, 'cursor-not-allowed opacity-60' => ! $canToggleStatus])
        >
            <span class="relative inline-flex h-6 w-11 shrink-0">
                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    class="peer sr-only"
                    @checked($settingsUser->isActive())
                    @disabled(! $canToggleStatus)
                >
                <span class="absolute inset-0 rounded-full bg-slate-200 transition-colors peer-checked:bg-emerald-500 peer-disabled:bg-slate-200"></span>
                <span class="absolute left-0.5 top-0.5 size-5 rounded-full bg-white shadow-sm transition-transform peer-checked:translate-x-5"></span>
            </span>
        </label>
    </form>

    <x-ui.action-icon
        icon="edit"
        type="button"
        :title="__('Edit')"
        @click="$dispatch('open-modal', 'edit-settings-user-{{ $settingsUser->id }}')"
    />

    @unless ($isSelf)
        <form
            method="POST"
            action="{{ route('tenant.settings.users.destroy', $settingsUser) }}"
            onsubmit="return confirm(@js(__('Delete this user?')))"
            class="inline"
        >
            @csrf
            @method('DELETE')
            <x-ui.action-icon icon="delete" type="submit" :title="__('Delete')" />
        </form>
    @endunless
</x-ui.action-icon-group>
