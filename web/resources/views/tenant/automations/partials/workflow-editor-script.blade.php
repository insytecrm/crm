<script>
    window.automationWorkflowEditorConfig = @json($editorConfig);

    function automationWorkflowEditor(config) {
        const leadStatuses = config.leadStatuses ?? [];
        const normalizeAction = (action) => ({
            type: action.type,
            title: action.title ?? '',
            body: action.body ?? '',
            status: action.status || (leadStatuses[1]?.value ?? leadStatuses[0]?.value ?? 'contacted'),
            delay_hours: action.delay_hours || 24,
            delay_minutes: action.delay_minutes || 60,
        });

        return {
            name: config.name ?? '',
            isActive: Boolean(config.isActive),
            trigger: config.trigger ?? '',
            conditions: config.conditions ?? [],
            actions: (config.actions ?? []).map(normalizeAction),
            triggers: config.triggers ?? [],
            actionTypes: config.actionTypes ?? [],
            conditionFields: config.conditionFields ?? [],
            conditionOperators: config.conditionOperators ?? [],
            conditionValues: config.conditionValues ?? {},
            leadStatuses,
            expanded: null,

            triggerOption() {
                return this.triggers.find((option) => option.value === this.trigger) ?? null;
            },

            triggerLabel() {
                return this.triggerOption()?.label ?? '';
            },

            triggerDescription() {
                return this.triggerOption()?.description ?? '';
            },

            openTriggerPicker() {
                window.dispatchEvent(new CustomEvent('open-workflow-picker', { detail: 'trigger' }));
            },

            closePicker() {
                window.dispatchEvent(new CustomEvent('close-workflow-picker'));
            },

            selectTrigger(value) {
                if (! value) {
                    return;
                }

                this.trigger = value;
                this.expanded = null;
                this.closePicker();
            },

            addFilter() {
                if (this.conditions.length === 0) {
                    this.addCondition();
                }

                this.expanded = 'filter';
            },

            addCondition() {
                const field = this.conditionFields[0]?.value ?? '';
                const operator = this.conditionOperators[0]?.value ?? '';
                const options = this.conditionValues[field] ?? [];

                this.conditions.push({
                    field,
                    operator,
                    value: options[0]?.value ?? '',
                });
            },

            conditionValueOptions(condition) {
                const options = [...(this.conditionValues[condition.field] ?? [])];

                if (condition.value && ! options.some((option) => option.value === condition.value)) {
                    options.unshift({ value: condition.value, label: condition.value });
                }

                return options;
            },

            showsValueSelect(condition) {
                return condition.operator !== 'is_empty' && this.conditionValueOptions(condition).length > 0;
            },

            showsValueText(condition) {
                return condition.operator !== 'is_empty' && this.conditionValueOptions(condition).length === 0;
            },

            syncConditionValue(condition) {
                if (condition.operator === 'is_empty') {
                    condition.value = '';

                    return;
                }

                const options = this.conditionValues[condition.field] ?? [];

                if (options.length === 0) {
                    return;
                }

                if (! options.some((option) => option.value === condition.value)) {
                    condition.value = options[0]?.value ?? '';
                }
            },

            removeCondition(index) {
                this.conditions.splice(index, 1);
            },

            clearFilter() {
                this.conditions = [];
                this.expanded = null;
            },

            filterSummary() {
                if (this.conditions.length === 0) {
                    return @js(__('No conditions'));
                }

                if (this.conditions.length === 1) {
                    return @js(__('1 condition'));
                }

                return @js(__(':count conditions')).replace(':count', String(this.conditions.length));
            },

            openActionPicker() {
                window.dispatchEvent(new CustomEvent('open-workflow-picker', { detail: 'action' }));
            },

            selectAction(type) {
                if (! type) {
                    return;
                }

                this.actions.push(normalizeAction({ type }));
                this.closePicker();
                this.expanded = 'action-' + (this.actions.length - 1);
            },

            removeAction(index) {
                this.actions.splice(index, 1);
            },

            actionType(type) {
                return this.actionTypes.find((option) => option.value === type) ?? null;
            },

            actionLabel(type) {
                return this.actionType(type)?.label ?? type;
            },

            actionDescription(type) {
                return this.actionType(type)?.description ?? '';
            },

            actionSummary(action) {
                if (action.type === 'create_task') {
                    return action.title || @js(__('Follow up'));
                }

                if (action.type === 'add_note') {
                    return action.body || @js(__('Added by automation.'));
                }

                if (action.type === 'change_status') {
                    const status = this.leadStatuses.find((option) => option.value === action.status);

                    return status ? @js(__('Set status to :status')).replace(':status', status.label) : this.actionDescription(action.type);
                }

                if (action.type === 'schedule_follow_up' || action.type === 'create_site_visit' || action.type === 'reschedule_follow_up') {
                    return @js(__('In :hours hours')).replace(':hours', String(action.delay_hours || 24));
                }

                if (action.type === 'wait') {
                    return @js(__('Wait :minutes minutes')).replace(':minutes', String(action.delay_minutes || 60));
                }

                if (action.type === 'notify_salesperson' || action.type === 'notify_team_leader' || action.type === 'notify_manager') {
                    return action.title || this.actionDescription(action.type);
                }

                return this.actionDescription(action.type);
            },

            actionNumber(index) {
                return this.conditions.length > 0 ? index + 3 : index + 2;
            },
        };
    }
</script>

<script>
    function workflowPickerHost() {
        return {
            type: null,

            openPicker(detail) {
                if (detail !== 'trigger' && detail !== 'action') {
                    return;
                }

                this.type = detail;
                document.body.classList.add('overflow-hidden');
            },

            close() {
                this.type = null;
                document.body.classList.remove('overflow-hidden');
            },

            choose(value) {
                if (! value || this.type === null) {
                    return;
                }

                window.dispatchEvent(new CustomEvent(
                    this.type === 'trigger' ? 'workflow-trigger-selected' : 'workflow-action-selected',
                    { detail: value },
                ));
                this.close();
            },
        };
    }
</script>
