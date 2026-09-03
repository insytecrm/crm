@props(['property'])

<button
    type="button"
    @click="$dispatch('open-modal', 'property-details-{{ $property->id }}')"
    class="group flex h-full w-full flex-col rounded-xl border border-slate-100 bg-white p-3 text-start shadow-sm transition hover:border-navy/20 hover:shadow focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-navy focus-visible:ring-offset-2"
>
    <div class="flex items-start justify-between gap-2">
        <div class="min-w-0 flex-1">
            <h3 class="truncate text-sm font-bold text-black">{{ $property->project_name }}</h3>
            <p class="mt-0.5 truncate text-xs text-slate-500">{{ $property->developer_name ?? '—' }}</p>
        </div>
        @if ($property->project_status)
            <x-tenant.project-status-badge :status="$property->project_status" class="shrink-0 scale-90" />
        @endif
    </div>

    <div class="mt-2 space-y-1.5">
        <p class="truncate text-xs text-slate-600">{{ $property->project_location ?? '—' }}</p>
        <div class="flex flex-wrap gap-1">
            <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600">
                {{ $property->property_type?->label() ?? '—' }}
            </span>
            @if ($property->possession_date)
                <span class="inline-flex items-center rounded-full bg-sky-50 px-2 py-0.5 text-[11px] font-medium text-sky-700">
                    {{ $property->possession_date }}
                </span>
            @endif
        </div>
    </div>
</button>
