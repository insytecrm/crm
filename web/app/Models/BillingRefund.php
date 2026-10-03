<?php

namespace App\Models;

use App\Enums\BillingRefundStatus;
use Database\Factories\BillingRefundFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

#[Fillable([
    'tenant_id',
    'billing_payment_id',
    'amount',
    'reason',
    'status',
    'refunded_at',
])]
class BillingRefund extends Model
{
    /** @use HasFactory<BillingRefundFactory> */
    use CentralConnection, HasFactory;

    protected $attributes = [
        'status' => 'requested',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'status' => BillingRefundStatus::class,
            'refunded_at' => 'datetime',
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
     * @return BelongsTo<BillingPayment, $this>
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(BillingPayment::class, 'billing_payment_id');
    }
}
