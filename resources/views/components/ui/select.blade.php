@php($config = $config())

<div
    x-data="uiSelect(@js($config))"
    x-modelable="selected"
    data-ui-select
    x-on:keydown.escape.window="open && close()"
    @if ($disabled) data-disabled="true" @endif
    {{ $attributes->merge(['class' => 'relative w-full']) }}
>
    <input
        type="hidden"
        x-ref="hiddenInput"
        x-model="selected"
        name="{{ $name }}"
        id="{{ $id }}"
        :disabled="disabled"
        @required($required)
    >

    <button
        type="button"
        x-ref="trigger"
        @click="toggle()"
        :disabled="disabled"
        :aria-expanded="open"
        aria-haspopup="listbox"
        @if ($ariaLabel)
            aria-label="{{ $ariaLabel }}"
        @endif
        class="{{ $triggerClasses() }}"
    >
        <span
            class="min-w-0 flex-1 truncate text-left {{ $triggerClass ? 'font-semibold' : 'font-normal' }}"
            @unless ($triggerClass)
                :class="selected === '' ? 'text-slate-400' : 'text-black'"
            @endunless
            x-text="selectedLabel"
        ></span>
        <svg class="size-4 shrink-0 {{ $triggerClass ? 'text-current opacity-70' : 'text-slate-400' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
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
        class="z-[9999] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-lg shadow-slate-900/10"
        style="display: none;"
    >
        <div
            x-ref="list"
            class="overflow-y-auto overscroll-contain p-1"
            style="max-height: 20rem;"
        >
            <ul role="listbox">
                <template x-for="option in options" :key="String(option.value)">
                    <li>
                        <button
                            type="button"
                            role="option"
                            @click.stop="select(option)"
                            :aria-selected="String(selected) === String(option.value)"
                            class="flex w-full items-center gap-2 rounded-lg px-2 py-2 text-left text-sm text-black hover:bg-slate-100"
                            :class="String(selected) === String(option.value) ? 'bg-slate-100 font-semibold' : 'font-medium'"
                        >
                            <span class="min-w-0 flex-1 whitespace-normal leading-snug" x-text="option.label"></span>
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
            </ul>
        </div>
    </div>
</div>
