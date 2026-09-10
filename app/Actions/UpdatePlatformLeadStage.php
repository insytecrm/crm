<?php

namespace App\Actions;

use App\Enums\PlatformLeadActivityType;
use App\Enums\PlatformLeadStage;
use App\Models\PlatformLead;
use App\Models\User;

class UpdatePlatformLeadStage
{
    public function __construct(
        private LogPlatformLeadActivity $logActivity,
    ) {}

    /**
     * @param  array{
     *     demo_date?: string|null,
     *     demo_time?: string|null,
     *     next_action_label?: string|null,
     *     next_action_at?: string|null,
     * }  $extras
     */
    public function handle(
        PlatformLead $lead,
        PlatformLeadStage $stage,
        ?User $user = null,
        array $extras = [],
    ): PlatformLead {
        $previous = $lead->stage;

        if ($previous === $stage && $extras === []) {
            return $lead;
        }

        $payload = ['stage' => $stage];

        if ($stage->requiresDemoFields()) {
            $payload['demo_date'] = $extras['demo_date'] ?? $lead->demo_date;
            $payload['demo_time'] = $extras['demo_time'] ?? $lead->demo_time;

            if (isset($extras['demo_date'], $extras['demo_time'])) {
                $payload['next_action_label'] = __('Demo scheduled');
                $payload['next_action_at'] = ($extras['demo_date'].' '.$extras['demo_time']);
            }
        }

        if (array_key_exists('next_action_label', $extras)) {
            $payload['next_action_label'] = $extras['next_action_label'];
        }

        if (array_key_exists('next_action_at', $extras)) {
            $payload['next_action_at'] = $extras['next_action_at'];
        }

        $lead->update($payload);

        if ($previous !== $stage) {
            $this->logActivity->handle(
                $lead,
                PlatformLeadActivityType::StageChanged,
                __('Stage changed to :stage', ['stage' => $stage->label()]),
                $user,
                [
                    'from' => $previous->value,
                    'to' => $stage->value,
                ],
            );
        }

        if ($stage === PlatformLeadStage::DemoScheduled && isset($extras['demo_date'])) {
            $this->logActivity->handle(
                $lead,
                PlatformLeadActivityType::DemoScheduled,
                __('Demo scheduled for :date', [
                    'date' => $lead->demo_date?->timezone(config('app.timezone'))->format('d M Y') ?? '—',
                ]),
                $user,
            );
        }

        return $lead->refresh();
    }
}
