@props([
    'dataTableKey',
    'dataTableColumnLabels' => [],
    'dataTableRequiredColumns' => [],
])

<x-modal :name="'edit-columns-'.$dataTableKey" maxWidth="lg">
    @php
        $storeName = 'tablePreferences_'.$dataTableKey;
    @endphp

    <div x-init="$store.{{ $storeName }}.ensureLoaded()">
        <x-ui.modal.header
            :title="__('Edit Columns')"
            :description="__('Choose which columns appear in this table, or add custom columns.')"
            :modal-name="'edit-columns-'.$dataTableKey"
        >
            <x-slot:icon>
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 4.5v15m6-15v15m-10.5-9h15m-15 6h15" />
                </svg>
            </x-slot:icon>
        </x-ui.modal.header>

        <x-ui.modal.body>
            <div class="grid gap-6 lg:grid-cols-2">
                <x-ui.modal.section :title="__('Columns')">
                    <div class="space-y-2">
                        @foreach ($dataTableColumnLabels as $key => $label)
                            <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-slate-100 px-3 py-2 hover:bg-slate-50">
                                <input
                                    type="checkbox"
                                    class="rounded border-slate-300 text-black focus:ring-navy"
                                    x-model="$store.{{ $storeName }}.columns.{{ $key }}"
                                    @change="$store.{{ $storeName }}.persistPreferences()"
                                    @if (in_array($key, $dataTableRequiredColumns, true)) disabled @endif
                                >
                                <span class="text-sm font-medium text-black">{{ $label }}</span>
                                @if (in_array($key, $dataTableRequiredColumns, true))
                                    <span class="ms-auto text-xs text-slate-400">{{ __('Required') }}</span>
                                @endif
                            </label>
                        @endforeach

                        <template x-for="column in $store.{{ $storeName }}.customColumns" :key="column.key">
                            <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-dashed border-slate-200 px-3 py-2 hover:bg-slate-50">
                                <input
                                    type="checkbox"
                                    class="rounded border-slate-300 text-black focus:ring-navy"
                                    x-model="column.visible"
                                    @change="$store.{{ $storeName }}.persistPreferences()"
                                >
                                <span class="text-sm font-medium text-black" x-text="column.label"></span>
                                <span class="text-xs text-slate-400" x-text="column.type === 'select' ? __('Select') : __('Text')"></span>
                                <button
                                    type="button"
                                    class="ms-auto text-xs font-semibold text-rose-600 hover:underline"
                                    @click="$store.{{ $storeName }}.removeCustomColumn(column.key)"
                                >
                                    {{ __('Remove') }}
                                </button>
                            </label>
                        </template>
                    </div>
                </x-ui.modal.section>

                <x-ui.modal.section :title="__('Add Custom Column')">
                    <div class="space-y-3">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Column label') }}</label>
                            <input
                                type="text"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-black focus:border-navy focus:ring-navy"
                                x-model="$store.{{ $storeName }}.newColumnLabel"
                                placeholder="{{ __('e.g. Internal notes') }}"
                            >
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Field type') }}</label>
                            @php
                                $columnTypeOptions = [
                                    ['value' => 'text', 'label' => __('Text')],
                                    ['value' => 'select', 'label' => __('Select list')],
                                ];
                            @endphp
                            <x-ui.form-select
                                name="new_column_type_{{ $dataTableKey }}"
                                :options="$columnTypeOptions"
                                value="text"
                                :placeholder="__('Select field type')"
                                x-model="$store.{{ $storeName }}.newColumnType"
                            />
                        </div>
                        <div x-show="$store.{{ $storeName }}.newColumnType === 'select'" x-cloak>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Options (comma-separated)') }}</label>
                            <input
                                type="text"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-black focus:border-navy focus:ring-navy"
                                x-model="$store.{{ $storeName }}.newColumnOptions"
                                placeholder="{{ __('Hot, Warm, Cold') }}"
                            >
                        </div>
                        <x-ui.button
                            type="button"
                            variant="outline"
                            class="!rounded-xl"
                            @click="$store.{{ $storeName }}.addCustomColumn()"
                        >
                            {{ __('Add column') }}
                        </x-ui.button>

                        <button
                            type="button"
                            class="text-sm font-semibold text-black hover:underline"
                            @click="$store.{{ $storeName }}.resetPreferences()"
                        >
                            {{ __('Reset to defaults') }}
                        </button>
                    </div>
                </x-ui.modal.section>
            </div>
        </x-ui.modal.body>

        <x-ui.modal.footer>
            <x-ui.button type="button" variant="default" class="!rounded-xl !px-4 !py-2.5" @click="$dispatch('close-modal', 'edit-columns-{{ $dataTableKey }}')">
                {{ __('Done') }}
            </x-ui.button>
        </x-ui.modal.footer>
    </div>
</x-modal>
