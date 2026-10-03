@php
    $leadSearchUrl = route('tenant.leads.search');
@endphp

<div
    x-data="{
        open: false,
        query: '',
        results: [],
        loading: false,
        searchUrl: @js($leadSearchUrl),
        async search() {
            if (this.query.trim().length < 2) {
                this.results = [];
                return;
            }
            this.loading = true;
            try {
                const response = await fetch(`${this.searchUrl}?q=${encodeURIComponent(this.query.trim())}`, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                });
                if (! response.ok) {
                    return;
                }
                const payload = await response.json();
                this.results = payload.results ?? [];
            } finally {
                this.loading = false;
            }
        },
        openLead(id) {
            this.open = false;
            this.query = '';
            this.results = [];
            window.dispatchEvent(new CustomEvent('open-lead', { detail: id }));
        },
    }"
    x-on:keydown.window.ctrl.k.prevent="open = ! open; if (open) { $nextTick(() => $refs.paletteInput?.focus()) }"
    x-on:keydown.window.meta.k.prevent="open = ! open; if (open) { $nextTick(() => $refs.paletteInput?.focus()) }"
>
    <button
        type="button"
        class="inline-flex size-8 items-center justify-center rounded-md text-slate-600 hover:bg-slate-100"
        aria-label="{{ __('Search leads') }}"
        @click="open = true; $nextTick(() => $refs.paletteInput?.focus())"
    >
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
        </svg>
    </button>

    <div
        x-show="open"
        x-cloak
        class="fixed inset-0 z-[80] flex items-start justify-center bg-navy/40 px-4 pt-[15vh] backdrop-blur-sm"
        @click.self="open = false"
    >
        <div class="w-full max-w-lg overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl" @click.stop>
            <div class="border-b border-slate-100 px-4 py-3">
                <input
                    x-ref="paletteInput"
                    type="search"
                    x-model="query"
                    @input.debounce.250ms="search()"
                    class="w-full border-0 text-sm text-black placeholder:text-slate-400 focus:ring-0"
                    placeholder="{{ __('Search leads by name, phone, or email…') }}"
                />
            </div>
            <div class="max-h-72 overflow-y-auto p-2">
                <template x-if="loading">
                    <p class="px-2 py-4 text-center text-xs text-slate-500">{{ __('Searching…') }}</p>
                </template>
                <template x-if="! loading && query.trim().length >= 2 && results.length === 0">
                    <p class="px-2 py-4 text-center text-xs text-slate-500">{{ __('No leads found.') }}</p>
                </template>
                <template x-for="result in results" :key="result.id">
                    <button
                        type="button"
                        class="flex w-full flex-col rounded-lg px-3 py-2 text-left hover:bg-slate-50"
                        @click="openLead(result.id)"
                    >
                        <span class="text-sm font-semibold text-black" x-text="result.name"></span>
                        <span class="text-xs text-slate-500" x-text="result.subtitle"></span>
                    </button>
                </template>
            </div>
        </div>
    </div>
</div>
