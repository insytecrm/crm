<?php

namespace App\Actions;

use App\Enums\PlatformLeadActivityType;
use App\Enums\PlatformLeadSource;
use App\Enums\PlatformLeadStage;
use App\Models\PlatformLead;
use App\Models\User;

class UpdatePlatformLead
{
    public function __construct(
        private LogPlatformLeadActivity $logActivity,
        private UpdatePlatformLeadStage $updateStage,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(PlatformLead $lead, array $data, ?User $user = null): PlatformLead
    {
        $stage = isset($data['stage'])
            ? PlatformLeadStage::from((string) $data['stage'])
            : $lead->stage;

        $extras = [];
        if (array_key_exists('demo_date', $data)) {
            $extras['demo_date'] = $data['demo_date'];
        }
        if (array_key_exists('demo_time', $data)) {
            $extras['demo_time'] = $data['demo_time'];
        }
        if (array_key_exists('next_action_label', $data)) {
            $extras['next_action_label'] = $data['next_action_label'];
        }
        if (array_key_exists('next_action_at', $data)) {
            $extras['next_action_at'] = $data['next_action_at'];
        }

        if ($stage !== $lead->stage || $extras !== []) {
            $this->updateStage->handle($lead, $stage, $user, $extras);
            $lead->refresh();
        }

        $updates = [
            'company_name' => $data['company_name'] ?? $lead->company_name,
            'contact_person' => $data['contact_person'] ?? $lead->contact_person,
            'email' => $data['email'] ?? $lead->email,
            'phone' => $data['phone'] ?? $lead->phone,
            'location' => array_key_exists('location', $data) ? $data['location'] : $lead->location,
            'source' => isset($data['source'])
                ? PlatformLeadSource::from((string) $data['source'])
                : $lead->source,
            'owner_id' => array_key_exists('owner_id', $data) ? $data['owner_id'] : $lead->owner_id,
        ];

        $changed = false;
        foreach ($updates as $key => $value) {
            $current = $lead->getAttribute($key);
            if ($current instanceof \BackedEnum) {
                $current = $current->value;
            }
            if ($value instanceof \BackedEnum) {
                $value = $value->value;
            }
            if ($current != $value) {
                $changed = true;
                break;
            }
        }

        $lead->update($updates);

        if ($changed) {
            $this->logActivity->handle(
                $lead,
                PlatformLeadActivityType::FieldUpdated,
                __('Lead information updated'),
                $user,
            );
        }

        return $lead->refresh();
    }
}
