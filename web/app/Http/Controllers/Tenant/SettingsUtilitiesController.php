<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\EnsureTenantUtilityEmailTemplates;
use App\Actions\UpsertUtilityMailSetting;
use App\Enums\TenantPermission;
use App\Enums\TenantUtilityEmailTemplateType;
use App\Enums\UtilityCredentialsDeliveryMode;
use App\Enums\UtilityMailEncryption;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\SendUtilityTestMailRequest;
use App\Http\Requests\Tenant\UpdateUtilityEmailTemplateRequest;
use App\Http\Requests\Tenant\UpdateUtilityMailSettingRequest;
use App\Mail\UtilityTemplatedMail;
use App\Models\UtilityEmailTemplate;
use App\Models\UtilityMailSetting;
use App\Support\ConfigureUtilitySmtpMailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class SettingsUtilitiesController extends Controller
{
    public function show(EnsureTenantUtilityEmailTemplates $ensureTenantUtilityEmailTemplates): View
    {
        abort_unless(auth()->user()?->hasPermission(TenantPermission::IntegrationsView), 403);

        $ensureTenantUtilityEmailTemplates->handle();

        return view('tenant.settings.integrations.utilities', [
            'setting' => UtilityMailSetting::current(),
            'templates' => UtilityEmailTemplate::query()->orderBy('id')->get(),
            'encryptions' => UtilityMailEncryption::cases(),
            'deliveryModes' => UtilityCredentialsDeliveryMode::cases(),
            'templateTypes' => TenantUtilityEmailTemplateType::cases(),
            'canManage' => auth()->user()->hasPermission(TenantPermission::IntegrationsManage),
        ]);
    }

    public function updateMailSetting(
        UpdateUtilityMailSettingRequest $request,
        UpsertUtilityMailSetting $upsertUtilityMailSetting,
    ): RedirectResponse {
        $validated = $request->validated();

        $upsertUtilityMailSetting->handle(
            UtilityMailSetting::current(),
            [
                'host' => $validated['host'],
                'port' => (int) $validated['port'],
                'username' => $validated['username'],
                'password' => $request->input('password'),
                'from_email' => $validated['from_email'],
                'from_name' => $validated['from_name'],
                'reply_to_email' => $validated['reply_to_email'] ?? null,
                'reply_to_name' => $validated['reply_to_name'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'is_active' => $request->boolean('is_active', true),
                'encryption' => $validated['encryption'] instanceof \BackedEnum
                    ? $validated['encryption']->value
                    : (string) $validated['encryption'],
                'credentials_delivery_mode' => $validated['credentials_delivery_mode'] instanceof \BackedEnum
                    ? $validated['credentials_delivery_mode']->value
                    : (string) $validated['credentials_delivery_mode'],
                'remove_signature' => $request->boolean('remove_signature'),
            ],
            $request->file('signature'),
            UtilityMailSetting::class,
        );

        return redirect()
            ->route('tenant.settings.integrations.utilities')
            ->with('status', __('Configuration saved.'));
    }

    public function updateTemplate(
        UpdateUtilityEmailTemplateRequest $request,
        UtilityEmailTemplate $template,
    ): RedirectResponse {
        $template->update([
            'subject' => $request->validated('subject'),
            'body' => $request->validated('body'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('tenant.settings.integrations.utilities')
            ->with('status', __('Template updated.'));
    }

    public function sendTest(
        SendUtilityTestMailRequest $request,
        ConfigureUtilitySmtpMailer $configureUtilitySmtpMailer,
    ): RedirectResponse {
        $setting = UtilityMailSetting::current();

        if ($setting === null || ! $setting->isConfigured()) {
            return redirect()
                ->route('tenant.settings.integrations.utilities')
                ->with('status', __('Save SMTP settings with a password before sending a test email.'));
        }

        try {
            $mailer = $configureUtilitySmtpMailer->handle($setting);

            Mail::mailer($mailer)->to($request->validated('test_email'))->send(new UtilityTemplatedMail(
                emailSubject: __('InSyte CRM utilities test email'),
                emailBody: __('This is a test email from workspace Utilities SMTP settings.'),
                fromAddress: $setting->from_email,
                fromName: $setting->from_name,
            ));
        } catch (Throwable $exception) {
            return redirect()
                ->route('tenant.settings.integrations.utilities')
                ->with('status', __('Test email failed: :message', ['message' => $exception->getMessage()]));
        }

        return redirect()
            ->route('tenant.settings.integrations.utilities')
            ->with('status', __('Test email sent.'));
    }
}
