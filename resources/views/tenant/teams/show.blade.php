<x-tenant-layout :title="$team->name . ' | ' . __('Teams') . ' | InSyte CRM'">
    <div
        @if ($openEditTeam)
            x-data
            x-init="$nextTick(() => $dispatch('open-modal', 'edit-team-{{ $team->id }}'))"
        @endif
    >
    <div class="mb-4">
        <a href="{{ route('tenant.teams.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-slate-500 transition hover:text-black">
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
            </svg>
            {{ __('Back to Teams') }}
        </a>
    </div>

    <x-auth-session-status class="mb-3 rounded-lg bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700" :status="session('status')" />

    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <h1 class="text-2xl font-bold tracking-tight text-black">{{ $team->name }}</h1>
                @if ($team->isActive())
                    <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">{{ __('Active') }}</span>
                @else
                    <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-700">{{ __('Inactive') }}</span>
                @endif
            </div>
            @if ($team->description)
                <p class="mt-1 max-w-3xl text-sm text-slate-500">{{ $team->description }}</p>
            @endif
        </div>

        @if ($canManage)
            <div class="flex flex-wrap items-center gap-2">
                <x-ui.button type="button" variant="outline" @click="$dispatch('open-modal', 'edit-team-{{ $team->id }}')">
                    {{ __('Edit Team') }}
                </x-ui.button>
                @include('tenant.teams.partials.team-manage-actions', ['team' => $team])
            </div>
        @endif
    </div>

    <div class="grid gap-4 lg:grid-cols-3">
        <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm lg:col-span-1">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('Team Details') }}</h2>
            <dl class="mt-4 space-y-4">
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ __('Manager') }}</dt>
                    <dd class="mt-1 text-sm font-medium text-black">{{ $team->manager?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ __('Members') }}</dt>
                    <dd class="mt-1 text-sm font-medium text-black">{{ $team->members->count() }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ __('Created') }}</dt>
                    <dd class="mt-1 text-sm text-slate-600">{{ $team->created_at?->format('M j, Y') ?? '—' }}</dd>
                </div>
                @if ($team->createdBy)
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ __('Created By') }}</dt>
                        <dd class="mt-1 text-sm text-slate-600">{{ $team->createdBy->name }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        <div class="rounded-2xl border border-slate-100 bg-white shadow-sm lg:col-span-2">
            <div class="flex flex-col gap-3 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-black">{{ __('Team Members') }}</h2>
                    <p class="mt-0.5 text-sm text-slate-500">{{ __('Users assigned to this sales team') }}</p>
                </div>

                @if ($canManage && $memberCandidates->isNotEmpty())
                    <x-ui.button type="button" variant="default" @click="$dispatch('open-modal', 'add-team-member')">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        {{ __('Add Member') }}
                    </x-ui.button>
                @endif
            </div>

            <div class="overflow-hidden">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Name') }}</th>
                            <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Email') }}</th>
                            <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Role') }}</th>
                            @if ($canManage)
                                <th class="px-4 py-3 text-end text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Actions') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($team->members as $member)
                            <tr class="align-middle transition hover:bg-slate-50/60">
                                <td class="whitespace-nowrap px-4 py-3 align-middle text-sm font-medium text-black">{{ $member->name }}</td>
                                <td class="whitespace-nowrap px-4 py-3 align-middle text-sm text-slate-600">{{ $member->email }}</td>
                                <td class="whitespace-nowrap px-4 py-3 align-middle text-sm text-slate-600">{{ $member->role?->name ?? '—' }}</td>
                                @if ($canManage)
                                    <td class="whitespace-nowrap px-4 py-3 align-middle text-end">
                                        <form
                                            method="POST"
                                            action="{{ route('tenant.teams.members.destroy', [$team, $member]) }}"
                                            onsubmit="return confirm(@js(__('Remove this member from the team?')))"
                                            class="inline"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                title="{{ __('Remove') }}"
                                                aria-label="{{ __('Remove') }}"
                                                class="inline-flex size-8 items-center justify-center rounded-lg bg-rose-100 text-rose-600 shadow-sm ring-1 ring-rose-600/15 transition hover:bg-rose-200"
                                            >
                                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $canManage ? 4 : 3 }}" class="px-4 py-10 text-center text-sm text-slate-500">{{ __('No members yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if ($canManage)
        @push('modals')
            @include('tenant.teams.partials.team-form-modal', [
                'modalName' => 'edit-team-'.$team->id,
                'title' => __('Edit Team'),
                'action' => route('tenant.teams.update', $team),
                'method' => 'PATCH',
                'team' => $team,
                'managers' => $managers,
            ])

            <x-modal name="add-team-member" maxWidth="xl">
                <x-ui.modal.header
                    :title="__('Add Member')"
                    :description="__('Add a salesperson to this team')"
                    modal-name="add-team-member"
                >
                    <x-slot:icon>
                        <svg class="size-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                        </svg>
                    </x-slot:icon>
                </x-ui.modal.header>

                <form method="POST" action="{{ route('tenant.teams.members.store', $team) }}">
                    @csrf

                    <x-ui.modal.body>
                        <x-ui.modal.section :title="__('Member')">
                            <div>
                                <x-ui.modal.field-label for="add_team_member_user_id" :value="__('User')" required />
                                <x-ui.combobox
                                    id="add_team_member_user_id"
                                    name="user_id"
                                    :options="collect($memberCandidates)->map(fn ($user) => ['value' => (string) $user->id, 'label' => $user->name . ' (' . $user->email . ')'])->prepend(['value' => '', 'label' => __('Select a user')])->all()"
                                    :value="old('user_id', '')"
                                    :placeholder="__('Select a user')"
                                />
                                <x-input-error class="mt-1" :messages="$errors->get('user_id')" />
                            </div>
                        </x-ui.modal.section>
                    </x-ui.modal.body>

                    <x-ui.modal.footer>
                        <x-ui.modal.cancel-button modal-name="add-team-member" />
                        <x-ui.modal.submit-button>{{ __('Add Member') }}</x-ui.modal.submit-button>
                    </x-ui.modal.footer>
                </form>
            </x-modal>
        @endpush
    @endif
    </div>
</x-tenant-layout>
