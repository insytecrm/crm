@props([
    'action',
    'label',
    'hidden' => [],
    'method' => 'PATCH',
])

@php
    $httpMethod = strtoupper($method);
@endphp

<div
    x-data="{
        open: false,
        panelStyle: '',
        toggle() {
            this.open = ! this.open;

            if (this.open) {
                this.$nextTick(() => this.position());
            }
        },
        position() {
            const trigger = this.$refs.trigger;
            const rect = trigger.getBoundingClientRect();
            const width = 288;
            const left = Math.min(
                Math.max(8, rect.right - width),
                window.innerWidth - width - 8,
            );
            const top = Math.max(8, rect.top - 8);

            this.panelStyle = `top:${top}px;left:${left}px;transform:translateY(-100%);`;
        },
        close() {
            this.open = false;
        },
    }"
    @click.outside="close()"
    @keydown.escape.window="if (open) close()"
    class="inline-flex"
>
    <x-ui.action-icon
        type="button"
        x-ref="trigger"
        @click.stop="toggle()"
        icon="cancel"
        :title="__('Cancel')"
        x-bind:aria-expanded="open"
    />

    <div
        x-show="open"
        x-cloak
        :style="panelStyle"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @click.stop
        role="dialog"
        aria-modal="true"
        class="fixed z-[80] w-72 overflow-hidden rounded-2xl border border-slate-200 bg-white p-3 shadow-sm shadow-slate-900/5"
        style="display: none;"
    >
        <p class="text-xs font-semibold text-black">{{ $label }}</p>
        <p class="mt-0.5 text-[11px] text-slate-500">{{ __('Why are you cancelling this task?') }}</p>

        <form method="POST" action="{{ $action }}" class="mt-2.5 space-y-2.5">
            @csrf
            @if ($httpMethod !== 'POST')
                @method($httpMethod)
            @endif
            <input type="hidden" name="status" value="{{ \App\Enums\TaskStatus::Cancelled->value }}">
            @foreach ($hidden as $name => $value)
                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
            @endforeach
            <textarea
                name="notes"
                rows="2"
                required
                placeholder="{{ __('Cancellation reason') }}"
                class="block w-full resize-none rounded-lg border-slate-200 text-sm focus:border-navy focus:ring-navy"
            ></textarea>
            <div class="flex items-center justify-end gap-2">
                <button
                    type="button"
                    @click="close()"
                    class="rounded-lg px-2.5 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-slate-100"
                >
                    {{ __('Back') }}
                </button>
                <button
                    type="submit"
                    class="rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-rose-500"
                >
                    {{ __('Cancel Task') }}
                </button>
            </div>
        </form>
    </div>
</div>
