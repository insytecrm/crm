<div
    x-data="{
        activeTab: @js($default),
        indicator: { left: '0px', width: '0px' },
        moveIndicator() {
            const list = this.$refs.tabList;

            if (! list) {
                return;
            }

            const active = list.querySelector('[aria-selected=true]');

            if (! active) {
                return;
            }

            this.indicator = {
                left: `${active.offsetLeft}px`,
                width: `${active.offsetWidth}px`,
            };
        },
    }"
    x-init="$nextTick(() => moveIndicator())"
    @resize.window="$nextTick(() => moveIndicator())"
    {{ $attributes->merge(['class' => 'group/tabs flex flex-col gap-2']) }}
    data-slot="tabs"
>
    {{ $slot }}
</div>
