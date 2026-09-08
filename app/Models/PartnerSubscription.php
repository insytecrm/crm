<?php

namespace App\Models;

use App\Enums\BillingCycle;
use App\Enums\SubscriptionStatus;
use Database\Factories\PartnerSubscriptionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

#[Fillable([
    'tenant_id',
    'plan_id',
    'billing_cycle',
    'amount',
    'currency',
    'status',
    'started_at',
    'trial_ends_at',
    'next_billing_at',
    'paused_at',
    'cancelled_at',
])]
class PartnerSubscription extends Model
{
    /** @use HasFactory<PartnerSubscriptionFactory> */
    use CentralConnection, HasFactory;

    protected $attributes = [
        'currency' => 'INR',
        'status' => 'active',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'billing_cycle' => BillingCycle::class,
            'amount' => 'integer',
            'status' => SubscriptionStatus::class,
            'started_at' => 'datetime',
            'trial_ends_at' => 'datetime',
            'next_billing_at' => 'datetime',
            'paused_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
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
     * @return HasMany<BillingInvoice, $this>
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(BillingInvoice::class);
    }

    /**
     * @return HasMany<BillingPayment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(BillingPayment::class);
    }

    /**
     * Subscriptions that are currently billable / in force for a channel partner.
     *
     * @return list<SubscriptionStatus>
     */
    public static function activeStatuses(): array
    {
        return [
            SubscriptionStatus::Active,
            SubscriptionStatus::Trial,
            SubscriptionStatus::PastDue,
        ];
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', self::activeStatuses());
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeExpiringSoon(Builder $query, ?Carbon $within = null): Builder
    {
        $within ??= now()->addDays(7);

        return $query
            ->active()
            ->whereNotNull('next_billing_at')
            ->where('next_billing_at', '<=', $within)
            ->where('next_billing_at', '>=', now());
    }

    public function isExpiringSoon(?Carbon $within = null): bool
    {
        $within ??= now()->addDays(7);

        if ($this->next_billing_at === null) {
            return false;
        }

        return $this->next_billing_at->greaterThanOrEqualTo(now())
            && $this->next_billing_at->lessThanOrEqualTo($within)
            && in_array($this->status, self::activeStatuses(), true);
    }
}
