<?php

use App\Enums\TenantUtilityEmailTemplateType;
use App\Enums\UtilityCredentialsDeliveryMode;
use App\Enums\UtilityMailEncryption;
use App\Mail\UtilityTemplatedMail;
use App\Models\Role;
use App\Models\User;
use App\Models\UtilityEmailTemplate;
use App\Models\UtilityMailSetting;
use Illuminate\Support\Facades\Mail;

test('creating a user does not send mail when smtp is not configured', function () {
    Mail::fake();

    createTestTenant(['plan_key' => 'growth']);
    actingAsTenantUser();

    $role = Role::query()->where('slug', 'administrator')->firstOrFail();

    $this->post('/acme/settings/users', [
        'name' => 'New Member',
        'email' => 'member@acme.test',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role_id' => $role->id,
        'email_credentials' => '1',
    ])
        ->assertRedirect('/acme/settings?tab=users')
        ->assertSessionHas('status', __('User created.'));

    expect(User::query()->where('email', 'member@acme.test')->exists())->toBeTrue();

    Mail::assertNothingSent();
});

test('creating a user emails credentials when always mode is configured', function () {
    Mail::fake();

    createTestTenant(['plan_key' => 'growth']);
    actingAsTenantUser();

    UtilityMailSetting::query()->create([
        'host' => 'smtp.tenant.test',
        'port' => 587,
        'username' => 'crm@acme.test',
        'password' => 'tenant-secret',
        'from_email' => 'crm@acme.test',
        'from_name' => 'Acme CRM',
        'encryption' => UtilityMailEncryption::Tls,
        'credentials_delivery_mode' => UtilityCredentialsDeliveryMode::Always,
    ]);

    UtilityEmailTemplate::query()->create([
        'type' => TenantUtilityEmailTemplateType::TeamUserWelcome,
        'subject' => 'Welcome {{user.name}}',
        'body' => 'Login {{login.url}} password {{user.password}}',
        'is_active' => true,
    ]);

    $role = Role::query()->where('slug', 'administrator')->firstOrFail();

    $this->post('/acme/settings/users', [
        'name' => 'New Member',
        'email' => 'member@acme.test',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role_id' => $role->id,
    ])
        ->assertRedirect('/acme/settings?tab=users')
        ->assertSessionHas('status', __('User created.'));

    Mail::assertSent(UtilityTemplatedMail::class, function (UtilityTemplatedMail $mail): bool {
        return $mail->hasTo('member@acme.test')
            && str_contains($mail->emailBody, 'password123');
    });
});

test('ask mode only emails when checkbox is checked', function () {
    Mail::fake();

    createTestTenant(['plan_key' => 'growth']);
    actingAsTenantUser();

    UtilityMailSetting::query()->create([
        'host' => 'smtp.tenant.test',
        'port' => 587,
        'username' => 'crm@acme.test',
        'password' => 'tenant-secret',
        'from_email' => 'crm@acme.test',
        'from_name' => 'Acme CRM',
        'encryption' => UtilityMailEncryption::Tls,
        'credentials_delivery_mode' => UtilityCredentialsDeliveryMode::Ask,
    ]);

    UtilityEmailTemplate::query()->create([
        'type' => TenantUtilityEmailTemplateType::TeamUserWelcome,
        'subject' => 'Welcome',
        'body' => 'Hi',
        'is_active' => true,
    ]);

    $role = Role::query()->where('slug', 'administrator')->firstOrFail();

    $this->post('/acme/settings/users', [
        'name' => 'Skipped Mail',
        'email' => 'skipped@acme.test',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role_id' => $role->id,
    ])->assertRedirect('/acme/settings?tab=users');

    Mail::assertNothingSent();

    $this->post('/acme/settings/users', [
        'name' => 'Sent Mail',
        'email' => 'sent@acme.test',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role_id' => $role->id,
        'email_credentials' => '1',
    ])->assertRedirect('/acme/settings?tab=users');

    Mail::assertSent(UtilityTemplatedMail::class, function (UtilityTemplatedMail $mail): bool {
        return $mail->hasTo('sent@acme.test');
    });
});
