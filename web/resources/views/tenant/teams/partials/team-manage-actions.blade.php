@props([
    'team',
])

<x-ui.action-icon-group {{ $attributes }}>
    <form
        method="POST"
        action="{{ route('tenant.teams.status.update', $team) }}"
        class="inline-flex"
        @change="$el.submit()"
    >
        @csrf
        @method('PATCH')
        <input type="hidden" name="is_active" value="0">
        <label
            title="{{ __('Active') }}"
            aria-label="{{ __('Toggle team status') }}"
            class="inline-flex cursor-pointer items-center"
        >
            <span class="relative inline-flex h-6 w-11 shrink-0">
                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    class="peer sr-only"
                    @checked($team->isActive())
                >
                <span class="absolute inset-0 rounded-full bg-slate-200 transition-colors peer-checked:bg-emerald-500"></span>
                <span class="absolute left-0.5 top-0.5 size-5 rounded-full bg-white shadow-sm transition-transform peer-checked:translate-x-5"></span>
            </span>
        </label>
    </form>

    <form
        method="POST"
        action="{{ route('tenant.teams.destroy', $team) }}"
        onsubmit="return confirm(@js(__('Archive this team? Historical records will be preserved.')))"
        class="inline"
    >
        @csrf
        @method('DELETE')
        <x-ui.action-icon icon="archive" type="submit" :title="__('Archive')" />
    </form>
</x-ui.action-icon-group>
