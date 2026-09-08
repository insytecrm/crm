<div class="w-full overflow-hidden rounded-xl border border-dashed border-slate-300 bg-white">
    <button
        type="button"
        class="w-full px-3 py-2.5 text-left hover:bg-slate-50/70"
        @click="openTriggerPicker()"
    >
        @include('tenant.automations.partials.workflow-step-badge', ['label' => __('Trigger')])
        <p class="mt-1.5 text-sm leading-5">
            <span class="font-semibold text-black">1.</span>
            <span class="text-slate-500" x-text="triggerLabel() || @js(__('Select the event that starts this workflow'))"></span>
        </p>
    </button>
</div>
