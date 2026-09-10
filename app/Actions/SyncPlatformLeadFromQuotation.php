<?php

namespace App\Actions;

use App\Enums\PlatformLeadActivityType;
use App\Enums\PlatformLeadStage;
use App\Enums\QuotationStatus;
use App\Models\PlatformLead;
use App\Models\Quotation;

class SyncPlatformLeadFromQuotation
{
    public function __construct(
        private LogPlatformLeadActivity $logActivity,
        private UpdatePlatformLeadStage $updateStage,
    ) {}

    public function afterCreated(Quotation $quotation): void
    {
        $lead = $quotation->platformLead;
        if ($lead === null) {
            return;
        }

        $this->logActivity->handle(
            $lead,
            PlatformLeadActivityType::QuotationCreated,
            __('Quotation :number created', ['number' => '#'.$quotation->number]),
        );

        if ($lead->stage->orderIndex() < PlatformLeadStage::QuotationSent->orderIndex()) {
            $this->updateStage->handle($lead, PlatformLeadStage::QuotationSent);
        }
    }

    public function afterSent(Quotation $quotation): void
    {
        $lead = $quotation->platformLead;
        if ($lead === null) {
            return;
        }

        $this->logActivity->handle(
            $lead,
            PlatformLeadActivityType::QuotationSent,
            __('Quotation :number sent', ['number' => '#'.$quotation->number]),
        );

        if ($lead->stage->orderIndex() < PlatformLeadStage::QuotationSent->orderIndex()) {
            $this->updateStage->handle($lead, PlatformLeadStage::QuotationSent);
        }
    }

    public function afterAccepted(Quotation $quotation): void
    {
        $lead = $quotation->platformLead;
        if ($lead === null) {
            return;
        }

        $this->logActivity->handle(
            $lead,
            PlatformLeadActivityType::QuotationAccepted,
            __('Quotation :number accepted', ['number' => '#'.$quotation->number]),
        );

        if ($lead->stage->orderIndex() < PlatformLeadStage::QuotationAccepted->orderIndex()) {
            $this->updateStage->handle($lead, PlatformLeadStage::QuotationAccepted);
        }
    }

    public function afterOnboarded(Quotation $quotation, string $tenantId): void
    {
        $lead = $quotation->platformLead;
        if ($lead === null) {
            return;
        }

        $lead->update(['tenant_id' => $tenantId]);

        $this->logActivity->handle(
            $lead,
            PlatformLeadActivityType::AccountLinked,
            __('Channel Partner account linked'),
            null,
            ['tenant_id' => $tenantId],
        );

        if ($lead->stage->orderIndex() < PlatformLeadStage::Onboarding->orderIndex()) {
            $this->updateStage->handle($lead, PlatformLeadStage::Onboarding);
        }
    }

    public function resolveLeadForQuotation(array $data): ?PlatformLead
    {
        if (isset($data['platform_lead_id'])) {
            return PlatformLead::query()->find($data['platform_lead_id']);
        }

        return null;
    }

    public function prospectFromLead(PlatformLead $lead): array
    {
        return [
            'platform_lead_id' => $lead->id,
            'company_name' => $lead->company_name,
            'owner_name' => $lead->contact_person,
            'email' => $lead->email,
            'phone' => $lead->phone,
        ];
    }

    public function shouldAdvanceOnStatus(QuotationStatus $status): ?PlatformLeadStage
    {
        return match ($status) {
            QuotationStatus::Sent => PlatformLeadStage::QuotationSent,
            QuotationStatus::Accepted => PlatformLeadStage::QuotationAccepted,
            default => null,
        };
    }
}
