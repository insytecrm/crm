<?php

use App\Enums\BillingInvoiceStatus;
use App\Enums\UtilityCredentialsDeliveryMode;
use App\Enums\UtilityMailEncryption;
use App\Mail\UtilityTemplatedMail;
use App\Models\BillingInvoice;
use App\Models\PartnerSubscription;
use App\Models\Plan;
use App\Models\PlatformMailSetting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

test('super admins can send invoices through utility smtp', function () {
    Mail::fake();

    $admin = User::factory()->superAdmin()->create();
    $tenant = Tenant::factory()->create(['name' => 'ABC Realty', 'email' => 'billing@abcrealty.test']);
    $plan = Plan::factory()->create(['name' => 'Growth']);
    $subscription = PartnerSubscription::factory()->create([
        'tenant_id' => $tenant->id,
        'plan_id' => $plan->id,
    ]);

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

    $invoice = BillingInvoice::factory()->forSubscription($subscription)->create([
        'number' => 'INV-9001',
        'status' => BillingInvoiceStatus::Pending,
        'billed_to_email' => 'billing@abcrealty.test',
        'total' => 4999,
    ]);

    $this->actingAs($admin)
        ->from(route('platform.revenue.invoices.show', $invoice))
        ->post(route('platform.revenue.invoices.send', $invoice), [
            'email' => 'billing@abcrealty.test',
        ])
        ->assertRedirect(route('platform.revenue.invoices.show', $invoice))
        ->assertSessionHas('status', __('Invoice sent to :email.', ['email' => 'billing@abcrealty.test']));

    expect($invoice->refresh()->sent_at)->not->toBeNull();

    Mail::assertSent(UtilityTemplatedMail::class, function (UtilityTemplatedMail $mail): bool {
        return $mail->hasTo('billing@abcrealty.test');
    });
});
