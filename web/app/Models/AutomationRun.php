<?php

namespace App\Models;

use App\Enums\AutomationRunStatus;
use App\Enums\AutomationTrigger;
use Database\Factories\AutomationRunFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'automation_id',
    'user_id',
    'lead_id',
    'trigger',
    'status',
    'dry_run',
    'context',
    'result',
])]
class AutomationRun extends Model
{
    /** @use HasFactory<AutomationRunFactory> */
    use HasFactory;

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'trigger' => AutomationTrigger::class,
            'status' => AutomationRunStatus::class,
            'dry_run' => 'boolean',
            'context' => 'array',
            'result' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Automation, $this>
     */
    public function automation(): BelongsTo
    {
        return $this->belongsTo(Automation::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Lead, $this>
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function message(): string
    {
        $message = $this->result['message'] ?? null;

        return is_string($message) ? $message : '';
    }
}
