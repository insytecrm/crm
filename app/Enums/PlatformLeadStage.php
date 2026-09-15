<?php

namespace App\Enums;

enum PlatformLeadStage: string
{
    case NewLead = 'new_lead';
    case Contacted = 'contacted';
    case Demo = 'demo';
    case Trial = 'trial';
    case Quoted = 'quoted';
    case Paid = 'paid';
    case Onboarding = 'onboarding';
    case Live = 'live';
    case Retention = 'retention';

    public function label(): string
    {
        return match ($this) {
            self::NewLead => __('New Lead'),
            self::Contacted => __('Contacted'),
            self::Demo => __('Demo'),
            self::Trial => __('Trial'),
            self::Quoted => __('Quoted'),
            self::Paid => __('Paid'),
            self::Onboarding => __('Onboarding'),
            self::Live => __('Live'),
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
            self::Demo,
            self::Trial,
            self::Quoted,
            self::Paid,
            self::Onboarding,
            self::Live,
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
        return $this === self::Demo;
    }

    public function hasAccount(): bool
    {
        return in_array($this, [self::Trial, self::Onboarding, self::Live, self::Retention], true);
    }
}
