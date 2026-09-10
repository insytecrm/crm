export function registerActivityDueNotifications(Alpine) {
    Alpine.data('activityDueNotifications', (config) => ({
        open: false,
        loading: false,
        unreadCount: 0,
        notifications: [],
        activePopup: null,
        popupQueue: [],
        upcomingTimers: {},
        pollTimer: null,
        indexUrl: config.indexUrl ?? '',
        dismissUrl: config.dismissUrl ?? '',
        readAllUrl: config.readAllUrl ?? '',
        readUrlTemplate: config.readUrlTemplate ?? '',
        callStoreUrlTemplate: config.callStoreUrlTemplate ?? '',
        csrfToken: config.csrfToken ?? '',
        pollMs: config.pollMs ?? 30000,

        init() {
            void this.refresh();
            this.pollTimer = window.setInterval(() => {
                void this.refresh();
            }, this.pollMs);

            document.addEventListener('visibilitychange', () => {
                if (document.visibilityState === 'visible') {
                    void this.refresh();
                }
            });
        },

        destroy() {
            if (this.pollTimer) {
                window.clearInterval(this.pollTimer);
            }

            Object.values(this.upcomingTimers).forEach((timerId) => window.clearTimeout(timerId));
        },

        itemKey(item) {
            return `${item.subject_type}:${item.subject_id}`;
        },

        async refresh(force = false) {
            if (! this.indexUrl || (this.loading && ! force)) {
                return;
            }

            this.loading = true;

            try {
                const response = await fetch(this.indexUrl, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                if (! response.ok) {
                    return;
                }

                const payload = await response.json();
                this.notifications = payload.notifications ?? [];
                this.unreadCount = payload.unread_count ?? 0;
                this.enqueuePopups(payload.popups ?? []);
                this.scheduleUpcoming(payload.upcoming ?? [], payload.server_now);
            } finally {
                this.loading = false;
            }
        },

        enqueuePopups(popups) {
            const activeKey = this.activePopup ? this.itemKey(this.activePopup) : null;
            const queuedKeys = new Set(this.popupQueue.map((item) => this.itemKey(item)));

            popups.forEach((popup) => {
                const key = this.itemKey(popup);

                if (key === activeKey || queuedKeys.has(key)) {
                    return;
                }

                this.popupQueue.push(popup);
                queuedKeys.add(key);
            });

            if (! this.activePopup) {
                this.showNextPopup();
            }
        },

        scheduleUpcoming(upcoming, serverNowIso) {
            const serverNow = serverNowIso ? Date.parse(serverNowIso) : Date.now();
            const clientNow = Date.now();
            const skew = Number.isFinite(serverNow) ? serverNow - clientNow : 0;

            Object.values(this.upcomingTimers).forEach((timerId) => window.clearTimeout(timerId));
            this.upcomingTimers = {};

            upcoming.forEach((item) => {
                const key = this.itemKey(item);
                const dueAt = Date.parse(item.due_at);

                if (! Number.isFinite(dueAt)) {
                    return;
                }

                const delay = Math.max(0, dueAt - skew - Date.now());

                this.upcomingTimers[key] = window.setTimeout(() => {
                    this.enqueuePopups([{ ...item, is_due: true }]);
                    void this.refresh();
                }, Math.min(delay, 2147483647));
            });
        },

        showNextPopup() {
            if (this.activePopup || this.popupQueue.length === 0) {
                return;
            }

            this.activePopup = this.popupQueue.shift();
        },

        async dismissActivePopup() {
            const popup = this.activePopup;

            if (! popup) {
                return;
            }

            this.activePopup = null;

            try {
                await fetch(this.dismissUrl, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        subject_type: popup.subject_type,
                        subject_id: popup.subject_id,
                    }),
                });
            } finally {
                await this.refresh(true);
                this.showNextPopup();
            }
        },

        async markAllRead() {
            await fetch(this.readAllUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            await this.refresh();
        },

        async openNotification(notification) {
            if (notification.id && this.readUrlTemplate) {
                await fetch(this.readUrlTemplate.replace('__ID__', notification.id), {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });
            }

            this.open = false;

            if (notification.lead_id) {
                window.dispatchEvent(new CustomEvent('open-lead', { detail: notification.lead_id }));
            }

            await this.refresh();
        },

        openLeadFromPopup() {
            const leadId = this.activePopup?.lead?.id;

            if (! leadId) {
                return;
            }

            window.dispatchEvent(new CustomEvent('open-lead', { detail: leadId }));
        },

        openWhatsAppFromPopup() {
            const leadId = this.activePopup?.lead?.id;

            if (! leadId) {
                return;
            }

            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'send-whatsapp' }));
            window.dispatchEvent(new CustomEvent('prepare-whatsapp', { detail: leadId }));
        },

        callFromPopup() {
            const popup = this.activePopup;
            const leadId = popup?.lead?.id;
            const callUrl = popup?.lead?.call_url;

            if (! leadId || ! callUrl || ! this.callStoreUrlTemplate) {
                return;
            }

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = this.callStoreUrlTemplate.replace('__ID__', String(leadId));
            form.style.display = 'none';

            const token = document.createElement('input');
            token.type = 'hidden';
            token.name = '_token';
            token.value = this.csrfToken;
            form.appendChild(token);

            const type = document.createElement('input');
            type.type = 'hidden';
            type.name = 'type';
            type.value = 'call_made';
            form.appendChild(type);

            const redirect = document.createElement('input');
            redirect.type = 'hidden';
            redirect.name = 'redirect_url';
            redirect.value = callUrl;
            form.appendChild(redirect);

            document.body.appendChild(form);
            form.submit();
        },

        formatTime(iso) {
            if (! iso) {
                return '';
            }

            const date = new Date(iso);

            if (Number.isNaN(date.getTime())) {
                return '';
            }

            return date.toLocaleString(undefined, {
                month: 'short',
                day: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
            });
        },

        kindLabel(kind) {
            return ({
                follow_up: 'Follow-up',
                site_visit: 'Site Visit',
                task: 'Task',
            })[kind] ?? 'Activity';
        },
    }));
}
