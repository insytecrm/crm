<?php

namespace App\Enums;

enum LeadListingFilter: string
{
    case All = 'all';
    case Priority = 'priority';
    case New = 'new';
    case FollowUpDue = 'follow_up_due';
    case SiteVisitsScheduled = 'site_visits_scheduled';
    case Unassigned = 'unassigned';
    case Converted = 'converted';
    case Lost = 'lost';

    public function routeName(): string
    {
        return match ($this) {
            self::All => 'tenant.leads.index',
            self::Priority => 'tenant.leads.priority.index',
            self::New,
            self::FollowUpDue,
            self::SiteVisitsScheduled,
            self::Unassigned,
            self::Converted,
            self::Lost => 'tenant.leads.index',
        };
    }

    /**
     * @param  array<string, mixed>  $query
     */
    public function indexUrl(string $search = '', array $query = []): string
    {
        $parameters = $query;

        if ($this !== self::All) {
            $parameters['filter'] = $this->value;
        }

        if ($search !== '') {
            $parameters['search'] = $search;
        }

        return route('tenant.leads.index', $parameters);
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function redirectParameters(string $search = '', array $query = []): array
    {
        $parameters = $query;

        if ($this !== self::All) {
            $parameters['filter'] = $this->value;
        }

        if ($search !== '') {
            $parameters['search'] = $search;
        }

        return $parameters;
    }

    public function title(): string
    {
        return match ($this) {
            self::All => __('Leads'),
            self::Priority => __('Priority Leads'),
            self::New => __('New Leads'),
            self::FollowUpDue => __('Follow-up Due'),
            self::SiteVisitsScheduled => __('Site Visits Scheduled'),
            self::Unassigned => __('Unassigned Leads'),
            self::Converted => __('Converted Leads'),
            self::Lost => __('Lost Leads'),
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::All => __('Manage and track your sales pipeline'),
            self::Priority => __('High-score and urgent leads that need attention now'),
            self::New => __('Leads with a new status'),
            self::FollowUpDue => __('Leads that need follow-up now'),
            self::SiteVisitsScheduled => __('Leads with upcoming site visits'),
            self::Unassigned => __('Leads waiting to be assigned to a team member'),
            self::Converted => __('Leads that have been successfully converted'),
            self::Lost => __('Leads that were closed without conversion'),
        };
    }

    public function emptyMessage(): string
    {
        return match ($this) {
            self::All => __('No leads found.'),
            self::Priority => __('No priority leads right now.'),
            self::New => __('No new leads right now.'),
            self::FollowUpDue => __('No follow-ups due right now.'),
            self::SiteVisitsScheduled => __('No site visits scheduled.'),
            self::Unassigned => __('No unassigned leads right now.'),
            self::Converted => __('No converted leads yet.'),
            self::Lost => __('No lost leads yet.'),
        };
    }

    public static function fromRequest(?string $value): self
    {
        return self::tryFrom($value) ?? self::All;
    }
}
