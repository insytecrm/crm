<?php

namespace App\Actions;

use App\Models\Automation;
use Illuminate\Support\Facades\DB;

class SaveAutomationWorkflow
{
    /**
     * @param  array{
     *     name: string,
     *     is_active: bool,
     *     trigger: ?string,
     *     conditions?: list<array{field: string, operator: string, value?: ?string}>,
     *     actions?: list<array{type: string, title?: ?string, body?: ?string, status?: ?string, delay_hours?: int|string|null, delay_minutes?: int|string|null}>
     * }  $data
     */
    public function handle(Automation $automation, array $data): Automation
    {
        return DB::transaction(function () use ($automation, $data): Automation {
            $automation->update([
                'name' => $data['name'],
                'is_active' => $data['is_active'],
                'trigger' => $data['trigger'],
            ]);

            $this->syncConditions($automation, $data['conditions'] ?? []);
            $this->syncActions($automation, $data['actions'] ?? []);

            return $automation->fresh(['conditions', 'actions']) ?? $automation;
        });
    }

    /**
     * @param  list<array{field: string, operator: string, value?: ?string}>  $conditions
     */
    private function syncConditions(Automation $automation, array $conditions): void
    {
        $automation->conditions()->delete();

        foreach (array_values($conditions) as $index => $condition) {
            $automation->conditions()->create([
                'field' => $condition['field'],
                'operator' => $condition['operator'],
                'value' => ['text' => $condition['value'] ?? null],
                'sort_order' => $index,
            ]);
        }
    }

    /**
     * @param  list<array{type: string, title?: ?string, body?: ?string, status?: ?string, delay_hours?: int|string|null, delay_minutes?: int|string|null}>  $actions
     */
    private function syncActions(Automation $automation, array $actions): void
    {
        $automation->actions()->delete();

        foreach (array_values($actions) as $index => $action) {
            $automation->actions()->create([
                'type' => $action['type'],
                'config' => $this->actionConfig($action),
                'sort_order' => $index,
            ]);
        }
    }

    /**
     * @param  array{type: string, title?: ?string, body?: ?string, status?: ?string, delay_hours?: int|string|null, delay_minutes?: int|string|null}  $action
     * @return array<string, mixed>
     */
    private function actionConfig(array $action): array
    {
        $config = [];

        $title = trim((string) ($action['title'] ?? ''));
        $body = trim((string) ($action['body'] ?? ''));
        $status = trim((string) ($action['status'] ?? ''));
        $delayHours = $action['delay_hours'] ?? null;
        $delayMinutes = $action['delay_minutes'] ?? null;

        if ($title !== '') {
            $config['title'] = $title;
        }

        if ($body !== '') {
            $config['body'] = $body;
        }

        if ($status !== '') {
            $config['status'] = $status;
        }

        if ($delayHours !== null && $delayHours !== '') {
            $config['delay_hours'] = (int) $delayHours;
        }

        if ($delayMinutes !== null && $delayMinutes !== '') {
            $config['delay_minutes'] = (int) $delayMinutes;
        }

        return $config;
    }
}
