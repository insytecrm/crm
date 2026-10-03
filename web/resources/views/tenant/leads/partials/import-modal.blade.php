<x-modal name="import-leads" maxWidth="lg">
    <x-ui.modal.header
        :title="__('Import Leads')"
        :description="__('Upload a CSV file with columns: Name, Phone, Email, Source, Budget, Location, Property Type, Configuration')"
        modal-name="import-leads"
    >
        <x-slot:icon>
            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
            </svg>
        </x-slot:icon>
    </x-ui.modal.header>

    <form method="POST" action="{{ route('tenant.leads.import') }}" enctype="multipart/form-data">
        @csrf
        <x-ui.modal.body>
            <x-ui.modal.section :title="__('Upload File')">
                <x-ui.modal.field-label :value="__('CSV File')" required />
                <input type="file" name="file" accept=".csv,.txt" required class="block w-full rounded-lg border border-slate-200 bg-white text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-black hover:file:bg-slate-200">
            </x-ui.modal.section>
        </x-ui.modal.body>

        <x-ui.modal.footer>
            <x-ui.modal.cancel-button modal-name="import-leads" />
            <x-ui.modal.submit-button>{{ __('Import') }}</x-ui.modal.submit-button>
        </x-ui.modal.footer>
    </form>
</x-modal>
