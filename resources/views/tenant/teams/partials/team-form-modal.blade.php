@props([
    'modalName',
    'title',
    'action',
    'method' => 'POST',
    'team' => null,
    'managers',
])

<x-modal :name="$modalName" maxWidth="lg" :focusable="! $team">
    <div class="p-6">
        <h2 class="text-lg font-bold text-black">{{ $title }}</h2>

        <form method="POST" action="{{ $action }}" class="mt-4 space-y-4">
            @csrf
            @if ($method !== 'POST')
                @method($method)
            @endif

            @if ($team)
                <input type="hidden" name="_edit_team_id" value="{{ $team->id }}">
            @else
                <input type="hidden" name="_create_team" value="1">
            @endif

            <div>
                <x-input-label for="{{ $modalName }}_name" :value="__('Team Name')" />
                <x-text-input
                    id="{{ $modalName }}_name"
                    name="name"
                    type="text"
                    class="mt-1 block w-full"
                    :value="old('name', $team?->name)"
                    required
                />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <div>
                <x-input-label for="{{ $modalName }}_description" :value="__('Description')" />
                <textarea
                    id="{{ $modalName }}_description"
                    name="description"
                    rows="3"
                    class="mt-1 block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-navy focus:ring-navy"
                    placeholder="{{ __('Optional team description') }}"
                >{{ old('description', $team?->description) }}</textarea>
                <x-input-error class="mt-2" :messages="$errors->get('description')" />
            </div>

            <div>
                <x-input-label for="{{ $modalName }}_manager_id" :value="__('Manager')" />
                <x-ui.combobox
                    id="{{ $modalName }}_manager_id"
                    name="manager_id"
                    :options="collect($managers)->map(fn ($manager) => ['value' => (string) $manager->id, 'label' => $manager->name])->prepend(['value' => '', 'label' => __('Select a manager')])->all()"
                    :value="old('manager_id', $team?->manager_id ? (string) $team->manager_id : '')"
                    :placeholder="__('Select a manager')"
                />
                <x-input-error class="mt-2" :messages="$errors->get('manager_id')" />
            </div>

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

            <div class="flex justify-end gap-2">
                <x-ui.button type="button" variant="outline" @click="$dispatch('close-modal', '{{ $modalName }}')">{{ __('Cancel') }}</x-ui.button>
                <x-ui.button type="submit" variant="default">{{ $team ? __('Save Team') : __('Create Team') }}</x-ui.button>
            </div>
        </form>
    </div>
</x-modal>
