<div
    x-data="reminderHost({
        dueUrl: @js(route('tenant.reminders.due')),
        dismissUrl: @js(route('tenant.reminders.dismiss')),
        pollMs: 30000,
    })"
    x-cloak
>
    <template x-if="current">
        <div
            class="fixed inset-0 z-[10050] flex items-center justify-center bg-slate-900/50 px-4 py-6"
            role="dialog"
            aria-modal="true"
            :aria-labelledby="'reminder-title'"
        >
            <div class="w-full max-w-md overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-2xl shadow-slate-900/20">
                <div class="border-b border-slate-100 px-5 py-4">
                    <div class="flex items-start gap-3">
                        <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600 ring-1 ring-amber-100">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h2 id="reminder-title" class="text-base font-semibold text-black" x-text="current.title"></h2>
                            <p class="mt-1 text-sm text-slate-600" x-text="current.subtitle"></p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 px-5 py-4">
                    <button
                        type="button"
                        class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50"
                        @click="dismissCurrent()"
                        :disabled="busy"
                    >
                        {{ __('Close') }}
                    </button>
                    <button
                        type="button"
                        class="inline-flex items-center justify-center rounded-lg bg-navy px-3.5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-navy/90"
                        @click="goToCurrent()"
                        :disabled="busy"
                    >
                        {{ __('Go to item') }}
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>
