<?php

namespace App\Actions;

use App\Enums\QuotationStatus;
use App\Models\Quotation;
use Illuminate\Validation\ValidationException;

class AcceptQuotation
{
    public function handle(Quotation $quotation, ?string $acceptedByName = null): Quotation
    {
        if (! $quotation->canMarkAccepted()) {
            throw ValidationException::withMessages([
                'quotation' => __('Only draft or sent quotations can be marked accepted.'),
            ]);
        }

        $name = trim((string) ($acceptedByName ?: ''));
        if ($name === '') {
            $name = is_string($quotation->owner_name) && $quotation->owner_name !== ''
                ? $quotation->owner_name
                : (string) ($quotation->company_name ?: __('Prospect'));
        }

        $quotation->update([
            'status' => QuotationStatus::Accepted,
            'accepted_at' => now(),
            'accepted_by_name' => $name,
            'rejected_at' => null,
            'expired_at' => null,
        ]);

        return $quotation->refresh();
    }
}
