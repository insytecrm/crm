<div class="w-full overflow-hidden rounded-xl border border-dashed border-slate-300 bg-white" x-show="conditions.length > 0" x-cloak>
    <button
        type="button"
        class="w-full px-3 py-2.5 text-left hover:bg-slate-50/70"
        @click="expanded = expanded === 'filter' ? null : 'filter'"
    >
        <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
                @include('tenant.automations.partials.workflow-step-badge', ['label' => __('Filter')])
                <p class="mt-1.5 text-sm leading-5">
                    <span class="font-semibold text-black">2.</span>
                    <span class="text-slate-500" x-text="filterSummary()"></span>
                </p>
            </div>
            <button
                type="button"
                class="shrink-0 text-xs font-medium text-rose-600 hover:text-rose-700"
                @click.stop="clearFilter()"
            >
                {{ __('Remove') }}
            </button>
        </div>
    </button>

    <div
        x-show="expanded === 'filter'"
        x-cloak
        class="space-y-2 border-t border-dashed border-slate-200 px-3 py-2.5"
    >
        <p class="text-xs text-slate-500">{{ __('All conditions must match.') }}</p>
        <template x-for="(condition, index) in conditions" :key="index">
            <div class="grid grid-cols-1 gap-1.5 sm:grid-cols-3">
                <select
                    class="h-8 w-full rounded-md border border-slate-200 bg-white px-2 text-xs text-black shadow-sm focus:border-navy focus:ring-navy"
                    :name="`conditions[${index}][field]`"
                    x-model="condition.field"
                    @change="syncConditionValue(condition)"
                >
                    @foreach ($editorConfig['conditionFields'] as $field)
                        <option value="{{ $field['value'] }}">{{ $field['label'] }}</option>
                    @endforeach
                </select>
                <select
                    class="h-8 w-full rounded-md border border-slate-200 bg-white px-2 text-xs text-black shadow-sm focus:border-navy focus:ring-navy"
                    :name="`conditions[${index}][operator]`"
                    x-model="condition.operator"
                    @change="syncConditionValue(condition)"
                >
                    @foreach ($editorConfig['conditionOperators'] as $operator)
                        <option value="{{ $operator['value'] }}">{{ $operator['label'] }}</option>
                    @endforeach
                </select>
                <div class="flex gap-1.5">
                    <select
                        x-show="showsValueSelect(condition)"
                        class="h-8 min-w-0 flex-1 rounded-md border border-slate-200 bg-white px-2 text-xs text-black shadow-sm focus:border-navy focus:ring-navy"
                        :name="showsValueSelect(condition) ? `conditions[${index}][value]` : ''"
                        x-model="condition.value"
                    >
                        <template x-for="option in conditionValueOptions(condition)" :key="option.value + ':' + option.label">
                            <option :value="option.value" x-text="option.label"></option>
                        </template>
                    </select>
                    <input
                        x-show="showsValueText(condition)"
                        type="text"
                        class="h-8 min-w-0 flex-1 rounded-md border border-slate-200 bg-white px-2 text-xs text-black shadow-sm focus:border-navy focus:ring-navy"
                        :name="showsValueText(condition) ? `conditions[${index}][value]` : ''"
                        x-model="condition.value"
                        placeholder="{{ __('Value') }}"
                    >
                    <input
                        x-show="condition.operator === 'is_empty'"
                        type="hidden"
                        :name="condition.operator === 'is_empty' ? `conditions[${index}][value]` : ''"
                        value=""
                    >
                    <button
                        type="button"
                        class="inline-flex size-8 shrink-0 items-center justify-center rounded-md text-slate-400 hover:bg-slate-50 hover:text-rose-600"
                        @click="removeCondition(index)"
                        aria-label="{{ __('Remove condition') }}"
                    >
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </template>
        <x-ui.button type="button" variant="outline" size="sm" @click="addCondition()">
            {{ __('Add condition') }}
        </x-ui.button>
    </div>
</div>
