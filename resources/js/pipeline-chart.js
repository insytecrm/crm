export function registerPipelineChart(Alpine) {
    Alpine.data('pipelineChart', (config) => ({
        endpoint: config.endpoint,
        period: config.period,
        periodLabel: config.periodLabel,
        from: config.from ?? '',
        to: config.to ?? '',
        stages: config.stages ?? [],
        scaleMax: config.scaleMax || 1,
        ticks: config.ticks ?? [0, 1],
        periodOptions: config.periodOptions ?? [],
        customValue: config.customValue,
        open: false,
        customOpen: false,
        loading: false,

        barWidth(count) {
            if (! this.scaleMax || ! count) {
                return 0;
            }

            return Math.max((count / this.scaleMax) * 100, 2);
        },

        openCustom() {
            this.open = false;
            this.customOpen = true;
        },

        selectPeriod(option) {
            if (option.is_custom) {
                this.openCustom();

                return;
            }

            this.open = false;
            this.fetchPipeline({ period: option.value });
        },

        applyCustomRange() {
            if (! this.from || ! this.to) {
                return;
            }

            this.customOpen = false;
            this.fetchPipeline({
                period: this.customValue,
                from: this.from,
                to: this.to,
            });
        },

        async fetchPipeline(params) {
            this.loading = true;

            try {
                const url = new URL(this.endpoint, window.location.origin);

                Object.entries(params).forEach(([key, value]) => {
                    if (value !== null && value !== undefined && value !== '') {
                        url.searchParams.set(key, String(value));
                    }
                });

                const response = await fetch(url.toString(), {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                if (! response.ok) {
                    throw new Error('Failed to load pipeline');
                }

                const data = await response.json();

                this.period = data.period;
                this.periodLabel = data.period_label;
                this.from = data.from ?? '';
                this.to = data.to ?? '';
                this.stages = data.pipeline.stages ?? [];
                this.scaleMax = data.scale_max || 1;
                this.ticks = data.ticks ?? [0, 1];

                const pageUrl = new URL(window.location.href);
                pageUrl.searchParams.set('period', data.period);

                if (data.period === this.customValue) {
                    if (data.from) {
                        pageUrl.searchParams.set('from', data.from);
                    }
                    if (data.to) {
                        pageUrl.searchParams.set('to', data.to);
                    }
                } else {
                    pageUrl.searchParams.delete('from');
                    pageUrl.searchParams.delete('to');
                }

                window.history.replaceState({}, '', pageUrl);
            } catch (error) {
                console.error(error);
            } finally {
                this.loading = false;
            }
        },
    }));
}
