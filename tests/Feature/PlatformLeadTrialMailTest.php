<?php

use App\Actions\CreateTenant;
use App\Enums\UtilityCredentialsDeliveryMode;
use App\Enums\UtilityMailEncryption;
use App\Mail\UtilityTemplatedMail;
use App\Models\Plan;
use App\Models\PlatformLead;
use App\Models\PlatformMailSetting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\mock;

test('starting a trial sends login details through utility smtp when requested', function () {
    Mail::fake();

    $admin = User::factory()->superAdmin()->create();
    $plan = Plan::query()->where('key', 'growth')->firstOrFail();
    $lead = PlatformLead::factory()->create([
        'company_name' => 'Trial Mail Co',
        'contact_person' => 'Trial Owner',
        'email' => 'owner@trialmail.test',
        'phone' => '9888888888',
    ]);
    $tenant = Tenant::query()->create([
        'id' => 'provisionedtrialmailco',
        'name' => 'Trial Mail Co',
        'email' => 'owner@trialmail.test',
    ]);
    $startingTransactionLevel = DB::connection()->transactionLevel();

    mock(CreateTenant::class)
        ->shouldReceive('handle')
        ->once()
        ->andReturnUsing(function () use ($startingTransactionLevel, $tenant): Tenant {
            expect(DB::connection()->transactionLevel())->toBe($startingTransactionLevel);

            return $tenant;
        });

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
        ->from(route('platform.leads'))
        ->post(route('platform.leads.start-trial', $lead), [
            'company_name' => 'Trial Mail Co',
            'email' => 'owner@trialmail.test',
            'phone' => '9888888888',
            'plan_id' => $plan->id,
            'trial_days' => 7,
            'slug' => 'trialmailco',
            'email_credentials' => '1',
        ])
        ->assertRedirect()
        ->assertSessionHas('status', __('Trial started and login details emailed.'));

    Mail::assertSent(UtilityTemplatedMail::class, function (UtilityTemplatedMail $mail): bool {
        return $mail->hasTo('owner@trialmail.test');
    });
});

test('starting a trial does not send mail when utilities smtp is not configured', function () {
    Mail::fake();

    $admin = User::factory()->superAdmin()->create();
    $plan = Plan::query()->where('key', 'growth')->firstOrFail();
    $lead = PlatformLead::factory()->create([
        'email' => 'owner@trialmail.test',
    ]);

    $this->actingAs($admin)
        ->post(route('platform.leads.start-trial', $lead), [
            'company_name' => $lead->company_name,
            'email' => 'owner@trialmail.test',
            'plan_id' => $plan->id,
            'trial_days' => 7,
            'slug' => 'notrialmail',
            'email_credentials' => '1',
        ])
        ->assertRedirect()
        ->assertSessionHas('status', __('Trial started. Share the login details with the client.'));

    Mail::assertNothingSent();
});
