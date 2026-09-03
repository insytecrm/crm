<x-modal name="create-task" maxWidth="2xl">
    <div class="p-6">
        <h2 class="text-lg font-bold text-black">{{ __('Create Task') }}</h2>
        <form method="POST" action="{{ route('tenant.tasks.store') }}" class="mt-4 space-y-4">
            @csrf
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <x-input-label for="task_lead_id" :value="__('Related Lead')" />
                    <x-ui.combobox
                        id="task_lead_id"
                        name="lead_id"
                        :options="collect($leads)->map(fn ($lead) => ['value' => (string) $lead->id, 'label' => $lead->name])->all()"
                        :placeholder="__('Select a lead')"
                        required
                    />
                </div>
                <div class="sm:col-span-2">
                    <x-input-label for="task_title" :value="__('Title')" />
                    <x-auth.icon-input id="task_title" name="title" required placeholder="{{ __('Task title') }}" />
                </div>
                <div class="sm:col-span-2">
                    <x-input-label for="task_description" :value="__('Description')" />
                    <textarea
                        id="task_description"
                        name="description"
                        rows="3"
                        class="mt-1 block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-navy focus:ring-navy"
                        placeholder="{{ __('Optional details') }}"
                    ></textarea>
                </div>
                <div>
                    <x-input-label for="task_due_at" :value="__('Due Date')" />
                    <x-ui.datetime-picker id="task_due_at" name="due_at" />
                </div>
                <div>
                    <x-input-label for="task_assigned_to_id" :value="__('Assigned To')" />
                    <x-ui.combobox
                        id="task_assigned_to_id"
                        name="assigned_to_id"
                        :options="collect($users)->map(fn ($user) => ['value' => (string) $user->id, 'label' => $user->name])->prepend(['value' => '', 'label' => __('Assign to me')])->all()"
                        :placeholder="__('Assign to me')"
                        :searchable="false"
                    />
                </div>
                <div class="sm:col-span-2">
                    @include('tenant.partials.reminder-fields', [
                        'idPrefix' => 'task_reminder',
                    ])
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <x-ui.button type="button" variant="outline" @click="$dispatch('close-modal', 'create-task')">{{ __('Cancel') }}</x-ui.button>
                <x-ui.button type="submit" variant="default">{{ __('Create Task') }}</x-ui.button>
            </div>
        </form>
    </div>
</x-modal>
