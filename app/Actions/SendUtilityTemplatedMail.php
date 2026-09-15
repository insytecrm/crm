<?php

namespace App\Actions;

use App\Mail\UtilityTemplatedMail;
use App\Models\PlatformMailSetting;
use App\Models\PlatformUtilityEmailTemplate;
use App\Models\UtilityEmailTemplate;
use App\Models\UtilityMailSetting;
use App\Support\ConfigureUtilitySmtpMailer;
use App\Support\RenderMessageTemplate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Throwable;

class SendUtilityTemplatedMail
{
    public function __construct(
        private ConfigureUtilitySmtpMailer $configureUtilitySmtpMailer,
        private RenderMessageTemplate $renderMessageTemplate,
    ) {}

    /**
     * @param  array<string, mixed>  $values
     */
    public function handle(
        PlatformMailSetting|UtilityMailSetting $setting,
        PlatformUtilityEmailTemplate|UtilityEmailTemplate $template,
        string $toEmail,
        array $values,
    ): bool {
        if (! $setting->isConfigured() || ! $template->isActive()) {
            return false;
        }

        if (! filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        try {
            $mailer = $this->configureUtilitySmtpMailer->handle($setting);

            $subject = $this->renderMessageTemplate->handle($template->subject, $values);
            $body = $this->renderMessageTemplate->handle($template->body, $values);
            $signatureUrl = $this->signatureUrl($setting);

            Mail::mailer($mailer)->to($toEmail)->send(new UtilityTemplatedMail(
                emailSubject: $subject,
                emailBody: $body,
                fromAddress: $setting->from_email,
                fromName: $setting->from_name,
                signatureUrl: $signatureUrl,
                replyToAddress: $setting instanceof UtilityMailSetting ? $setting->reply_to_email : null,
                replyToName: $setting instanceof UtilityMailSetting ? $setting->reply_to_name : null,
            ));

            return true;
        } catch (Throwable $exception) {
            Log::warning('Utility email failed to send.', [
                'to' => $toEmail,
                'template' => $template->type instanceof \BackedEnum ? $template->type->value : $template->type,
                'message' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    private function signatureUrl(PlatformMailSetting|UtilityMailSetting $setting): ?string
    {
        if (! filled($setting->signature_path)) {
            return null;
        }

        if (! Storage::disk('public')->exists($setting->signature_path)) {
            return null;
        }

        return Storage::disk('public')->url($setting->signature_path);
    }
}
