<?php

namespace App\Actions;

use App\Enums\QuotationStatus;
use App\Models\Quotation;
use Illuminate\Validation\ValidationException;

class SendQuotation
{
    public function __construct(
        private SyncPlatformLeadFromQuotation $syncLead,
    ) {}

    public function handle(Quotation $quotation): Quotation
    {
        if (! $quotation->canSend()) {
            throw ValidationException::withMessages([
                'quotation' => __('Only draft or sent quotations can be sent.'),
            ]);
        }

        $quotation->update([
            'status' => QuotationStatus::Sent,
            'sent_at' => $quotation->sent_at ?? now(),
        ]);

        $quotation = $quotation->refresh();
        $this->syncLead->afterSent($quotation);

        return $quotation;
    }
}
