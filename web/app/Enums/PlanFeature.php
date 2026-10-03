<?php

namespace App\Enums;

enum PlanFeature: string
{
    case Crm = 'crm';
    case Properties = 'properties';
    case Bookings = 'bookings';
    case Revenue = 'revenue';
    case Reports = 'reports';
    case Teams = 'teams';
    case Automations = 'automations';
    case TeamInbox = 'team_inbox';
    case InsyteAi = 'insyte_ai';
    case WhatsApp = 'whatsapp';
    case Microsites = 'microsites';
    case CustomDomains = 'custom_domains';
    case Integrations = 'integrations';

    public function label(): string
    {
        return match ($this) {
            self::Crm => __('CRM'),
            self::Properties => __('Properties'),
            self::Bookings => __('Bookings'),
            self::Revenue => __('Revenue'),
            self::Reports => __('Reports'),
            self::Teams => __('Teams'),
            self::Automations => __('Automations'),
            self::TeamInbox => __('Team Inbox'),
            self::InsyteAi => __('InSyte AI'),
            self::WhatsApp => __('WhatsApp'),
            self::Microsites => __('Property Microsites'),
            self::CustomDomains => __('Custom Domains'),
            self::Integrations => __('Integrations'),
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Crm => __('Leads, activities, and tasks.'),
            self::Properties => __('Property catalog.'),
            self::Bookings => __('Bookings and agreements.'),
            self::Revenue => __('Revenue, invoices, and payouts.'),
            self::Reports => __('Reports and analytics.'),
            self::Teams => __('Sales teams and performance.'),
            self::Automations => __('Workflows and message templates.'),
            self::TeamInbox => __('Team inbox messaging.'),
            self::InsyteAi => __('InSyte AI OS.'),
            self::WhatsApp => __('WhatsApp messages from leads.'),
            self::Microsites => __('Public property microsites.'),
            self::CustomDomains => __('Custom CRM and website domains.'),
            self::Integrations => __('Lead capture integrations.'),
        };
    }

    public function isPackable(): bool
    {
        return in_array($this, [
            self::Crm,
            self::Reports,
            self::Automations,
            self::InsyteAi,
        ], true);
    }

    /**
     * @return list<self>
     */
    public static function modules(): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $feature): bool => $feature !== self::Integrations,
        ));
    }
}
