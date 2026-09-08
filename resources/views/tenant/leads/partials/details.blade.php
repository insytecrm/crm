@php($inPlace = $inPlace ?? false)

<div class="flex h-full min-h-0 flex-col overflow-hidden" x-data="{ editing: @js($errors->any()) }">
    {{-- View mode --}}
    <div x-show="!editing" class="flex h-full min-h-0 flex-col overflow-hidden">
    {{-- Header --}}
    <div class="shrink-0 border-b border-slate-100 bg-slate-50 px-4 py-4 sm:px-6 sm:py-5">
        <div class="flex items-start gap-3">
            @if ($inPlace)
                <x-ui.button type="button" variant="ghost" size="icon" class="shrink-0 text-slate-400 hover:bg-white hover:text-black lg:hidden" @click="$dispatch('close-lead-drawer')">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </x-ui.button>
            @else
                <x-ui.button type="button" variant="ghost" size="icon" :href="$closeUrl ?? route('tenant.leads.index', request()->only('search'))" class="shrink-0 text-slate-400 hover:bg-white hover:text-black lg:hidden">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </x-ui.button>
            @endif

            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                    <h2 id="lead-details-title" class="text-lg font-bold text-black sm:text-xl">{{ $lead->name }}</h2>
                    @include('tenant.leads.partials.status-select', ['lead' => $lead])
                </div>
                <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-sm text-slate-600">
                    @if ($lead->phone)
                        <span>{{ $lead->phone }}</span>
                    @endif
                    @if ($lead->email)
                        <span class="break-all">{{ $lead->email }}</span>
                    @endif
                </div>
            </div>

            <div class="shrink-0 self-start">
                @include('tenant.leads.partials.action-icons', ['lead' => $lead, 'showEdit' => true, 'inlineEdit' => true])
            </div>
        </div>
    </div>

    {{-- Tabbed body --}}
    <x-ui.tabs default="details" class="flex min-h-0 flex-1 flex-col overflow-hidden gap-0">
        <div class="shrink-0 border-b border-slate-100 bg-white px-4 py-3 sm:px-6">
            <x-ui.scroll-area orientation="horizontal" hide-scrollbar class="min-h-9">
                <x-ui.tabs.tab-list class="w-max min-w-full flex-nowrap">
                    <x-ui.tabs.tab-trigger value="details" class="shrink-0">{{ __('Details') }}</x-ui.tabs.tab-trigger>
                    <x-ui.tabs.tab-trigger value="timeline" class="shrink-0">{{ __('Activity') }}</x-ui.tabs.tab-trigger>
                    <x-ui.tabs.tab-trigger value="tasks" class="shrink-0">{{ __('Tasks') }}</x-ui.tabs.tab-trigger>
                    <x-ui.tabs.tab-trigger value="documents" class="shrink-0">{{ __('Documents') }}</x-ui.tabs.tab-trigger>
                    <x-ui.tabs.tab-trigger value="notes" class="shrink-0">{{ __('Notes') }}</x-ui.tabs.tab-trigger>
                    <x-ui.tabs.tab-trigger value="history" class="shrink-0">{{ __('History') }}</x-ui.tabs.tab-trigger>
                </x-ui.tabs.tab-list>
            </x-ui.scroll-area>
        </div>

        <div class="relative min-h-0 flex-1 overflow-hidden bg-slate-50/40">
            <x-ui.tabs.tab-content value="timeline" default="details" class="absolute inset-0">
                <x-ui.scroll-area class="h-full" fade>
                    <div class="p-4 sm:p-6">
                        <div class="rounded-xl border border-slate-100 bg-white p-4 shadow-sm shadow-slate-200/50 sm:p-5">
                    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-slate-500">{{ __('Activity Timeline') }}</h3>
                    <div class="space-y-0">
                        @forelse ($lead->activities as $activity)
                            @include('tenant.leads.partials.activity-timeline-item', [
                                'activity' => $activity,
                                'last' => $loop->last,
                                'notesById' => $lead->notes->keyBy('id'),
                            ])
                        @empty
                            <p class="text-sm text-slate-500">{{ __('No activity yet.') }}</p>
                        @endforelse
                    </div>
                        </div>
                    </div>
                </x-ui.scroll-area>
            </x-ui.tabs.tab-content>

            <x-ui.tabs.tab-content value="details" default="details" class="absolute inset-0">
                <x-ui.scroll-area class="h-full" fade>
                    <div class="p-4 sm:p-6">
                        <div class="rounded-xl border border-slate-100 bg-white p-4 shadow-sm shadow-slate-200/50 sm:p-5">
                    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-slate-500">{{ __('Lead Details') }}</h3>

                    <dl class="grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <dt class="text-xs font-medium text-slate-500">{{ __('Source') }}</dt>
                            <dd class="mt-1 text-sm font-medium text-black">{{ $lead->sourceLabel() ?? '—' }}</dd>
                        </div>
                        @if (filled($lead->subSourceDisplay()))
                            <div>
                                <dt class="text-xs font-medium text-slate-500">{{ __('Sub-source') }}</dt>
                                <dd class="mt-1 text-sm font-medium text-black">{{ $lead->subSourceDisplay() }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-xs font-medium text-slate-500">{{ __('Budget') }}</dt>
                            <dd class="mt-1">
                                @if ($lead->budget)
                                    <x-tenant.budget-badge :budget="$lead->budget" />
                                @else
                                    <span class="text-sm font-medium text-black">—</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500">{{ __('Preferred Location') }}</dt>
                            <dd class="mt-1 text-sm font-medium text-black">{{ $lead->location ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500">{{ __('Property Type') }}</dt>
                            <dd class="mt-1">
                                @if ($lead->property_type)
                                    <x-tenant.property-type-badge :property-type="$lead->property_type" />
                                @else
                                    <span class="text-sm font-medium text-black">—</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500">{{ __('Configuration') }}</dt>
                            <dd class="mt-1 text-sm font-medium text-black">{{ $lead->configuration ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500">{{ __('Assigned To') }}</dt>
                            <dd class="mt-1 text-sm font-medium text-black">{{ $lead->assignedTo?->name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500">{{ __('Lead Status') }}</dt>
                            <dd class="mt-1"><x-tenant.status-badge :status="$lead->status" :lead="$lead" /></dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500">{{ __('Lead Score') }}</dt>
                            <dd class="mt-1 text-sm font-medium text-black">{{ $lead->lead_score }}/100</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500">{{ __('Next Follow-up') }}</dt>
                            <dd class="mt-1 text-sm font-medium text-black">{{ $lead->next_follow_up_at?->format('M j, Y g:i A') ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500">{{ __('Upcoming Site Visit') }}</dt>
                            <dd class="mt-1 text-sm font-medium text-black">{{ $lead->upcoming_site_visit_at?->format('M j, Y g:i A') ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500">{{ __('Next Action') }}</dt>
                            <dd class="mt-1 text-sm font-medium text-black">{{ $lead->next_action ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500">{{ __('Created On') }}</dt>
                            <dd class="mt-1 text-sm font-medium text-black">{{ $lead->created_at->format('M j, Y g:i A') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500">{{ __('Last Activity') }}</dt>
                            <dd class="mt-1 text-sm font-medium text-black">{{ $lead->last_activity_at?->diffForHumans() ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500">{{ __('Property Interest') }}</dt>
                            <dd class="mt-1 text-sm font-medium text-black">{{ $lead->propertyInterestLabel() ?? '—' }}</dd>
                        </div>
                    </dl>
                        </div>
                    </div>
                </x-ui.scroll-area>
            </x-ui.tabs.tab-content>

            <x-ui.tabs.tab-content value="tasks" default="details" class="absolute inset-0">
                <x-ui.scroll-area class="h-full" fade>
                    <div class="p-4 sm:p-6">
                        <div class="rounded-xl border border-slate-100 bg-white p-4 shadow-sm shadow-slate-200/50 sm:p-5">
                    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-slate-500">{{ __('Tasks') }}</h3>
                    <form method="POST" action="{{ route('tenant.leads.tasks.store', $lead) }}" class="mb-4 space-y-3">
                        @csrf
                        <div class="flex flex-col gap-2 sm:flex-row">
                            <input type="text" name="title" required placeholder="{{ __('Task title') }}" class="flex-1 rounded-lg border-slate-200 text-sm focus:border-navy focus:ring-navy">
                            <x-ui.datetime-picker name="due_at" class="sm:max-w-xs sm:flex-none" />
                            <x-ui.button type="submit" variant="default" size="sm">{{ __('Add Task') }}</x-ui.button>
                        </div>
                    </form>
                    <div class="space-y-3">
                        @forelse ($lead->tasks as $task)
                            <div class="space-y-3 rounded-lg border border-slate-100 p-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-black {{ $task->isClosed() ? 'line-through text-slate-400' : '' }}">{{ $task->title }}</p>
                                        @if ($task->due_at)
                                            <p class="mt-0.5 text-xs {{ $task->isOverdue() ? 'font-medium text-rose-600' : 'text-slate-500' }}">{{ __('Due') }}: {{ $task->due_at->format('M j, Y g:i A') }}</p>
                                        @endif
                                    </div>
                                    <x-tenant.task-status-badge :task="$task" class="shrink-0" />
                                </div>
                                <x-tenant.task-status-timeline :task="$task" />
                                @include('tenant.tasks.partials.task-status-actions', [
                                    'task' => $task,
                                    'statusAction' => route('tenant.leads.tasks.status.update', [$lead, $task]),
                                    'completeAction' => route('tenant.leads.tasks.complete', [$lead, $task]),
                                ])
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">{{ __('No tasks yet.') }}</p>
                        @endforelse
                    </div>
                        </div>
                    </div>
                </x-ui.scroll-area>
            </x-ui.tabs.tab-content>

            <x-ui.tabs.tab-content value="documents" default="details" class="absolute inset-0">
                <x-ui.scroll-area class="h-full" fade>
                    <div class="p-4 sm:p-6">
                        <div class="rounded-xl border border-slate-100 bg-white p-4 shadow-sm shadow-slate-200/50 sm:p-5">
                    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-slate-500">{{ __('Documents') }}</h3>
                    <form method="POST" action="{{ route('tenant.leads.documents.store', $lead) }}" enctype="multipart/form-data" class="mb-4 flex flex-col gap-2 sm:flex-row">
                        @csrf
                        <input type="file" name="document" required class="flex-1 text-sm text-slate-600">
                        <x-ui.button type="submit" variant="default" size="sm">{{ __('Upload') }}</x-ui.button>
                    </form>
                    <div class="space-y-3">
                        @forelse ($lead->documents as $document)
                            <div class="flex items-center justify-between rounded-lg border border-slate-100 p-3">
                                <span class="text-sm text-black">{{ $document->name }}</span>
                                <div class="flex gap-3">
                                    <a href="{{ route('tenant.leads.documents.download', [$lead, $document]) }}" class="text-xs font-semibold text-black hover:underline">{{ __('Download') }}</a>
                                    <form method="POST" action="{{ route('tenant.leads.documents.destroy', [$lead, $document]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-semibold text-rose-600 hover:underline">{{ __('Delete') }}</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">{{ __('No documents yet.') }}</p>
                        @endforelse
                    </div>
                        </div>
                    </div>
                </x-ui.scroll-area>
            </x-ui.tabs.tab-content>

            <x-ui.tabs.tab-content value="notes" default="details" class="absolute inset-0">
                <x-ui.scroll-area class="h-full" fade>
                    <div class="p-4 sm:p-6">
                        <div class="rounded-xl border border-slate-100 bg-white p-4 shadow-sm shadow-slate-200/50 sm:p-5">
                    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-slate-500">{{ __('Notes') }}</h3>
                    <form method="POST" action="{{ route('tenant.leads.notes.store', $lead) }}" class="mb-4">
                        @csrf
                        <textarea name="body" rows="2" required placeholder="{{ __('Add an internal note...') }}" class="w-full rounded-lg border-slate-200 text-sm focus:border-navy focus:ring-navy"></textarea>
                        <x-ui.button type="submit" variant="default" size="sm" class="mt-2">{{ __('Add Note') }}</x-ui.button>
                    </form>
                    <div class="space-y-3">
                        @forelse ($lead->notes as $note)
                            <div class="rounded-lg border border-slate-100 p-3">
                                <p class="text-sm text-slate-700">{{ $note->body }}</p>
                                <div class="mt-2 flex items-center justify-between text-xs text-slate-400">
                                    <span>{{ $note->user?->name }} · {{ $note->created_at->diffForHumans() }}</span>
                                    @if ($note->user_id === auth()->id())
                                        <form method="POST" action="{{ route('tenant.leads.notes.destroy', [$lead, $note]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="font-semibold text-rose-600 hover:underline">{{ __('Delete') }}</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">{{ __('No notes yet.') }}</p>
                        @endforelse
                    </div>
                        </div>
                    </div>
                </x-ui.scroll-area>
            </x-ui.tabs.tab-content>

            <x-ui.tabs.tab-content value="history" default="details" class="absolute inset-0">
                <x-ui.scroll-area class="h-full" fade>
                    <div class="p-4 sm:p-6">
                        <div class="rounded-xl border border-slate-100 bg-white p-4 shadow-sm shadow-slate-200/50 sm:p-5">
                            @include('tenant.leads.partials.history-tab', ['lead' => $lead])
                        </div>
                    </div>
                </x-ui.scroll-area>
            </x-ui.tabs.tab-content>
        </div>
    </x-ui.tabs>

    {{-- Footer actions --}}
    <div class="flex shrink-0 flex-col gap-3 border-t border-slate-100 bg-slate-50 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
        @if ($inPlace)
            <x-ui.button type="button" variant="outline" class="hidden text-slate-600 lg:inline-flex" @click="$dispatch('close-lead-drawer')">{{ __('Close') }}</x-ui.button>
        @else
            <x-ui.button type="button" variant="outline" :href="$closeUrl ?? route('tenant.leads.index', request()->only('search'))" class="hidden text-slate-600 lg:inline-flex">{{ __('Close') }}</x-ui.button>
        @endif
        <div class="flex flex-wrap gap-2">
            @unless ($lead->status->isClosed())
                @unless ($lead->hasBooking())
                    <x-ui.button type="button" variant="success" class="bg-emerald-600 text-white hover:bg-emerald-700" @click="$dispatch('open-modal', 'create-booking')">{{ __('Create Booking') }}</x-ui.button>
                @endunless
                <x-ui.button type="button" variant="destructive" class="bg-red-600 text-white hover:bg-red-700" @click="$dispatch('open-modal', 'mark-lost-{{ $lead->id }}')">{{ __('Mark Lost') }}</x-ui.button>
            @endunless
        </div>
    </div>
    </div>

    {{-- Edit mode --}}
    <div x-show="editing" x-cloak class="flex h-full min-h-0 flex-col overflow-hidden">
        @include('tenant.leads.partials.edit-form', ['lead' => $lead, 'users' => $users])
    </div>
</div>
