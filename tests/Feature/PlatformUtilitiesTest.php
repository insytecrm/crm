<?php

use App\Enums\UtilityCredentialsDeliveryMode;
use App\Enums\UtilityMailEncryption;
use App\Mail\UtilityTemplatedMail;
use App\Models\PlatformMailSetting;
use App\Models\PlatformUtilityEmailTemplate;
use App\Models\User;
use App\Support\ConfigureUtilitySmtpMailer;
use Illuminate\Support\Facades\Mail;

test('super admins can open utilities and see seeded templates', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get(route('platform.utilities'))
        ->assertOk()
        ->assertSee('SMTP configuration')
        ->assertSee('Partner login credentials')
        ->assertSee('Partner access reset')
        ->assertSee('Quotation sent')
        ->assertSee('Invoice sent');

    expect(PlatformUtilityEmailTemplate::query()->count())->toBe(4);
});

test('super admins can save platform smtp settings', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->put(route('platform.utilities.mail.update'), [
            'host' => 'smtp.mail.test',
            'port' => 587,
            'username' => 'noreply@insyte.test',
            'password' => 'secret-pass',
            'from_email' => 'noreply@insyte.test',
            'from_name' => 'InSyte CRM',
            'encryption' => UtilityMailEncryption::Tls->value,
            'credentials_delivery_mode' => UtilityCredentialsDeliveryMode::Always->value,
        ])
        ->assertRedirect(route('platform.utilities'))
        ->assertSessionHas('status');

    $setting = PlatformMailSetting::current();

    expect($setting)->not->toBeNull()
        ->and($setting->host)->toBe('smtp.mail.test')
        ->and($setting->password)->toBe('secret-pass')
        ->and($setting->alwaysSends())->toBeTrue()
        ->and($setting->isConfigured())->toBeTrue();
});

test('saving smtp again can leave password blank to keep existing', function () {
    $admin = User::factory()->superAdmin()->create();

    PlatformMailSetting::query()->create([
        'host' => 'smtp.mail.test',
        'port' => 587,
        'username' => 'noreply@insyte.test',
        'password' => 'original-pass',
        'from_email' => 'noreply@insyte.test',
        'from_name' => 'InSyte',
        'encryption' => UtilityMailEncryption::Tls,
        'credentials_delivery_mode' => UtilityCredentialsDeliveryMode::Ask,
    ]);

    $this->actingAs($admin)
        ->put(route('platform.utilities.mail.update'), [
            'host' => 'smtp.updated.test',
            'port' => 465,
            'username' => 'noreply@insyte.test',
            'password' => '',
            'from_email' => 'noreply@insyte.test',
            'from_name' => 'InSyte',
            'encryption' => UtilityMailEncryption::Ssl->value,
            'credentials_delivery_mode' => UtilityCredentialsDeliveryMode::Ask->value,
        ])
        ->assertRedirect(route('platform.utilities'));

    expect(PlatformMailSetting::current()->password)->toBe('original-pass')
        ->and(PlatformMailSetting::current()->host)->toBe('smtp.updated.test');
});

test('platform test email uses configured smtp mailer', function () {
    Mail::fake();

    $admin = User::factory()->superAdmin()->create();

    PlatformMailSetting::query()->create([
        'host' => 'smtp.mail.test',
        'port' => 587,
        'username' => 'noreply@insyte.test',
        'password' => 'secret-pass',
        'from_email' => 'noreply@insyte.test',
        'from_name' => 'InSyte CRM',
        'encryption' => UtilityMailEncryption::Tls,
        'credentials_delivery_mode' => UtilityCredentialsDeliveryMode::Ask,
    ]);

    $this->actingAs($admin)
        ->post(route('platform.utilities.test'), [
            'test_email' => 'admin@example.test',
        ])
        ->assertRedirect(route('platform.utilities'))
        ->assertSessionHas('status', __('Test email sent.'));

    Mail::assertSent(UtilityTemplatedMail::class, function (UtilityTemplatedMail $mail): bool {
        return $mail->hasTo('admin@example.test');
    });

    expect(config('mail.mailers.'.ConfigureUtilitySmtpMailer::MailerName.'.scheme'))->toBe('smtp');
});
