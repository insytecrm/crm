<?php

namespace App\Http\Controllers\Platform;

use App\Actions\EnsurePlatformUtilityEmailTemplates;
use App\Actions\UpsertUtilityMailSetting;
use App\Enums\PlatformUtilityEmailTemplateType;
use App\Enums\UtilityCredentialsDeliveryMode;
use App\Enums\UtilityMailEncryption;
use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\SendPlatformUtilityTestMailRequest;
use App\Http\Requests\Platform\UpdatePlatformMailSettingRequest;
use App\Http\Requests\Platform\UpdatePlatformUtilityEmailTemplateRequest;
use App\Mail\UtilityTemplatedMail;
use App\Models\PlatformMailSetting;
use App\Models\PlatformUtilityEmailTemplate;
use App\Support\ConfigureUtilitySmtpMailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class UtilitiesController extends Controller
{
    public function show(EnsurePlatformUtilityEmailTemplates $ensurePlatformUtilityEmailTemplates): View
    {
        $ensurePlatformUtilityEmailTemplates->handle();

        return view('platform.utilities.index', [
            'setting' => PlatformMailSetting::current(),
            'templates' => PlatformUtilityEmailTemplate::query()->orderBy('id')->get(),
            'encryptions' => UtilityMailEncryption::cases(),
            'deliveryModes' => UtilityCredentialsDeliveryMode::cases(),
            'templateTypes' => PlatformUtilityEmailTemplateType::cases(),
        ]);
    }

    public function updateMailSetting(
        UpdatePlatformMailSettingRequest $request,
        UpsertUtilityMailSetting $upsertUtilityMailSetting,
    ): RedirectResponse {
        $validated = $request->validated();

        $upsertUtilityMailSetting->handle(
            PlatformMailSetting::current(),
            [
                'host' => $validated['host'],
                'port' => (int) $validated['port'],
                'username' => $validated['username'],
                'password' => $request->input('password'),
                'from_email' => $validated['from_email'],
                'from_name' => $validated['from_name'],
                'encryption' => $validated['encryption'] instanceof \BackedEnum
                    ? $validated['encryption']->value
                    : (string) $validated['encryption'],
                'credentials_delivery_mode' => $validated['credentials_delivery_mode'] instanceof \BackedEnum
                    ? $validated['credentials_delivery_mode']->value
                    : (string) $validated['credentials_delivery_mode'],
                'remove_signature' => $request->boolean('remove_signature'),
            ],
            $request->file('signature'),
            PlatformMailSetting::class,
        );

        return redirect()
            ->route('platform.utilities')
            ->with('status', __('SMTP settings saved.'));
    }

    public function updateTemplate(
        UpdatePlatformUtilityEmailTemplateRequest $request,
        PlatformUtilityEmailTemplate $template,
    ): RedirectResponse {
        $template->update([
            'subject' => $request->validated('subject'),
            'body' => $request->validated('body'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('platform.utilities')
            ->with('status', __('Template updated.'));
    }

    public function sendTest(
        SendPlatformUtilityTestMailRequest $request,
        ConfigureUtilitySmtpMailer $configureUtilitySmtpMailer,
    ): RedirectResponse {
        $setting = PlatformMailSetting::current();

        if ($setting === null || ! $setting->isConfigured()) {
            return redirect()
                ->route('platform.utilities')
                ->with('status', __('Save SMTP settings with a password before sending a test email.'));
        }

        try {
            $mailer = $configureUtilitySmtpMailer->handle($setting);

            Mail::mailer($mailer)->to($request->validated('test_email'))->send(new UtilityTemplatedMail(
                emailSubject: __('InSyte CRM utilities test email'),
                emailBody: __('This is a test email from platform Utilities SMTP settings.'),
                fromAddress: $setting->from_email,
                fromName: $setting->from_name,
            ));
        } catch (Throwable $exception) {
            return redirect()
                ->route('platform.utilities')
                ->with('status', __('Test email failed: :message', ['message' => $exception->getMessage()]));
        }

        return redirect()
            ->route('platform.utilities')
            ->with('status', __('Test email sent.'));
    }
}
