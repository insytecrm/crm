<div
    id="lead-drawer-host-root"
    x-data="leadDrawerHost({ showUrlTemplate: @js(route('tenant.leads.show', ['lead' => '__ID__'])) })"
    x-on:open-lead.window="openLead($event.detail)"
    x-on:prefetch-lead.window="prefetchLead($event.detail)"
    x-on:close-lead-drawer.window="close()"
>
    <button
        type="button"
        x-show="open"
        x-transition.opacity
        class="fixed top-14 bottom-0 start-0 end-0 z-40 bg-navy/40 backdrop-blur-sm lg:start-[var(--sidebar-width,256px)]"
        aria-label="{{ __('Close lead details') }}"
        style="display: none;"
        @click="close()"
    ></button>

    <div
        x-show="open"
        x-transition:enter="transform transition ease-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transform transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed top-14 bottom-0 end-0 z-[60] flex w-full flex-col overflow-hidden border-s border-slate-200 bg-white text-black shadow-2xl lg:w-[calc((100vw-var(--sidebar-width,256px))*0.6)] lg:max-w-[calc((100vw-var(--sidebar-width,256px))*0.6)]"
        role="dialog"
        aria-modal="true"
        style="display: none;"
        @click.stop
    >
        <div
            x-ref="content"
            class="flex min-h-0 flex-1 flex-col overflow-hidden"
            data-testid="lead-drawer-content"
        ></div>
    </div>
</div>
