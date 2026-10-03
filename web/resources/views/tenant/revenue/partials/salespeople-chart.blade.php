@props([
    'people',
    'maxValues',
])

<div
    x-data="{
        metric: 'revenue',
        maxValues: @js($maxValues),
        people: @js($people->values()->all()),
        sortedPeople() {
            return [...this.people].sort((a, b) => {
                const valueFor = (person) => this.metric === 'bookings'
                    ? person.bookings
                    : (this.metric === 'sales_value' ? person.sales_value : person.revenue);

                return valueFor(b) - valueFor(a);
            });
        },
        barWidth(person) {
            const value = this.metric === 'bookings'
                ? person.bookings
                : (this.metric === 'sales_value' ? person.sales_value : person.revenue);

            return Math.max(4, (value / this.maxValues[this.metric]) * 100);
        },
        displayValue(person) {
            const value = this.metric === 'bookings'
                ? person.bookings
                : (this.metric === 'sales_value' ? person.sales_value : person.revenue);

            return this.metric === 'bookings'
                ? value.toLocaleString()
                : '₹' + value.toLocaleString('en-IN');
        },
        barClass() {
            if (this.metric === 'bookings') {
                return 'bg-gradient-to-r from-violet-400 to-violet-600';
            }

            if (this.metric === 'sales_value') {
                return 'bg-gradient-to-r from-sky-400 to-sky-600';
            }

            return 'bg-gradient-to-r from-emerald-400 to-emerald-600';
        },
    }"
    class="space-y-5"
>
    <div class="inline-flex rounded-xl border border-violet-100 bg-violet-50/50 p-1">
        <button
            type="button"
            @click="metric = 'revenue'"
            :class="metric === 'revenue' ? 'bg-white text-emerald-700 shadow-sm ring-1 ring-emerald-100' : 'text-slate-600 hover:text-emerald-700'"
            class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-all"
        >
            {{ __('Revenue') }}
        </button>
        <button
            type="button"
            @click="metric = 'bookings'"
            :class="metric === 'bookings' ? 'bg-white text-violet-700 shadow-sm ring-1 ring-violet-100' : 'text-slate-600 hover:text-violet-700'"
            class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-all"
        >
            {{ __('Bookings') }}
        </button>
        <button
            type="button"
            @click="metric = 'sales_value'"
            :class="metric === 'sales_value' ? 'bg-white text-sky-700 shadow-sm ring-1 ring-sky-100' : 'text-slate-600 hover:text-sky-700'"
            class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-all"
        >
            {{ __('Sales Value') }}
        </button>
    </div>

    <template x-if="people.length === 0">
        <div class="flex h-40 items-center justify-center rounded-xl border border-dashed border-violet-200 bg-violet-50/30">
            <p class="text-sm text-slate-500">{{ __('No salesperson data for the selected filters.') }}</p>
        </div>
    </template>

    <div class="space-y-3.5" x-show="people.length > 0">
        <template x-for="(person, index) in sortedPeople()" :key="person.id ?? index">
            <div class="grid grid-cols-[minmax(0,8rem)_1fr_minmax(0,5rem)] items-center gap-3 rounded-lg px-1 py-0.5 transition-colors hover:bg-violet-50/40">
                <p class="truncate text-sm font-medium text-black" x-text="person.name"></p>
                <div class="h-2.5 overflow-hidden rounded-full bg-slate-100/90">
                    <div
                        class="h-full rounded-full transition-all duration-500 ease-out"
                        :class="barClass()"
                        :style="`width: ${barWidth(person)}%`"
                    ></div>
                </div>
                <p class="text-end text-xs font-semibold tabular-nums text-slate-600" x-text="displayValue(person)"></p>
            </div>
        </template>
    </div>
</div>
