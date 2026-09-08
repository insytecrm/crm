

import Alpine from 'alpinejs';

if (document.getElementById('landing-root')) {
    void import('./landing');
}

if (document.getElementById('welcome-root')) {
    void import('./welcome');
}

import './drawers';
import './lead-hover-cards';
import './report-donut-charts-loader';
import './datetime-pickers-loader';
import { registerLeadTablePreferencesStore } from './lead-table-preferences';
import { registerTablePreferencesStore, registerManageableDataTable } from './manageable-data-table';
import { registerPipelineChart } from './pipeline-chart';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    registerLeadTablePreferencesStore(Alpine, window.leadTablePreferencesConfig ?? {});
    registerManageableDataTable(Alpine);
    registerPipelineChart(Alpine);

    if (window.manageableDataTableConfigs) {
        Object.entries(window.manageableDataTableConfigs).forEach(([tableKey, config]) => {
            registerTablePreferencesStore(Alpine, tableKey, config);
        });
    }

    Alpine.store('drawers', {
        active: null,

        open(name) {
            this.active = name;
            document.body.classList.add('overflow-hidden');
        },

        close(name = null) {
            if (name !== null && this.active !== name) {
                return;
            }

            this.active = null;
            document.body.classList.remove('overflow-hidden');
        },

        isOpen(name) {
            return this.active === name;
        },
    });

    Alpine.data('leadDrawerHost', (config) => {
        const cache = new Map();
        const inflight = new Map();

        return {
            open: false,
            activeId: null,
            showUrlTemplate: config.showUrlTemplate ?? '',
            prefetchTimers: new Map(),

            init() {
                const leadId = new URLSearchParams(window.location.search).get('lead');

                if (leadId) {
                    this.openLead(Number.parseInt(leadId, 10));
                }

                window.addEventListener('popstate', () => this.handlePopState());
            },

            handlePopState() {
                const leadId = new URLSearchParams(window.location.search).get('lead');

                if (leadId) {
                    this.openLead(Number.parseInt(leadId, 10));

                    return;
                }

                this.close(false);
            },

            removeLeadFromUrl() {
                const url = new URL(window.location.href);

                if (! url.searchParams.has('lead')) {
                    return;
                }

                url.searchParams.delete('lead');
                const nextUrl = url.searchParams.toString() === ''
                    ? `${url.pathname}${url.hash}`
                    : `${url.pathname}?${url.searchParams.toString()}${url.hash}`;

                window.history.replaceState({}, '', nextUrl);
            },

            syncUrl(id) {
                const url = new URL(window.location.href);
                url.searchParams.set('lead', String(id));
                window.history.replaceState({}, '', url);
            },

            renderLeadHtml(html) {
                this.$nextTick(() => {
                    if (! this.$refs.content) {
                        return;
                    }

                    this.$refs.content.innerHTML = html;
                    Alpine.initTree(this.$refs.content);
                });
            },

            async fetchLeadHtml(id) {
                if (cache.has(id)) {
                    return cache.get(id);
                }

                if (inflight.has(id)) {
                    return inflight.get(id);
                }

                const promise = fetch(this.showUrlTemplate.replace('__ID__', String(id)), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        Accept: 'text/html',
                    },
                }).then(async (response) => {
                    if (! response.ok) {
                        return null;
                    }

                    const html = await response.text();
                    cache.set(id, html);

                    return html;
                }).finally(() => {
                    inflight.delete(id);
                });

                inflight.set(id, promise);

                return promise;
            },

            prefetchLead(id) {
                if (! id || cache.has(id) || inflight.has(id)) {
                    return;
                }

                const existing = this.prefetchTimers.get(id);

                if (existing) {
                    window.clearTimeout(existing);
                }

                // Avoid flooding the single-threaded PHP server while scrolling the list.
                const timer = window.setTimeout(() => {
                    this.prefetchTimers.delete(id);
                    void this.fetchLeadHtml(id);
                }, 250);

                this.prefetchTimers.set(id, timer);
            },

            close(updateUrl = true) {
                if (this.$refs.content) {
                    this.$refs.content.innerHTML = '';
                }

                this.activeId = null;
                this.open = false;
                document.body.classList.remove('overflow-hidden');

                if (updateUrl) {
                    this.removeLeadFromUrl();
                }
            },

            async openLead(id) {
                if (! id) {
                    return;
                }

                this.activeId = id;
                this.open = true;
                this.syncUrl(id);
                document.body.classList.add('overflow-hidden');

                const cached = cache.get(id);

                if (cached) {
                    this.renderLeadHtml(cached);

                    return;
                }

                if (this.$refs.content) {
                    this.$refs.content.innerHTML = '';
                }

                const html = await this.fetchLeadHtml(id);

                if (this.activeId !== id) {
                    return;
                }

                if (! html) {
                    return;
                }

                this.renderLeadHtml(html);
            },
        };
    });

    Alpine.data('uiDrawer', (config) => ({
        visible: Boolean(config.show),
        name: config.name ?? null,
        side: config.side ?? 'right',
        closeUrl: config.closeUrl ?? null,
        closeOnOverlay: config.closeOnOverlay ?? true,
        overlayOnly: config.overlayOnly ?? false,

        init() {
            if (this.name) {
                this.$watch('$store.drawers.active', (active) => {
                    this.visible = active === this.name;
                });

                if (config.show) {
                    this.visible = true;
                    Alpine.store('drawers').open(this.name);
                }
            } else if (config.show) {
                this.visible = true;
                document.body.classList.add('overflow-hidden');
            }
        },

        handleOpenEvent(event) {
            if (this.name && event.detail === this.name) {
                this.openDrawer();
            }
        },

        handleCloseEvent(event) {
            if (this.name) {
                if (event.detail === this.name) {
                    this.closeDrawer();
                }

                return;
            }

            if (this.visible && (event.detail === undefined || event.detail === null || event.detail === '')) {
                this.closeDrawer();
            }
        },

        openDrawer() {
            this.visible = true;

            if (this.name) {
                Alpine.store('drawers').open(this.name);

                return;
            }

            document.body.classList.add('overflow-hidden');
        },

        closeDrawer() {
            this.visible = false;

            if (this.name) {
                Alpine.store('drawers').close(this.name);
            } else {
                document.body.classList.remove('overflow-hidden');
            }

            this.$dispatch('drawer-close');

            if (this.closeUrl) {
                window.location.href = this.closeUrl;
            }
        },
    }));

    Alpine.data('uiScrollArea', () => ({
        init() {
            this.$refs.viewport?.setAttribute('tabindex', '-1');
        },
    }));

    Alpine.data('leadStatusHover', () => ({
        open: false,
        panelStyle: {},
        hideTimer: null,

        show(trigger) {
            this.cancelHide();

            if (! trigger) {
                return;
            }

            const rect = trigger.getBoundingClientRect();
            const panelWidth = 288;
            const gap = 6;
            const padding = 12;
            const left = Math.min(
                Math.max(padding, rect.left),
                Math.max(padding, window.innerWidth - panelWidth - padding),
            );
            const spaceBelow = window.innerHeight - rect.bottom - padding;
            const openAbove = spaceBelow < 220 && rect.top > spaceBelow;

            this.panelStyle = {
                position: 'fixed',
                left: `${left}px`,
                top: openAbove ? `${rect.top - gap}px` : `${rect.bottom + gap}px`,
                transform: openAbove ? 'translateY(-100%)' : 'none',
                zIndex: '9999',
            };
            this.open = true;
        },

        scheduleHide() {
            this.cancelHide();
            this.hideTimer = window.setTimeout(() => {
                this.open = false;
                this.hideTimer = null;
            }, 120);
        },

        cancelHide() {
            if (this.hideTimer !== null) {
                window.clearTimeout(this.hideTimer);
                this.hideTimer = null;
            }
        },
    }));

    Alpine.data('leadFieldSelect', (config) => ({
        field: config.field,
        value: config.value ?? '',
        classes: config.classes ?? {},
        widthClass: config.widthClass ?? 'min-w-[8.5rem]',
        saving: false,

        formElement() {
            return this.$el?.tagName === 'FORM' ? this.$el : this.$el?.closest('form');
        },

        triggerClass() {
            const base = `flex h-8 ${this.widthClass} w-full items-center gap-1.5 rounded-lg border-0 px-2.5 text-left text-xs font-semibold shadow-sm ring-1 ring-inset ring-black/5 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-navy/30`;

            return `${base} ${this.classes[this.value] ?? 'bg-slate-100 text-slate-500'}`;
        },

        syncTriggerClass() {
            const trigger = this.formElement()?.querySelector('button[aria-haspopup="listbox"]');

            if (trigger) {
                trigger.className = this.triggerClass();
            }
        },

        syncSelectValue(value) {
            const form = this.formElement();

            if (! form) {
                return;
            }

            const input = form.querySelector(`input[name="${this.field}"]`);

            if (input) {
                input.value = value;
                input.dispatchEvent(new Event('input', { bubbles: true }));
            }

            const selectRoot = form.querySelector('[data-ui-select]');

            if (selectRoot && window.Alpine) {
                const selectData = Alpine.$data(selectRoot);

                if (selectData && Object.prototype.hasOwnProperty.call(selectData, 'selected')) {
                    selectData.selected = value;
                }
            }
        },

        async submitValue(selectedValue) {
            if (this.saving || selectedValue === this.value) {
                return;
            }

            const form = this.formElement();

            if (! form) {
                return;
            }

            const previousValue = this.value;
            this.value = selectedValue;
            this.syncSelectValue(selectedValue);
            this.syncTriggerClass();

            this.saving = true;

            const formData = new FormData(form);
            formData.set(this.field, selectedValue);
            formData.set('_method', 'PATCH');

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
                    },
                    credentials: 'same-origin',
                });

                if (! response.ok) {
                    throw new Error('Field update failed');
                }

                const contentType = response.headers.get('content-type') ?? '';

                if (! contentType.includes('application/json')) {
                    throw new Error('Unexpected response format');
                }

                const data = await response.json();
                const nextValue = data?.[this.field] ?? '';

                this.value = nextValue;
                this.syncSelectValue(nextValue);
                this.syncTriggerClass();
            } catch {
                this.value = previousValue;
                this.syncSelectValue(previousValue);
                this.syncTriggerClass();
            } finally {
                this.saving = false;
            }
        },

        init() {
            this.$nextTick(() => this.syncTriggerClass());
        },
    }));

    Alpine.data('propertyMicrositeSwitch', (config) => ({
        enabled: Boolean(config.enabled),
        url: config.url ?? null,
        updateUrl: config.updateUrl ?? null,
        canPublish: config.canPublish !== false,
        saving: false,
        lastSavedEnabled: Boolean(config.enabled),

        async save() {
            if (! this.updateUrl || this.saving) {
                return;
            }

            const nextEnabled = Boolean(this.enabled);
            const previousEnabled = this.lastSavedEnabled;
            const previousUrl = this.url;

            if (nextEnabled && ! this.canPublish) {
                this.enabled = previousEnabled;
                return;
            }

            // Keep public link in sync with the switch immediately.
            if (! nextEnabled) {
                this.url = null;
            }

            this.saving = true;

            const formData = new FormData();
            formData.append('_method', 'PATCH');
            formData.append('microsite_enabled', nextEnabled ? '1' : '0');

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            if (csrfToken) {
                formData.append('_token', csrfToken);
            }

            try {
                const response = await fetch(this.updateUrl, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        Accept: 'application/json',
                        ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
                    },
                    credentials: 'same-origin',
                });

                if (! response.ok) {
                    throw new Error('Microsite update failed');
                }

                const data = await response.json();
                this.enabled = Boolean(data.microsite_enabled);
                this.url = this.enabled ? (data.microsite_url ?? null) : null;
                this.lastSavedEnabled = this.enabled;
            } catch {
                this.enabled = previousEnabled;
                this.url = previousUrl;
                this.lastSavedEnabled = previousEnabled;
            } finally {
                this.saving = false;
            }
        },
    }));

    Alpine.data('uiSelect', (config) => ({
        open: false,
        selected: config.value ?? '',
        options: config.options ?? [],
        placeholder: config.placeholder ?? 'Select an option',
        submitOnSelect: config.submitOnSelect ?? false,
        minMenuWidth: config.minMenuWidth ?? 300,
        shouldPortalDropdown: config.portal ?? true,
        disabled: config.disabled ?? false,
        outsideClickHandler: null,
        repositionHandler: null,
        disabledObserver: null,

        init() {
            this.syncDisabledFromElement();

            this.disabledObserver = new MutationObserver(() => {
                this.syncDisabledFromElement();
            });

            this.disabledObserver.observe(this.$el, {
                attributes: true,
                attributeFilter: ['data-disabled', 'disabled'],
            });

            this.$nextTick(() => {
                if (this.shouldPortalDropdown && this.$refs.dropdown) {
                    document.body.appendChild(this.$refs.dropdown);
                }
            });
        },

        destroy() {
            this.removeListeners();

            if (this.disabledObserver) {
                this.disabledObserver.disconnect();
                this.disabledObserver = null;
            }

            if (this.shouldPortalDropdown && this.$refs.dropdown?.parentNode === document.body) {
                this.$refs.dropdown.remove();
            }
        },

        syncDisabledFromElement() {
            this.disabled = this.$el.dataset.disabled === 'true'
                || this.$el.hasAttribute('disabled')
                || Boolean(config.disabled);
        },

        get selectedLabel() {
            const match = this.options.find((option) => String(option.value) === String(this.selected));

            if (match) {
                return match.label;
            }

            return this.placeholder;
        },

        positionDropdown() {
            const trigger = this.$refs.trigger;
            const dropdown = this.$refs.dropdown;
            const list = this.$refs.list;

            if (! trigger || ! dropdown) {
                return;
            }

            const rect = trigger.getBoundingClientRect();
            const viewportPadding = 12;
            const gap = 4;
            const preferredHeight = 320;
            const spaceBelow = window.innerHeight - rect.bottom - viewportPadding;
            const spaceAbove = rect.top - viewportPadding;
            const openAbove = spaceBelow < 200 && spaceAbove > spaceBelow;
            const availableHeight = Math.max(160, (openAbove ? spaceAbove : spaceBelow) - gap);
            const maxHeight = Math.min(preferredHeight, availableHeight);
            const minWidth = Math.max(rect.width, this.minMenuWidth);
            const maxLeft = Math.max(viewportPadding, window.innerWidth - minWidth - viewportPadding);
            const left = Math.min(rect.left, maxLeft);

            dropdown.style.position = 'fixed';
            dropdown.style.left = `${left}px`;
            dropdown.style.width = `${minWidth}px`;
            dropdown.style.minWidth = `${minWidth}px`;
            dropdown.style.zIndex = '9999';
            dropdown.style.transform = 'none';

            if (openAbove) {
                dropdown.style.top = `${rect.top - gap}px`;
                dropdown.style.transform = 'translateY(-100%)';
            } else {
                dropdown.style.top = `${rect.bottom + gap}px`;
            }

            if (list) {
                list.style.maxHeight = `${maxHeight}px`;
            }
        },

        addListeners() {
            this.outsideClickHandler = (event) => {
                const trigger = this.$refs.trigger;
                const dropdown = this.$refs.dropdown;

                if (trigger?.contains(event.target) || dropdown?.contains(event.target)) {
                    return;
                }

                this.close();
            };

            this.repositionHandler = () => {
                if (this.open) {
                    this.positionDropdown();
                }
            };

            document.addEventListener('click', this.outsideClickHandler, true);
            window.addEventListener('resize', this.repositionHandler);
            window.addEventListener('scroll', this.repositionHandler, true);
        },

        removeListeners() {
            if (this.outsideClickHandler) {
                document.removeEventListener('click', this.outsideClickHandler, true);
                this.outsideClickHandler = null;
            }

            if (this.repositionHandler) {
                window.removeEventListener('resize', this.repositionHandler);
                window.removeEventListener('scroll', this.repositionHandler, true);
                this.repositionHandler = null;
            }
        },

        toggle() {
            if (this.disabled) {
                return;
            }

            if (this.open) {
                this.close();

                return;
            }

            this.open = true;

            this.$nextTick(() => {
                this.positionDropdown();
                this.addListeners();
            });
        },

        close() {
            this.open = false;
            this.removeListeners();
        },

        select(option) {
            this.selected = String(option.value);

            if (this.$refs.hiddenInput) {
                this.$refs.hiddenInput.value = String(option.value);
                this.$refs.hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
                this.$refs.hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
            }

            this.close();

            const form = this.$refs.hiddenInput?.closest('form');

            if (form && window.Alpine) {
                const formComponent = Alpine.$data(form);

                if (formComponent && typeof formComponent.submitValue === 'function') {
                    formComponent.submitValue(option.value);

                    return;
                }
            }

            this.$dispatch('selected', option.value);

            if (this.submitOnSelect) {
                if (form) {
                    form.requestSubmit();
                }
            }
        },
    }));

    Alpine.data('uiCombobox', (config) => ({
        open: false,
        search: '',
        selected: config.value ?? '',
        options: config.options ?? [],
        placeholder: config.placeholder ?? 'Select an option',
        searchable: config.searchable ?? true,
        submitOnSelect: config.submitOnSelect ?? false,
        shouldPortalDropdown: config.portal ?? true,
        outsideClickHandler: null,
        repositionHandler: null,

        init() {
            this.$nextTick(() => {
                if (this.shouldPortalDropdown && this.$refs.dropdown) {
                    document.body.appendChild(this.$refs.dropdown);
                }
            });
        },

        destroy() {
            this.removeListeners();

            if (this.shouldPortalDropdown && this.$refs.dropdown?.parentNode === document.body) {
                this.$refs.dropdown.remove();
            }
        },

        get selectedLabel() {
            const match = this.options.find((option) => String(option.value) === String(this.selected));

            if (match) {
                return match.label;
            }

            return this.placeholder;
        },

        get filteredOptions() {
            if (! this.searchable || this.search.trim() === '') {
                return this.options;
            }

            const query = this.search.trim().toLowerCase();

            return this.options.filter((option) => option.label.toLowerCase().includes(query));
        },

        positionDropdown() {
            const trigger = this.$refs.trigger;
            const dropdown = this.$refs.dropdown;

            if (! trigger || ! dropdown) {
                return;
            }

            const rect = trigger.getBoundingClientRect();
            const viewportPadding = 12;
            const gap = 4;
            const spaceBelow = window.innerHeight - rect.bottom - viewportPadding;
            const spaceAbove = rect.top - viewportPadding;
            const openAbove = spaceBelow < 200 && spaceAbove > spaceBelow;
            const maxLeft = Math.max(viewportPadding, window.innerWidth - rect.width - viewportPadding);
            const left = Math.min(rect.left, maxLeft);

            dropdown.style.position = 'fixed';
            dropdown.style.left = `${left}px`;
            dropdown.style.width = `${rect.width}px`;
            dropdown.style.zIndex = '9999';
            dropdown.style.transform = 'none';

            if (openAbove) {
                dropdown.style.top = `${rect.top - gap}px`;
                dropdown.style.transform = 'translateY(-100%)';
            } else {
                dropdown.style.top = `${rect.bottom + gap}px`;
            }
        },

        addListeners() {
            this.outsideClickHandler = (event) => {
                const trigger = this.$refs.trigger;
                const dropdown = this.$refs.dropdown;

                if (trigger?.contains(event.target) || dropdown?.contains(event.target)) {
                    return;
                }

                this.open = false;
                this.removeListeners();
            };

            this.repositionHandler = () => {
                if (this.open) {
                    this.positionDropdown();
                }
            };

            document.addEventListener('click', this.outsideClickHandler, true);
            window.addEventListener('resize', this.repositionHandler);
            window.addEventListener('scroll', this.repositionHandler, true);
        },

        removeListeners() {
            if (this.outsideClickHandler) {
                document.removeEventListener('click', this.outsideClickHandler, true);
                this.outsideClickHandler = null;
            }

            if (this.repositionHandler) {
                window.removeEventListener('resize', this.repositionHandler);
                window.removeEventListener('scroll', this.repositionHandler, true);
                this.repositionHandler = null;
            }
        },

        toggle() {
            this.open = ! this.open;

            if (this.open) {
                this.$nextTick(() => {
                    this.positionDropdown();
                    this.addListeners();
                    if (this.searchable) {
                        this.$refs.searchInput?.focus();
                    }
                });
            } else {
                this.removeListeners();
            }
        },

        select(option) {
            this.selected = String(option.value);
            this.open = false;
            this.search = '';
            this.removeListeners();

            if (this.$refs.hiddenInput) {
                this.$refs.hiddenInput.value = String(option.value);
                this.$refs.hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
                this.$refs.hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
            }

            this.$dispatch('selected', option.value);

            if (this.submitOnSelect) {
                const input = this.$refs.hiddenInput;
                const form = input?.closest('form');

                if (! input || ! form) {
                    return;
                }

                this.$nextTick(() => form.requestSubmit());
            }
        },
    }));

    Alpine.data('uiPopover', (config) => ({
        open: Boolean(config.open),

        toggle() {
            this.open = ! this.open;
        },

        close() {
            this.open = false;
        },
    }));

    Alpine.data('uiTagInput', (config) => ({
        name: config.name,
        items: config.items ?? [],
        draft: '',
        maxItems: config.maxItems ?? 50,
        removeLabel: config.removeLabel ?? 'Remove',

        add() {
            const value = this.draft.trim();

            if (value === '') {
                return;
            }

            if (this.items.some((item) => item.toLowerCase() === value.toLowerCase())) {
                this.draft = '';

                return;
            }

            if (this.items.length >= this.maxItems) {
                return;
            }

            this.items.push(value);
            this.draft = '';
        },

        remove(index) {
            this.items.splice(index, 1);
        },
    }));

    Alpine.data('uiConfigurationRepeater', (config) => ({
        name: config.name,
        rows: config.rows ?? [],
        maxRows: config.maxRows ?? 50,
        placeholders: config.placeholders ?? {},
        removeLabel: config.removeLabel ?? 'Remove configuration',

        init() {
            if (this.rows.length === 0) {
                this.addRow();
            }
        },

        blankRow() {
            return {
                name: '',
                carpet_area_sqft: '',
                price: '',
                unit_count: '',
            };
        },

        addRow() {
            if (this.rows.length >= this.maxRows) {
                return;
            }

            this.rows.push(this.blankRow());
        },

        removeRow(index) {
            this.rows.splice(index, 1);

            if (this.rows.length === 0) {
                this.addRow();
            }
        },
    }));
});

Alpine.start();
