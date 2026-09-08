<?php

namespace App\Models;

use App\Enums\FacebookPageConnectionStatus;
use Database\Factories\FacebookPageConnectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'created_by_id',
    'page_id',
    'page_name',
    'status',
    'page_access_token',
    'campaigns',
    'lead_forms',
    'selected_form_ids',
    'form_fields',
    'field_map',
    'total_synced',
    'total_skipped',
    'total_failed',
    'verified_at',
    'connected_at',
    'last_lead_at',
    'last_error',
])]
#[Hidden(['page_access_token'])]
class FacebookPageConnection extends Model
{
    /** @use HasFactory<FacebookPageConnectionFactory> */
    use HasFactory;

    protected $attributes = [
        'status' => 'draft',
        'total_synced' => 0,
        'total_skipped' => 0,
        'total_failed' => 0,
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'status' => FacebookPageConnectionStatus::class,
            'page_access_token' => 'encrypted',
            'campaigns' => 'array',
            'lead_forms' => 'array',
            'selected_form_ids' => 'array',
            'form_fields' => 'array',
            'field_map' => 'array',
            'total_synced' => 'integer',
            'total_skipped' => 'integer',
            'total_failed' => 'integer',
            'verified_at' => 'datetime',
            'connected_at' => 'datetime',
            'last_lead_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeConnected(Builder $query): Builder
    {
        return $query->where('status', FacebookPageConnectionStatus::Connected);
    }

    public function isConnected(): bool
    {
        return $this->status === FacebookPageConnectionStatus::Connected;
    }

    public function isPaused(): bool
    {
        return $this->status === FacebookPageConnectionStatus::Paused;
    }

    public function isVerified(): bool
    {
        return in_array($this->status, [
            FacebookPageConnectionStatus::Verified,
            FacebookPageConnectionStatus::Connected,
            FacebookPageConnectionStatus::Paused,
        ], true);
    }

    public function acceptsForm(string $formId): bool
    {
        $selected = $this->selected_form_ids ?? [];

        if ($selected === []) {
            return false;
        }

        return in_array($formId, $selected, true);
    }

    public function resolvedAccessToken(): ?string
    {
        if (is_string($this->page_access_token) && $this->page_access_token !== '') {
            return $this->page_access_token;
        }

        $configured = config('services.meta.page_access_token');

        return is_string($configured) && $configured !== '' ? $configured : null;
    }
}
