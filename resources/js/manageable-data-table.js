function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

function mergeTablePreferences(defaults, saved, requiredColumns = []) {
    const columns = { ...defaults.columns, ...(saved?.columns ?? {}) };

    requiredColumns.forEach((column) => {
        columns[column] = true;
    });

    const customColumns = Array.isArray(saved?.custom_columns) ? saved.custom_columns : [];

    return { columns, customColumns };
}

export function registerTablePreferencesStore(Alpine, tableKey, config = {}) {
    const storeName = `tablePreferences_${tableKey}`;
    const defaults = config.defaults ?? { columns: {}, custom_columns: [] };
    const requiredColumns = config.requiredColumns ?? [];
    const updateUrl = config.updateUrl ?? null;
    const initial = config.initial ?? null;

    Alpine.store(storeName, {
        columns: { ...defaults.columns },
        customColumns: [],
        loaded: false,
        saving: false,
        newColumnLabel: '',
        newColumnType: 'text',
        newColumnOptions: '',

        ensureLoaded() {
            if (this.loaded) {
                return;
            }

            this.loaded = true;
            this.applyPreferences(initial);
        },

        applyPreferences(saved) {
            const merged = mergeTablePreferences(defaults, saved, requiredColumns);
            this.columns = merged.columns;
            this.customColumns = merged.customColumns;
        },

        isColumnVisible(key) {
            return Boolean(this.columns[key]);
        },

        isCustomColumnVisible(key) {
            const column = this.customColumns.find((item) => item.key === key);

            return column ? Boolean(column.visible) : false;
        },

        async persistPreferences() {
            requiredColumns.forEach((column) => {
                this.columns[column] = true;
            });

            if (! updateUrl) {
                return;
            }

            const payload = {
                columns: this.columns,
                custom_columns: this.customColumns,
            };

            if (config.listing) {
                payload.listing = config.listing;
            }

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
                this.applyPreferences(initial);
            } finally {
                this.saving = false;
            }
        },

        async resetPreferences() {
            this.applyPreferences(null);

            await this.persistPreferences();
        },

        addCustomColumn() {
            const label = this.newColumnLabel.trim();

            if (label === '') {
                return;
            }

            const key = label.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '');
            const options = this.newColumnType === 'select'
                ? this.newColumnOptions.split(',').map((option) => option.trim()).filter(Boolean)
                : [];

            this.customColumns.push({
                key,
                label,
                type: this.newColumnType,
                options,
                visible: true,
            });

            this.newColumnLabel = '';
            this.newColumnType = 'text';
            this.newColumnOptions = '';

            this.persistPreferences();
        },

        removeCustomColumn(key) {
            this.customColumns = this.customColumns.filter((column) => column.key !== key);
            this.persistPreferences();
        },
    });

    return storeName;
}

export function registerManageableDataTable(Alpine) {
    Alpine.data('manageableDataTable', (tableKey, itemIds = [], customValues = {}) => ({
        tableKey,
        selected: [],
        itemIds,
        customValues,

        init() {
            this.$store[`tablePreferences_${this.tableKey}`]?.ensureLoaded();
        },

        get preferencesStore() {
            return this.$store[`tablePreferences_${this.tableKey}`];
        },

        get columns() {
            return this.preferencesStore?.columns ?? {};
        },

        get customColumns() {
            return this.preferencesStore?.customColumns ?? [];
        },

        isColumnVisible(key) {
            return this.preferencesStore?.isColumnVisible(key) ?? true;
        },

        isCustomColumnVisible(key) {
            return this.preferencesStore?.isCustomColumnVisible(key) ?? false;
        },

        customValue(recordId, columnKey) {
            return this.customValues?.[recordId]?.[columnKey] ?? '—';
        },

        toggleAll() {
            if (this.allSelected) {
                this.selected = [];
            } else {
                this.selected = [...this.itemIds];
            }
        },

        toggle(id) {
            if (this.selected.includes(id)) {
                this.selected = this.selected.filter((item) => item !== id);
            } else {
                this.selected.push(id);
            }
        },

        get allSelected() {
            return this.itemIds.length > 0 && this.selected.length === this.itemIds.length;
        },

        get someSelected() {
            return this.selected.length > 0 && this.selected.length < this.itemIds.length;
        },
    }));
}
