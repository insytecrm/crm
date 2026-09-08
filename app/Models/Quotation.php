<?php

namespace App\Models;

use App\Enums\BillingCycle;
use App\Enums\QuotationStatus;
use App\Support\Platform\BillingMoney;
use Database\Factories\QuotationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

#[Fillable([
    'number',
    'company_name',
    'owner_name',
    'email',
    'phone',
    'tenant_id',
    'plan_id',
    'billing_cycle',
    'plan_price',
    'discount_amount',
    'tax_amount',
    'total',
    'trial_enabled',
    'trial_days',
    'valid_until',
    'status',
    'sent_at',
    'viewed_at',
    'accepted_at',
    'rejected_at',
    'expired_at',
    'accepted_by_name',
    'partner_subscription_id',
    'onboarded_at',
])]
class Quotation extends Model
{
    /** @use HasFactory<QuotationFactory> */
    use CentralConnection, HasFactory;

    protected $attributes = [
        'discount_amount' => 0,
        'tax_amount' => 0,
        'total' => 0,
        'trial_enabled' => false,
        'status' => 'draft',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'billing_cycle' => BillingCycle::class,
            'plan_price' => 'integer',
            'discount_amount' => 'integer',
            'tax_amount' => 'integer',
            'total' => 'integer',
            'trial_enabled' => 'boolean',
            'trial_days' => 'integer',
            'valid_until' => 'date',
            'status' => QuotationStatus::class,
            'sent_at' => 'datetime',
            'viewed_at' => 'datetime',
            'accepted_at' => 'datetime',
            'rejected_at' => 'datetime',
            'expired_at' => 'datetime',
            'onboarded_at' => 'datetime',
        ];
    }

    public function companyDisplayName(): string
    {
        if (is_string($this->company_name) && $this->company_name !== '') {
            return $this->company_name;
        }

        return $this->tenant?->name ?? __('Unknown company');
    }

    public function isOnboarded(): bool
    {
        return $this->tenant_id !== null && $this->tenant_id !== '' && $this->onboarded_at !== null;
    }

    public function getRouteKeyName(): string
    {
        return 'number';
    }

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * @return BelongsTo<Plan, $this>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * @return BelongsTo<PartnerSubscription, $this>
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(PartnerSubscription::class, 'partner_subscription_id');
    }

    public function amountLabel(): string
    {
        return BillingMoney::format((int) $this->plan_price).($this->billing_cycle?->priceSuffix() ?? '');
    }

    public function totalLabel(): string
    {
        return BillingMoney::format((int) $this->total);
    }

    public function trialLabel(): string
    {
        if (! $this->trial_enabled) {
            return __('No trial');
        }

        return trans_choice(':count day|:count days', (int) $this->trial_days, [
            'count' => (int) $this->trial_days,
        ]);
    }

    public function isDraft(): bool
    {
        return $this->status === QuotationStatus::Draft;
    }

    public function isSent(): bool
    {
        return $this->status === QuotationStatus::Sent;
    }

    public function isAccepted(): bool
    {
        return $this->status === QuotationStatus::Accepted;
    }

    public function isRejected(): bool
    {
        return $this->status === QuotationStatus::Rejected;
    }

    public function isExpired(): bool
    {
        return $this->status === QuotationStatus::Expired;
    }

    public function canEdit(): bool
    {
        return $this->isDraft();
    }

    public function canSend(): bool
    {
        return $this->isDraft() || $this->isSent();
    }

    public function canMarkAccepted(): bool
    {
        return $this->isDraft() || $this->isSent();
    }

    public function canMarkRejected(): bool
    {
        return $this->isDraft() || $this->isSent();
    }

    public function canStartOnboarding(): bool
    {
        return $this->isAccepted()
            && $this->partner_subscription_id === null
            && ($this->tenant_id === null || $this->tenant_id === '');
    }

    public function canCreateSubscription(): bool
    {
        return $this->isAccepted()
            && $this->partner_subscription_id === null
            && $this->tenant_id !== null
            && $this->tenant_id !== '';
    }

    public function canDuplicate(): bool
    {
        return true;
    }

    /**
     * @return list<array{label: string, at: Carbon|null}>
     */
    public function timeline(): array
    {
        $events = [
            ['label' => __('Quotation Created'), 'at' => $this->created_at],
            ['label' => __('Quotation Sent'), 'at' => $this->sent_at],
            ['label' => __('Quotation Viewed'), 'at' => $this->viewed_at],
        ];

        if ($this->isRejected() || $this->rejected_at !== null) {
            $events[] = ['label' => __('Quotation Rejected'), 'at' => $this->rejected_at];
        } elseif ($this->isExpired() || $this->expired_at !== null) {
            $events[] = ['label' => __('Quotation Expired'), 'at' => $this->expired_at];
        } else {
            $events[] = ['label' => __('Quotation Accepted'), 'at' => $this->accepted_at];
        }

        return array_values(array_filter(
            $events,
            fn (array $event): bool => $event['at'] !== null,
        ));
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeDueForExpiry(Builder $query): Builder
    {
        return $query
            ->whereIn('status', [QuotationStatus::Draft, QuotationStatus::Sent])
            ->whereDate('valid_until', '<', now()->toDateString());
    }

    public static function nextNumber(): string
    {
        $latest = static::query()->orderByDesc('id')->value('number');
        $sequence = 1001;

        if (is_string($latest) && preg_match('/^QT-(\d+)$/', $latest, $matches) === 1) {
            $sequence = ((int) $matches[1]) + 1;
        }

        return 'QT-'.$sequence;
    }
}
