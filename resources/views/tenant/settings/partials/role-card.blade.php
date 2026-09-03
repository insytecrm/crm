@props([
    'role',
])

@php
    $canDelete = ! $role->is_system;
    $canToggleStatus = ! $role->isAdministrator();
@endphp

<div @class([
    'flex h-full flex-col rounded-2xl border bg-white p-4 shadow-sm transition',
    'border-slate-100' => $role->is_active,
    'border-slate-200 bg-slate-50/80 opacity-90' => ! $role->is_active,
])>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <h3 class="truncate text-sm font-semibold text-black">{{ $role->name }}</h3>
                @if ($role->is_system)
                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-500">{{ __('System') }}</span>
                @endif
                @unless ($role->is_active)
                    <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-amber-700">{{ __('Inactive') }}</span>
                @endunless
            </div>
            <p class="mt-1 line-clamp-2 text-sm text-slate-500">{{ $role->description ?: __('No description provided.') }}</p>
        </div>
    </div>

    <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
        <div class="rounded-xl border border-slate-100 bg-slate-50/70 px-3 py-2">
            <dt class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Users') }}</dt>
            <dd class="mt-0.5 font-semibold text-black">{{ number_format($role->users_count) }}</dd>
        </div>
        <div class="rounded-xl border border-slate-100 bg-slate-50/70 px-3 py-2">
            <dt class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Permissions') }}</dt>
            <dd class="mt-0.5 font-semibold text-black">{{ number_format($role->permissions_count) }}</dd>
        </div>
    </dl>

    <div class="mt-4 flex items-center justify-between gap-3 border-t border-slate-100 pt-4">
        <form
            method="POST"
            action="{{ route('tenant.settings.roles.status.update', $role) }}"
            class="inline-flex items-center gap-2"
            @change="$el.submit()"
        >
            @csrf
            @method('PATCH')
            <input type="hidden" name="is_active" value="0">
            <label @class(['inline-flex items-center gap-2', 'cursor-pointer' => $canToggleStatus, 'cursor-not-allowed opacity-60' => ! $canToggleStatus])>
                <span class="text-xs font-medium text-slate-500">{{ __('Active') }}</span>
                <span class="relative inline-flex h-6 w-11 shrink-0">
                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        class="peer sr-only"
                        @checked($role->is_active)
                        @disabled(! $canToggleStatus)
                    >
                    <span class="absolute inset-0 rounded-full bg-slate-200 transition-colors peer-checked:bg-emerald-500 peer-disabled:bg-slate-200"></span>
                    <span class="absolute left-0.5 top-0.5 size-5 rounded-full bg-white shadow-sm transition-transform peer-checked:translate-x-5"></span>
                </span>
            </label>
        </form>

        <div class="inline-flex items-center gap-1.5">
            <x-ui.action-icon
                icon="edit"
                type="button"
                :title="__('Edit')"
                @click="$dispatch('open-modal', 'edit-settings-role-{{ $role->id }}')"
            />

            @if ($canDelete)
                <form
                    method="POST"
                    action="{{ route('tenant.settings.roles.destroy', $role) }}"
                    onsubmit="return confirm(@js(__('Delete this role?')))"
                    class="inline"
                >
                    @csrf
                    @method('DELETE')
                    <x-ui.action-icon icon="delete" type="submit" :title="__('Delete')" />
                </form>
            @endif
        </div>
    </div>
</div>
