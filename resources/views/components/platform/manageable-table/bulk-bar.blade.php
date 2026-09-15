@props([
    'deleteUrl' => null,
    'deleteParam' => 'ids',
    'confirmMessage' => __('Are you sure you want to delete the selected items?'),
    'showDelete' => true,
])

<div
    x-show="selected.length > 0"
    x-cloak
    class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 bg-slate-50 px-4 py-3"
>
    <p class="text-sm font-medium text-black">
        <span x-text="selected.length"></span>
        {{ __('selected') }}
    </p>

    <div class="flex flex-wrap items-center gap-2">
        {{ $actions ?? '' }}

        @if ($showDelete && filled($deleteUrl))
            <form
                method="POST"
                action="{{ $deleteUrl }}"
                class="inline"
                @submit.prevent="if (confirm(@js($confirmMessage))) { $el.submit(); }"
            >
                @csrf
                @method('DELETE')
                @foreach (request()->query() as $key => $value)
                    @if (is_string($value) || is_numeric($value))
                        <input type="hidden" name="_redirect_query[{{ $key }}]" value="{{ $value }}">
                    @endif
                @endforeach
                <template x-for="id in selected" :key="id">
                    <input type="hidden" name="{{ $deleteParam }}[]" :value="id">
                </template>
                <x-ui.button type="submit" variant="destructive" size="sm">
                    {{ __('Delete') }}
                </x-ui.button>
            </form>
        @endif
    </div>
</div>
