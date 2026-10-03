<x-app-layout :title="__('Create Quotation') . ' | InSyte CRM'">
    <x-platform.page-header
        :title="__('Create Quotation')"
        :description="__('Step 1 of 4 — Lead')"
    >
        <x-slot:actions>
            <x-ui.button variant="outline" :href="route('platform.quotations')">{{ __('Cancel') }}</x-ui.button>
        </x-slot:actions>
    </x-platform.page-header>

    <x-platform.quotation-wizard-steps :step="$step" />

    <form method="POST" action="{{ route('platform.quotations.wizard.prospect.store') }}" class="mx-auto max-w-2xl">
        @csrf
        <x-platform.panel :title="__('Lead')">
            <div class="space-y-4">
                <div>
                    <x-input-label for="platform_lead_id" :value="__('Select Lead')" />
                    <select id="platform_lead_id" name="platform_lead_id" class="mt-1 block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-navy focus:ring-navy" required onchange="fillLeadFields(this)">
                        <option value="">{{ __('Choose a lead...') }}</option>
                        @foreach (\App\Models\PlatformLead::quotationSelectOptions() as $lead)
                            <option
                                value="{{ $lead['id'] }}"
                                data-company="{{ $lead['company_name'] }}"
                                data-contact="{{ $lead['contact_person'] }}"
                                data-email="{{ $lead['email'] }}"
                                data-phone="{{ $lead['phone'] ?? '' }}"
                                @selected((int) old('platform_lead_id') === $lead['id'])
                            >
                                {{ $lead['company_name'] }} · {{ $lead['contact_person'] }} · {{ $lead['stage'] }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('platform_lead_id')" />
                </div>
                <div>
                    <x-input-label for="company_name" :value="__('Company Name')" />
                    <x-text-input id="company_name" name="company_name" type="text" class="mt-1 block w-full" :value="old('company_name', $draft['company_name'] ?? '')" required readonly />
                    <x-input-error class="mt-2" :messages="$errors->get('company_name')" />
                </div>
                <div>
                    <x-input-label for="owner_name" :value="__('Contact Person')" />
                    <x-text-input id="owner_name" name="owner_name" type="text" class="mt-1 block w-full" :value="old('owner_name', $draft['owner_name'] ?? '')" required readonly />
                    <x-input-error class="mt-2" :messages="$errors->get('owner_name')" />
                </div>
                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $draft['email'] ?? '')" required readonly />
                    <x-input-error class="mt-2" :messages="$errors->get('email')" />
                </div>
                <div>
                    <x-input-label for="phone" :value="__('Phone')" />
                    <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $draft['phone'] ?? '')" readonly />
                    <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <x-ui.button type="submit" variant="default">{{ __('Continue') }}</x-ui.button>
            </div>
        </x-platform.panel>
    </form>

    @pushOnce('scripts')
        <script>
            function fillLeadFields(select) {
                const option = select.options[select.selectedIndex];
                document.getElementById('company_name').value = option.dataset.company || '';
                document.getElementById('owner_name').value = option.dataset.contact || '';
                document.getElementById('email').value = option.dataset.email || '';
                document.getElementById('phone').value = option.dataset.phone || '';
            }
        </script>
    @endpushOnce
</x-app-layout>
