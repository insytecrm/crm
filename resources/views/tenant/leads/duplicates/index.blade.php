<x-tenant-layout :title="__('Duplicate Leads') . ' | InSyte CRM'">
    <x-auth-session-status class="mb-3 rounded-lg bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700" :status="session('status')" />

    <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <x-tenant.stat-card :label="__('Duplicate Groups')" :value="(string) $duplicateGroupCount" accent="amber">
            <x-slot:icon>
                <svg class="size-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                </svg>
            </x-slot:icon>
        </x-tenant.stat-card>

        <x-tenant.stat-card :label="__('Leads in Duplicate Groups')" :value="(string) $duplicateLeadCount" accent="sky">
            <x-slot:icon>
                <svg class="size-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
            </x-slot:icon>
        </x-tenant.stat-card>
    </div>

    @if ($groups->isEmpty())
        <div class="rounded-2xl border border-slate-100 bg-white p-8 text-center shadow-sm">
            <p class="text-sm font-medium text-slate-700">{{ __('No duplicate leads found.') }}</p>
            <p class="mt-1 text-sm text-slate-500">{{ __('Duplicates are detected when two or more leads share the same phone number or email address.') }}</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($groups as $group)
                <section class="overflow-hidden rounded-2xl border border-amber-100/80 bg-white shadow-sm">
                    <div class="border-b border-amber-100/80 bg-gradient-to-r from-amber-50/80 to-white px-5 py-4 sm:px-6">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <h2 class="text-base font-semibold text-black">
                                    {{ trans_choice(':count possible duplicate|:count possible duplicates', $group['leads']->count(), ['count' => $group['leads']->count()]) }}
                                </h2>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @foreach ($group['match_reasons'] as $reason)
                                        <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-800">
                                            {{ $reason }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <form
                        method="POST"
                        action="{{ route('tenant.leads.duplicates.merge') }}"
                        class="divide-y divide-slate-100"
                        x-data="{
                            primaryLeadId: @js($group['leads']->first()->id),
                            selectedDuplicates: @js($group['leads']->skip(1)->pluck('id')->values()),
                            toggleDuplicate(id) {
                                if (this.selectedDuplicates.includes(id)) {
                                    this.selectedDuplicates = this.selectedDuplicates.filter((item) => item !== id);
                                } else {
                                    this.selectedDuplicates.push(id);
                                }
                            },
                            get canMerge() {
                                return this.selectedDuplicates.length > 0 && this.primaryLeadId;
                            },
                        }"
                        onsubmit="return confirm(@js(__('Merge the selected duplicate leads into the primary lead? This cannot be undone.')));"
                    >
                        @csrf

                        <template x-for="duplicateId in selectedDuplicates" :key="duplicateId">
                            <input type="hidden" name="duplicate_lead_ids[]" :value="duplicateId">
                        </template>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-100">
                                <thead class="bg-slate-50/80">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Primary') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Merge') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Name') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Phone') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Email') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Status') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Assigned To') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Created') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    @foreach ($group['leads'] as $lead)
                                        <tr class="hover:bg-slate-50/60">
                                            <td class="px-4 py-3">
                                                <input
                                                    type="radio"
                                                    name="primary_lead_id"
                                                    value="{{ $lead->id }}"
                                                    class="h-4 w-4 border-slate-300 text-black focus:ring-navy"
                                                    x-model.number="primaryLeadId"
                                                    @change="selectedDuplicates = selectedDuplicates.filter((id) => id !== primaryLeadId)"
                                                >
                                            </td>
                                            <td class="px-4 py-3">
                                                <input
                                                    type="checkbox"
                                                    value="{{ $lead->id }}"
                                                    class="h-4 w-4 rounded border-slate-300 text-black focus:ring-navy"
                                                    :checked="selectedDuplicates.includes(@js($lead->id))"
                                                    :disabled="primaryLeadId === @js($lead->id)"
                                                    @change="toggleDuplicate(@js($lead->id))"
                                                >
                                            </td>
                                            <td class="px-4 py-3">
                                                <button
                                                    type="button"
                                                    class="font-medium text-black hover:underline"
                                                    @click="$dispatch('open-lead-drawer', { leadId: {{ $lead->id }} })"
                                                >
                                                    {{ $lead->name }}
                                                </button>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-slate-600">{{ $lead->phone ?: '—' }}</td>
                                            <td class="px-4 py-3 text-sm text-slate-600">{{ $lead->email ?: '—' }}</td>
                                            <td class="px-4 py-3">
                                                <x-tenant.status-badge :status="$lead->status" :lead="$lead" />
                                            </td>
                                            <td class="px-4 py-3 text-sm text-slate-600">{{ $lead->assignedTo?->name ?: '—' }}</td>
                                            <td class="px-4 py-3 text-sm text-slate-500">{{ $lead->created_at?->format('M j, Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                            <p class="text-sm text-slate-500">{{ __('Choose the primary lead to keep. Selected duplicates will be merged into it and removed.') }}</p>
                            <x-ui.button type="submit" variant="default" x-bind:disabled="!canMerge">
                                {{ __('Merge Selected') }}
                            </x-ui.button>
                        </div>
                    </form>
                </section>
            @endforeach
        </div>
    @endif
</x-tenant-layout>
