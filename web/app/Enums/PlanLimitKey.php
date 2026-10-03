<?php

namespace App\Enums;

enum PlanLimitKey: string
{
    case Users = 'users';
    case Leads = 'leads';
    case Automations = 'automations';
    case AutomationRunsMonthly = 'automation_runs_monthly';
    case WhatsAppMessagesMonthly = 'whatsapp_messages_monthly';
    case AiMessagesMonthly = 'ai_messages_monthly';
    case Integrations = 'integrations';
    case Properties = 'properties';
    case Teams = 'teams';
    case Microsites = 'microsites';

    public function label(): string
    {
        return match ($this) {
            self::Users => __('Users'),
            self::Leads => __('Leads'),
            self::Automations => __('Automations'),
            self::AutomationRunsMonthly => __('Automation runs'),
            self::WhatsAppMessagesMonthly => __('WhatsApp messages'),
            self::AiMessagesMonthly => __('AI messages'),
            self::Integrations => __('Integrations'),
            self::Properties => __('Properties'),
            self::Teams => __('Teams'),
            self::Microsites => __('Microsites'),
        };
    }

    public function unit(): ?string
    {
        return match ($this) {
            self::AutomationRunsMonthly, self::WhatsAppMessagesMonthly, self::AiMessagesMonthly => __(' / month'),
            default => null,
        };
    }
}
