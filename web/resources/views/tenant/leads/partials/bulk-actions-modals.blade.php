<x-modal name="bulk-assign-leads" maxWidth="md">
    <div class="p-6">
        <h2 class="text-lg font-bold text-black">{{ __('Assign Leads') }}</h2>
        <p class="mt-1 text-sm text-slate-500">{{ __('Assign the selected leads to a team member.') }}</p>

        <form
            method="POST"
            action="{{ route('tenant.leads.bulk-assign') }}"
            class="mt-4 space-y-4"
        >
            @csrf
            @method('PATCH')
            <input type="hidden" name="listing" value="{{ $listing->value }}">
            @if ($search !== '')
                <input type="hidden" name="search" value="{{ $search }}">
            @endif
            <template x-for="id in $store.leadBulkSelection.ids" :key="id">
                <input type="hidden" name="lead_ids[]" :value="id">
            </template>

            <div>
                <x-input-label for="bulk_assigned_to_id" :value="__('Assigned To')" />
                <x-ui.combobox
                    id="bulk_assigned_to_id"
                    name="assigned_to_id"
                    :options="collect($users)->map(fn ($user) => ['value' => (string) $user->id, 'label' => $user->name])->prepend(['value' => '', 'label' => __('Unassigned')])->all()"
                    :placeholder="__('Unassigned')"
                    :searchable="false"
                />
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <x-ui.button type="button" variant="outline" @click="$dispatch('close-modal', 'bulk-assign-leads')">
                    {{ __('Cancel') }}
                </x-ui.button>
                <x-ui.button type="submit" variant="default">
                    {{ __('Assign') }}
                </x-ui.button>
            </div>
        </form>
    </div>
</x-modal>

<x-modal name="bulk-change-lead-status" maxWidth="md">
    <div class="p-6">
        <h2 class="text-lg font-bold text-black">{{ __('Change Status') }}</h2>
        <p class="mt-1 text-sm text-slate-500">{{ __('Update the status for all selected leads.') }}</p>

        <form
            method="POST"
            action="{{ route('tenant.leads.bulk-status.update') }}"
            class="mt-4 space-y-4"
        >
            @csrf
            @method('PATCH')
            <input type="hidden" name="listing" value="{{ $listing->value }}">
            @if ($search !== '')
                <input type="hidden" name="search" value="{{ $search }}">
            @endif
            <template x-for="id in $store.leadBulkSelection.ids" :key="id">
                <input type="hidden" name="lead_ids[]" :value="id">
            </template>

            <div>
                <x-input-label for="bulk_lead_status" :value="__('Lead Status')" />
                <x-ui.form-select
                    id="bulk_lead_status"
                    name="status"
                    :options="collect(\App\Enums\LeadStatus::manuallySelectableCases())->map(fn ($status) => ['value' => $status->value, 'label' => $status->label()])->all()"
                    :placeholder="__('Select status')"
                    required
                />
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <x-ui.button type="button" variant="outline" @click="$dispatch('close-modal', 'bulk-change-lead-status')">
                    {{ __('Cancel') }}
                </x-ui.button>
                <x-ui.button type="submit" variant="default">
                    {{ __('Update Status') }}
                </x-ui.button>
            </div>
        </form>
    </div>
</x-modal>
