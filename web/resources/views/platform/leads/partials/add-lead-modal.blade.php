@php
    use App\Enums\PlatformLeadSource;
    use App\Enums\PlatformLeadStage;

    $sources = $sources ?? PlatformLeadSource::cases();
    $stages = $stages ?? PlatformLeadStage::orderedCases();
    $owners = $owners ?? [];
    $openAddLeadModal = $openAddLeadModal ?? false;
@endphp

@push('modals')
    <x-modal name="add-platform-lead" maxWidth="2xl" :show="$openAddLeadModal" focusable>
        <form method="POST" action="{{ route('platform.leads.store') }}">
            @csrf
            <input type="hidden" name="_add_lead_modal" value="1">

            <x-ui.modal.header
                :title="__('Add Lead')"
                :description="__('Add a new potential InSyte customer quickly.')"
                modal-name="add-platform-lead"
            >
                <x-slot:icon>
                    <svg class="size-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                    </svg>
                </x-slot:icon>
            </x-ui.modal.header>

            <x-ui.modal.body class="max-h-[60vh] overflow-y-auto">
                <x-ui.modal.section :title="__('Lead Details')">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-x-4 sm:gap-y-3">
                        <div class="sm:col-span-2">
                            <x-ui.modal.field-label for="modal_company_name" :value="__('Company Name')" required />
                            <x-text-input id="modal_company_name" name="company_name" type="text" class="mt-0.5 block w-full" :value="old('company_name')" required autofocus />
                            <x-input-error class="mt-1" :messages="$errors->get('company_name')" />
                        </div>
                        <div class="sm:col-span-2">
                            <x-ui.modal.field-label for="modal_contact_person" :value="__('Contact Person')" required />
                            <x-text-input id="modal_contact_person" name="contact_person" type="text" class="mt-0.5 block w-full" :value="old('contact_person')" required />
                            <x-input-error class="mt-1" :messages="$errors->get('contact_person')" />
                        </div>
                        <div>
                            <x-ui.modal.field-label for="modal_email" :value="__('Email')" required />
                            <x-text-input id="modal_email" name="email" type="email" class="mt-0.5 block w-full" :value="old('email')" required />
                            <x-input-error class="mt-1" :messages="$errors->get('email')" />
                        </div>
                        <div>
                            <x-ui.modal.field-label for="modal_phone" :value="__('Phone')" required />
                            <x-text-input id="modal_phone" name="phone" type="text" class="mt-0.5 block w-full" :value="old('phone')" required />
                            <x-input-error class="mt-1" :messages="$errors->get('phone')" />
                        </div>
                        <div class="sm:col-span-2">
                            <x-ui.modal.field-label for="modal_location" :value="__('Location')" />
                            <x-text-input id="modal_location" name="location" type="text" class="mt-0.5 block w-full" :value="old('location')" />
                            <x-input-error class="mt-1" :messages="$errors->get('location')" />
                        </div>
                        <div>
                            <x-ui.modal.field-label for="modal_source" :value="__('Source')" />
                            <select id="modal_source" name="source" class="mt-0.5 block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-navy focus:ring-navy">
                                @foreach ($sources as $source)
                                    <option value="{{ $source->value }}" @selected(old('source', 'website') === $source->value)>{{ $source->label() }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-1" :messages="$errors->get('source')" />
                        </div>
                        <div>
                            <x-ui.modal.field-label for="modal_owner_id" :value="__('Owner')" />
                            <select id="modal_owner_id" name="owner_id" class="mt-0.5 block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-navy focus:ring-navy">
                                <option value="">{{ __('Unassigned') }}</option>
                                @foreach ($owners as $owner)
                                    <option value="{{ $owner['id'] }}" @selected((int) old('owner_id', auth()->id()) === $owner['id'])>{{ $owner['name'] }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-1" :messages="$errors->get('owner_id')" />
                        </div>
                        <div class="sm:col-span-2">
                            <x-ui.modal.field-label for="modal_stage" :value="__('Stage')" />
                            <select id="modal_stage" name="stage" class="mt-0.5 block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-navy focus:ring-navy">
                                @foreach ($stages as $stage)
                                    <option value="{{ $stage->value }}" @selected(old('stage', 'new_lead') === $stage->value)>{{ $stage->label() }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-1" :messages="$errors->get('stage')" />
                        </div>
                    </div>
                </x-ui.modal.section>
            </x-ui.modal.body>

            <x-ui.modal.footer>
                <x-ui.modal.cancel-button modal-name="add-platform-lead" />
                <x-ui.modal.submit-button>{{ __('Create Lead') }}</x-ui.modal.submit-button>
            </x-ui.modal.footer>
        </form>
    </x-modal>
@endpush
