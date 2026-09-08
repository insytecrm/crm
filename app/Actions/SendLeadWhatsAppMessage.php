<?php

namespace App\Actions;

use App\Enums\LeadActivityType;
use App\Enums\PlanLimitKey;
use App\Models\Lead;
use App\Models\MessageTemplate;
use App\Models\User;

class SendLeadWhatsAppMessage
{
    public function __construct(
        private LogLeadActivity $logLeadActivity,
        private AssertPlanLimit $assertPlanLimit,
    ) {}

    /**
     * @return array{message: string, url: string}
     */
    public function handle(Lead $lead, User $user, string $message, ?MessageTemplate $template = null): array
    {
        $this->assertPlanLimit->handle(PlanLimitKey::WhatsAppMessagesMonthly);

        $url = $lead->whatsAppComposeUrl($message);

        if ($url === null) {
            abort(422, __('This lead does not have a WhatsApp number.'));
        }

        $metadata = [];

        if ($template instanceof MessageTemplate) {
            $metadata = [
                'template_id' => $template->id,
                'template_name' => $template->name,
            ];
        }

        $this->logLeadActivity->handle(
            $lead,
            LeadActivityType::WhatsAppMessage,
            $message !== '' ? $message : LeadActivityType::WhatsAppMessage->label(),
            $user,
            $metadata,
        );

        return [
            'message' => $message,
            'url' => $url,
        ];
    }
}
