<?php

namespace App\Actions;

use App\Enums\PlatformLeadActivityType;
use App\Enums\SubscriptionStatus;
use App\Models\PlatformLead;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EndPlatformLeadTrial
{
    public function __construct(
        private LogPlatformLeadActivity $logActivity,
    ) {}

    public function handle(PlatformLead $lead, ?User $actor = null): PlatformLead
    {
        if (! $lead->canEndTrial()) {
            throw ValidationException::withMessages([
                'lead' => __('This lead does not have an active trial to end.'),
            ]);
        }

        $subscription = $lead->latestPartnerSubscription();

        if ($subscription === null || $subscription->status !== SubscriptionStatus::Trial) {
            throw ValidationException::withMessages([
                'lead' => __('This lead does not have an active trial to end.'),
            ]);
        }

        return DB::transaction(function () use ($lead, $subscription, $actor): PlatformLead {
            $subscription->update([
                'status' => SubscriptionStatus::TrialEnded,
                'trial_ends_at' => now(),
                'next_billing_at' => null,
            ]);

            $this->logActivity->handle(
                $lead,
                PlatformLeadActivityType::TrialEnded,
                __('Trial ended. Channel partner record kept.'),
                $actor,
            );

            return $lead->refresh();
        });
    }
}
