export function registerReminderHost(Alpine) {
    Alpine.data('reminderHost', (config) => ({
        dueUrl: config.dueUrl,
        dismissUrl: config.dismissUrl,
        pollMs: config.pollMs ?? 30000,
        queue: [],
        current: null,
        busy: false,
        dismissedKeys: new Set(),
        timer: null,

        init() {
            this.poll();
            this.timer = window.setInterval(() => this.poll(), this.pollMs);

            document.addEventListener('visibilitychange', () => {
                if (document.visibilityState === 'visible') {
                    this.poll();
                }
            });
        },

        destroy() {
            if (this.timer) {
                window.clearInterval(this.timer);
            }
        },

        async poll() {
            try {
                const response = await fetch(this.dueUrl, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    return;
                }

                const payload = await response.json();
                const reminders = Array.isArray(payload.reminders) ? payload.reminders : [];

                this.queue = reminders.filter((item) => !this.dismissedKeys.has(item.key));
                this.showNext();
            } catch {
                // Ignore transient network errors; next poll will retry.
            }
        },

        showNext() {
            if (this.current !== null || this.busy) {
                return;
            }

            this.current = this.queue.find((item) => !this.dismissedKeys.has(item.key)) ?? null;
        },

        csrfToken() {
            return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
        },

        async dismissCurrent({ redirectUrl = null } = {}) {
            if (!this.current || this.busy) {
                return;
            }

            const item = this.current;
            this.busy = true;

            try {
                const response = await fetch(this.dismissUrl, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': this.csrfToken(),
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        subject_type: item.subject_type,
                        subject_id: item.subject_id,
                    }),
                });

                if (!response.ok) {
                    return;
                }

                this.dismissedKeys.add(item.key);
                this.queue = this.queue.filter((queued) => queued.key !== item.key);
                this.current = null;

                if (redirectUrl) {
                    window.location.href = redirectUrl;

                    return;
                }

                this.showNext();
            } finally {
                this.busy = false;
            }
        },

        goToCurrent() {
            if (!this.current) {
                return;
            }

            this.dismissCurrent({ redirectUrl: this.current.url });
        },
    }));
}
