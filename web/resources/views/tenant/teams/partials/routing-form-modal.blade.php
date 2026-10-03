@props([
    'modalName' => 'add-routing',
    'title' => null,
    'description' => null,
    'action',
    'method' => 'POST',
    'rule' => null,
    'teams',
    'sources',
    'distributions',
    'subSources' => [],
])

@php
    $title ??= $rule ? __('Edit Routing') : __('Add Routing');
    $description ??= __('Send leads from a source to a team with the distribution you choose');
    $selectedTeamId = old('sales_team_id', $rule?->sales_team_id);
    $selectedSource = old('source', $rule?->source ?? '');
    $selectedSubSource = old('sub_source', $rule?->sub_source ?? '');
    $selectedMemberIds = collect(old('members', $rule?->members?->map(fn ($member) => [
        'user_id' => $member->id,
        'weight' => (int) ($member->pivot->weight ?? 1),
    ])->all() ?? []))->keyBy(fn ($row) => (string) ($row['user_id'] ?? ''));
    $teamsPayload = collect($teams)->map(fn ($team) => [
        'id' => $team->id,
        'name' => $team->name,
        'members' => $team->members->map(fn ($member) => [
            'id' => $member->id,
            'name' => $member->name,
            'role' => $member->role?->name ?? '',
        ])->values()->all(),
    ])->values()->all();
@endphp

<x-modal :name="$modalName" maxWidth="xl">
    <x-ui.modal.header
        :title="$title"
        :description="$description"
        :modal-name="$modalName"
    >
        <x-slot:icon>
            <svg class="size-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
            </svg>
        </x-slot:icon>
    </x-ui.modal.header>

    <form
        method="POST"
        action="{{ $action }}"
        x-data="leadRoutingForm(@js([
            'teams' => $teamsPayload,
            'subSources' => $subSources,
            'source' => (string) $selectedSource,
            'subSource' => (string) $selectedSubSource,
            'teamId' => $selectedTeamId ? (string) $selectedTeamId : '',
            'distribution' => old('distribution', $rule?->distribution?->value ?? 'round_robin'),
            'selected' => $selectedMemberIds->map(fn ($row) => [
                'user_id' => (string) ($row['user_id'] ?? ''),
                'weight' => (int) ($row['weight'] ?? 1),
            ])->values()->all(),
        ]))"
    >
        @csrf
        @if ($method !== 'POST')
            @method($method)
        @endif
        @if ($rule)
            <input type="hidden" name="_edit_routing_id" value="{{ $rule->id }}">
        @else
            <input type="hidden" name="_create_routing" value="1">
        @endif

        <x-ui.modal.body>
            <x-ui.modal.section :title="__('Lead Source')">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-x-4 sm:gap-y-3">
                    <div>
                        <x-ui.modal.field-label for="{{ $modalName }}_source" :value="__('Source')" required />
                        <select
                            id="{{ $modalName }}_source"
                            name="source"
                            required
                            x-model="source"
                            @change="onSourceChange()"
                            class="mt-1 block h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-black shadow-sm transition-colors focus:border-navy focus:ring-navy"
                        >
                            <option value="">{{ __('Select source') }}</option>
                            @foreach ($sources as $sourceOption)
                                <option value="{{ $sourceOption->value }}">{{ $sourceOption->label() }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-1" :messages="$errors->get('source')" />
                    </div>
                    <div>
                        <x-ui.modal.field-label for="{{ $modalName }}_sub_source" :value="__('Sub-source')" />
                        <select
                            id="{{ $modalName }}_sub_source"
                            name="sub_source"
                            x-model="subSource"
                            class="mt-1 block h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-black shadow-sm transition-colors focus:border-navy focus:ring-navy"
                            :disabled="! source"
                        >
                            <option value="">{{ __('Any sub-source') }}</option>
                            <template x-for="option in availableSubSources" :key="option.value">
                                <option :value="option.value" x-text="option.label"></option>
                            </template>
                        </select>
                        <p class="mt-1 text-xs text-slate-500" x-show="source && availableSubSources.length === 0">
                            {{ __('No campaigns, sheets, or other sub-sources found for this source yet.') }}
                        </p>
                        <x-input-error class="mt-1" :messages="$errors->get('sub_source')" />
                    </div>
                </div>
            </x-ui.modal.section>

            <x-ui.modal.section :title="__('Team & Members')">
                <div class="grid grid-cols-1 gap-3 sm:gap-y-3">
                    <div>
                        <x-ui.modal.field-label for="{{ $modalName }}_sales_team_id" :value="__('Sales team')" required />
                        <select
                            id="{{ $modalName }}_sales_team_id"
                            name="sales_team_id"
                            required
                            x-model="teamId"
                            @change="onTeamChange()"
                            class="mt-1 block h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-black shadow-sm transition-colors focus:border-navy focus:ring-navy"
                        >
                            <option value="">{{ __('Select team') }}</option>
                            @foreach ($teams as $team)
                                <option value="{{ $team->id }}">{{ $team->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-1" :messages="$errors->get('sales_team_id')" />
                    </div>

                    <div>
                        <x-ui.modal.field-label :value="__('Members in pool')" required />
                        <p class="mt-0.5 text-xs text-slate-500">{{ __('Choose who can receive leads from this rule.') }}</p>
                        <div class="mt-2 max-h-48 space-y-2 overflow-y-auto rounded-lg border border-slate-200 p-3" x-show="teamMembers.length > 0">
                            <template x-for="member in teamMembers" :key="member.id">
                                <label class="flex items-center justify-between gap-3 rounded-md px-1 py-1 hover:bg-slate-50">
                                    <span class="flex min-w-0 items-center gap-2">
                                        <input
                                            type="checkbox"
                                            class="rounded border-slate-300 text-navy focus:ring-navy"
                                            :checked="isSelected(member.id)"
                                            @change="toggleMember(member.id)"
                                        >
                                        <span class="truncate text-sm text-black">
                                            <span x-text="member.name"></span>
                                            <span class="text-slate-500" x-show="member.role" x-text="' - ' + member.role"></span>
                                        </span>
                                    </span>
                                    <span class="flex items-center gap-1" x-show="distribution === 'custom' && isSelected(member.id)">
                                        <span class="text-[11px] font-medium uppercase tracking-wide text-slate-400">{{ __('Weight') }}</span>
                                        <input
                                            type="number"
                                            min="1"
                                            max="100"
                                            class="h-8 w-16 rounded-md border border-slate-200 px-2 text-sm text-black focus:border-navy focus:ring-navy"
                                            :value="memberWeight(member.id)"
                                            @input="setWeight(member.id, $event.target.value)"
                                        >
                                    </span>
                                </label>
                            </template>
                        </div>
                        <p class="mt-2 text-sm text-slate-500" x-show="teamId && teamMembers.length === 0">{{ __('This team has no members yet.') }}</p>
                        <p class="mt-2 text-sm text-slate-500" x-show="!teamId">{{ __('Select a team to choose members.') }}</p>
                        <x-input-error class="mt-1" :messages="$errors->get('members')" />
                        <template x-for="(row, index) in selected" :key="'member-'+row.user_id">
                            <span>
                                <input type="hidden" :name="`members[${index}][user_id]`" :value="row.user_id">
                                <input type="hidden" :name="`members[${index}][weight]`" :value="row.weight">
                            </span>
                        </template>
                    </div>
                </div>
            </x-ui.modal.section>

            <x-ui.modal.section :title="__('Distribution')">
                <div class="grid grid-cols-1 gap-3">
                    <div>
                        <x-ui.modal.field-label for="{{ $modalName }}_distribution" :value="__('Distribution type')" required />
                        <select
                            id="{{ $modalName }}_distribution"
                            name="distribution"
                            required
                            x-model="distribution"
                            class="mt-1 block h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-black shadow-sm transition-colors focus:border-navy focus:ring-navy"
                        >
                            @foreach ($distributions as $distribution)
                                <option value="{{ $distribution->value }}">{{ $distribution->label() }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-slate-500">
                            <template x-if="distribution === 'equal'"><span>{{ __('Share leads evenly by who has the fewest open leads.') }}</span></template>
                            <template x-if="distribution === 'round_robin'"><span>{{ __('Give the next lead to the next teammate in turn.') }}</span></template>
                            <template x-if="distribution === 'custom'"><span>{{ __('Send a set number of leads to each teammate (for example 2, 2, 1).') }}</span></template>
                        </p>
                        <x-input-error class="mt-1" :messages="$errors->get('distribution')" />
                    </div>

                    <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-4">
                        <input type="hidden" name="is_active" value="0">
                        <label class="flex cursor-pointer items-center justify-between gap-4">
                            <span class="min-w-0">
                                <span class="block text-sm font-medium text-black">{{ __('Active') }}</span>
                                <span class="mt-0.5 block text-sm text-slate-500">{{ __('Only active rules assign new leads.') }}</span>
                            </span>
                            <span class="relative inline-flex h-6 w-11 shrink-0">
                                <input
                                    type="checkbox"
                                    name="is_active"
                                    value="1"
                                    class="peer sr-only"
                                    tabindex="-1"
                                    @checked(filter_var(old('is_active', $rule?->is_active ?? true), FILTER_VALIDATE_BOOLEAN))
                                >
                                <span class="absolute inset-0 rounded-full bg-slate-200 transition-colors peer-checked:bg-navy peer-focus-visible:ring-2 peer-focus-visible:ring-navy/40 peer-focus-visible:ring-offset-2"></span>
                                <span class="absolute left-0.5 top-0.5 size-5 rounded-full bg-white shadow-sm transition-transform peer-checked:translate-x-5"></span>
                            </span>
                        </label>
                    </div>
                </div>
            </x-ui.modal.section>
        </x-ui.modal.body>

        <x-ui.modal.footer>
            <x-ui.modal.cancel-button :modal-name="$modalName" />
            <x-ui.modal.submit-button>{{ $rule ? __('Save Routing') : __('Create Routing') }}</x-ui.modal.submit-button>
        </x-ui.modal.footer>
    </form>
</x-modal>

@once
    @push('scripts')
        <script>
            function leadRoutingForm(config) {
                return {
                    teams: config.teams ?? [],
                    subSources: config.subSources ?? {},
                    source: config.source ?? '',
                    subSource: config.subSource ?? '',
                    teamId: config.teamId ?? '',
                    distribution: config.distribution ?? 'round_robin',
                    selected: config.selected ?? [],

                    get teamMembers() {
                        const team = this.teams.find((item) => String(item.id) === String(this.teamId));

                        return team?.members ?? [];
                    },

                    get availableSubSources() {
                        if (! this.source) {
                            return [];
                        }

                        const options = [...(this.subSources[this.source] ?? [])];

                        if (this.subSource && ! options.some((option) => option.value === this.subSource)) {
                            options.unshift({ value: this.subSource, label: this.subSource });
                        }

                        return options;
                    },

                    onSourceChange() {
                        const options = this.subSources[this.source] ?? [];

                        if (this.subSource && ! options.some((option) => option.value === this.subSource)) {
                            this.subSource = '';
                        }
                    },

                    onTeamChange() {
                        const allowed = new Set(this.teamMembers.map((member) => String(member.id)));
                        this.selected = this.selected.filter((row) => allowed.has(String(row.user_id)));
                    },

                    isSelected(userId) {
                        return this.selected.some((row) => String(row.user_id) === String(userId));
                    },

                    memberWeight(userId) {
                        return this.selected.find((row) => String(row.user_id) === String(userId))?.weight ?? 1;
                    },

                    toggleMember(userId) {
                        if (this.isSelected(userId)) {
                            this.selected = this.selected.filter((row) => String(row.user_id) !== String(userId));

                            return;
                        }

                        this.selected.push({ user_id: String(userId), weight: 1 });
                    },

                    setWeight(userId, value) {
                        const weight = Math.max(1, Math.min(100, Number(value) || 1));
                        const row = this.selected.find((item) => String(item.user_id) === String(userId));

                        if (row) {
                            row.weight = weight;
                        }
                    },
                };
            }
        </script>
    @endpush
@endonce
