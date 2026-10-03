<div class="w-full overflow-hidden rounded-xl border border-dashed border-slate-300 bg-white">
    <button
        type="button"
        class="w-full px-3 py-2.5 text-left hover:bg-slate-50/70"
        @click="expanded = expanded === 'action-' + index ? null : 'action-' + index"
    >
        <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
                @include('tenant.automations.partials.workflow-step-badge', ['label' => __('Action')])
                <p class="mt-1.5 text-sm leading-5">
                    <span class="font-semibold text-black" x-text="actionNumber(index) + '.'"></span>
                    <span class="text-slate-500" x-text="actionLabel(action.type)"></span>
                </p>
                <p class="mt-0.5 truncate text-xs text-slate-400" x-text="actionSummary(action)"></p>
            </div>
            <button
                type="button"
                class="shrink-0 text-xs font-medium text-rose-600 hover:text-rose-700"
                @click.stop="removeAction(index)"
            >
                {{ __('Remove') }}
            </button>
        </div>
    </button>
    <input type="hidden" :name="`actions[${index}][type]`" :value="action.type">

    <div
        x-show="expanded === 'action-' + index"
        x-cloak
        class="space-y-2 border-t border-dashed border-slate-200 px-3 py-2.5"
    >
        <template x-if="action.type === 'create_task'">
            <div class="space-y-2">
                <div>
                    <label class="mb-1 block text-xs font-medium text-black">{{ __('Task title') }}</label>
                    <input
                        type="text"
                        class="h-8 w-full rounded-md border border-slate-200 bg-white px-2 text-xs text-black shadow-sm focus:border-navy focus:ring-navy"
                        :name="`actions[${index}][title]`"
                        x-model="action.title"
                        maxlength="255"
                        placeholder="{{ __('Follow up') }}"
                    >
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-black">{{ __('Description') }}</label>
                    <textarea
                        class="min-h-16 w-full rounded-md border border-slate-200 bg-white px-2 py-1.5 text-xs text-black shadow-sm focus:border-navy focus:ring-navy"
                        :name="`actions[${index}][body]`"
                        x-model="action.body"
                        maxlength="5000"
                    ></textarea>
                </div>
            </div>
        </template>

        <template x-if="action.type === 'add_note'">
            <div>
                <label class="mb-1 block text-xs font-medium text-black">{{ __('Note') }}</label>
                <textarea
                    class="min-h-16 w-full rounded-md border border-slate-200 bg-white px-2 py-1.5 text-xs text-black shadow-sm focus:border-navy focus:ring-navy"
                    :name="`actions[${index}][body]`"
                    x-model="action.body"
                    maxlength="5000"
                    placeholder="{{ __('Added by automation.') }}"
                ></textarea>
            </div>
        </template>

        <template x-if="action.type === 'change_status'">
            <div>
                <label class="mb-1 block text-xs font-medium text-black">{{ __('New status') }}</label>
                <select
                    class="h-8 w-full rounded-md border border-slate-200 bg-white px-2 text-xs text-black shadow-sm focus:border-navy focus:ring-navy"
                    :name="`actions[${index}][status]`"
                    x-model="action.status"
                >
                    @foreach ($editorConfig['leadStatuses'] as $status)
                        <option value="{{ $status['value'] }}">{{ $status['label'] }}</option>
                    @endforeach
                </select>
            </div>
        </template>

        <template x-if="action.type === 'schedule_follow_up' || action.type === 'create_site_visit' || action.type === 'reschedule_follow_up'">
            <div>
                <label class="mb-1 block text-xs font-medium text-black">{{ __('Delay (hours)') }}</label>
                <input
                    type="number"
                    min="1"
                    max="8760"
                    class="h-8 w-full rounded-md border border-slate-200 bg-white px-2 text-xs text-black shadow-sm focus:border-navy focus:ring-navy"
                    :name="`actions[${index}][delay_hours]`"
                    x-model="action.delay_hours"
                >
            </div>
        </template>

        <template x-if="action.type === 'wait'">
            <div>
                <label class="mb-1 block text-xs font-medium text-black">{{ __('Wait (minutes)') }}</label>
                <input
                    type="number"
                    min="1"
                    max="10080"
                    class="h-8 w-full rounded-md border border-slate-200 bg-white px-2 text-xs text-black shadow-sm focus:border-navy focus:ring-navy"
                    :name="`actions[${index}][delay_minutes]`"
                    x-model="action.delay_minutes"
                >
            </div>
        </template>

        <template x-if="action.type === 'notify_salesperson' || action.type === 'notify_team_leader' || action.type === 'notify_manager'">
            <div class="space-y-2">
                <div>
                    <label class="mb-1 block text-xs font-medium text-black">{{ __('Alert title') }}</label>
                    <input
                        type="text"
                        class="h-8 w-full rounded-md border border-slate-200 bg-white px-2 text-xs text-black shadow-sm focus:border-navy focus:ring-navy"
                        :name="`actions[${index}][title]`"
                        x-model="action.title"
                        maxlength="255"
                        placeholder="{{ __('Automation alert') }}"
                    >
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-black">{{ __('Message') }}</label>
                    <textarea
                        class="min-h-16 w-full rounded-md border border-slate-200 bg-white px-2 py-1.5 text-xs text-black shadow-sm focus:border-navy focus:ring-navy"
                        :name="`actions[${index}][body]`"
                        x-model="action.body"
                        maxlength="5000"
                    ></textarea>
                </div>
            </div>
        </template>

        <template x-if="action.type === 'create_site_visit'">
            <div>
                <label class="mb-1 block text-xs font-medium text-black">{{ __('Notes') }}</label>
                <textarea
                    class="min-h-16 w-full rounded-md border border-slate-200 bg-white px-2 py-1.5 text-xs text-black shadow-sm focus:border-navy focus:ring-navy"
                    :name="`actions[${index}][body]`"
                    x-model="action.body"
                    maxlength="5000"
                ></textarea>
            </div>
        </template>

        <template x-if="action.type === 'mark_priority' || action.type === 'remove_priority'">
            <p class="text-xs text-slate-500" x-text="actionDescription(action.type)"></p>
        </template>

        <p x-show="action.type === 'send_whatsapp' || action.type === 'send_email' || action.type === 'send_sms'" class="text-xs text-slate-500">
            {{ __('Messaging actions need templates and channels in a later phase.') }}
        </p>
    </div>
</div>
