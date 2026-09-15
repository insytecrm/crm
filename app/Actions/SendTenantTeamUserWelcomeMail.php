<?php

namespace App\Actions;

use App\Enums\TenantUtilityEmailTemplateType;
use App\Models\User;
use App\Models\UtilityEmailTemplate;
use App\Models\UtilityMailSetting;

class SendTenantTeamUserWelcomeMail
{
    public function __construct(
        private EnsureTenantUtilityEmailTemplates $ensureTenantUtilityEmailTemplates,
        private SendUtilityTemplatedMail $sendUtilityTemplatedMail,
    ) {}

    public function handle(User $user, string $plainPassword, bool $requested): bool
    {
        $setting = UtilityMailSetting::current();

        if ($setting === null || ! $setting->isConfigured()) {
            return false;
        }

        if ($setting->asksBeforeSending() && ! $requested) {
            return false;
        }

        if ($setting->alwaysSends() || $requested) {
            $this->ensureTenantUtilityEmailTemplates->handle();

            $template = UtilityEmailTemplate::query()
                ->where('type', TenantUtilityEmailTemplateType::TeamUserWelcome)
                ->first();

            if ($template === null) {
                return false;
            }

            $tenant = tenant();
            $loginUrl = route('tenant.login', ['tenant' => tenant('id')]);

            return $this->sendUtilityTemplatedMail->handle(
                $setting,
                $template,
                $user->email,
                [
                    'user.name' => $user->name,
                    'user.email' => $user->email,
                    'user.password' => $plainPassword,
                    'login.url' => $loginUrl,
                    'company.name' => is_object($tenant) ? (string) $tenant->name : config('app.name'),
                ],
            );
        }

        return false;
    }
}
