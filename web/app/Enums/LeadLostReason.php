<?php

namespace App\Enums;

enum LeadLostReason: string
{
    case NoResponse = 'no_response';
    case NotInterested = 'not_interested';
    case BudgetMismatch = 'budget_mismatch';
    case PriceTooHigh = 'price_too_high';
    case PropertyNotSuitable = 'property_not_suitable';
    case LocationNotSuitable = 'location_not_suitable';
    case ConfigurationNotSuitable = 'configuration_not_suitable';
    case ProjectNotSuitable = 'project_not_suitable';
    case PossessionTimelineIssue = 'possession_timeline_issue';
    case PaymentFinancingIssue = 'payment_financing_issue';
    case BoughtAnotherProperty = 'bought_another_property';
    case LostToCompetitor = 'lost_to_competitor';
    case NotReadyToBuy = 'not_ready_to_buy';
    case RequirementChanged = 'requirement_changed';
    case DuplicateLead = 'duplicate_lead';
    case InvalidFakeLead = 'invalid_fake_lead';
    case LeadUnqualified = 'lead_unqualified';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::NoResponse => __('No Response'),
            self::NotInterested => __('Not Interested'),
            self::BudgetMismatch => __('Budget Mismatch'),
            self::PriceTooHigh => __('Price Too High'),
            self::PropertyNotSuitable => __('Property Not Suitable'),
            self::LocationNotSuitable => __('Location Not Suitable'),
            self::ConfigurationNotSuitable => __('Configuration Not Suitable'),
            self::ProjectNotSuitable => __('Project Not Suitable'),
            self::PossessionTimelineIssue => __('Possession / Timeline Issue'),
            self::PaymentFinancingIssue => __('Payment / Financing Issue'),
            self::BoughtAnotherProperty => __('Bought Another Property'),
            self::LostToCompetitor => __('Lost to Competitor'),
            self::NotReadyToBuy => __('Not Ready to Buy'),
            self::RequirementChanged => __('Requirement Changed'),
            self::DuplicateLead => __('Duplicate Lead'),
            self::InvalidFakeLead => __('Invalid / Fake Lead'),
            self::LeadUnqualified => __('Lead Unqualified'),
            self::Other => __('Other'),
        };
    }

    /**
     * @return list<self>
     */
    public static function casesForForm(): array
    {
        return self::cases();
    }
}
