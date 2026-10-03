@props([
    'modalName',
    'title',
    'action',
    'method' => 'POST',
    'team' => null,
    'managers',
])

<x-modal :name="$modalName" maxWidth="xl" :focusable="! $team">
    <x-ui.modal.header
        :title="$title"
        :description="$team ? __('Update this sales team') : __('Create a sales team for lead routing and performance')"
        :modal-name="$modalName"
    >
        <x-slot:icon>
            <svg class="size-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
            </svg>
        </x-slot:icon>
    </x-ui.modal.header>

    <form method="POST" action="{{ $action }}">
        @csrf
        @if ($method !== 'POST')
            @method($method)
        @endif

        @if ($team)
            <input type="hidden" name="_edit_team_id" value="{{ $team->id }}">
        @else
            <input type="hidden" name="_create_team" value="1">
        @endif

        <x-ui.modal.body>
            <x-ui.modal.section :title="__('Team Details')">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-x-4 sm:gap-y-3">
                    <div class="sm:col-span-2">
                        <x-ui.modal.field-label for="{{ $modalName }}_name" :value="__('Team Name')" required />
                        <x-auth.icon-input
                            id="{{ $modalName }}_name"
                            name="name"
                            type="text"
                            required
                            value="{{ old('name', $team?->name) }}"
                            placeholder="{{ __('Team name') }}"
                        />
                        <x-input-error class="mt-1" :messages="$errors->get('name')" />
                    </div>

                    <div class="sm:col-span-2">
                        <x-ui.modal.field-label for="{{ $modalName }}_description" :value="__('Description')" />
                        <textarea
                            id="{{ $modalName }}_description"
                            name="description"
                            rows="3"
                            class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-black shadow-sm transition-colors focus:border-navy focus:ring-navy"
                            placeholder="{{ __('Optional team description') }}"
                        >{{ old('description', $team?->description) }}</textarea>
                        <x-input-error class="mt-1" :messages="$errors->get('description')" />
                    </div>

                    <div class="sm:col-span-2">
                        <x-ui.modal.field-label for="{{ $modalName }}_manager_id" :value="__('Manager')" />
                        <x-ui.combobox
                            id="{{ $modalName }}_manager_id"
                            name="manager_id"
                            :options="collect($managers)->map(fn ($manager) => ['value' => (string) $manager->id, 'label' => $manager->name])->prepend(['value' => '', 'label' => __('Select a manager')])->all()"
                            :value="old('manager_id', $team?->manager_id ? (string) $team->manager_id : '')"
                            :placeholder="__('Select a manager')"
                        />
                        <x-input-error class="mt-1" :messages="$errors->get('manager_id')" />
                    </div>
                </div>
            </x-ui.modal.section>

            <x-ui.modal.section :title="__('Status')">
                @if ($team)
                    <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-4">
                        <input type="hidden" name="is_active" value="0">
                        <label class="flex cursor-pointer items-center justify-between gap-4">
                            <span class="min-w-0">
                                <span class="block text-sm font-medium text-black">{{ __('Active') }}</span>
                                <span class="mt-0.5 block text-sm text-slate-500">{{ __('Inactive teams remain visible but can be excluded from future routing.') }}</span>
                            </span>
                            <span class="relative inline-flex h-6 w-11 shrink-0">
                                <input
                                    type="checkbox"
                                    name="is_active"
                                    value="1"
                                    class="peer sr-only"
                                    tabindex="-1"
                                    @checked(filter_var(old('is_active', $team->isActive()), FILTER_VALIDATE_BOOLEAN))
                                >
                                <span class="absolute inset-0 rounded-full bg-slate-200 transition-colors peer-checked:bg-navy peer-focus-visible:ring-2 peer-focus-visible:ring-navy/40 peer-focus-visible:ring-offset-2"></span>
                                <span class="absolute left-0.5 top-0.5 size-5 rounded-full bg-white shadow-sm transition-transform peer-checked:translate-x-5"></span>
                            </span>
                        </label>
                        <x-input-error class="mt-2" :messages="$errors->get('is_active')" />
                    </div>
                @else
                    <p class="text-sm text-slate-500">{{ __('New teams are created as active.') }}</p>
                @endif
            </x-ui.modal.section>
        </x-ui.modal.body>

        <x-ui.modal.footer>
            <x-ui.modal.cancel-button :modal-name="$modalName" />
            <x-ui.modal.submit-button>{{ $team ? __('Save Team') : __('Create Team') }}</x-ui.modal.submit-button>
        </x-ui.modal.footer>
    </form>
</x-modal>
