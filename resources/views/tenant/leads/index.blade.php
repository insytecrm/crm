<x-tenant-layout :title="$listing->title() . ' | InSyte CRM'">
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('leadBulkSelection', { ids: [] });
        });

        window.leadTablePreferencesConfig = {
            listing: @js($leadListingKey),
            updateUrl: @js(route('tenant.leads.table-preferences.update')),
            defaults: @js($leadTableDefaults),
            initial: @js($leadTablePreferences),
        };
    </script>
    <div
        @if ($openModal ?? null)
            x-init="$nextTick(() => $dispatch('open-modal', @js($openModal)))"
        @endif
        x-data="{
            selected: [],
            leadIds: @js($leads->pluck('id')->values()),
            filtersOpen: @js($listFilters->isActive()),
            init() {
                this.$store.leadTablePreferences.ensureLoaded();
                this.syncBulkSelection();
            },
            get columns() {
                return this.$store.leadTablePreferences.columns;
            },
            get actions() {
                return this.$store.leadTablePreferences.actions;
            },
            get visibleColumnCount() {
                return this.$store.leadTablePreferences.visibleColumnCount;
            },
            toggleAll() {
                if (this.allSelected) {
                    this.selected = [];
                } else {
                    this.selected = [...this.leadIds];
                }

                this.syncBulkSelection();
            },
            toggle(id) {
                if (this.selected.includes(id)) {
                    this.selected = this.selected.filter((item) => item !== id);
                } else {
                    this.selected.push(id);
                }

                this.syncBulkSelection();
            },
            syncBulkSelection() {
                this.$store.leadBulkSelection.ids = [...this.selected];
            },
            openBulkModal(name) {
                this.syncBulkSelection();
                this.$dispatch('open-modal', name);
            },
            exportSelected() {
                if (this.selected.length === 0) {
                    return;
                }

                const params = new URLSearchParams();

                this.selected.forEach((id) => params.append('lead_ids[]', String(id)));

                window.location.href = `${@js(route('tenant.leads.export'))}?${params.toString()}`;
            },
            get allSelected() {
                return this.leadIds.length > 0 && this.selected.length === this.leadIds.length;
            },
            get someSelected() {
                return this.selected.length > 0 && this.selected.length < this.leadIds.length;
            },
        }"
    >
        <x-auth-session-status class="mb-3 rounded-lg bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700" :status="session('status')" />

        @php
            $listFilterQuery = $listFilters->toQueryArray();
        @endphp

        {{-- Search and actions --}}
        <div class="mb-4 overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
            <div class="flex flex-col gap-3 p-3 sm:flex-row sm:items-center sm:justify-between sm:p-4">
            <form
                method="GET"
                action="{{ route($listing->routeName(), $listing->routeName() === 'tenant.leads.index' ? $listing->redirectParameters('', $listFilterQuery) : []) }}"
                class="w-full sm:max-w-md"
            >
                @if ($listing !== \App\Enums\LeadListingFilter::All)
                    <input type="hidden" name="filter" value="{{ $listing->value }}">
                @endif
                @foreach ($listFilterQuery as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                <x-auth.icon-input
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="{{ __('Search by name, phone, or email...') }}"
                >
                    <x-slot:icon>
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                    </x-slot:icon>
                </x-auth.icon-input>
            </form>

            <div class="flex flex-wrap items-center gap-2">
                <x-ui.button type="button" variant="default" @click="$dispatch('open-modal', 'add-lead')">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    {{ __('Add Lead') }}
                </x-ui.button>
                <button
                    type="button"
                    @click="filtersOpen = !filtersOpen"
                    class="inline-flex h-10 items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 text-sm font-medium text-black shadow-sm transition-colors hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-navy focus-visible:ring-offset-2"
                    :class="filtersOpen && 'border-navy bg-slate-50'"
                    :aria-expanded="filtersOpen"
                >
                    <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                    </svg>
                    {{ __('Filters') }}
                    @if ($listFilters->isActive())
                        <span class="inline-flex min-w-5 items-center justify-center rounded-full bg-navy px-1.5 py-0.5 text-[10px] font-bold text-white">
                            {{ $listFilters->activeCount() }}
                        </span>
                    @endif
                    <svg
                        class="h-4 w-4 text-slate-400 transition-transform duration-200"
                        :class="filtersOpen && 'rotate-180'"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="2"
                        stroke="currentColor"
                        aria-hidden="true"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                    </svg>
                </button>
                <x-ui.button type="button" variant="outline" @click="$dispatch('open-modal', 'import-leads')">
                    {{ __('Import') }}
                </x-ui.button>
                <x-ui.button type="button" variant="outline" @click="$dispatch('open-modal', 'edit-columns')">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 4.5v15m6-15v15m-10.5-9h15m-15 6h15" />
                    </svg>
                    {{ __('Edit Columns') }}
                </x-ui.button>
            </div>
            </div>

            @include('tenant.leads.partials.list-filters-panel', [
                'listing' => $listing,
                'listFilters' => $listFilters,
                'search' => $search,
                'users' => $users,
                'sources' => $sources,
            ])
        </div>

        {{-- Statistics --}}
        <div class="mb-4 flex gap-2">
            <x-tenant.stat-card
                comfortable
                :label="__('Total Leads')"
                :value="$statistics['total']"
                :href="\App\Enums\LeadListingFilter::All->indexUrl($search, $listFilterQuery)"
                :active="$listing === \App\Enums\LeadListingFilter::All"
            >
                <x-slot:icon>
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                </x-slot:icon>
            </x-tenant.stat-card>
            <x-tenant.stat-card
                comfortable
                :label="__('New Leads')"
                :value="$statistics['new']"
                accent="sky"
                :href="\App\Enums\LeadListingFilter::New->indexUrl($search, $listFilterQuery)"
                :active="$listing === \App\Enums\LeadListingFilter::New"
            >
                <x-slot:icon>
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                </x-slot:icon>
            </x-tenant.stat-card>
            <x-tenant.stat-card
                comfortable
                :label="__('Unassigned')"
                :value="$statistics['unassigned']"
                accent="rose"
                :href="\App\Enums\LeadListingFilter::Unassigned->indexUrl($search, $listFilterQuery)"
                :active="$listing === \App\Enums\LeadListingFilter::Unassigned"
            >
                <x-slot:icon>
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                </x-slot:icon>
            </x-tenant.stat-card>
            <x-tenant.stat-card
                comfortable
                :label="__('Converted')"
                :value="$statistics['converted']"
                accent="emerald"
                :href="\App\Enums\LeadListingFilter::Converted->indexUrl($search, $listFilterQuery)"
                :active="$listing === \App\Enums\LeadListingFilter::Converted"
            >
                <x-slot:icon>
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </x-slot:icon>
            </x-tenant.stat-card>
            <x-tenant.stat-card
                comfortable
                :label="__('Lost')"
                :value="$statistics['lost']"
                accent="rose"
                :href="\App\Enums\LeadListingFilter::Lost->indexUrl($search, $listFilterQuery)"
                :active="$listing === \App\Enums\LeadListingFilter::Lost"
            >
                <x-slot:icon>
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </x-slot:icon>
            </x-tenant.stat-card>
        </div>

        {{-- Leads table --}}
        <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
            <div
                x-show="selected.length > 0"
                x-cloak
                class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 bg-slate-50 px-4 py-3"
            >
                <p class="text-sm font-medium text-black">
                    <span x-text="selected.length"></span>
                    {{ __('selected') }}
                </p>
                <div class="flex flex-wrap items-center gap-2">
                    <x-ui.button type="button" variant="outline" size="sm" @click="openBulkModal('bulk-assign-leads')">
                        {{ __('Assign') }}
                    </x-ui.button>
                    <x-ui.button type="button" variant="outline" size="sm" @click="openBulkModal('bulk-change-lead-status')">
                        {{ __('Change Status') }}
                    </x-ui.button>
                    <x-ui.button type="button" variant="outline" size="sm" @click="exportSelected()">
                        {{ __('Export') }}
                    </x-ui.button>
                    <form
                        method="POST"
                        action="{{ route('tenant.leads.bulk-destroy') }}"
                        class="inline"
                        @submit.prevent="if (confirm(@js(__('Are you sure you want to delete the selected leads?')))) { $el.submit(); }"
                    >
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="listing" value="{{ $listing->value }}">
                        @if ($search !== '')
                            <input type="hidden" name="search" value="{{ $search }}">
                        @endif
                        @foreach ($listFilterQuery as $key => $value)
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endforeach
                        <template x-for="id in selected" :key="id">
                            <input type="hidden" name="lead_ids[]" :value="id">
                        </template>
                        <x-ui.button type="submit" variant="destructive" size="sm" class="bg-red-600 text-white hover:bg-red-700">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                            {{ __('Delete') }}
                        </x-ui.button>
                    </form>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50">
                        <tr class="align-middle">
                            <th class="w-10 whitespace-nowrap px-4 py-3 align-middle">
                                <input
                                    type="checkbox"
                                    class="rounded border-slate-300 text-black focus:ring-navy"
                                    :checked="allSelected"
                                    x-effect="$el.indeterminate = someSelected"
                                    @change="toggleAll()"
                                    @click.stop
                                    aria-label="{{ __('Select all leads on this page') }}"
                                >
                            </th>
                            <th x-show="columns.name" class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Lead Name') }}</th>
                            <th x-show="columns.phone" x-cloak class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Phone') }}</th>
                            <th x-show="columns.source" x-cloak class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Source') }}</th>
                            <th x-show="columns.requirement" x-cloak class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Requirement') }}</th>
                            <th x-show="columns.assigned_to" x-cloak class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Assigned To') }}</th>
                            <th x-show="columns.status" x-cloak class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Lead Status') }}</th>
                            <th x-show="columns.next_follow_up" x-cloak class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Next Follow-up') }}</th>
                            <th x-show="columns.follow_ups_count" x-cloak class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Follow-ups') }}</th>
                            <th x-show="columns.site_visits_count" x-cloak class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Site Visits') }}</th>
                            <th x-show="columns.last_activity" x-cloak class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Last Activity') }}</th>
                            <th x-show="columns.property_interest" x-cloak class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Property Interest') }}</th>
                            <th x-show="columns.booking_date" x-cloak class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Booking Date') }}</th>
                            <th x-show="columns.property_booked" x-cloak class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Property Booked') }}</th>
                            <th x-show="columns.created_at" x-cloak class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Created Date') }}</th>
                            <th x-show="columns.actions" x-cloak class="whitespace-nowrap px-4 py-3 text-end align-middle text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($leads as $lead)
                            <tr
                                class="cursor-pointer align-middle transition hover:bg-slate-50"
                                @mouseenter="$dispatch('prefetch-lead', {{ $lead->id }})"
                                @pointerdown="$dispatch('prefetch-lead', {{ $lead->id }})"
                                @click="$dispatch('open-lead', {{ $lead->id }})"
                            >
                                <td class="whitespace-nowrap px-4 py-3 align-middle" @click.stop>
                                    <input
                                        type="checkbox"
                                        class="rounded border-slate-300 text-black focus:ring-navy"
                                        :checked="selected.includes({{ $lead->id }})"
                                        @change="toggle({{ $lead->id }})"
                                        aria-label="{{ __('Select :name', ['name' => $lead->name]) }}"
                                    >
                                </td>
                                <td x-show="columns.name" class="min-w-0 px-4 py-3 align-middle">
                                    <x-tenant.lead-link :lead="$lead" />
                                </td>
                                <td x-show="columns.phone" x-cloak class="whitespace-nowrap px-4 py-3 align-middle text-sm text-slate-600">{{ $lead->phone ?? '—' }}</td>
                                <td x-show="columns.source" x-cloak class="whitespace-nowrap px-4 py-3 align-middle text-sm text-slate-600">{{ $lead->source ?? '—' }}</td>
                                <td x-show="columns.requirement" x-cloak class="px-4 py-3 align-middle">
                                    @include('tenant.leads.partials.requirement-card', ['lead' => $lead])
                                </td>
                                <td x-show="columns.assigned_to" x-cloak class="whitespace-nowrap px-4 py-3 align-middle text-sm text-slate-600">{{ $lead->assignedTo?->name ?? '—' }}</td>
                                <td x-show="columns.status" x-cloak class="whitespace-nowrap px-4 py-3 align-middle">
                                    <div class="flex flex-col items-start gap-0.5">
                                        <x-tenant.status-badge :status="$lead->status" />
                                        @if ($stage = $lead->statusStageLabel())
                                            <span class="max-w-[9.5rem] truncate text-xs leading-snug text-slate-500" title="{{ $stage }}">{{ $stage }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td x-show="columns.next_follow_up" x-cloak class="whitespace-nowrap px-4 py-3 align-middle text-sm text-slate-600">{{ $lead->next_follow_up_at?->format('M j, Y g:i A') ?? '—' }}</td>
                                <td x-show="columns.follow_ups_count" x-cloak class="whitespace-nowrap px-4 py-3 align-middle text-sm font-medium text-black">{{ number_format($lead->follow_ups_count) }}</td>
                                <td x-show="columns.site_visits_count" x-cloak class="whitespace-nowrap px-4 py-3 align-middle text-sm font-medium text-black">{{ number_format($lead->site_visits_count) }}</td>
                                <td x-show="columns.last_activity" x-cloak class="whitespace-nowrap px-4 py-3 align-middle text-sm text-slate-600">{{ $lead->last_activity_at?->diffForHumans() ?? '—' }}</td>
                                <td x-show="columns.property_interest" x-cloak class="max-w-xs px-4 py-3 align-middle text-sm text-slate-600">
                                    @php($propertyInterest = $lead->propertyInterestLabel())
                                    <span class="line-clamp-2" title="{{ $propertyInterest ?? '' }}">{{ $propertyInterest ?? '—' }}</span>
                                </td>
                                <td x-show="columns.booking_date" x-cloak class="whitespace-nowrap px-4 py-3 align-middle text-sm text-slate-600">
                                    {{ $lead->latestBooking?->booking_date?->format('M j, Y') ?? '—' }}
                                </td>
                                <td x-show="columns.property_booked" x-cloak class="px-4 py-3 align-middle">
                                    @include('tenant.leads.partials.booked-property-card', ['booking' => $lead->latestBooking])
                                </td>
                                <td x-show="columns.created_at" x-cloak class="whitespace-nowrap px-4 py-3 align-middle text-sm text-slate-600">{{ $lead->created_at->format('M j, Y') }}</td>
                                <td x-show="columns.actions" x-cloak class="whitespace-nowrap px-4 py-3 align-middle text-end" @click.stop>
                                    @include('tenant.leads.partials.action-icons', [
                                        'lead' => $lead,
                                        'listView' => true,
                                        'alpine' => true,
                                        'hideCreateBooking' => $listing === \App\Enums\LeadListingFilter::Converted,
                                    ])
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td :colspan="visibleColumnCount" class="px-4 py-12 text-center text-sm text-slate-500">{{ $listing->emptyMessage() }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($leads->hasPages())
                <div class="border-t border-slate-100 px-4 py-3">{{ $leads->links() }}</div>
            @endif
        </div>

        @push('modals')
            @include('tenant.leads.partials.add-lead-modal', ['users' => $users])
            @include('tenant.leads.partials.import-modal')
            @include('tenant.leads.partials.edit-columns-modal', ['listing' => $listing])
            @include('tenant.leads.partials.bulk-actions-modals', ['users' => $users, 'listing' => $listing, 'search' => $search])

            @foreach ($leads as $lead)
                @include('tenant.leads.partials.follow-up-modal', ['lead' => $lead])
                @include('tenant.leads.partials.site-visit-modal', [
                    'lead' => $lead,
                    'properties' => $bookingProperties,
                ])
                @if ($listing !== \App\Enums\LeadListingFilter::Converted && ! $lead->hasBooking())
                    @include('tenant.bookings.partials.create-booking-modal', [
                        'leads' => collect([$lead]),
                        'properties' => $bookingProperties,
                        'defaultLeadId' => $lead->id,
                        'modalName' => 'create-booking-'.$lead->id,
                        'lockLead' => true,
                    ])
                @endif
            @endforeach
        @endpush
    </div>

    @if (session('external_redirect'))
        <div
            x-data
            x-init="
                const url = @js(session('external_redirect'));
                if (url.startsWith('tel:')) {
                    window.location.href = url;
                } else {
                    window.open(url, '_blank');
                }
            "
            class="hidden"
            aria-hidden="true"
        ></div>
    @endif
</x-tenant-layout>
