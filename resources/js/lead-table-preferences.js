const defaultColumns = {
    name: true,
    phone: true,
    source: true,
    requirement: true,
    assigned_to: true,
    status: true,
    next_follow_up: true,
    follow_ups_count: false,
    site_visits_count: false,
    last_activity: true,
    property_interest: false,
    booking_date: false,
    property_booked: false,
    created_at: true,
    actions: true,
};

const defaultActions = {
    call: true,
    whatsapp: true,
    follow_up: true,
    site_visit: true,
    create_booking: true,
};

function mergePreferences(saved) {
    const columns = { ...defaultColumns, ...(saved?.columns ?? {}), name: true };
    const actions = { ...defaultActions, ...(saved?.actions ?? {}) };

    return { columns, actions };
}

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

export function registerLeadTablePreferencesStore(Alpine, config = {}) {
    const updateUrl = config.updateUrl ?? window.leadTablePreferencesConfig?.updateUrl ?? null;
    const initial = config.initial ?? window.leadTablePreferencesConfig?.initial ?? null;
    const listing = config.listing ?? window.leadTablePreferencesConfig?.listing ?? 'all';
    const listingDefaults = config.defaults ?? window.leadTablePreferencesConfig?.defaults ?? null;

    Alpine.store('leadTablePreferences', {
        listing,
        columns: { ...defaultColumns },
        actions: { ...defaultActions },
        loaded: false,
        saving: false,

        ensureLoaded() {
            if (this.loaded) {
                return;
            }

            this.loaded = true;
            this.listing = listing;
            this.applyPreferences(initial);
        },

        applyPreferences(saved) {
            const merged = mergePreferences(saved ?? listingDefaults);
            this.columns = merged.columns;
            this.actions = merged.actions;
        },

        async persistPreferences() {
            this.columns.name = true;

            if (! updateUrl) {
                return;
            }

            const payload = {
                listing: this.listing,
                columns: this.columns,
                actions: this.actions,
            };

            this.saving = true;

            try {
                const response = await fetch(updateUrl, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken(),
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(payload),
                });

                if (! response.ok) {
                    throw new Error('Preference update failed');
                }

                const saved = await response.json();
                this.applyPreferences(saved);
            } catch {
                this.applyPreferences(initial ?? listingDefaults);
            } finally {
                this.saving = false;
            }
        },

        async resetPreferences() {
            this.applyPreferences(listingDefaults);

            await this.persistPreferences();
        },

        get visibleColumnCount() {
            return Object.values(this.columns).filter(Boolean).length + 1;
        },
    });
}
