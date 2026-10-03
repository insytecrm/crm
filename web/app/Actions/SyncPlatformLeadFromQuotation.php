<?php

namespace App\Actions;

use App\Enums\BillingInvoiceStatus;
use App\Enums\PlatformLeadActivityType;
use App\Enums\PlatformLeadStage;
use App\Enums\QuotationStatus;
use App\Models\BillingInvoice;
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

        if ($lead->stage->orderIndex() < PlatformLeadStage::Quoted->orderIndex()) {
            $this->updateStage->handle($lead, PlatformLeadStage::Quoted);
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

        if ($lead->stage->orderIndex() < PlatformLeadStage::Quoted->orderIndex()) {
            $this->updateStage->handle($lead, PlatformLeadStage::Quoted);
        }
    }

    public function afterInvoicePaid(PlatformLead $lead): void
    {
        if ($lead->stage->orderIndex() < PlatformLeadStage::Paid->orderIndex()) {
            $this->updateStage->handle($lead, PlatformLeadStage::Paid);
        }

        $this->logActivity->handle(
            $lead,
            PlatformLeadActivityType::InvoicePaid,
            __('Invoice paid'),
        );

        $this->maybeMoveToRetention($lead->refresh());
    }

    private function maybeMoveToRetention(PlatformLead $lead): void
    {
        $paidCount = BillingInvoice::query()
            ->where('status', BillingInvoiceStatus::Paid)
            ->where(function ($query) use ($lead): void {
                $query->whereHas('quotation', fn ($builder) => $builder->where('platform_lead_id', $lead->id));

                if ($lead->tenant_id !== null && $lead->tenant_id !== '') {
                    $query->orWhere('tenant_id', $lead->tenant_id);
                }
            })
            ->count();

        if ($paidCount < 2 || $lead->stage === PlatformLeadStage::Retention) {
            return;
        }

        $this->updateStage->handle($lead, PlatformLeadStage::Retention);
        $this->logActivity->handle(
            $lead,
            PlatformLeadActivityType::MovedToRetention,
            __('Second invoice paid — moved to retention'),
        );
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
            'rera_number' => $lead->rera_number,
            'gst_number' => $lead->gst_number,
        ];
    }

    public function shouldAdvanceOnStatus(QuotationStatus $status): ?PlatformLeadStage
    {
        return match ($status) {
            QuotationStatus::Sent, QuotationStatus::Accepted => PlatformLeadStage::Quoted,
            default => null,
        };
    }
}
