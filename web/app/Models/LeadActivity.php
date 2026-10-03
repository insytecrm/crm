<?php

namespace App\Models;

use App\Enums\LeadActivityType;
use Database\Factories\LeadActivityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'lead_id',
    'user_id',
    'type',
    'description',
    'metadata',
])]
class LeadActivity extends Model
{
    /** @use HasFactory<LeadActivityFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'type' => LeadActivityType::class,
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Lead, $this>
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function timelineHoverDetail(?LeadNote $note = null): ?string
    {
        if ($this->type === LeadActivityType::NoteAdded) {
            $body = $note?->body ?? ($this->metadata['body'] ?? null);

            return filled($body) ? $body : null;
        }

        if (filled($this->description) && $this->description !== $this->type->label()) {
            return $this->description;
        }

        return $this->user?->name
            ? __('By :name', ['name' => $this->user->name])
            : null;
    }

    public function hasTimelineHoverDetail(?LeadNote $note = null): bool
    {
        return filled($this->timelineHoverDetail($note));
    }
}
