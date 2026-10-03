<?php

namespace App\Actions;

use App\Enums\QuotationStatus;
use App\Models\Quotation;
use Illuminate\Validation\ValidationException;

class RejectQuotation
{
    public function handle(Quotation $quotation): Quotation
    {
        if (! $quotation->canMarkRejected()) {
            throw ValidationException::withMessages([
                'quotation' => __('Only draft or sent quotations can be marked rejected.'),
            ]);
        }

        $quotation->update([
            'status' => QuotationStatus::Rejected,
            'rejected_at' => now(),
            'accepted_at' => null,
            'accepted_by_name' => null,
            'expired_at' => null,
        ]);

        return $quotation->refresh();
    }
}
