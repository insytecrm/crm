@props([
    'lead',
])

@php
    $preview = \App\Support\LeadStatusHoverPreview::for($lead);
@endphp

<div
    {{ $attributes->class('relative inline-flex max-w-full') }}
    x-data="leadStatusHover()"
    @mouseenter="show($refs.trigger)"
    @mouseleave="scheduleHide()"
>
    <div x-ref="trigger" class="inline-flex max-w-full">
        {{ $slot }}
    </div>

    <template x-teleport="body">
        <div
            x-show="open"
            x-cloak
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            @mouseenter="cancelHide()"
            @mouseleave="scheduleHide()"
            :style="panelStyle"
            class="w-72 origin-top-left rounded-lg border border-slate-200 bg-white p-4 shadow-md"
            role="tooltip"
        >
            <div class="space-y-1">
                <p class="text-sm font-semibold text-black">{{ $preview['status'] }}</p>
                @if (filled($preview['stage']))
                    <p class="text-xs text-slate-500">{{ $preview['stage'] }}</p>
                @endif
            </div>

            <dl class="mt-3 grid grid-cols-2 gap-3">
                @foreach ($preview['fields'] as $field)
                    <div @class(['col-span-2' => ($field['wide'] ?? false)])>
                        <dt class="text-xs text-slate-500">{{ $field['label'] }}</dt>
                        <dd class="text-sm font-medium text-black">{{ $field['value'] ?? '—' }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </template>
</div>
