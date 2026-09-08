@props([
    'name',
    'show' => false,
    'maxWidth' => '2xl'
])

@php
$maxWidth = [
    'sm' => 'sm:max-w-sm',
    'md' => 'sm:max-w-md',
    'lg' => 'sm:max-w-lg',
    'xl' => 'sm:max-w-xl',
    '2xl' => 'sm:max-w-2xl',
][$maxWidth];

$shouldFocusOnOpen = $attributes->has('focusable');
@endphp

<div
    x-data="{
        show: @js($show),
        allowBackdropClose: @js($show),
        shouldFocusOnOpen: @js($shouldFocusOnOpen),
        focusables() {
            let selector = 'a, button, input:not([type=\'hidden\']):not([tabindex=\'-1\']), textarea, select, details, [tabindex]:not([tabindex=\'-1\'])'

            return [...$el.querySelectorAll(selector)]
                .filter(el => ! el.hasAttribute('disabled'))
        },
        firstFocusable() { return this.focusables()[0] },
        lastFocusable() { return this.focusables().slice(-1)[0] },
        nextFocusable() { return this.focusables()[this.nextFocusableIndex()] || this.firstFocusable() },
        prevFocusable() { return this.focusables()[this.prevFocusableIndex()] || this.lastFocusable() },
        nextFocusableIndex() {
            const focusables = this.focusables();

            if (focusables.length === 0) {
                return 0;
            }

            return (focusables.indexOf(document.activeElement) + 1) % focusables.length;
        },
        prevFocusableIndex() {
            const focusables = this.focusables();

            if (focusables.length === 0) {
                return 0;
            }

            const currentIndex = focusables.indexOf(document.activeElement);

            return currentIndex <= 0 ? focusables.length - 1 : currentIndex - 1;
        },
        focusFirstField() {
            if (! this.shouldFocusOnOpen) {
                return;
            }

            this.$nextTick(() => {
                this.firstFocusable()?.focus();
            });
        },
        openModal() {
            this.show = true;
            this.allowBackdropClose = false;
            setTimeout(() => {
                this.allowBackdropClose = true;
                this.focusFirstField();
            }, 150);
        },
        trapTab(event) {
            if (! this.show) {
                return;
            }

            event.preventDefault();

            if (event.shiftKey) {
                this.prevFocusable().focus();
            } else {
                this.nextFocusable().focus();
            }
        },
    }"
    x-init="$watch('show', value => {
        if (value) {
            document.body.classList.add('overflow-y-hidden');
            window.dispatchEvent(new CustomEvent('crm-overlay-opened'));
        } else {
            document.body.classList.remove('overflow-y-hidden');
        }
    })"
    x-on:open-modal.window="$event.detail == '{{ $name }}' ? openModal() : null"
    x-on:close-modal.window="$event.detail == '{{ $name }}' ? show = false : null"
    x-on:close.stop="show = false"
    x-on:keydown.escape.window="show && (show = false)"
    @if ($shouldFocusOnOpen)
        x-on:keydown.tab="trapTab($event)"
    @endif
    x-show="show"
    class="fixed inset-0 z-[80] overflow-y-auto px-4 py-6 sm:px-0"
    style="display: {{ $show ? 'block' : 'none' }};"
>
    <div
        x-show="show"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-500/75"
        x-on:click="allowBackdropClose && (show = false)"
        aria-hidden="true"
    ></div>

    <div
        x-show="show"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        class="relative z-10 mb-6 w-full {{ $maxWidth }} overflow-hidden rounded-2xl bg-white shadow-lg shadow-slate-900/15 sm:mx-auto"
        @click.stop
        role="dialog"
        aria-modal="true"
    >
        {{ $slot }}
    </div>
</div>
