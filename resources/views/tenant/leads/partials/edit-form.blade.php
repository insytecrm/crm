<form
    method="POST"
    action="{{ route('tenant.leads.update', $lead) }}"
    class="flex h-full min-h-0 flex-col overflow-hidden"
    @click.stop
>
    @csrf
    @method('PATCH')

    {{-- Header --}}
    <div class="shrink-0 border-b border-slate-100 bg-slate-50 px-4 py-4 sm:px-6 sm:py-5">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-black sm:text-xl">{{ __('Edit Lead') }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ __('Update lead information below.') }}</p>
            </div>
            <button
                type="button"
                class="rounded-md p-2 text-slate-400 hover:bg-white hover:text-black"
                @click="editing = false"
                aria-label="{{ __('Cancel editing') }}"
            >
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    {{-- Form body --}}
    <x-ui.scroll-area class="min-h-0 flex-1" fade>
        <div class="p-4 sm:p-6">
        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <x-input-label for="edit_name_{{ $lead->id }}" :value="__('Name')" />
                <x-auth.icon-input id="edit_name_{{ $lead->id }}" name="name" :value="old('name', $lead->name)" required />
            </div>
            <div>
                <x-input-label for="edit_phone_{{ $lead->id }}" :value="__('Phone')" />
                <x-auth.icon-input id="edit_phone_{{ $lead->id }}" name="phone" :value="old('phone', $lead->phone)" />
            </div>
            <div>
                <x-input-label for="edit_email_{{ $lead->id }}" :value="__('Email')" />
                <x-auth.icon-input id="edit_email_{{ $lead->id }}" type="email" name="email" :value="old('email', $lead->email)" />
            </div>
            <div>
                <x-input-label for="edit_source_{{ $lead->id }}" :value="__('Source')" />
                <x-auth.icon-input id="edit_source_{{ $lead->id }}" name="source" :value="old('source', $lead->source)" />
            </div>
            <div>
                <x-input-label for="edit_budget_{{ $lead->id }}" :value="__('Budget')" />
                @include('tenant.leads.partials.budget-combobox', [
                    'id' => 'edit_budget_'.$lead->id,
                    'value' => $lead->budget?->value,
                ])
                <x-input-error class="mt-2" :messages="$errors->get('budget')" />
            </div>
            <div>
                <x-input-label for="edit_location_{{ $lead->id }}" :value="__('Location')" />
                <x-auth.icon-input id="edit_location_{{ $lead->id }}" name="location" :value="old('location', $lead->location)" />
            </div>
            <div>
                <x-input-label for="edit_property_type_{{ $lead->id }}" :value="__('Property Type')" />
                @include('tenant.leads.partials.property-type-select', [
                    'id' => 'edit_property_type_'.$lead->id,
                    'value' => $lead->property_type?->value,
                ])
                <x-input-error class="mt-2" :messages="$errors->get('property_type')" />
            </div>
            <div>
                <x-input-label for="edit_configuration_{{ $lead->id }}" :value="__('Configuration')" />
                <x-auth.icon-input id="edit_configuration_{{ $lead->id }}" name="configuration" :value="old('configuration', $lead->configuration)" />
            </div>
            <div>
                <x-input-label for="edit_lead_score_{{ $lead->id }}" :value="__('Lead Score')" />
                <x-auth.icon-input id="edit_lead_score_{{ $lead->id }}" type="number" name="lead_score" :value="old('lead_score', $lead->lead_score)" min="0" max="100" />
            </div>
            <div>
                <x-input-label for="edit_assigned_to_id_{{ $lead->id }}" :value="__('Assigned To')" />
                <x-ui.combobox
                    name="assigned_to_id"
                    id="edit_assigned_to_id_{{ $lead->id }}"
                    :options="collect($users)->map(fn ($user) => ['value' => (string) $user->id, 'label' => $user->name])->prepend(['value' => '', 'label' => __('Unassigned')])->all()"
                    :value="old('assigned_to_id', (string) ($lead->assigned_to_id ?? ''))"
                    :placeholder="__('Unassigned')"
                />
            </div>
            <div class="sm:col-span-2">
                <x-input-label for="edit_next_action_{{ $lead->id }}" :value="__('Next Action')" />
                <x-auth.icon-input id="edit_next_action_{{ $lead->id }}" name="next_action" :value="old('next_action', $lead->next_action)" />
            </div>
        </div>
        </div>
    </x-ui.scroll-area>

    {{-- Footer --}}
    <div class="flex shrink-0 flex-col gap-3 border-t border-slate-100 bg-slate-50 px-4 py-4 sm:flex-row sm:items-center sm:justify-end sm:px-6">
        <x-ui.button type="button" variant="outline" @click="editing = false">{{ __('Cancel') }}</x-ui.button>
        <x-ui.button type="submit" variant="default">{{ __('Save Changes') }}</x-ui.button>
    </div>
</form>
