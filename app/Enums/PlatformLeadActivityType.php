<?php

namespace App\Enums;

enum PlatformLeadActivityType: string
{
    case LeadCreated = 'lead_created';
    case StageChanged = 'stage_changed';
    case NoteAdded = 'note_added';
    case DemoScheduled = 'demo_scheduled';
    case FieldUpdated = 'field_updated';
    case QuotationCreated = 'quotation_created';
    case QuotationSent = 'quotation_sent';
    case QuotationAccepted = 'quotation_accepted';
    case AccountLinked = 'account_linked';
    case NextActionUpdated = 'next_action_updated';

    public function label(): string
    {
        return match ($this) {
            self::LeadCreated => __('Lead created'),
            self::StageChanged => __('Stage changed'),
            self::NoteAdded => __('Note added'),
            self::DemoScheduled => __('Demo scheduled'),
            self::FieldUpdated => __('Lead updated'),
            self::QuotationCreated => __('Quotation created'),
            self::QuotationSent => __('Quotation sent'),
            self::QuotationAccepted => __('Quotation accepted'),
            self::AccountLinked => __('Account linked'),
            self::NextActionUpdated => __('Next action updated'),
        };
    }
}
