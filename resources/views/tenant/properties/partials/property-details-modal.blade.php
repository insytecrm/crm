@php
    $amenities = $property->amenities ?? [];
    $configurations = $property->configurations ?? [];
    $layoutFiles = $property->layout_files ?? [];
    $brochureFiles = $property->brochure_files ?? [];

    $priceRange = match (true) {
        $property->price_from && $property->price_to => '₹'.number_format($property->price_from).' – ₹'.number_format($property->price_to),
        filled($property->price_from) => __('From').' ₹'.number_format($property->price_from),
        filled($property->price_to) => __('Up to').' ₹'.number_format($property->price_to),
        default => null,
    };

    $carpetRange = match (true) {
        $property->carpet_area_from_sqft && $property->carpet_area_to_sqft => number_format($property->carpet_area_from_sqft).' – '.number_format($property->carpet_area_to_sqft).' '.__('sq.ft'),
        filled($property->carpet_area_from_sqft) => __('From').' '.number_format($property->carpet_area_from_sqft).' '.__('sq.ft'),
        filled($property->carpet_area_to_sqft) => __('Up to').' '.number_format($property->carpet_area_to_sqft).' '.__('sq.ft'),
        default => null,
    };

    $sections = [
        ['key' => 'basic', 'label' => __('Basic Info')],
        ['key' => 'project', 'label' => __('Project')],
        ['key' => 'tagging', 'label' => __('Tagging')],
        ['key' => 'amenities', 'label' => __('Amenities')],
        ['key' => 'configurations', 'label' => __('Configurations')],
        ['key' => 'attachments', 'label' => __('Attachments')],
    ];
@endphp

<x-modal name="property-details-{{ $property->id }}" maxWidth="2xl">
    <div
        x-data="{ section: 'basic' }"
        class="flex flex-col p-4 sm:p-5"
    >
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <h2 class="text-base font-bold text-black">{{ $property->project_name }}</h2>
                <p class="mt-0.5 text-xs text-slate-500">{{ $property->developer_name ?? '—' }}</p>
            </div>
            <button
                type="button"
                @click="$dispatch('close-modal', 'property-details-{{ $property->id }}')"
                class="inline-flex size-7 shrink-0 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                aria-label="{{ __('Close') }}"
            >
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="mt-3 flex flex-nowrap items-center gap-1 overflow-x-auto text-xs font-medium whitespace-nowrap">
            @foreach ($sections as $section)
                <button
                    type="button"
                    @click="section = @js($section['key'])"
                    :class="section === @js($section['key']) ? 'shrink-0 rounded-full bg-navy px-2.5 py-1 text-white' : 'shrink-0 rounded-full px-2.5 py-1 text-slate-600 hover:bg-slate-100 hover:text-black'"
                >
                    {{ $section['label'] }}
                </button>
                @if (! $loop->last)
                    <span class="shrink-0 text-slate-300" aria-hidden="true">|</span>
                @endif
            @endforeach
        </div>

        <div class="mt-4 h-72 overflow-y-auto rounded-xl border border-slate-100 bg-slate-50/60 p-3 sm:p-4">
            {{-- Basic Info --}}
            <div x-show="section === 'basic'" x-cloak>
                <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    @include('tenant.properties.partials.property-detail-field', ['label' => __('Developer Name'), 'value' => $property->developer_name])
                    @include('tenant.properties.partials.property-detail-field', ['label' => __('Project Name'), 'value' => $property->project_name])
                    @include('tenant.properties.partials.property-detail-field', ['label' => __('Project Location'), 'value' => $property->project_location])
                    @include('tenant.properties.partials.property-detail-field', ['label' => __('RERA Number'), 'value' => $property->rera_number])
                    @include('tenant.properties.partials.property-detail-field', ['label' => __('Property Type'), 'value' => $property->property_type?->label()])
                    @include('tenant.properties.partials.property-detail-field', ['label' => __('Project Status'), 'value' => $property->project_status?->label()])
                    @include('tenant.properties.partials.property-detail-field', ['label' => __('Possession Date'), 'value' => $property->possession_date])
                </dl>
            </div>

            {{-- Project Scale --}}
            <div x-show="section === 'project'" x-cloak style="display: none;">
                <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    @include('tenant.properties.partials.property-detail-field', ['label' => __('Total Land Parcel (Acres)'), 'value' => $property->total_land_parcel_acres])
                    @include('tenant.properties.partials.property-detail-field', ['label' => __('Total Towers'), 'value' => $property->total_towers])
                    @include('tenant.properties.partials.property-detail-field', ['label' => __('Total Floors'), 'value' => $property->total_floors])
                    @include('tenant.properties.partials.property-detail-field', ['label' => __('Carpet Area'), 'value' => $carpetRange])
                    @include('tenant.properties.partials.property-detail-field', ['label' => __('Price Range'), 'value' => $priceRange])
                </dl>
            </div>

            {{-- Tagging & Payout --}}
            <div x-show="section === 'tagging'" x-cloak style="display: none;">
                <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    @include('tenant.properties.partials.property-detail-field', ['label' => __('Tagging Period (Days)'), 'value' => $property->tagging_period_days])
                    @include('tenant.properties.partials.property-detail-field', ['label' => __('Payout (%)'), 'value' => $property->payout_percent])
                    @include('tenant.properties.partials.property-detail-field', ['label' => __('Sourcing Manager Name'), 'value' => $property->sourcing_manager_name])
                    @include('tenant.properties.partials.property-detail-field', ['label' => __('Sourcing Manager Contact'), 'value' => $property->sourcing_manager_contact])
                </dl>
            </div>

            {{-- Amenities --}}
            <div x-show="section === 'amenities'" x-cloak style="display: none;">
                @if ($amenities !== [])
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($amenities as $amenity)
                            <span class="inline-flex items-center rounded-full bg-violet-50 px-2.5 py-1 text-xs font-medium text-violet-700">
                                {{ $amenity }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-slate-400">—</p>
                @endif
            </div>

            {{-- Configurations --}}
            <div x-show="section === 'configurations'" x-cloak style="display: none;">
                @if ($configurations !== [])
                    <div class="space-y-2">
                        @foreach ($configurations as $configuration)
                            <div class="rounded-lg border border-slate-200 bg-white px-3 py-2">
                                <p class="text-sm font-semibold text-black">{{ $configuration['name'] ?? '—' }}</p>
                                <div class="mt-1.5 flex flex-wrap gap-1.5">
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600">
                                        {{ __('Carpet') }}: {{ ! empty($configuration['carpet_area_sqft']) ? number_format($configuration['carpet_area_sqft']).' '.__('sq.ft') : '—' }}
                                    </span>
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600">
                                        {{ __('Price') }}: {{ ! empty($configuration['price']) ? '₹'.number_format($configuration['price']) : '—' }}
                                    </span>
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600">
                                        {{ __('Units') }}: {{ ! empty($configuration['unit_count']) ? number_format($configuration['unit_count']) : '—' }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-slate-400">—</p>
                @endif
            </div>

            {{-- Attachments --}}
            <div x-show="section === 'attachments'" x-cloak style="display: none;">
                <dl class="space-y-3">
                    <div>
                        <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-400">{{ __('Layout Files') }}</dt>
                        <dd class="mt-1.5 flex flex-wrap gap-1.5">
                            @forelse ($layoutFiles as $file)
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">
                                    {{ $file['name'] ?? __('File') }}
                                </span>
                            @empty
                                <span class="text-sm text-slate-400">—</span>
                            @endforelse
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-400">{{ __('Brochure Files') }}</dt>
                        <dd class="mt-1.5 flex flex-wrap gap-1.5">
                            @forelse ($brochureFiles as $file)
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">
                                    {{ $file['name'] ?? __('File') }}
                                </span>
                            @empty
                                <span class="text-sm text-slate-400">—</span>
                            @endforelse
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 pt-3">
            <x-ui.button
                type="button"
                variant="outline"
                size="sm"
                @click="$dispatch('close-modal', 'property-details-{{ $property->id }}'); $dispatch('open-modal', 'edit-property-{{ $property->id }}')"
            >
                {{ __('Edit') }}
            </x-ui.button>
            <form
                method="POST"
                action="{{ route('tenant.properties.destroy', $property) }}"
                class="inline"
                @submit.prevent="if (confirm(@js(__('Are you sure you want to delete this property?')))) { $el.submit(); }"
            >
                @csrf
                @method('DELETE')
                <x-ui.button type="submit" variant="destructive" size="sm" class="bg-red-600 text-white hover:bg-red-700">
                    {{ __('Delete') }}
                </x-ui.button>
            </form>
        </div>
    </div>
</x-modal>
