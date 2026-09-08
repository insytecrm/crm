<?php

namespace App\Actions;

use App\Enums\QuotationStatus;
use App\Models\Quotation;
use Illuminate\Validation\ValidationException;

class SendQuotation
{
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

        return $quotation->refresh();
    }
}
