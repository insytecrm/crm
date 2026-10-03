<?php

namespace App\Models;

use App\Enums\PropertyPortal;
use Database\Factories\PortalWebhookEndpointFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class PortalWebhookEndpoint extends Model
{
    /** @use HasFactory<PortalWebhookEndpointFactory> */
    use CentralConnection, HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'portal',
        'webhook_id',
        'secret_hash',
        'secret_encrypted',
        'secret_last_four',
        'is_active',
        'generated_at',
        'last_used_at',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'secret_hash',
        'secret_encrypted',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'portal' => PropertyPortal::class,
            'secret_encrypted' => 'encrypted',
            'is_active' => 'boolean',
            'generated_at' => 'datetime',
            'last_used_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plainTextSecret(): ?string
    {
        return $this->secret_encrypted;
    }

    public function webhookUrl(): string
    {
        return route('api.webhooks.portal.receive', [
            'portal' => $this->portal->value,
            'webhookId' => $this->webhook_id,
        ]);
    }
}
