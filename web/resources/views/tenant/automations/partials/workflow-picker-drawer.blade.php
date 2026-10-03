<div
    id="workflow-picker-host-root"
    x-data="workflowPickerHost()"
    x-on:open-workflow-picker.window="openPicker($event.detail)"
    x-on:close-workflow-picker.window="close()"
    data-testid="workflow-picker-drawer"
>
    <button
        type="button"
        x-show="type !== null"
        x-transition.opacity
        class="fixed top-14 bottom-0 start-0 end-0 z-40 bg-navy/40 backdrop-blur-sm lg:start-[var(--sidebar-width,256px)]"
        style="display: none;"
        @click="close()"
        aria-label="{{ __('Close') }}"
    ></button>

    <div
        x-show="type !== null"
        x-transition:enter="transform transition ease-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transform transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed top-14 bottom-0 end-0 z-[60] flex w-full max-w-md flex-col overflow-hidden border-s border-slate-200 bg-white text-black shadow-2xl"
        role="dialog"
        aria-modal="true"
        style="display: none;"
        @click.stop
        @keydown.escape.window="if (type !== null) close()"
    >
        <div class="flex shrink-0 items-start justify-between gap-3 border-b border-slate-100 bg-slate-50 px-4 py-3">
            <div class="min-w-0">
                <h2 class="text-sm font-semibold text-black" x-show="type === 'trigger'" style="display: none;">{{ __('Select a trigger') }}</h2>
                <h2 class="text-sm font-semibold text-black" x-show="type === 'action'" style="display: none;">{{ __('Select an action') }}</h2>
                <p class="mt-0.5 text-xs text-slate-500" x-show="type === 'trigger'" style="display: none;">{{ __('Choose the event that starts this workflow.') }}</p>
                <p class="mt-0.5 text-xs text-slate-500" x-show="type === 'action'" style="display: none;">{{ __('Choose what this workflow should do next.') }}</p>
            </div>
            <button
                type="button"
                class="inline-flex size-8 shrink-0 items-center justify-center rounded-md text-slate-400 hover:bg-white hover:text-black"
                @click="close()"
                aria-label="{{ __('Close') }}"
            >
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="min-h-0 flex-1 overflow-y-auto" x-show="type === 'trigger'" style="display: none;">
            @include('tenant.automations.partials.workflow-picker-list', [
                'sections' => $editorConfig['triggerSections'],
                'select' => 'choose',
            ])
        </div>

        <div class="min-h-0 flex-1 overflow-y-auto" x-show="type === 'action'" style="display: none;">
            @include('tenant.automations.partials.workflow-picker-list', [
                'sections' => $editorConfig['actionSections'],
                'select' => 'choose',
            ])
        </div>
    </div>
</div>
