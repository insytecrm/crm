@props(['dataTableKey'])

<x-ui.button type="button" variant="outline" @click="$dispatch('open-modal', 'edit-columns-{{ $dataTableKey }}')">
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 4.5v15m6-15v15m-10.5-9h15m-15 6h15" />
    </svg>
    {{ __('Edit Columns') }}
</x-ui.button>
