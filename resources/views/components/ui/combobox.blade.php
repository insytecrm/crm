@php($config = $config())

<div
    x-data="uiCombobox(@js($config))"
    @click.outside="open = false"
    @keydown.escape.window="open = false"
    x-on:resize.window="open && positionDropdown()"
    x-on:scroll.window.passive="open && positionDropdown()"
    {{ $attributes->merge(['class' => 'relative w-full']) }}
>
    <input
        type="hidden"
        x-ref="hiddenInput"
        x-model="selected"
        name="{{ $name }}"
        id="{{ $id }}"
        @disabled($disabled)
        @required($required)
    >

    <button
        type="button"
        x-ref="trigger"
        @click="toggle()"
        @disabled($disabled)
        :aria-expanded="open"
        aria-haspopup="listbox"
        class="flex h-10 w-full items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 text-left text-sm shadow-sm transition-colors hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-navy focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
    >
        <span
            class="min-w-0 flex-1 truncate text-left font-normal"
            :class="selected === '' ? 'text-slate-400' : 'text-black'"
            x-text="selectedLabel"
        ></span>
        <svg class="size-4 shrink-0 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
        </svg>
    </button>

    <div
        x-ref="dropdown"
        x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        x-cloak
        class="z-[100] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm shadow-slate-900/5"
        style="display: none;"
    >
        <div x-show="searchable" class="border-b border-slate-100 p-2">
            <input
                type="text"
                x-model="search"
                x-ref="searchInput"
                placeholder="{{ __('Search...') }}"
                class="h-8 w-full rounded-md border border-slate-200 px-2 text-sm text-black placeholder:text-slate-400 focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy"
                @keydown.stop
            >
        </div>

        <x-ui.scroll-area class="max-h-60">
            <ul
                role="listbox"
                class="p-1"
            >
                <template x-for="option in filteredOptions" :key="option.value">
                    <li>
                        <button
                            type="button"
                            role="option"
                            @click="select(option)"
                            :aria-selected="String(selected) === String(option.value)"
                            class="flex w-full items-center justify-between rounded-md px-2 py-1.5 text-sm text-black hover:bg-slate-100"
                            :class="String(selected) === String(option.value) ? 'bg-slate-100 font-semibold' : 'font-medium'"
                        >
                            <span class="truncate" x-text="option.label"></span>
                            <svg
                                x-show="String(selected) === String(option.value)"
                                class="size-4 shrink-0 text-black"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="2"
                                stroke="currentColor"
                                aria-hidden="true"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        </button>
                    </li>
                </template>
                <li x-show="filteredOptions.length === 0" class="px-2 py-6 text-center text-sm text-slate-500">
                    {{ __('No results found.') }}
                </li>
            </ul>
        </x-ui.scroll-area>
    </div>
</div>
