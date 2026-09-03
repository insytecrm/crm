@props(['recordId'])

<template x-for="column in customColumns" :key="column.key">
    <td
        x-show="isCustomColumnVisible(column.key)"
        class="whitespace-nowrap px-4 py-3 align-middle text-sm text-slate-600"
        x-text="customValue({{ (int) $recordId }}, column.key)"
    ></td>
</template>
