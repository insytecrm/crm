<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UtilityTemplatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $emailSubject,
        public string $emailBody,
        public string $fromAddress,
        public string $fromName,
        public ?string $signatureUrl = null,
        public ?string $replyToAddress = null,
        public ?string $replyToName = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address($this->fromAddress, $this->fromName),
            subject: $this->emailSubject,
            replyTo: filled($this->replyToAddress)
                ? [new Address($this->replyToAddress, $this->replyToName ?: '')]
                : [],
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: $this->renderedHtml(),
        );
    }

    private function renderedHtml(): string
    {
        $body = nl2br(e($this->emailBody));

        $signature = '';
        if (filled($this->signatureUrl)) {
            $signature = '<p style="margin-top:24px"><img src="'.e($this->signatureUrl).'" alt="" style="max-width:240px;height:auto"></p>';
        }

        return <<<HTML
<!DOCTYPE html>
<html>
<body style="font-family:ui-sans-serif,system-ui,sans-serif;color:#0f172a;line-height:1.5;font-size:14px">
{$body}
{$signature}
</body>
</html>
HTML;
    }
}
