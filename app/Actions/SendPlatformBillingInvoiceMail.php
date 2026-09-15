<?php

namespace App\Actions;

use App\Enums\PlatformUtilityEmailTemplateType;
use App\Models\BillingInvoice;
use App\Models\PlatformMailSetting;
use App\Models\PlatformUtilityEmailTemplate;
use App\Support\Platform\BillingMoney;

class SendPlatformBillingInvoiceMail
{
    public function __construct(
        private EnsurePlatformUtilityEmailTemplates $ensurePlatformUtilityEmailTemplates,
        private SendUtilityTemplatedMail $sendUtilityTemplatedMail,
    ) {}

    public function handle(BillingInvoice $invoice, string $toEmail): bool
    {
        $setting = PlatformMailSetting::current();

        if ($setting === null || ! $setting->isConfigured()) {
            return false;
        }

        $this->ensurePlatformUtilityEmailTemplates->handle();

        $template = PlatformUtilityEmailTemplate::query()
            ->where('type', PlatformUtilityEmailTemplateType::InvoiceSent)
            ->first();

        if ($template === null) {
            return false;
        }

        $invoice->loadMissing(['tenant', 'plan']);

        return $this->sendUtilityTemplatedMail->handle(
            $setting,
            $template,
            $toEmail,
            $this->mergeValues($invoice),
        );
    }

    /**
     * @return array<string, string>
     */
    private function mergeValues(BillingInvoice $invoice): array
    {
        $timezone = config('app.timezone');
        $contactName = filled($invoice->billed_to_contact)
            ? $invoice->billed_to_contact
            : ($invoice->tenant?->name ?? __('there'));

        return [
            'contact.name' => $contactName,
            'company.name' => $invoice->tenant?->name ?? $invoice->billed_to_name ?? '—',
            'invoice.number' => $invoice->number,
            'plan.name' => $invoice->plan?->name ?? '—',
            'billing.cycle' => $invoice->billing_cycle?->label() ?? '—',
            'invoice.total' => BillingMoney::format((int) $invoice->total),
            'invoice.due_at' => $invoice->due_at?->timezone($timezone)->format('d M Y') ?? '—',
            'invoice.period_start' => $invoice->period_start?->timezone($timezone)->format('d M Y') ?? '—',
            'invoice.period_end' => $invoice->period_end?->timezone($timezone)->format('d M Y') ?? '—',
        ];
    }
}
