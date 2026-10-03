<?php

namespace App\Actions;

use App\Enums\QuotationStatus;
use App\Models\Quotation;

class ExpireQuotations
{
    public function handle(): int
    {
        $count = 0;

        Quotation::query()
            ->dueForExpiry()
            ->orderBy('id')
            ->each(function (Quotation $quotation) use (&$count): void {
                $quotation->update([
                    'status' => QuotationStatus::Expired,
                    'expired_at' => $quotation->expired_at ?? now(),
                ]);
                $count++;
            });

        return $count;
    }
}
