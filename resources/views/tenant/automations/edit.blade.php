<x-tenant-layout :title="($workflow->name ?: __('Untitled workflow')) . ' | InSyte CRM'">
    <x-slot:drawers>
        @include('tenant.automations.partials.workflow-picker-drawer')
    </x-slot:drawers>

    @include('tenant.automations.partials.workflow-editor-script')

    <div
        x-data="automationWorkflowEditor(window.automationWorkflowEditorConfig)"
        @workflow-trigger-selected.window="selectTrigger($event.detail)"
        @workflow-action-selected.window="selectAction($event.detail)"
        class="w-full"
    >
        <form
            method="POST"
            action="{{ route('tenant.automations.workflows.update', $workflow) }}"
            class="space-y-3"
        >
            @csrf
            @method('PATCH')
            <input type="hidden" name="trigger" :value="trigger">
            <input type="hidden" name="is_active" :value="isActive ? '1' : '0'">

            <div class="sticky top-0 z-10 -mx-4 -mt-2 border-b border-slate-100 bg-white/95 px-4 py-2.5 shadow-sm backdrop-blur sm:-mx-6 sm:-mt-3 sm:px-6 lg:-mx-8 lg:px-8">
                <div class="flex flex-wrap items-center gap-2">
                    <a
                        href="{{ route('tenant.automations.workflows') }}"
                        class="inline-flex size-8 shrink-0 items-center justify-center rounded-md text-slate-500 hover:bg-slate-50 hover:text-black"
                        aria-label="{{ __('Back to workflows') }}"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                        </svg>
                    </a>

                    <div class="min-w-0 flex-1">
                        <label for="workflow_name" class="sr-only">{{ __('Workflow name') }}</label>
                        <input
                            id="workflow_name"
                            type="text"
                            name="name"
                            x-model="name"
                            maxlength="255"
                            required
                            class="h-8 w-full rounded-md border border-slate-200 bg-white px-2.5 text-sm font-semibold text-black shadow-sm placeholder:font-normal placeholder:text-slate-400 focus:border-navy focus:ring-navy"
                            placeholder="{{ __('Untitled workflow') }}"
                        >
                    </div>

                    <label class="inline-flex shrink-0 cursor-pointer items-center gap-1.5">
                        <span class="text-[11px] font-semibold text-slate-500">{{ __('On') }}</span>
                        <span class="relative inline-flex h-5 w-9 shrink-0">
                            <input type="checkbox" class="peer sr-only" x-model="isActive">
                            <span class="absolute inset-0 rounded-full bg-slate-200 transition-colors peer-checked:bg-navy"></span>
                            <span class="absolute left-0.5 top-0.5 size-4 rounded-full bg-white shadow-sm transition-transform peer-checked:translate-x-4"></span>
                        </span>
                    </label>

                    <x-ui.button type="submit" variant="default" size="sm">
                        {{ __('Save') }}
                    </x-ui.button>
                </div>
                <x-input-error class="mt-1" :messages="$errors->get('name')" />
            </div>

            <div class="mx-auto max-w-lg space-y-3">
                <x-auth-session-status class="rounded-md bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-700" :status="session('status')" />
                <x-input-error :messages="$errors->get('is_active')" />
                <x-input-error :messages="$errors->get('trigger')" />
                <x-input-error :messages="$errors->get('conditions')" />
                <x-input-error :messages="$errors->get('actions')" />

                <div class="rounded-xl bg-[radial-gradient(#cbd5e1_1px,transparent_1px)] bg-[size:14px_14px] bg-slate-50 px-4 py-5">
                <div class="flex flex-col items-center">
                    @include('tenant.automations.partials.workflow-when-step')
                    @include('tenant.automations.partials.workflow-connector', ['action' => 'addFilterOrAction'])
                    @include('tenant.automations.partials.workflow-filter-step')
                    <template x-for="(action, index) in actions" :key="index">
                        <div class="flex w-full flex-col items-center">
                            <div class="w-full" x-show="index > 0 || conditions.length > 0">
                                @include('tenant.automations.partials.workflow-connector', ['action' => 'addAction'])
                            </div>
                            @include('tenant.automations.partials.workflow-action-step')
                        </div>
                    </template>
                    <div class="flex w-full flex-col items-center" x-show="actions.length === 0">
                        <div class="w-full" x-show="conditions.length > 0">
                            @include('tenant.automations.partials.workflow-connector', ['action' => 'addAction'])
                        </div>
                        <button
                            type="button"
                            class="w-full rounded-xl border border-dashed border-slate-300 bg-white px-3 py-2.5 text-left hover:border-navy/40 hover:bg-slate-50/70"
                            @click="openActionPicker()"
                        >
                            @include('tenant.automations.partials.workflow-step-badge', ['label' => __('Action')])
                            <p class="mt-1.5 text-sm leading-5">
                                <span class="font-semibold text-black" x-text="(conditions.length > 0 ? 3 : 2) + '.'"></span>
                                <span class="text-slate-500">{{ __('Select an action for this workflow') }}</span>
                            </p>
                        </button>
                    </div>
                    @include('tenant.automations.partials.workflow-connector', ['action' => 'addAction'])
                </div>
                </div>
            </div>
        </form>
    </div>
</x-tenant-layout>
