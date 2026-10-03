@php
    $inPlace = $inPlace ?? false;
    $closeUrl = $closeUrl ?? route('tenant.leads.index', request()->only('search'));
@endphp

<div class="lead-details-drawer">
    @if ($inPlace)
        <button
            type="button"
            class="fixed top-14 bottom-0 start-0 end-0 z-[60] bg-navy/40 backdrop-blur-sm lg:start-[var(--sidebar-width,256px)]"
            aria-label="{{ __('Close lead details') }}"
            @click="$dispatch('close-lead-drawer')"
        ></button>
    @else
        <a
            href="{{ $closeUrl }}"
            class="fixed top-14 bottom-0 start-0 end-0 z-[60] bg-navy/40 backdrop-blur-sm lg:start-[var(--sidebar-width,256px)]"
            aria-label="{{ __('Close lead details') }}"
        ></a>
    @endif

    <div
        class="fixed top-14 bottom-0 end-0 z-[60] flex w-full lg:w-[calc((100vw-var(--sidebar-width,256px))*0.6)] lg:max-w-[calc((100vw-var(--sidebar-width,256px))*0.6)]"
        role="dialog"
        aria-modal="true"
        aria-labelledby="lead-details-title"
    >
        <div class="flex h-full w-full flex-col overflow-hidden border-s border-slate-200 bg-white shadow-2xl">
            @include('tenant.leads.partials.details', [
                'lead' => $lead,
                'users' => $users,
                'closeUrl' => $closeUrl,
                'inPlace' => $inPlace,
            ])
        </div>
    </div>
</div>
