<?php

namespace App\Enums;

enum LeadActivityType: string
{
    case LeadCreated = 'lead_created';
    case CallMade = 'call_made';
    case WhatsAppMessage = 'whatsapp_message';
    case FollowUpScheduled = 'follow_up_scheduled';
    case FollowUpCompleted = 'follow_up_completed';
    case SiteVisitScheduled = 'site_visit_scheduled';
    case SiteVisitCompleted = 'site_visit_completed';
    case StatusChanged = 'status_changed';
    case PropertyShared = 'property_shared';
    case NoteAdded = 'note_added';
    case TaskCreated = 'task_created';
    case TaskCompleted = 'task_completed';
    case BookingCreated = 'booking_created';
    case AgreementMarked = 'agreement_marked';
    case InvoiceCreated = 'invoice_created';
    case PayoutReceived = 'payout_received';
    case LeadMerged = 'lead_merged';

    public function label(): string
    {
        return match ($this) {
            self::LeadCreated => __('Lead Created'),
            self::CallMade => __('Call Made'),
            self::WhatsAppMessage => __('WhatsApp Message'),
            self::FollowUpScheduled => __('Follow-up Scheduled'),
            self::FollowUpCompleted => __('Follow-up Completed'),
            self::SiteVisitScheduled => __('Site Visit Scheduled'),
            self::SiteVisitCompleted => __('Site Visit Completed'),
            self::StatusChanged => __('Status Changed'),
            self::PropertyShared => __('Property Shared'),
            self::NoteAdded => __('Note Added'),
            self::TaskCreated => __('Task Created'),
            self::TaskCompleted => __('Task Completed'),
            self::BookingCreated => __('Booking Created'),
            self::AgreementMarked => __('Agreement Marked'),
            self::InvoiceCreated => __('Invoice Created'),
            self::PayoutReceived => __('Payout Received'),
            self::LeadMerged => __('Lead Merged'),
        };
    }
}
