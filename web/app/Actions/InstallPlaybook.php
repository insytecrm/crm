<?php

namespace App\Actions;

use App\Models\Automation;
use App\Models\User;
use Illuminate\Support\Str;

class InstallPlaybook
{
    public function __construct(
        private CreateAutomationWorkflow $createAutomationWorkflow,
        private SaveAutomationWorkflow $saveAutomationWorkflow,
    ) {}

    public function handle(User $user, string $key, bool $active = true): Automation
    {
        /** @var array<string, array<string, mixed>> $playbooks */
        $playbooks = config('playbooks', []);

        if (! isset($playbooks[$key])) {
            throw new \InvalidArgumentException(__('Unknown playbook.'));
        }

        $definition = $playbooks[$key];
        $name = $this->automationName($key, (string) ($definition['label'] ?? $key));

        $automation = Automation::query()
            ->where('user_id', $user->id)
            ->where('name', $name)
            ->first();

        if ($automation === null) {
            $automation = $this->createAutomationWorkflow->handle($user);
        }

        $this->saveAutomationWorkflow->handle($automation, [
            'name' => $name,
            'is_active' => $active,
            'trigger' => $definition['trigger'] ?? null,
            'conditions' => [],
            'actions' => $definition['actions'] ?? [],
        ]);

        return $automation->fresh(['actions', 'conditions']) ?? $automation;
    }

    public function isEnabled(User $user, string $key): bool
    {
        if (! isset(config('playbooks', [])[$key])) {
            return false;
        }

        $definition = config('playbooks')[$key];
        $name = $this->automationName($key, (string) ($definition['label'] ?? $key));

        return Automation::query()
            ->where('user_id', $user->id)
            ->where('name', $name)
            ->where('is_active', true)
            ->exists();
    }

    private function automationName(string $key, string $label): string
    {
        $display = $label !== '' ? $label : Str::headline($key);

        return 'Playbook: '.$display;
    }
}
