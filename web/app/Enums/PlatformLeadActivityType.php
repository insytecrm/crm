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
    case TrialStarted = 'trial_started';
    case TrialEnded = 'trial_ended';
    case AccountLinked = 'account_linked';
    case Onboarded = 'onboarded';
    case SubscriptionActivated = 'subscription_activated';
    case InvoicePaid = 'invoice_paid';
    case MovedToRetention = 'moved_to_retention';
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
            self::TrialStarted => __('Trial started'),
            self::TrialEnded => __('Trial ended'),
            self::AccountLinked => __('Account linked'),
            self::Onboarded => __('Onboarded'),
            self::SubscriptionActivated => __('Subscription activated'),
            self::InvoicePaid => __('Invoice paid'),
            self::MovedToRetention => __('Moved to retention'),
            self::NextActionUpdated => __('Next action updated'),
        };
    }
}
