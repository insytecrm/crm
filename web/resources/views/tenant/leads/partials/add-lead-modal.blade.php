<x-modal name="add-lead" maxWidth="xl">
    <x-ui.modal.header
        :title="__('Add Lead')"
        :description="__('Fill in the lead information below')"
        modal-name="add-lead"
    >
        <x-slot:icon>
            <svg class="size-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
            </svg>
        </x-slot:icon>
    </x-ui.modal.header>

    <form method="POST" action="{{ route('tenant.leads.store') }}">
        @csrf
        <x-ui.modal.body>
            <x-ui.modal.section :title="__('Basic Information')">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-x-4 sm:gap-y-3">
                    <div class="sm:col-span-2">
                        <x-ui.modal.field-label for="name" :value="__('Name')" required />
                        <x-auth.icon-input id="name" name="name" required placeholder="{{ __('Lead name') }}" />
                    </div>
                    <div>
                        <x-ui.modal.field-label for="phone" :value="__('Phone')" />
                        <x-auth.icon-input id="phone" name="phone" placeholder="{{ __('Phone number') }}" />
                    </div>
                    <div>
                        <x-ui.modal.field-label for="email" :value="__('Email')" />
                        <x-auth.icon-input id="email" type="email" name="email" placeholder="{{ __('Email address') }}" />
                    </div>
                    <div class="sm:col-span-2">
                        <x-ui.modal.field-label for="source" :value="__('Source')" />
                        <select
                            id="source"
                            name="source"
                            class="mt-1 block h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-black shadow-sm transition-colors focus:border-navy focus:ring-navy"
                        >
                            <option value="">{{ __('Select source') }}</option>
                            @foreach (\App\Enums\LeadSource::manualSelectableCases() as $source)
                                <option value="{{ $source->value }}" @selected(old('source') === $source->value)>{{ $source->label() }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-1" :messages="$errors->get('source')" />
                    </div>
                </div>
            </x-ui.modal.section>

            <x-ui.modal.section :title="__('Lead Details')">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-x-4 sm:gap-y-3">
                    <div>
                        <x-ui.modal.field-label for="budget" :value="__('Budget')" />
                        @include('tenant.leads.partials.budget-combobox', ['id' => 'budget'])
                        <x-input-error class="mt-1" :messages="$errors->get('budget')" />
                    </div>
                    <div>
                        <x-ui.modal.field-label for="location" :value="__('Location')" />
                        <x-auth.icon-input id="location" name="location" placeholder="{{ __('Preferred location') }}" />
                    </div>
                    <div>
                        <x-ui.modal.field-label for="property_type" :value="__('Property Type')" />
                        @include('tenant.leads.partials.property-type-select', ['id' => 'property_type'])
                        <x-input-error class="mt-1" :messages="$errors->get('property_type')" />
                    </div>
                    <div>
                        <x-ui.modal.field-label for="configuration" :value="__('Configuration')" />
                        <x-auth.icon-input id="configuration" name="configuration" placeholder="{{ __('2 BHK, 3 BHK...') }}" />
                    </div>
                    <div class="sm:col-span-2">
                        <x-ui.modal.field-label for="assigned_to_id" :value="__('Assigned To')" />
                        <x-ui.combobox
                            id="assigned_to_id"
                            name="assigned_to_id"
                            :options="collect($users)->map(fn ($user) => ['value' => (string) $user->id, 'label' => $user->name])->prepend(['value' => '', 'label' => __('Assign to me')])->all()"
                            :placeholder="__('Assign to me')"
                            :searchable="false"
                        />
                    </div>
                </div>
            </x-ui.modal.section>
        </x-ui.modal.body>

        <x-ui.modal.footer>
            <x-ui.modal.cancel-button modal-name="add-lead" />
            <x-ui.modal.submit-button>{{ __('Create Lead') }}</x-ui.modal.submit-button>
        </x-ui.modal.footer>
    </form>
</x-modal>
