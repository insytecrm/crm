@props(['property'])

<div
    @class([
        'group flex h-full w-full flex-col rounded-xl border bg-white p-4 shadow-sm transition',
        'border-slate-100 hover:border-navy/20 hover:shadow' => $property->isActive(),
        'border-slate-200 opacity-70' => ! $property->isActive(),
    ])
>
    <div class="flex items-start justify-between gap-2">
        <button
            type="button"
            @click="$dispatch('open-modal', 'property-details-{{ $property->id }}')"
            class="min-w-0 flex-1 text-start focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-navy focus-visible:ring-offset-2"
        >
            <h3 class="line-clamp-2 text-sm font-bold leading-snug text-black">{{ $property->project_name }}</h3>
            <p class="mt-1 line-clamp-1 text-xs text-slate-500">{{ $property->developer_name ?? '—' }}</p>
        </button>

        <div class="flex shrink-0 items-center gap-2">
            @if ($property->project_status)
                <x-tenant.project-status-badge :status="$property->project_status" class="scale-90" />
            @endif

            <form
                method="POST"
                action="{{ route('tenant.properties.status.update', $property) }}"
                class="inline-flex"
                @click.stop
                @change="$el.submit()"
            >
                @csrf
                @method('PATCH')
                <input type="hidden" name="is_active" value="0">
                <label
                    title="{{ $property->isActive() ? __('Active') : __('Inactive') }}"
                    aria-label="{{ __('Toggle property status') }}"
                    class="inline-flex cursor-pointer items-center"
                >
                    <span class="relative inline-flex h-6 w-11 shrink-0">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            class="peer sr-only"
                            @checked($property->isActive())
                        >
                        <span class="absolute inset-0 rounded-full bg-slate-200 transition-colors peer-checked:bg-emerald-500"></span>
                        <span class="absolute left-0.5 top-0.5 size-5 rounded-full bg-white shadow-sm transition-transform peer-checked:translate-x-5"></span>
                    </span>
                </label>
            </form>
        </div>
    </div>

    <button
        type="button"
        @click="$dispatch('open-modal', 'property-details-{{ $property->id }}')"
        class="mt-2 space-y-1.5 text-start focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-navy focus-visible:ring-offset-2"
    >
        <p class="line-clamp-2 text-xs leading-relaxed text-slate-600">{{ $property->project_location ?? '—' }}</p>
        <div class="flex flex-wrap gap-1">
            <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600">
                {{ $property->property_type?->label() ?? '—' }}
            </span>
            @if ($property->possession_date)
                <span class="inline-flex items-center rounded-full bg-sky-50 px-2 py-0.5 text-[11px] font-medium text-sky-700">
                    {{ $property->possession_date }}
                </span>
            @endif
            @unless ($property->isActive())
                <span class="inline-flex items-center rounded-full bg-rose-50 px-2 py-0.5 text-[11px] font-medium text-rose-700">
                    {{ __('Inactive') }}
                </span>
            @endunless
        </div>
    </button>
</div>
