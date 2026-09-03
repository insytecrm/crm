@props([
    'activity',
    'last' => false,
    'notesById' => null,
])

@php
    use App\Enums\LeadActivityType;

    $notesById ??= collect();

    $relatedNote = null;
    if ($activity->type === LeadActivityType::NoteAdded) {
        $noteId = $activity->metadata['note_id'] ?? null;
        $relatedNote = $noteId ? $notesById->get($noteId) : null;
    }

    $hoverDetail = $activity->timelineHoverDetail($relatedNote);
    $hasHoverDetail = filled($hoverDetail);

    [$iconBg, $iconColor] = match ($activity->type) {
        LeadActivityType::CallMade => ['bg-emerald-100', 'text-emerald-600'],
        LeadActivityType::WhatsAppMessage => ['bg-green-100', 'text-green-600'],
        LeadActivityType::FollowUpScheduled,
        LeadActivityType::FollowUpCompleted => ['bg-sky-100', 'text-sky-600'],
        LeadActivityType::SiteVisitScheduled,
        LeadActivityType::SiteVisitCompleted => ['bg-amber-100', 'text-amber-600'],
        LeadActivityType::StatusChanged => ['bg-violet-100', 'text-violet-700'],
        LeadActivityType::NoteAdded => ['bg-blue-100', 'text-blue-600'],
        LeadActivityType::TaskCreated,
        LeadActivityType::TaskCompleted => ['bg-indigo-100', 'text-indigo-600'],
        LeadActivityType::BookingCreated,
        LeadActivityType::LeadCreated => ['bg-emerald-100', 'text-emerald-600'],
        default => ['bg-slate-100', 'text-black'],
    };
@endphp

<div
    @class(['relative flex gap-3', 'pb-4' => ! $last])
    @if ($hasHoverDetail)
        x-data="{
            showDetail: false,
            panelStyle: '',
            openDetail() {
                this.showDetail = true;
                this.$nextTick(() => this.position());
            },
            closeDetail() {
                this.showDetail = false;
            },
            toggleDetail() {
                this.showDetail = ! this.showDetail;
                if (this.showDetail) {
                    this.$nextTick(() => this.position());
                }
            },
            position() {
                const trigger = this.$refs.trigger;
                const rect = trigger.getBoundingClientRect();
                const width = 288;
                const left = Math.min(
                    Math.max(8, rect.left),
                    window.innerWidth - width - 8,
                );
                const top = rect.bottom + 8;

                this.panelStyle = `top:${top}px;left:${left}px;`;
            },
        }"
        @mouseenter="openDetail()"
        @mouseleave="closeDetail()"
    @endif
>
    {{-- Date & time --}}
    <div class="w-[4.75rem] shrink-0 pt-0.5 text-end" title="{{ $activity->created_at->format('M j, Y g:i A') }}">
        <p class="text-xs font-semibold leading-tight text-black">{{ $activity->created_at->format('M j') }}</p>
        <p class="mt-0.5 text-[11px] leading-tight text-slate-400">{{ $activity->created_at->format('g:i A') }}</p>
    </div>

    {{-- Timeline line & dot --}}
    <div class="relative flex w-5 shrink-0 justify-center">
        @unless ($last)
            <span class="absolute top-3 bottom-0 w-px bg-slate-200" aria-hidden="true"></span>
        @endunless
        <span class="relative z-10 mt-1.5 size-2.5 shrink-0 rounded-full border-2 border-white bg-navy ring-1 ring-slate-200" aria-hidden="true"></span>
    </div>

    {{-- Compact activity row --}}
    <div class="min-w-0 flex-1">
        <div
            @if ($hasHoverDetail)
                x-ref="trigger"
                @click.stop="toggleDetail()"
                @keydown.enter.prevent="toggleDetail()"
                @keydown.space.prevent="toggleDetail()"
                tabindex="0"
                role="button"
                aria-describedby="timeline-detail-{{ $activity->id }}"
            @endif
            @class([
                'flex items-center gap-2 rounded-lg border border-slate-100 bg-slate-50/80 px-2.5 py-1.5 transition-colors',
                'cursor-help hover:border-slate-200 hover:bg-slate-100/80' => $hasHoverDetail,
            ])
        >
            <div class="flex size-7 shrink-0 items-center justify-center rounded-md {{ $iconBg }} {{ $iconColor }}">
                @switch($activity->type)
                    @case(LeadActivityType::CallMade)
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" /></svg>
                        @break
                    @case(LeadActivityType::WhatsAppMessage)
                        <svg class="size-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z" /></svg>
                        @break
                    @case(LeadActivityType::FollowUpScheduled)
                    @case(LeadActivityType::FollowUpCompleted)
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                        @break
                    @case(LeadActivityType::SiteVisitScheduled)
                    @case(LeadActivityType::SiteVisitCompleted)
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                        @break
                    @case(LeadActivityType::NoteAdded)
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                        @break
                    @case(LeadActivityType::TaskCreated)
                    @case(LeadActivityType::TaskCompleted)
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                        @break
                    @case(LeadActivityType::BookingCreated)
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                        @break
                    @case(LeadActivityType::StatusChanged)
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" /></svg>
                        @break
                    @default
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                @endswitch
            </div>

            <p class="min-w-0 truncate text-sm font-medium text-black">{{ $activity->type->label() }}</p>
        </div>

        @if ($hasHoverDetail)
            <div
                x-show="showDetail"
                x-cloak
                :style="panelStyle"
                @mouseenter="openDetail()"
                @mouseleave="closeDetail()"
                @click.stop
                id="timeline-detail-{{ $activity->id }}"
                role="tooltip"
                data-timeline-hover
                class="fixed z-[90] w-72 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm shadow-slate-900/5"
                style="display: none;"
            >
                <p class="text-xs font-semibold text-black">{{ $activity->type->label() }}</p>
                <p class="mt-1.5 text-sm leading-snug text-slate-600">{{ $hoverDetail }}</p>
                @if ($activity->user)
                    <p class="mt-2 text-[11px] text-slate-400">{{ $activity->user->name }} · {{ $activity->created_at->format('M j, g:i A') }}</p>
                @endif
            </div>
        @endif
    </div>
</div>
