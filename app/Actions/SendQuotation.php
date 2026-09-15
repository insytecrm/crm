<?php

namespace App\Actions;

use App\Enums\QuotationStatus;
use App\Models\Quotation;
use Illuminate\Validation\ValidationException;

class SendQuotation
{
    public function __construct(
        private SyncPlatformLeadFromQuotation $syncLead,
        private SendPlatformQuotationMail $sendPlatformQuotationMail,
    ) {}

    /**
     * @return array{quotation: Quotation, mail_sent: bool}
     */
    public function handle(Quotation $quotation, string $email): array
    {
        if (! $quotation->canSend()) {
            throw ValidationException::withMessages([
                'quotation' => __('Only draft or sent quotations can be sent.'),
            ]);
        }

        $quotation->update([
            'status' => QuotationStatus::Sent,
            'sent_at' => now(),
        ]);

        $quotation = $quotation->refresh();
        $this->syncLead->afterSent($quotation);

        $mailSent = $this->sendPlatformQuotationMail->handle($quotation, $email);

        return [
            'quotation' => $quotation,
            'mail_sent' => $mailSent,
        ];
    }
}
