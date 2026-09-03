@props(['id'])

<td class="w-10 whitespace-nowrap px-4 py-3 align-middle" @click.stop>
    <input
        type="checkbox"
        class="rounded border-slate-300 text-black focus:ring-navy"
        :checked="selected.includes({{ (int) $id }})"
        @change="toggle({{ (int) $id }})"
        aria-label="{{ __('Select row') }}"
    >
</td>
