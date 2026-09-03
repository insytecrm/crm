<?php

namespace App\Enums;

enum LeadClosingReason: string
{
    case Converted = 'converted';
    case Lost = 'lost';
    case NotInterested = 'not_interested';
    case BudgetIssue = 'budget_issue';
    case LocationIssue = 'location_issue';
    case AlreadyPurchased = 'already_purchased';
    case DuplicateLead = 'duplicate_lead';
    case NotReachable = 'not_reachable';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Converted => __('Converted'),
            self::Lost => __('Lost'),
            self::NotInterested => __('Not Interested'),
            self::BudgetIssue => __('Budget Issue'),
            self::LocationIssue => __('Location Issue'),
            self::AlreadyPurchased => __('Already Purchased'),
            self::DuplicateLead => __('Duplicate Lead'),
            self::NotReachable => __('Not Reachable'),
            self::Other => __('Other'),
        };
    }
}
