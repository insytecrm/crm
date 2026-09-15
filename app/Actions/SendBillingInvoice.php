<?php

namespace App\Actions;

use App\Models\BillingInvoice;

class SendBillingInvoice
{
    public function __construct(
        private SendPlatformBillingInvoiceMail $sendPlatformBillingInvoiceMail,
    ) {}

    /**
     * @return array{invoice: BillingInvoice, mail_sent: bool}
     */
    public function handle(BillingInvoice $invoice, string $email): array
    {
        $invoice->update([
            'sent_at' => now(),
        ]);

        $invoice = $invoice->refresh();
        $mailSent = $this->sendPlatformBillingInvoiceMail->handle($invoice, $email);

        return [
            'invoice' => $invoice,
            'mail_sent' => $mailSent,
        ];
    }
}
