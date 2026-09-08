<?php

namespace App\Enums;

enum QuotationStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::Sent => __('Sent'),
            self::Accepted => __('Accepted'),
            self::Rejected => __('Rejected'),
            self::Expired => __('Expired'),
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Draft => __('Quotation has been created but not sent.'),
            self::Sent => __('Quotation has been sent to the Channel Partner.'),
            self::Accepted => __('Channel Partner has accepted the quotation.'),
            self::Rejected => __('Channel Partner has rejected the quotation.'),
            self::Expired => __('Quotation passed its validity date without being accepted.'),
        };
    }
}
