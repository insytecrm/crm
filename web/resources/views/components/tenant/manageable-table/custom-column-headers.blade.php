<template x-for="column in customColumns" :key="column.key">
    <th
        x-show="isCustomColumnVisible(column.key)"
        class="whitespace-nowrap px-4 py-3 text-start align-middle text-xs font-semibold uppercase tracking-wider text-slate-500"
        x-text="column.label"
    ></th>
</template>
