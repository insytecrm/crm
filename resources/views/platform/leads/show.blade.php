<x-app-layout :title="$lead->company_name . ' | InSyte CRM'">
    <div x-data="{ editing: @js($editing) }">
    <div class="mb-4">
        <a href="{{ route('platform.leads') }}" class="text-sm font-medium text-slate-500 hover:text-navy">← {{ __('Leads') }}</a>
    </div>

    <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="min-w-0">
            <h1 class="text-2xl font-bold tracking-tight text-black">{{ $lead->company_name }}</h1>
            <p class="mt-1 text-sm text-slate-500">
                <span class="font-semibold text-navy">{{ $lead->stage->label() }}</span>
                <span class="mx-1.5 text-slate-300">·</span>
                <span>{{ __('Owner') }}: <span class="font-medium text-black">{{ $lead->owner?->name ?? '—' }}</span></span>
                <span class="mx-1.5 text-slate-300">·</span>
                <span>{{ __('Created') }}: <span class="font-medium text-black">{{ $lead->created_at?->timezone(config('app.timezone'))->format('d M Y') ?? '—' }}</span></span>
            </p>
        </div>

        <div class="flex shrink-0 flex-wrap items-center gap-2">
            <template x-if="! editing">
                <div class="flex flex-wrap items-center gap-2">
                    <x-ui.button type="button" variant="outline" x-on:click="editing = true">{{ __('Edit') }}</x-ui.button>
                    <x-ui.button type="button" variant="default" x-on:click="$dispatch('open-modal', 'create-quotation')">{{ __('Create Quotation') }}</x-ui.button>
                </div>
            </template>

            <x-ui.popover side="bottom" align="end" width="52" content-class="p-1" close-on-content-click>
                <x-slot:trigger>
                    <button type="button" class="inline-flex size-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 shadow-sm hover:bg-slate-50" aria-label="{{ __('More actions') }}">⋯</button>
                </x-slot:trigger>
                <x-ui.popover.item as="button" type="button" x-on:click="editing = true">{{ __('Edit') }}</x-ui.popover.item>
                <x-ui.popover.item as="button" type="button" x-on:click="$dispatch('open-modal', 'add-lead-note')">{{ __('Add Note') }}</x-ui.popover.item>
                <x-ui.popover.item as="button" type="button" x-on:click="$dispatch('open-modal', 'create-quotation')">{{ __('Create Quotation') }}</x-ui.popover.item>
                <x-ui.popover.item as="button" type="button" x-on:click="document.getElementById('pipeline')?.scrollIntoView({ behavior: 'smooth' })">{{ __('Change Stage') }}</x-ui.popover.item>
            </x-ui.popover>
        </div>
    </div>

    <x-auth-session-status class="mb-4 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700" :status="session('status')" />

    <x-platform.panel class="mb-4" id="pipeline" :title="__('Pipeline')" compact>
        @include('platform.leads.partials.stage-pipeline', ['lead' => $lead, 'stages' => $stages])
    </x-platform.panel>

    <div class="grid gap-4 lg:grid-cols-2">
        <x-platform.panel :title="__('Lead Information')" id="lead-information">
            <div x-show="editing" x-cloak>
                <form method="POST" action="{{ route('platform.leads.update', $lead) }}" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <div>
                        <x-input-label for="company_name" :value="__('Company Name')" />
                        <x-text-input id="company_name" name="company_name" type="text" class="mt-1 block w-full" :value="old('company_name', $lead->company_name)" required />
                        <x-input-error class="mt-2" :messages="$errors->get('company_name')" />
                    </div>
                    <div>
                        <x-input-label for="contact_person" :value="__('Contact Person')" />
                        <x-text-input id="contact_person" name="contact_person" type="text" class="mt-1 block w-full" :value="old('contact_person', $lead->contact_person)" required />
                        <x-input-error class="mt-2" :messages="$errors->get('contact_person')" />
                    </div>
                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $lead->email)" required />
                        <x-input-error class="mt-2" :messages="$errors->get('email')" />
                    </div>
                    <div>
                        <x-input-label for="phone" :value="__('Phone')" />
                        <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $lead->phone)" required />
                        <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                    </div>
                    <div>
                        <x-input-label for="location" :value="__('Location')" />
                        <x-text-input id="location" name="location" type="text" class="mt-1 block w-full" :value="old('location', $lead->location)" />
                    </div>
                    <div>
                        <x-input-label for="source" :value="__('Source')" />
                        <select id="source" name="source" class="mt-1 block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-navy focus:ring-navy">
                            @foreach ($sources as $source)
                                <option value="{{ $source->value }}" @selected(old('source', $lead->source->value) === $source->value)>{{ $source->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="owner_id" :value="__('Owner')" />
                        <select id="owner_id" name="owner_id" class="mt-1 block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-navy focus:ring-navy">
                            <option value="">{{ __('Unassigned') }}</option>
                            @foreach ($owners as $owner)
                                <option value="{{ $owner['id'] }}" @selected((int) old('owner_id', $lead->owner_id) === $owner['id'])>{{ $owner['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="stage" :value="__('Stage')" />
                        <select id="stage" name="stage" class="mt-1 block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-navy focus:ring-navy">
                            @foreach ($stages as $stage)
                                <option value="{{ $stage->value }}" @selected(old('stage', $lead->stage->value) === $stage->value)>{{ $stage->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <x-ui.button type="submit" variant="default">{{ __('Save Changes') }}</x-ui.button>
                        <x-ui.button type="button" variant="outline" x-on:click="editing = false">{{ __('Cancel') }}</x-ui.button>
                    </div>
                </form>
            </div>
            <div x-show="! editing">
                <dl class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Company Information') }}</dt>
                    </div>
                    <div>
                        <dt class="text-sm text-slate-500">{{ __('Company Name') }}</dt>
                        <dd class="mt-1 font-medium text-black">{{ $lead->company_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-slate-500">{{ __('Location') }}</dt>
                        <dd class="mt-1 font-medium text-black">{{ $lead->location ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-slate-500">{{ __('Source') }}</dt>
                        <dd class="mt-1 font-medium text-black">{{ $lead->source->label() }}</dd>
                    </div>
                    <div class="sm:col-span-2 mt-2">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Contact Information') }}</dt>
                    </div>
                    <div>
                        <dt class="text-sm text-slate-500">{{ __('Contact Person') }}</dt>
                        <dd class="mt-1 font-medium text-black">{{ $lead->contact_person }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-slate-500">{{ __('Email') }}</dt>
                        <dd class="mt-1 font-medium text-black"><a href="mailto:{{ $lead->email }}" class="text-navy hover:underline">{{ $lead->email }}</a></dd>
                    </div>
                    <div>
                        <dt class="text-sm text-slate-500">{{ __('Phone') }}</dt>
                        <dd class="mt-1 font-medium text-black">{{ $lead->phone }}</dd>
                    </div>
                    <div class="sm:col-span-2 mt-2">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Lead Information') }}</dt>
                    </div>
                    <div>
                        <dt class="text-sm text-slate-500">{{ __('Owner') }}</dt>
                        <dd class="mt-1 font-medium text-black">{{ $lead->owner?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-slate-500">{{ __('Current Stage') }}</dt>
                        <dd class="mt-1 font-medium text-black">{{ $lead->stage->label() }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-slate-500">{{ __('Created Date') }}</dt>
                        <dd class="mt-1 font-medium text-black">{{ $lead->created_at?->timezone(config('app.timezone'))->format('d M Y') ?? '—' }}</dd>
                    </div>
                </dl>
            </div>
        </x-platform.panel>

        <x-platform.panel :title="__('Next Action')">
            <form method="POST" action="{{ route('platform.leads.next-action.update', $lead) }}" class="space-y-3">
                @csrf
                @method('PATCH')
                <div>
                    <x-input-label for="next_action_label" :value="__('Next Action')" />
                    <x-text-input id="next_action_label" name="next_action_label" type="text" class="mt-1 block w-full" :value="old('next_action_label', $lead->next_action_label)" required />
                    <x-input-error class="mt-2" :messages="$errors->get('next_action_label')" />
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <x-input-label for="next_action_at" :value="__('Date')" />
                        <x-text-input id="next_action_at" name="next_action_at" type="date" class="mt-1 block w-full" :value="old('next_action_at', $lead->next_action_at?->timezone(config('app.timezone'))->toDateString())" />
                    </div>
                    <div>
                        <x-input-label for="next_action_time" :value="__('Time')" />
                        <x-text-input id="next_action_time" name="next_action_time" type="time" class="mt-1 block w-full" :value="old('next_action_time', $lead->next_action_at?->timezone(config('app.timezone'))->format('H:i'))" />
                    </div>
                </div>
                <x-ui.button type="submit" variant="outline" size="sm">{{ __('Update Next Action') }}</x-ui.button>
            </form>
        </x-platform.panel>
    </div>

    @if ($lead->quotations->isNotEmpty())
        <x-platform.panel class="mt-4" :title="__('Quotation')">
            @foreach ($lead->quotations as $quotation)
                <div @class(['flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between', 'mt-4 border-t border-slate-100 pt-4' => ! $loop->first])>
                    <div>
                        <p class="font-semibold text-black">#{{ $quotation->number }} · {{ $quotation->plan?->name ?? '—' }}</p>
                        <p class="mt-1 text-sm text-slate-600">{{ $quotation->amountLabel() }} · <x-platform.status-badge :status="$quotation->status" /> · {{ $quotation->sent_at?->timezone(config('app.timezone'))->format('d M Y') ?? $quotation->created_at?->timezone(config('app.timezone'))->format('d M Y') }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <x-ui.button variant="outline" size="sm" :href="route('platform.quotations.show', $quotation)">{{ __('View Quotation') }}</x-ui.button>
                        @if ($loop->first)
                            <x-ui.button type="button" variant="outline" size="sm" x-on:click="$dispatch('open-modal', 'create-quotation')">{{ __('Create New Quotation') }}</x-ui.button>
                        @endif
                    </div>
                </div>
            @endforeach
        </x-platform.panel>
    @endif

    @if ($lead->hasLinkedAccount() && $accountOverview !== null)
        <x-platform.panel class="mt-4" :title="__('Account')">
            <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($accountOverview['account'] as $row)
                    <div>
                        <dt class="text-sm text-slate-500">{{ $row['label'] }}</dt>
                        <dd class="mt-1 font-semibold text-black">{{ $row['value'] }}</dd>
                    </div>
                @endforeach
            </dl>
            @if (count($accountOverview['usage']) > 0)
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    @foreach (array_slice($accountOverview['usage'], 0, 2) as $row)
                        <div>
                            <div class="mb-1.5 flex items-center justify-between gap-4 text-sm">
                                <span class="text-slate-500">{{ $row['label'] }}</span>
                                <span class="font-medium tabular-nums text-black">{{ $row['used_label'] }}@if ($row['limit_label']) / {{ $row['limit_label'] }}@endif</span>
                            </div>
                            <x-platform.usage-bar :percent="$row['percent']" />
                        </div>
                    @endforeach
                </div>
            @endif
            <div class="mt-4 flex flex-wrap gap-2">
                @if ($lead->tenant)
                    <x-ui.button variant="outline" size="sm" :href="route('tenant.login', ['tenant' => $lead->tenant->id])" target="_blank">{{ __('Access Workspace') }}</x-ui.button>
                    <x-ui.button variant="outline" size="sm" :href="route('tenants.show', $lead->tenant)">{{ __('Manage Account') }}</x-ui.button>
                    <x-ui.button variant="outline" size="sm" :href="route('tenants.subscription', $lead->tenant)">{{ __('Manage Subscription') }}</x-ui.button>
                    <x-ui.button variant="outline" size="sm" :href="route('tenants.users', $lead->tenant)">{{ __('Users') }}</x-ui.button>
                    <x-ui.button variant="outline" size="sm" :href="route('tenants.usage', $lead->tenant)">{{ __('Usage') }}</x-ui.button>
                @endif
            </div>
        </x-platform.panel>
    @endif

    @if ($retentionSnapshot !== null)
        <x-platform.panel class="mt-4" :title="__('Retention')">
            <dl class="grid gap-4 sm:grid-cols-3">
                <div>
                    <dt class="text-sm text-slate-500">{{ __('Last login') }}</dt>
                    <dd class="mt-1 font-semibold text-black">{{ $retentionSnapshot['last_login'] }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-slate-500">{{ __('Subscription renews') }}</dt>
                    <dd class="mt-1 font-semibold text-black">{{ $retentionSnapshot['renewal'] }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-slate-500">{{ __('Health') }}</dt>
                    <dd class="mt-1 font-semibold text-black">{{ $retentionSnapshot['health'] }}</dd>
                </div>
            </dl>
        </x-platform.panel>
    @endif

    <div class="mt-4 grid gap-4 lg:grid-cols-2">
        <x-platform.panel :title="__('Notes')">
            <div class="mb-4">
                <x-ui.button type="button" variant="outline" size="sm" x-on:click="$dispatch('open-modal', 'add-lead-note')">{{ __('+ Add Note') }}</x-ui.button>
            </div>
            @if ($lead->notes->isEmpty())
                <p class="text-sm text-slate-500">{{ __('No notes yet.') }}</p>
            @else
                <div class="space-y-3">
                    @foreach ($lead->notes as $note)
                        <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                            <p class="text-sm text-black">{{ $note->body }}</p>
                            <p class="mt-2 text-xs text-slate-500">{{ $note->user?->name ?? __('System') }} · {{ $note->created_at?->timezone(config('app.timezone'))->format('d M Y g:i A') }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-platform.panel>

        <x-platform.panel :title="__('Activity')">
            @if (count($activityGroups) === 0)
                <p class="text-sm text-slate-500">{{ __('No activity yet.') }}</p>
            @else
                <div class="space-y-5">
                    @foreach ($activityGroups as $group)
                        <div>
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $group['label'] }}</p>
                            <ul class="space-y-3">
                                @foreach ($group['items'] as $activity)
                                    <li class="flex gap-3 text-sm">
                                        <span class="w-16 shrink-0 tabular-nums text-slate-400">{{ $activity->created_at?->timezone(config('app.timezone'))->format('g:i A') }}</span>
                                        <span class="text-black">{{ $activity->description }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-platform.panel>
    </div>

    @push('modals')
        <x-modal name="add-lead-note" maxWidth="md" :show="$openNoteModal" focusable>
            <form method="POST" action="{{ route('platform.leads.notes.store', $lead) }}">
                @csrf
                <input type="hidden" name="_lead_note_id" value="{{ $lead->id }}">
                <x-ui.modal.header :title="__('Add Note')" modal-name="add-lead-note" />
                <x-ui.modal.body>
                    <x-ui.modal.field-label for="note_body" :value="__('Note')" required />
                    <textarea id="note_body" name="body" rows="4" class="mt-1 block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-navy focus:ring-navy" required>{{ old('body') }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('body')" />
                </x-ui.modal.body>
                <x-ui.modal.footer>
                    <x-ui.modal.cancel-button modal-name="add-lead-note" />
                    <x-ui.modal.submit-button>{{ __('Add Note') }}</x-ui.modal.submit-button>
                </x-ui.modal.footer>
            </form>
        </x-modal>
    @endpush

    @include('platform.quotations.partials.create-wizard-modal', [
        'openQuotationModal' => $openQuotationModal,
        'selectedLeadId' => $lead->id,
        'selectedLead' => [
            'id' => $lead->id,
            'company_name' => $lead->company_name,
            'contact_person' => $lead->contact_person,
            'email' => $lead->email,
            'phone' => $lead->phone,
            'stage' => $lead->stage->label(),
        ],
        'leadSearchOptions' => $leadSearchOptions ?? [],
    ])
    </div>
</x-app-layout>
