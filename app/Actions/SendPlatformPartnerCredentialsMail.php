<?php

namespace App\Actions;

use App\Enums\PlatformUtilityEmailTemplateType;
use App\Models\PlatformMailSetting;
use App\Models\PlatformUtilityEmailTemplate;

class SendPlatformPartnerCredentialsMail
{
    public function __construct(
        private EnsurePlatformUtilityEmailTemplates $ensurePlatformUtilityEmailTemplates,
        private SendUtilityTemplatedMail $sendUtilityTemplatedMail,
    ) {}

    /**
     * @param  array{
     *     admin_name: string,
     *     admin_email: string,
     *     admin_password: string,
     *     login_url: string,
     *     company_name: string
     * }  $payload
     * @return bool True when mail was attempted and accepted by the mailer.
     */
    public function handle(array $payload, bool $requested): bool
    {
        $setting = PlatformMailSetting::current();

        if ($setting === null || ! $setting->isConfigured()) {
            return false;
        }

        if ($setting->asksBeforeSending() && ! $requested) {
            return false;
        }

        if ($setting->alwaysSends() || $requested) {
            $this->ensurePlatformUtilityEmailTemplates->handle();

            $template = PlatformUtilityEmailTemplate::query()
                ->where('type', PlatformUtilityEmailTemplateType::PartnerCredentials)
                ->first();

            if ($template === null) {
                return false;
            }

            return $this->sendUtilityTemplatedMail->handle(
                $setting,
                $template,
                $payload['admin_email'],
                [
                    'admin.name' => $payload['admin_name'],
                    'admin.email' => $payload['admin_email'],
                    'admin.password' => $payload['admin_password'],
                    'login.url' => $payload['login_url'],
                    'company.name' => $payload['company_name'],
                ],
            );
        }

        return false;
    }
}
