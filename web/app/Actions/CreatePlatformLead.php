<?php

namespace App\Actions;

use App\Enums\PlatformLeadActivityType;
use App\Enums\PlatformLeadSource;
use App\Enums\PlatformLeadStage;
use App\Models\PlatformLead;
use App\Models\User;

class CreatePlatformLead
{
    public function __construct(
        private LogPlatformLeadActivity $logActivity,
    ) {}

    /**
     * @param  array{
     *     company_name: string,
     *     contact_person: string,
     *     email: string,
     *     phone: string,
     *     location?: string|null,
     *     source?: string,
     *     owner_id?: int|null,
     *     stage?: string,
     *     demo_date?: string|null,
     *     demo_time?: string|null,
     *     next_action_label?: string|null,
     *     next_action_at?: string|null,
     * }  $data
     */
    public function handle(array $data, ?User $user = null): PlatformLead
    {
        $user ??= auth()->user();
        $stage = PlatformLeadStage::tryFrom((string) ($data['stage'] ?? '')) ?? PlatformLeadStage::NewLead;
        $source = PlatformLeadSource::tryFrom((string) ($data['source'] ?? '')) ?? PlatformLeadSource::Website;

        $lead = PlatformLead::query()->create([
            'company_name' => $data['company_name'],
            'contact_person' => $data['contact_person'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'location' => $data['location'] ?? null,
            'source' => $source,
            'stage' => $stage,
            'owner_id' => $data['owner_id'] ?? $user?->id,
            'demo_date' => $data['demo_date'] ?? null,
            'demo_time' => $data['demo_time'] ?? null,
            'next_action_label' => $data['next_action_label'] ?? null,
            'next_action_at' => $data['next_action_at'] ?? null,
            'created_by_id' => $user?->id,
        ]);

        $this->logActivity->handle(
            $lead,
            PlatformLeadActivityType::LeadCreated,
            __('Lead created'),
            $user,
        );

        return $lead;
    }
}
