<th class="w-10 whitespace-nowrap px-4 py-3 align-middle">
    <input
        type="checkbox"
        class="rounded border-slate-300 text-black focus:ring-navy"
        :checked="allSelected"
        x-effect="$el.indeterminate = someSelected"
        @change="toggleAll()"
        aria-label="{{ __('Select all') }}"
    >
</th>
