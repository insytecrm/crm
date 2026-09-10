<?php

namespace App\Enums;

enum PlatformLeadStage: string
{
    case NewLead = 'new_lead';
    case Contacted = 'contacted';
    case DemoScheduled = 'demo_scheduled';
    case DemoCompleted = 'demo_completed';
    case QuotationSent = 'quotation_sent';
    case QuotationAccepted = 'quotation_accepted';
    case Onboarding = 'onboarding';
    case Handover = 'handover';
    case ClientLive = 'client_live';
    case Retention = 'retention';

    public function label(): string
    {
        return match ($this) {
            self::NewLead => __('New Lead'),
            self::Contacted => __('Contacted'),
            self::DemoScheduled => __('Demo Scheduled'),
            self::DemoCompleted => __('Demo Completed'),
            self::QuotationSent => __('Quotation Sent'),
            self::QuotationAccepted => __('Quotation Accepted'),
            self::Onboarding => __('Onboarding'),
            self::Handover => __('Handover'),
            self::ClientLive => __('Client Live'),
            self::Retention => __('Retention'),
        };
    }

    /**
     * @return list<self>
     */
    public static function orderedCases(): array
    {
        return [
            self::NewLead,
            self::Contacted,
            self::DemoScheduled,
            self::DemoCompleted,
            self::QuotationSent,
            self::QuotationAccepted,
            self::Onboarding,
            self::Handover,
            self::ClientLive,
            self::Retention,
        ];
    }

    public function orderIndex(): int
    {
        foreach (self::orderedCases() as $index => $stage) {
            if ($stage === $this) {
                return $index;
            }
        }

        return 0;
    }

    public function requiresDemoFields(): bool
    {
        return $this === self::DemoScheduled;
    }

    public function hasAccount(): bool
    {
        return in_array($this, [self::Onboarding, self::Handover, self::ClientLive, self::Retention], true);
    }
}
