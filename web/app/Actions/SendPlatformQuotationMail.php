<?php

namespace App\Actions;

use App\Enums\PlatformUtilityEmailTemplateType;
use App\Models\PlatformMailSetting;
use App\Models\PlatformUtilityEmailTemplate;
use App\Models\Quotation;

class SendPlatformQuotationMail
{
    public function __construct(
        private EnsurePlatformUtilityEmailTemplates $ensurePlatformUtilityEmailTemplates,
        private SendUtilityTemplatedMail $sendUtilityTemplatedMail,
    ) {}

    public function handle(Quotation $quotation, string $toEmail): bool
    {
        $setting = PlatformMailSetting::current();

        if ($setting === null || ! $setting->isConfigured()) {
            return false;
        }

        $this->ensurePlatformUtilityEmailTemplates->handle();

        $template = PlatformUtilityEmailTemplate::query()
            ->where('type', PlatformUtilityEmailTemplateType::QuotationSent)
            ->first();

        if ($template === null) {
            return false;
        }

        $quotation->loadMissing('plan');

        return $this->sendUtilityTemplatedMail->handle(
            $setting,
            $template,
            $toEmail,
            $this->mergeValues($quotation),
        );
    }

    /**
     * @return array<string, string>
     */
    private function mergeValues(Quotation $quotation): array
    {
        return [
            'owner.name' => filled($quotation->owner_name) ? $quotation->owner_name : __('there'),
            'company.name' => $quotation->companyDisplayName(),
            'quotation.number' => $quotation->number,
            'plan.name' => $quotation->plan?->name ?? '—',
            'billing.cycle' => $quotation->billing_cycle?->label() ?? '—',
            'quotation.amount' => $quotation->amountLabel(),
            'quotation.total' => $quotation->totalLabel(),
            'quotation.trial' => $quotation->trialLabel(),
            'quotation.valid_until' => $quotation->valid_until?->timezone(config('app.timezone'))->format('d M Y') ?? '—',
        ];
    }
}
